<?php

namespace App\Contracts;

use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * پرداخت رو نزد درگاه ثبت می‌کند و لینک/توکن پرداخت را برمی‌گرداند.
     * هر مرحله‌ی داخلی (گرفتن توکن، feasibility check و ...) باید
     * داخل خودِ کلاس گیت‌وی مدیریت شود؛ خروجی این متد باید یکسان بماند.
     *
     * @return array{redirect_url: string, transaction_id: string}
     */
    public function initiate(Payment $payment): array;

    /**
     * callback ورودی از درگاه را اعتبارسنجی می‌کند.
     *
     * @return array{success: bool, transaction_id: ?string, raw: array}
     */
    public function verify(array $payload): array;
}
