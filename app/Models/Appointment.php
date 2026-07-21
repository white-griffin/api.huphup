<?php

namespace App\Models;

use App\Contracts\HandlesPayment;
use App\Contracts\PayableEntity;
use App\Models\Traits\BelongsToBusiness;
use App\Services\Appointment\AppointmentPaymentHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Appointment extends Model implements PayableEntity, HandlesPayment
{
    use BelongsToBusiness;

    protected $guarded = ['id'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'date'       => 'date',
    ];


    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
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

    public function getPayableUserId(): int
    {
        return $this->user_id;
    }

    public function getReceiverWallet(): Wallet
    {
        return $this->business->getWallet();
    }
}
