<?php

namespace App\Services\Payment;

use App\Contracts\HandlesPayment;
use App\Enums\PaymentGateways;
use App\Enums\PaymentStatuses;
use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Order\OrderPaymentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ValueError;

class PaymentService
{
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

    /**
     * پرداخت را نزد درگاه شروع می‌کند.
     */
    public function initiate(Payment $payment): array
    {
        if ($payment->payment_status != PaymentStatuses::UNPAID->value) {
            throw new PaymentGatewayException('این پرداخت قبلاً پردازش شده است.');
        }
        $gateway = GatewayFactory::make($payment->gateway);
        $result = $gateway->initiate($payment);

        $payment->update([
            'transaction_id' => $result['transaction_id'],
        ]);

        return $result;
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
            ->where('gateway', $gatewayName)
            ->lockForUpdate()
            ->firstOrFail();

        // idempotency: اگر قبلاً پردازش شده، دوباره پردازش نکن
        if ($payment->payment_status != PaymentStatuses::UNPAID->value) {
            return $payment;
        }

        $gateway = GatewayFactory::make($gatewayName);
        $result = $gateway->verify($payload);

        return DB::transaction(function () use ($payment, $result) {
            $payment->update([
                'payment_status' => $result['success']
                    ? PaymentStatuses::PAID->value
                    : PaymentStatuses::FAILED->value,
                'gateway_response' => $result['raw'],
            ]);

            // اطلاع‌رسانی به payable (Order, Wallet, Appointment) که پرداخت
            // انجام شد؛ هر مدل payable باید متد onPaymentSucceeded/onPaymentFailed
            // را پیاده‌سازی کند (یا از یک interface مشترک استفاده شود).
            $payable = $payment->payable()->lockForUpdate()->first();

            if ($payable instanceof HandlesPayment) {

                if ($result['success']) {
                    $payable->paymentSucceeded($payment);
                } else {
                    $payable->paymentFailed($payment);
                }

            }

            return $payment->fresh();
        });
    }
}
