<?php

namespace App\Services\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class TestGateway implements PaymentGatewayInterface
{
    /**
     * شروع پرداخت
     */
    public function initiate(Payment $payment): array
    {
        $transactionId = 'TEST-' . Str::upper(Str::random(12));

        return [
            'redirect_url' => route('payments.test', [
                'transaction_id' => $transactionId,
            ]),

            'transaction_id' => $transactionId,
        ];
    }

    /**
     * شبیه‌سازی صفحه پرداخت
     */
    public function simulate(Request $request): Response
    {
        $payment = Payment::query()
            ->where('transaction_id', $request->get('transaction_id'))
            ->firstOrFail();

        return response()->view('payment-gateways.test', [
            'payment' => $payment,
        ]);
    }

    /**
     * اعتبارسنجی Callback
     */
    public function verify(array $payload): array
    {
        return [
            'success' => ($payload['status'] ?? null) === 'OK',

            'transaction_id' => $payload['transaction_id'] ?? null,

            'raw' => $payload,
        ];
    }
}
