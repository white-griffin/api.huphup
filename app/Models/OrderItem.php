<?php

namespace App\Models;

use App\Contracts\Reviewable;
use App\Contracts\ReviewSource;
use App\Enums\OrderStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class OrderItem extends Model implements ReviewSource
{
    protected $guarded = ['id'];


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(OrderVendor::class, 'order_vendor_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function getReviewable(): Reviewable
    {
        return $this->product;
    }

    public function getReviewAuthor(): User
    {
        return $this->order->user;
    }

    public function canCreateReview(): bool
    {
        return $this->order->order_status === OrderStatuses::COMPLETED->value;
    }

    public function review(): MorphOne
    {
        return $this->morphOne(Review::class, 'source');
    }
}
