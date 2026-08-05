<?php

namespace App\Models;

use App\Contracts\CouponEligible;
use App\Contracts\HandlesPayment;
use App\Contracts\PayableEntity;
use App\Enums\OrderVendorStatuses;
use App\Services\Order\OrderPaymentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OrderVendor extends Model implements CouponEligible
{
    protected $guarded = ['id'];

    protected $casts = [
        'subtotal_amount' => 'integer',
        'discount_amount' => 'integer',
        'total_amount' => 'integer',
        'paid_amount' => 'integer',
        'status' => OrderVendorStatuses::class,

    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }


    public function canUseCoupon(): bool
    {
        return true;
    }

    public function couponRestrictionMessage(): string
    {
        return '';
    }

    public function getReceiverWallet(): Wallet
    {
        return $this->business->getWallet();
    }

}
