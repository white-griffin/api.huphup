<?php

namespace App\Services\Payment;

use App\Contracts\CouponEligible;
use App\Contracts\HandlesPayment;
use App\Contracts\PayableEntity;
use App\Enums\PaymentGateways;
use App\Enums\PaymentStatuses;
use App\Enums\WalletTransactionType;
use App\Exceptions\PaymentGatewayException;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Payment;
use App\Services\Discount\DiscountService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use ValueError;

class PaymentService
{
    public function createForPayable(
        PayableEntity $payable,
        string $gateway,
        ?Coupon $coupon = null,
        int $couponDiscountAmount = 0,
        ?int $originalAmount = null,
        ?int $finalAmount = null,
    ): Payment
    {
        return DB::transaction(function () use (
            $payable,
            $gateway,
            $coupon,
            $couponDiscountAmount,
            $originalAmount,
            $finalAmount
        ) {

            $payable = $payable::query()
                ->lockForUpdate()
                ->findOrFail($payable->getKey());

            $paidPayment = $payable->payments()
                ->where('payment_status', PaymentStatuses::PAID->value)
                ->exists();

            if ($paidPayment) {
                throw new PaymentGatewayException('این مورد قبلاً پرداخت شده است.');
            }

            $activePayment = $payable->payments()
                ->whereIn('payment_status', [
                    PaymentStatuses::UNPAID->value,
                    PaymentStatuses::PROCESSING->value,
                ])
                ->latest()
                ->first();

            if ($activePayment) {
                return $activePayment;
            }

            return $this->create(
                payable: $payable,
                userId: $payable->getPayableUserId(),
                amount: $finalAmount ?? $payable->getPayableAmount(),
                gateway: $gateway,
                coupon: $coupon,
                couponDiscountAmount: $couponDiscountAmount,
                originalAmount: $originalAmount ?? $payable->getPayableAmount(),
            );
        });
    }
    /**
     * یک رکورد Payment می‌سازد و متصل به payable (Order, Appointment, Wallet, ...)
     */
    public function create(
        PayableEntity $payable,
        int $userId,
        int $amount,
        string $gateway,
        ?Coupon $coupon = null,
        int $couponDiscountAmount = 0,
        ?int $originalAmount = null,
    ): Payment
    {
        return Payment::create([
            'user_id' => $userId,

            // موجودیت قابل پرداخت (Order / Appointment / ...)
            'payable_type' => $payable::class,
            'payable_id' => $payable->getKey(),

            // مبلغ قبل از اعمال کوپن
            'original_amount' => $originalAmount ?? $amount,

            // مبلغ تخفیف کوپن
            'coupon_discount_amount' => $couponDiscountAmount,

            // مبلغ نهایی قابل پرداخت
            'amount' => $amount,

            // کوپن اعمال‌شده
            'coupon_id' => $coupon?->id,

            // درگاه پرداخت
            'gateway' => $gateway,

            // وضعیت اولیه پرداخت
            'payment_status' => PaymentStatuses::UNPAID->value,
        ]);
    }

    public function pay(
        PayableEntity $payable,
        PaymentGateways $gateway,
        ?string $couponCode = null
    ): array
    {
        $baseAmount = $payable->getPayableAmount();

        $coupon = null;
        $couponDiscount = 0;

        if ($couponCode) {

            if ($payable instanceof CouponEligible &&
                ! $payable->canUseCoupon()) {

                throw ValidationException::withMessages([
                    'coupon_code' => $payable->couponRestrictionMessage(),
                ]);
            }

            $result = app(DiscountService::class)->validate(
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

        $payment = $this->createForPayable(
            payable: $payable,
            gateway: $gateway->value,
            coupon: $coupon,
            couponDiscountAmount: $couponDiscount,
            originalAmount: $baseAmount,
            finalAmount: $finalAmount,
        );

        if ($gateway === PaymentGateways::WALLET) {
            return $this->payByWallet($payment);
        }

        return $this->payByGateway($payment);
    }

    /**
     * پرداخت را نزد درگاه شروع می‌کند.
     */
    private function payByGateway(Payment $payment): array
    {
        if ($payment->payment_status != PaymentStatuses::UNPAID->value) {
            throw new PaymentGatewayException('این پرداخت قبلاً پردازش شده است.');
        }
        $gateway = GatewayFactory::make($payment->gateway);
        $result = $gateway->initiate($payment);

        $payment->update([
            'transaction_id' => $result['transaction_id'],
        ]);

        return [
            'redirect_url' => $result['redirect_url'],
        ];
    }

    private function payByWallet(Payment $payment): array
    {
        $userWallet = $payment->user->getWallet();

        $businessWallet = $payment->payable->getReceiverWallet();

        app(WalletService::class)->transfer(
            from: $userWallet,
            to: $businessWallet,
            amount: $payment->amount,
            debitType: WalletTransactionType::PAYMENT,
            creditType: WalletTransactionType::PAYMENT,
            payment: $payment,
            description: "پرداخت سفارش #{$payment->payable->id}",
        );

        $this->finalizePayment(
            payment: $payment,
            success: true,
        );

        return [
            'redirect_url' => null,
        ];
    }

    /**
     * callback ورودی از هر درگاهی را پردازش می‌کند.
     * چون همه‌ی گیت‌وی‌ها از یک روت مشترک عبور می‌کنند، gateway باید
     * از payload یا از رکورد Payment (بر اساس transaction_id) استخراج شود.
     */
    public function handleCallback(string $gatewayName, array $payload): Payment
    {
        $payment = Payment::query()
            ->where('transaction_id', $payload['transaction_id'] ?? null)
            ->where('gateway', PaymentGateways::fromEnglishLabel($gatewayName)->value)
            ->firstOrFail();


        $gateway = GatewayFactory::make(
            PaymentGateways::fromEnglishLabel($gatewayName)->value
        );

        $result = $gateway->verify($payload);


        return $this->finalizePayment(
            payment: $payment,
            success: $result['success'],
            gatewayResponse: $result['raw'],
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


            // idempotency
            if ($payment->payment_status != PaymentStatuses::UNPAID->value) {
                return $payment;
            }


            $payment->update([
                'payment_status' => $success
                    ? PaymentStatuses::PAID->value
                    : PaymentStatuses::FAILED->value,

                'gateway_response' => $gatewayResponse,
            ]);


            if ($success && $payment->coupon_id) {
                $this->registerCouponUsage($payment);
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
        $alreadyUsed = CouponUsage::query()
            ->where('coupon_id', $payment->coupon_id)
            ->where('discountable_type', $payment->payable_type)
            ->where('discountable_id', $payment->payable_id)
            ->exists();

        if ($alreadyUsed) {
            return;
        }

        $coupon = $payment->coupon()
            ->lockForUpdate()
            ->first();

        if ($coupon->usage_limit &&
            $coupon->used_count >= $coupon->usage_limit) {

            throw new PaymentGatewayException('ظرفیت استفاده از کد تخفیف تکمیل شده است.');
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
}
