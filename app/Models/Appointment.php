<?php

namespace App\Models;

use App\Contracts\CouponEligible;
use App\Contracts\HandlesPayment;
use App\Contracts\PayableEntity;
use App\Contracts\Reviewable;
use App\Contracts\ReviewSource;
use App\Enums\AppointmentStatuses;
use App\Models\Traits\BelongsToBusiness;
use App\Services\Appointment\AppointmentPaymentHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Appointment extends Model implements PayableEntity, HandlesPayment,CouponEligible,ReviewSource
{
    use BelongsToBusiness;

    protected $guarded = ['id'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }
    public function businessService(): BelongsTo
    {
        return $this->belongsTo(BusinessService::class);
    }

    public function review(): MorphOne
    {
        return $this->morphOne(Review::class, 'source');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentSucceeded(Payment $payment): void
    {
        app(AppointmentPaymentHandler::class)
            ->succeeded($this, $payment);
    }

    public function paymentFailed(Payment $payment): void
    {
        app(AppointmentPaymentHandler::class)
            ->failed($this, $payment);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function getPayableAmount(): int
    {
        return (int) $this->service_price;
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
        return true;
    }

    public function couponRestrictionMessage(): string
    {
        return '';
    }

    public function getReviewable(): Reviewable
    {
        return $this->businessService;
    }

    public function getReviewAuthor(): User
    {
        return $this->user;
    }

    public function canCreateReview(): bool
    {
        return $this->status === AppointmentStatuses::COMPLETED->value;
    }
}
