<?php

namespace App\Models;

use App\Contracts\CouponEligible;
use App\Contracts\HandlesPayment;
use App\Contracts\PayableEntity;
use App\Enums\OrderStatuses;
use App\Enums\PaymentStatuses;
use App\Models\Traits\BelongsToBusiness;
use App\Services\Order\OrderPaymentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

class Order extends Model implements PayableEntity,HandlesPayment,CouponEligible
{
    use BelongsToBusiness;

    protected $guarded = ['id'];


    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function paymentSucceeded(Payment $payment): void
    {
        app(OrderPaymentService::class)
            ->succeeded($this);
    }

    public function paymentFailed(Payment $payment): void
    {
        app(OrderPaymentService::class)
            ->failed($this);
    }

    public function getPayableAmount(): int
    {
        return (int) $this->total_amount;
    }

    public function getPayableUser(): User
    {
        return $this->user;
    }
    public function getPayableUserId(): int
    {
        return $this->user_id;
    }

    public function getReceiverWallet(): Wallet
    {
        return $this->business->getWallet();
    }


    public function canUseCoupon(): bool
    {
        return $this->discount_amount == 0;
    }

    public function couponRestrictionMessage(): string
    {
        return 'برای سفارش‌های دارای محصول تخفیف‌دار امکان استفاده از کد تخفیف وجود ندارد.';
    }
}
