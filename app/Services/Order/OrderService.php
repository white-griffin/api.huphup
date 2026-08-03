<?php

namespace App\Services\Order;

use App\Enums\OrderStatuses;
use App\Enums\OrderVendorStatuses;
use App\Enums\PaymentStatuses;
use App\Enums\WalletTransactionType;
use App\Helpers\Api\ApiResponse;
use App\Jobs\OrderExpiredJob;
use App\Models\Order;
use App\Models\ProductVariation;
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
        int $userId,
        array $items,
        ?string $notes = null
    ): Order {
        $order = DB::transaction(function () use (
            $userId,
            $items,
            $notes
        ) {
            $variationIds = collect($items)
                ->pluck('product_variation_id')
                ->sort()
                ->values();

            $variations = ProductVariation::query()
                ->whereIn('id', $variationIds)
                ->lockForUpdate()
                ->with('product')
                ->get()
                ->keyBy('id');

            if ($variations->count() !== $variationIds->unique()->count()) {
                throw new \DomainException(
                    'یکی از تنوع‌های درخواستی در دسترس نیست.'
                );
            }

            $order = Order::query()->create([
                'user_id' => $userId,
                'order_number' => $this->generateOrderNumber(),
                'total_amount' => 0,
                'discount_amount' => 0,
                'order_status' => OrderStatuses::PENDING->value,
                'payment_status' => PaymentStatuses::UNPAID->value,
                'notes' => $notes,
            ]);

            $vendors = [];

            foreach ($items as $item) {
                $variation = $variations->get(
                    $item['product_variation_id']
                );

                if ($variation->stock < $item['quantity']) {
                    throw new \DomainException(
                        'مقداری درخواستی از موجودی بیشتر است.'
                    );
                }

                $businessId = $variation->product->business_id;

                if (! isset($vendors[$businessId])) {
                    $vendors[$businessId] = $order->vendors()->create([
                        'business_id' => $businessId,
                        'subtotal_amount' => 0,
                        'discount_amount' => 0,
                        'total_amount' => 0,
                        'status' => OrderVendorStatuses::PENDING->value,
                    ]);
                }

                $vendor = $vendors[$businessId];

                $unitPrice = (int) $variation->price;

                $discountPrice = $variation->discount_price !== null
                    ? (int) $variation->discount_price
                    : $unitPrice;

                $lineTotal = $discountPrice * $item['quantity'];

                $vendor->items()->create([
                    'order_id' => $order->id,
                    'product_id' => $variation->product_id,
                    'product_variation_id' => $variation->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'discount_price' => $variation->discount_price,
                    'total_price' => $lineTotal,
                ]);

                $vendor->increment(
                    'subtotal_amount',
                    $unitPrice * $item['quantity']
                );

                $vendor->increment(
                    'total_amount',
                    $lineTotal
                );

                if ($variation->discount_price !== null) {
                    $vendor->increment(
                        'discount_amount',
                        ($unitPrice - $discountPrice) * $item['quantity']
                    );
                }

                $variation->decrement(
                    'stock',
                    $item['quantity']
                );
            }

            $order->update([
                'total_amount' => $order->vendors()->sum('total_amount'),
                'discount_amount' => $order->vendors()->sum('discount_amount'),
            ]);

            return $order;
        });

        return $order->load([
            'vendors.items',
            'vendors.business',
        ]);
    }

    public function cancel(Order $order): Order
    {
        return DB::transaction(function () use ($order) {

            $order = Order::query()
                ->lockForUpdate()
                ->with([
                    'vendors.shipments',
                    'vendors.payments' => fn ($query) => $query
                        ->where(
                            'payment_status',
                            PaymentStatuses::PAID->value
                        )
                        ->latest(),
                    'items',
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
                if ($vendor->shipments->contains(
                    fn (Shipment $shipment) =>
                    ! in_array($shipment->status, [
                        ShipmentStatuses::CANCELLED,
                        ShipmentStatuses::DELIVERED,
                    ], true)
                )) {
                    throw new \DomainException(
                        'برای یکی از فروشندگان درخواست ارسال ثبت شده و امکان لغو وجود ندارد.'
                    );
                }
            }

            if ($order->order_status === OrderStatuses::PAID->value) {
                foreach ($order->vendors as $vendor) {
                    $payment = $vendor->payments->first();

                    if (! $payment) {
                        throw new \DomainException(
                            "پرداخت موفقی برای فروشنده سفارش #{$vendor->id} پیدا نشد."
                        );
                    }

                    if ($payment->payment_status !== PaymentStatuses::PAID->value) {
                        throw new \DomainException(
                            'وضعیت پرداخت سفارش معتبر نیست.'
                        );
                    }

                    $businessWallet = $vendor->business->getWallet();
                    $userWallet = $order->user->getWallet();

                    app(WalletService::class)->refundPending(
                        from: $businessWallet,
                        to: $userWallet,
                        amount: $payment->amount,
                        debitType: WalletTransactionType::REFUND,
                        creditType: WalletTransactionType::REFUND,
                        payment: $payment,
                        description: "بازگشت وجه سفارش #{$order->id}",
                    );

                    $payment->update([
                        'payment_status' => PaymentStatuses::REFUNDED->value,
                    ]);

                    if ($payment->coupon_id) {
                        app(DiscountService::class)
                            ->releaseUsage($payment);
                    }
                }
            }

            foreach ($order->items as $item) {
                ProductVariation::query()
                    ->whereKey($item->product_variation_id)
                    ->increment('stock', $item->quantity);
            }

            $order->update([
                'order_status' => OrderStatuses::CANCELED->value,
            ]);

            return $order->fresh([
                'items',
                'vendors.business',
                'vendors.payments',
                'vendors.shipments',
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
