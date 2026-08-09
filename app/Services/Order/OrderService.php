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
use App\Services\Wallet\WalletService;
use Illuminate\Http\Response;
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
    ): Order
    {
        $order = DB::transaction(function () use (
            $userId,
            $items,
            $notes,
            $shippingAddressId,
        ) {
            /*
             * دریافت آدرس متعلق به خود کاربر
             */
            $shippingAddress = UserAddress::query()
                ->whereKey($shippingAddressId)
                ->where('user_id', $userId)
                ->firstOrFail();

            /*
             * دریافت و Lock کردن Variationها
             * برای جلوگیری از Overselling
             */
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

            /*
             * ایجاد Order
             *
             * اطلاعات آدرس به‌صورت Snapshot ذخیره می‌شوند.
             */
            $order = Order::query()->create([
                'user_id' => $userId,
                'order_number' => $this->generateOrderNumber(),

                'total_amount' => 0,
                'discount_amount' => 0,

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

                $quantity = (int)$item['quantity'];

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

                /*
                 * هر Business در یک Order فقط یک OrderVendor دارد.
                 */
                if (!isset($vendors[$businessId])) {
                    $vendors[$businessId] = $order->vendors()->create([
                        'business_id' => $businessId,

                        'subtotal_amount' => 0,
                        'discount_amount' => 0,
                        'total_amount' => 0,

                        'status' => OrderVendorStatuses::PENDING->value,
                    ]);
                }

                /** @var OrderVendor $vendor */
                $vendor = $vendors[$businessId];

                $unitPrice = (int)$variation->price;

                $discountPrice = $variation->discount_price !== null
                    ? (int)$variation->discount_price
                    : null;

                $effectivePrice = $discountPrice ?? $unitPrice;

                $lineSubtotal = $unitPrice * $quantity;

                $lineDiscount = $discountPrice !== null
                    ? ($unitPrice - $discountPrice) * $quantity
                    : 0;

                $lineTotal = $effectivePrice * $quantity;

                /*
                 * Snapshot اطلاعات محصول در زمان خرید
                 */
                $vendor->items()->create([
                    'order_id' => $order->id,
                    'product_id' => $variation->product_id,
                    'product_variation_id' => $variation->id,

                    'quantity' => $quantity,

                    'unit_price' => $unitPrice,
                    'discount_price' => $discountPrice,
                    'total_price' => $lineTotal,
                ]);

                /*
                 * subtotal = قیمت قبل از تخفیف
                 */
                $vendor->increment(
                    'subtotal_amount',
                    $lineSubtotal
                );

                /*
                 * discount = مقدار تخفیف
                 */
                if ($lineDiscount > 0) {
                    $vendor->increment(
                        'discount_amount',
                        $lineDiscount
                    );
                }

                /*
                 * total = مبلغ نهایی Vendor
                 */
                $vendor->increment(
                    'total_amount',
                    $lineTotal
                );

                /*
                 * رزرو موجودی
                 */
                $variation->decrement(
                    'stock',
                    $quantity
                );
            }

            /*
             * محاسبه Total نهایی Order
             */
            $order->update([
                'total_amount' => $order->vendors()->sum('total_amount'),

                'discount_amount' => $order->vendors()->sum(
                    'discount_amount'
                ),
            ]);

            return $order;
        });

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
            ], true)) {
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
                    $payment->payment_status !==
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
                            from: $vendor->business->getWallet(),
                            to: $order->user->getWallet(),
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
                    $vendor->status !==
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
