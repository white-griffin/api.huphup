<?php

namespace App\Services\Order;

use App\Enums\OrderStatuses;
use App\Enums\OrderVendorStatuses;
use App\Enums\PaymentStatuses;
use App\Enums\WalletTransactionType;
use App\Helpers\Api\ApiResponse;
use App\Jobs\OrderExpiredJob;
use App\Models\Order;
use App\Models\OrderVendor;
use App\Models\ProductVariation;
use App\Models\UserAddress;
use App\Services\Discount\DiscountService;
use App\Services\Logistics\ShippingCostService;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Response;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * $items: [['product_variation_id' => int, 'quantity' => int], ...]
     */
    public function create(
        int     $userId,
        array   $items,
        int     $shippingAddressId,
        ?string $notes = null,
    ): Order {
        $order = DB::transaction(function () use (
            $userId,
            $items,
            $notes,
            $shippingAddressId,
        ) {
            $shippingAddress = UserAddress::query()
                ->whereKey($shippingAddressId)
                ->where('user_id', $userId)
                ->firstOrFail();

            $variationIds = collect($items)
                ->pluck('product_variation_id')
                ->unique()
                ->sort()
                ->values();

            $variations = ProductVariation::query()
                ->whereIn('id', $variationIds)
                ->lockForUpdate()
                ->with('product')
                ->get()
                ->keyBy('id');

            if ($variations->count() !== $variationIds->count()) {
                throw new \DomainException(
                    'یکی از تنوع‌های درخواستی در دسترس نیست.'
                );
            }

            $order = Order::query()->create([
                'user_id' => $userId,
                'order_number' => $this->generateOrderNumber(),

                'subtotal_amount' => 0,
                'shipping_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,

                'order_status' => OrderStatuses::PENDING->value,
                'payment_status' => PaymentStatuses::UNPAID->value,

                'shipping_address' => $shippingAddress->address,
                'shipping_postal_code' => $shippingAddress->postal_code,
                'shipping_latitude' => $shippingAddress->latitude,
                'shipping_longitude' => $shippingAddress->longitude,

                'notes' => $notes,
            ]);

            $vendors = [];

            foreach ($items as $item) {
                $variation = $variations->get(
                    $item['product_variation_id']
                );

                $quantity = (int) $item['quantity'];

                if ($quantity <= 0) {
                    throw new \DomainException(
                        'تعداد محصول باید بیشتر از صفر باشد.'
                    );
                }

                if ($variation->stock < $quantity) {
                    throw new \DomainException(
                        'موجودی محصول برای تعداد درخواستی کافی نیست.'
                    );
                }

                $businessId = $variation->product->business_id;

                if (! isset($vendors[$businessId])) {
                    $vendors[$businessId] = $order->vendors()->create([
                        'business_id' => $businessId,

                        'subtotal_amount' => 0,
                        'discount_amount' => 0,
                        'shipping_amount' => 0,
                        'total_amount' => 0,

                        'status' => OrderVendorStatuses::PENDING->value,
                    ]);
                }

                /** @var OrderVendor $vendor */
                $vendor = $vendors[$businessId];

                $unitPrice = (int) $variation->price;

                $discountPrice = $variation->discount_price !== null
                    ? (int) $variation->discount_price
                    : null;

                $effectivePrice = $discountPrice ?? $unitPrice;

                $lineSubtotal = $unitPrice * $quantity;

                $lineDiscount = $discountPrice !== null
                    ? ($unitPrice - $discountPrice) * $quantity
                    : 0;

                $lineTotal = $effectivePrice * $quantity;

                $vendor->items()->create([
                    'order_id' => $order->id,
                    'product_id' => $variation->product_id,
                    'product_variation_id' => $variation->id,

                    'quantity' => $quantity,

                    'unit_price' => $unitPrice,
                    'discount_price' => $discountPrice,
                    'total_price' => $lineTotal,
                ]);

                $vendor->increment(
                    'subtotal_amount',
                    $lineSubtotal
                );

                if ($lineDiscount > 0) {
                    $vendor->increment(
                        'discount_amount',
                        $lineDiscount
                    );
                }

                $vendor->increment(
                    'total_amount',
                    $lineTotal
                );

                $variation->decrement(
                    'stock',
                    $quantity
                );
            }

            $order->load('vendors.business', 'vendors.items');

            $totalSubtotal = 0;
            $totalDiscount = 0;
            $totalShipping = 0;

            foreach ($order->vendors as $vendor) {
                $shippingAmount = app(ShippingCostService::class)->calculate(
                    business: $vendor->business,
                    items: $vendor->items->all(),
                    order: $order,
                );

                $vendorTotal =
                    (int) $vendor->subtotal_amount
                    - (int) $vendor->discount_amount
                    + $shippingAmount;

                $vendor->update([
                    'shipping_amount' => $shippingAmount,
                    'total_amount' => $vendorTotal,
                ]);

                $totalSubtotal += (int) $vendor->subtotal_amount;
                $totalDiscount += (int) $vendor->discount_amount;
                $totalShipping += $shippingAmount;
            }

            $order->update([
                'subtotal_amount' => $totalSubtotal,
                'discount_amount' => $totalDiscount,
                'shipping_amount' => $totalShipping,

                'total_amount' =>
                    $totalSubtotal
                    - $totalDiscount
                    + $totalShipping,
            ]);

            return $order;
        });

        if (Env::get('APP_ENV') == 'production') {
            OrderExpiredJob::dispatch($order->id)
                ->delay(now()->addMinutes(5))
                ->afterCommit();
        }

        return $order->load([
            'vendors.items.product',
            'vendors.items.variation',
            'vendors.business',
        ]);
    }

    public function cancel(Order $order): Order
    {
        return DB::transaction(function () use ($order) {

            $order = Order::query()
                ->lockForUpdate()
                ->with([
                    'items',
                    'vendors.business',
                    'vendors.shipments',
                    'payments' => fn ($query) => $query
                        ->where(
                            'payment_status',
                            PaymentStatuses::PAID->value
                        )
                        ->latest('id'),
                ])
                ->findOrFail($order->id);

            if (! in_array($order->order_status, [
                OrderStatuses::PENDING->value,
                OrderStatuses::PAID->value,
            ], false)) {
                throw new \DomainException(
                    'این سفارش قابل لغو نیست.'
                );
            }

            foreach ($order->vendors as $vendor) {
                if ($vendor->shipments->isNotEmpty()) {
                    throw new \DomainException(
                        'برای یکی از فروشندگان این سفارش درخواست ارسال ثبت شده و امکان لغو وجود ندارد.'
                    );
                }
            }

            $payment = $order->payments->first();

            if ($payment) {

                if (
                    $payment->payment_status !=
                    PaymentStatuses::PAID->value
                ) {
                    throw new \DomainException(
                        'وضعیت پرداخت سفارش معتبر نیست.'
                    );
                }

                foreach ($order->vendors as $vendor) {

                    if (
                        $vendor->status !=
                        OrderVendorStatuses::PAID->value
                    ) {
                        continue;
                    }

                    $refundAmount = (int) $vendor->paid_amount;

                    if ($refundAmount > 0) {
                        app(WalletService::class)->refundPending(
                            from: $vendor->business()->getWallet(),
                            to: $order->user()->getWallet(),
                            amount: $refundAmount,
                            debitType: WalletTransactionType::REFUND,
                            creditType: WalletTransactionType::REFUND,
                            payment: $payment,
                            description: "بازگشت وجه سفارش #{$order->id}",
                        );
                    }

                    $vendor->update([
                        'status' =>
                            OrderVendorStatuses::CANCELED->value,
                    ]);
                }

                $payment->update([
                    'payment_status' =>
                        PaymentStatuses::REFUNDED->value,
                ]);

                if ($payment->coupon_id) {
                    app(DiscountService::class)
                        ->releaseUsage($payment);
                }
            }

            foreach ($order->vendors as $vendor) {
                if (
                    $vendor->status !=
                    OrderVendorStatuses::CANCELED->value
                ) {
                    $vendor->update([
                        'status' =>
                            OrderVendorStatuses::CANCELED->value,
                    ]);
                }
            }

            foreach ($order->items as $item) {
                ProductVariation::query()
                    ->whereKey($item->product_variation_id)
                    ->increment(
                        'stock',
                        $item->quantity
                    );
            }

            $order->update([
                'order_status' =>
                    OrderStatuses::CANCELED->value,

                'payment_status' => $payment
                    ? PaymentStatuses::REFUNDED->value
                    : PaymentStatuses::CANCELLED->value,
            ]);

            return $order->fresh([
                'items',
                'vendors.business',
                'vendors.shipments',
                'payments',
            ]);
        });
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . Str::upper(
            Str::random(6)
        );
    }
}

