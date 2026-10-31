<?php

namespace App\Models;

use App\Contracts\HandlesPayment;
use App\Contracts\PayableEntity;
use App\Enums\WalletChargeStatus;
use App\Enums\WalletTransactionType;
use App\Services\Wallet\WalletService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class WalletCharge extends Model implements PayableEntity, HandlesPayment
{
    protected $fillable = [
        'user_id',
        'amount',
        'status',
    ];



    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPayableAmount(): int
    {
        return $this->amount;
    }

    public function getPayableUser(): User
    {
        return $this->user;
    }

    public function getPayableUserId(): int
    {
        return $this->user_id;
    }

    public function paymentSucceeded(Payment $payment): void
    {
        app(WalletService::class)->creditAvailable(
            wallet: $this->user->getWallet(),
            amount: (int) $payment->amount,
            type: WalletTransactionType::DEPOSIT,
            payment: $payment,
            description: "افزایش موجودی کیف پول - شارژ #{$this->id}",
        );

        $this->update([
            'status' => WalletChargeStatus::PAID->value,
        ]);
    }

    public function paymentFailed(Payment $payment): void
    {
        $this->update([
            'status' => WalletChargeStatus::FAILED->value,
        ]);
    }

}
