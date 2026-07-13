<?php

namespace App\Services\Order;

use App\Enums\OrderStatuses;
use App\Enums\PaymentStatuses;
use App\Helpers\Api\ApiResponse;
use App\Jobs\OrderExpiredJob;
use App\Models\Order;
use App\Models\ProductVariation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * $items: [['product_variation_id' => int, 'quantity' => int], ...]
     */
    public function create(int $userId, array $items, ?string $notes = null): Order
    {
        $order = DB::transaction(function () use ($userId, $items, $notes) {

            $variationIds = collect($items)->pluck('product_variation_id')->sort()->values();

            $variations = ProductVariation::query()
                ->whereIn('id', $variationIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $firstVariation = $variations->first();
            $businessId = $firstVariation->product->business_id;

            $totalAmount = 0;
            $orderItemsData = [];

            foreach ($items as $item) {
                $variation = $variations->get($item['product_variation_id']);

                if (!$variation) {
                    return ApiResponse::Fail(Response::HTTP_UNPROCESSABLE_ENTITY, 'تنوع درخواستی در دسترس نیست');
                }

                if ($variation->stock < $item['quantity']) {
                    return ApiResponse::Fail(Response::HTTP_CONFLICT, 'مقداری درخواستی از موجودی بیشتر است');
                }

                $unitPrice = $variation->price;
                $discountPrice = $variation->discount_price ?? $unitPrice;
                $lineTotal = $discountPrice * $item['quantity'];

                $totalAmount += $lineTotal;

                $orderItemsData[] = [
                    'product_id' => $variation->product_id,
                    'product_variation_id' => $variation->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'discount_price' => $discountPrice,
                    'total_price' => $lineTotal,
                ];

                $variation->decrement('stock', $item['quantity']);
            }

            $order = Order::query()
                ->create([
                    'business_id' => $businessId,
                    'user_id' => $userId,
                    'order_number' => $this->generateOrderNumber(),
                    'total_amount' => $totalAmount,
                    'discount_amount' => 0,
                    'order_status' => OrderStatuses::PENDING->value,
                    'payment_status' => PaymentStatuses::UNPAID->value,
                    'notes' => $notes,
                ]);

            $order->items()->createMany($orderItemsData);

            return $order;
        });

//        OrderExpiredJob::dispatch($order->id)
//            ->delay(now()->addMinutes(15))
//            ->afterCommit();

        return $order->load('items');
    }


    private function generateOrderNumber(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
    }
}
