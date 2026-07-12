<?php

namespace App\Models;

use App\Contracts\HandlesPayment;
use App\Enums\OrderStatuses;
use App\Enums\PaymentStatuses;
use App\Models\Traits\BelongsToBusiness;
use App\Services\Order\OrderPaymentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

class Order extends Model implements HandlesPayment
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
            ->succeeded($this, $payment);
    }

    public function paymentFailed(Payment $payment): void
    {
        app(OrderPaymentService::class)
            ->failed($this, $payment);
    }
}
