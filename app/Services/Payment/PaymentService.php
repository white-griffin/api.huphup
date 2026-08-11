<?php

namespace App\Services\Payment;

use App\Enums\PaymentGateways;
use App\Enums\PaymentStatuses;
use App\Enums\WalletTransactionType;
use App\Exceptions\PaymentGatewayException;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Payment;
use App\Contracts\CouponEligible;
use App\Contracts\HandlesPayment;
use App\Contracts\PayableEntity;
use App\Services\Discount\DiscountService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly DiscountService $discountService,
        private readonly WalletService $walletService,
    ) {
    }

    public function pay(
        PayableEntity $payable,
        PaymentGateways $gateway,
        ?string $couponCode = null,
    ): array {
        $payment = DB::transaction(function () use (
            $payable,
            $gateway,
            $couponCode
        ) {
            $payable = $payable::query()
                ->lockForUpdate()
                ->findOrFail($payable->getKey());

            $this->ensurePayableCanBePaid($payable);

            $baseAmount = $payable->getPayableAmount();

            $coupon = null;
            $couponDiscount = 0;

            if ($couponCode) {

                if (
                    $payable instanceof CouponEligible
                    && ! $payable->canUseCoupon()
                ) {

                    throw ValidationException::withMessages([
                        'coupon_code' => $payable->couponRestrictionMessage(),
                    ]);
                }


                $result = $this->discountService->validate(
                    code: $couponCode,
                    user: $payable->getPayableUser(),
                    amount: $baseAmount,
                );

                if (! $result->valid) {
                    throw ValidationException::withMessages([
                        'coupon_code' => $result->message,
                    ]);
                }

                $coupon = $result->coupon;
                $couponDiscount = $result->discountAmount;
            }

            $finalAmount = max(0, $baseAmount - $couponDiscount);

            return $this->createForPayable(
                payable: $payable,
                gateway: $gateway->value,
                coupon: $coupon,
                couponDiscountAmount: $couponDiscount,
                originalAmount: $baseAmount,
                finalAmount: $finalAmount,
            );
        });

        if ($payment->payment_status != PaymentStatuses::UNPAID->value) {
            throw new PaymentGatewayException(
                'این پرداخت قبلاً پردازش شده است.'
            );
        }

        if ($gateway == PaymentGateways::WALLET) {
            return $this->payByWallet($payment);
        }

        return $this->payByGateway($payment);
    }

    public function createForPayable(
        PayableEntity $payable,
        string $gateway,
        ?Coupon $coupon = null,
        int $couponDiscountAmount = 0,
        ?int $originalAmount = null,
        ?int $finalAmount = null,
    ): Payment {
        $payable = $payable::query()
            ->lockForUpdate()
            ->findOrFail($payable->getKey());

        $paidPayment = $payable->payments()
            ->where(
                'payment_status',
                PaymentStatuses::PAID->value
            )
            ->exists();

        if ($paidPayment) {
            throw new PaymentGatewayException(
                'این مورد قبلاً پرداخت شده است.'
            );
        }

        $activePayment = $payable->payments()
            ->whereIn('payment_status', [
                PaymentStatuses::UNPAID->value,
                PaymentStatuses::PROCESSING->value,
            ])
            ->latest('id')
            ->first();

        if ($activePayment) {
            if (
                $activePayment->payment_status ===
                PaymentStatuses::PROCESSING->value
            ) {
                return $activePayment;
            }

            $activePayment->update([
                'gateway' => $gateway,
                'coupon_id' => $coupon?->id,
                'original_amount' =>
                    $originalAmount ?? $payable->getPayableAmount(),
                'coupon_discount_amount' => $couponDiscountAmount,
                'amount' =>
                    $finalAmount ?? $payable->getPayableAmount(),
            ]);

            return $activePayment->fresh();
        }

        $amount = $finalAmount ?? $payable->getPayableAmount();

        return Payment::create([
            'user_id' => $payable->getPayableUserId(),

            'payable_type' => $payable::class,
            'payable_id' => $payable->getKey(),

            'original_amount' =>
                $originalAmount ?? $payable->getPayableAmount(),

            'coupon_discount_amount' => $couponDiscountAmount,

            'amount' => $amount,

            'coupon_id' => $coupon?->id,

            'gateway' => $gateway,

            'payment_status' => PaymentStatuses::UNPAID->value,
        ]);
    }

    private function payByGateway(Payment $payment): array
    {
        $payment = Payment::query()
            ->lockForUpdate()
            ->findOrFail($payment->id);

        if ($payment->payment_status != PaymentStatuses::UNPAID->value) {
            throw new PaymentGatewayException(
                'این پرداخت قبلاً پردازش شده است.'
            );
        }

        $gateway = GatewayFactory::make($payment->gateway);

        $result = $gateway->initiate($payment);

        $payment->update([
            'payment_status' => PaymentStatuses::PROCESSING->value,
            'transaction_id' => $result['transaction_id'],
        ]);

        return [
            'payment_id' => $payment->id,
            'redirect_url' => $result['redirect_url'],
        ];
    }

    private function payByWallet(Payment $payment): array
    {
        return DB::transaction(function () use ($payment) {

            $payment = Payment::query()
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if (
                $payment->payment_status !==
                PaymentStatuses::UNPAID->value
            ) {
                throw new PaymentGatewayException(
                    'این پرداخت قبلاً پردازش شده است.'
                );
            }

            $userWallet = $payment->user->getWallet();

            $this->walletService->debitAvailable(
                wallet: $userWallet,
                amount: (int) $payment->amount,
                type: WalletTransactionType::PAYMENT,
                payment: $payment,
                description: "پرداخت سفارش #{$payment->payable_id}",
            );

            $this->registerCouponUsage($payment);

            $payment->update([
                'payment_status' => PaymentStatuses::PAID->value,
            ]);

            $payable = $payment->payable()
                ->lockForUpdate()
                ->firstOrFail();

            if ($payable instanceof HandlesPayment) {
                $payable->paymentSucceeded($payment);
            }

            return [
                'payment_id' => $payment->id,
                'redirect_url' => null,
            ];
        });
    }

    public function handleCallback(
        string $gatewayName,
        array $payload
    ): Payment {
        $gatewayEnum = PaymentGateways::fromEnglishLabel($gatewayName);

        if (! $gatewayEnum) {
            throw new PaymentGatewayException(
                'درگاه پرداخت نامعتبر است.'
            );
        }

        $transactionId = $payload['transaction_id'] ?? null;

        if (! $transactionId) {
            throw new PaymentGatewayException(
                'شناسه تراکنش ارسال نشده است.'
            );
        }

        $payment = Payment::query()
            ->where('transaction_id', $transactionId)
            ->where('gateway', $gatewayEnum->value)
            ->firstOrFail();

        $gateway = GatewayFactory::make($gatewayEnum->value);

        $result = $gateway->verify($payload);

        if (
            isset($result['transaction_id'])
            && $result['transaction_id']
            && $result['transaction_id'] !== $payment->transaction_id
        ) {
            throw new PaymentGatewayException(
                'شناسه تراکنش با پرداخت مطابقت ندارد.'
            );
        }

        return $this->finalizePayment(
            payment: $payment,
            success: $result['success'],
            gatewayResponse: $result['raw'] ?? [],
        );
    }

    private function finalizePayment(
        Payment $payment,
        bool $success,
        array $gatewayResponse = [],
    ): Payment {
        return DB::transaction(function () use (
            $payment,
            $success,
            $gatewayResponse
        ) {
            $payment = Payment::query()
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if (
                in_array($payment->payment_status, [
                    PaymentStatuses::PAID->value,
                    PaymentStatuses::FAILED->value,
                    PaymentStatuses::CANCELLED->value,
                    PaymentStatuses::REFUNDED->value,
                    PaymentStatuses::EXPIRED->value,
                ], true)
            ) {
                return $payment->fresh();
            }

            if ($success) {
                $this->registerCouponUsage($payment);

                $payment->update([
                    'payment_status' => PaymentStatuses::PAID->value,
                    'gateway_response' => $gatewayResponse,
                ]);
            } else {
                $payment->update([
                    'payment_status' => PaymentStatuses::FAILED->value,
                    'gateway_response' => $gatewayResponse,
                ]);
            }

            $payable = $payment->payable()
                ->lockForUpdate()
                ->first();

            if ($payable instanceof HandlesPayment) {
                if ($success) {
                    $payable->paymentSucceeded($payment);
                } else {
                    $payable->paymentFailed($payment);
                }
            }

            return $payment->fresh();
        });
    }

    private function registerCouponUsage(Payment $payment): void
    {
        if (! $payment->coupon_id) {
            return;
        }

        $coupon = $payment->coupon()
            ->lockForUpdate()
            ->firstOrFail();

        $alreadyUsed = CouponUsage::query()
            ->where('coupon_id', $coupon->id)
            ->where(
                'discountable_type',
                $payment->payable_type
            )
            ->where(
                'discountable_id',
                $payment->payable_id
            )
            ->exists();

        if ($alreadyUsed) {
            return;
        }

        if (
            $coupon->usage_limit !== null
            && $coupon->used_count >= $coupon->usage_limit
        ) {
            throw new PaymentGatewayException(
                'ظرفیت استفاده از کد تخفیف تکمیل شده است.'
            );
        }

        $userUsageCount = CouponUsage::query()
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $payment->user_id)
            ->lockForUpdate()
            ->count();

        if (
            $coupon->usage_limit_per_user !== null
            && $userUsageCount >= $coupon->usage_limit_per_user
        ) {
            throw new PaymentGatewayException(
                'سقف استفاده شما از این کد تخفیف تکمیل شده است.'
            );
        }

        $coupon->increment('used_count');

        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $payment->user_id,

            'discountable_type' => $payment->payable_type,
            'discountable_id' => $payment->payable_id,

            'discount_amount' => $payment->coupon_discount_amount,

            'used_at' => now(),
        ]);
    }

    private function ensurePayableCanBePaid(
        PayableEntity $payable
    ): void {
        $paid = $payable->payments()
            ->where(
                'payment_status',
                PaymentStatuses::PAID->value
            )
            ->exists();

        if ($paid) {
            throw new PaymentGatewayException(
                'این مورد قبلاً پرداخت شده است.'
            );
        }
    }
}
