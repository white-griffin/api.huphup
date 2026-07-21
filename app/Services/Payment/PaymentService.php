<?php

namespace App\Services\Payment;

use App\Contracts\HandlesPayment;
use App\Contracts\PayableEntity;
use App\Enums\PaymentGateways;
use App\Enums\PaymentStatuses;
use App\Enums\WalletTransactionType;
use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Order\OrderPaymentService;
use App\Services\Wallet\WalletService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ValueError;

class PaymentService
{
    public function createForPayable(
        PayableEntity $payable,
        PaymentGateways $gateway,
    ): Payment {
        return DB::transaction(function () use ($payable, $gateway) {

            $payable = $payable::query()
                ->lockForUpdate()
                ->findOrFail($payable->getKey());

            $paidPayment = $payable->payments()
                ->where('payment_status', PaymentStatuses::PAID->value)
                ->exists();

            if ($paidPayment) {
                throw new PaymentGatewayException('این سفارش قبلاً پرداخت شده است.');
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
                amount: $payable->getPayableAmount(),
                gateway: $gateway->value,
            );
        });
    }
    /**
     * یک رکورد Payment می‌سازد و متصل به payable (Order, Appointment, Wallet, ...)
     */
    public function create(Model $payable, int $userId, int $amount, string $gateway): Payment
    {
        return Payment::create([
            'user_id' => $userId,
            'payable_type' => $payable::class,
            'payable_id' => $payable->getKey(),
            'amount' => $amount,
            'gateway' => $gateway,
            'payment_status' => PaymentStatuses::UNPAID->value,
        ]);
    }

    public function pay(PayableEntity $payable, PaymentGateways $gateway): array
    {
        $payment = $this->createForPayable(
            payable: $payable,
            gateway: $gateway,
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
            ->lockForUpdate()
            ->firstOrFail();

        // idempotency: اگر قبلاً پردازش شده، دوباره پردازش نکن
        if ($payment->payment_status != PaymentStatuses::UNPAID->value) {
            return $payment;
        }

        $gateway = GatewayFactory::make(PaymentGateways::fromEnglishLabel($gatewayName)->value);
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
    ): Payment{
        return DB::transaction(function () use ($payment, $success, $gatewayResponse) {

            $payment->update([
                'payment_status' => $success
                    ? PaymentStatuses::PAID->value
                    : PaymentStatuses::FAILED->value,
                'gateway_response' => $gatewayResponse,
            ]);

            $payable = $payment->payable()->lockForUpdate()->first();

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
}
