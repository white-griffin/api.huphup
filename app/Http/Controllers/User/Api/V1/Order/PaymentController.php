<?php

namespace App\Http\Controllers\User\Api\V1\Order;

use App\Enums\PaymentGateways;
use App\Enums\PaymentStatuses;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Order;
use App\Models\OrderVendor;
use App\Models\Payment;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    )
    {
    }


    /**
     * شروع فرآیند پرداخت
     */
    public function pay(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'gateway' => ['required', Rule::enum(PaymentGateways::class)],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        $order = $request->user()
            ->orders()
            ->findOrFail($data['order_id']);


        $result = $this->paymentService->pay(
            payable: $order,
            gateway: PaymentGateways::from($data['gateway']),
            couponCode: $data['coupon_code'] ?? null,
        );

        return ApiResponse::Success('عملیات موفق', [
            'payment' => $result,
        ]);
    }

    /**
     * Callback درگاه
     */
    public function callback(Request $request, string $gateway)
    {

        $payment = $this->paymentService->handleCallback(
            gatewayName: $gateway,
            payload: $request->all()
        );

        if ($payment->payment_status === PaymentStatuses::PAID->value) {
            return redirect()->to(
                $this->buildSuccessDeepLink($payment)
            );
        }

        return redirect()->to(
            'huphup://payments/failed?' . http_build_query([
                'type' => $this->payableType($payment),
                'payment_id' => $payment->id,
            ])
        );

    }

    private function buildSuccessDeepLink(Payment $payment): string
    {
        $payable = $payment->payable;

        $params = match (true) {

            $payable instanceof Order => [
                'type' => 'order',
                'order_id' => $payable->id,
                'transaction_id' => $payment->transaction_id,
                'amount' => $payment->amount,
            ],

            $payable instanceof Appointment => [
                'type' => 'appointment',
                'appointment_id' => $payable->id,
                'transaction_id' => $payment->transaction_id,
                'amount' => $payment->amount,

                // اطلاعات اختصاصی Appointment
                // 'date' => $payable->date,
                // 'time' => $payable->time,
                // ...
            ],

            default => [
                'type' => 'payment',
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'amount' => $payment->amount,
            ],
        };

        return 'huphup://payments/success?' . http_build_query($params);
    }

    private function payableType(Payment $payment): string
    {
        return match (true) {
            $payment->payable instanceof Order => 'order',
            $payment->payable instanceof Appointment => 'appointment',
            default => 'payment',
        };
    }
}
