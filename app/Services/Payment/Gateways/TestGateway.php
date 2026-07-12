<?php

namespace App\Services\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use Illuminate\Support\Str;

class TestGateway implements PaymentGatewayInterface
{

    /**
     * @inheritDoc
     */
    public function initiate(Payment $payment): array
    {
        $transactionId = 'TEST-' . Str::upper(Str::random(12));

        return [
            'redirect_url'   => route('payments.test.pay', ['transaction_id' => $transactionId]),
            'transaction_id' => $transactionId,
        ];
    }

    /**
     * @inheritDoc
     */
    public function verify(array $payload): array
    {
        // در محیط تست هر callback با status=success موفق فرض می‌شود
        return [
            'success'        => ($payload['status'] ?? null) === 'success',
            'transaction_id' => $payload['transaction_id'] ?? null,
            'raw'            => $payload,
        ];
    }
}
