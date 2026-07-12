<?php

namespace App\Http\Controllers\User\Api\V1\Order;

use App\Enums\PaymentGateways;
use App\Enums\PaymentStatuses;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {
    }


    /**
     * شروع فرآیند پرداخت
     */
    public function pay(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer'],
            'gateway' => ['required', Rule::enum(PaymentGateways::class)],
        ]);

        $order = $request->user()
            ->orders()
            ->findOrFail($data['order_id']);


        $payment = $this->paymentService->createForOrder(
            order: $order,
            gateway: $data['gateway'],
        );

        $result = $this->paymentService->initiate($payment);

        return ApiResponse::Success([
            'redirect_url' => $result['redirect_url'],
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

        if ($payment->payment_status == PaymentStatuses::PAID->value) {

            return ApiResponse::Success(
                'پرداخت با موفقیت انجام شد.',
                $payment,
            );
        }

        return ApiResponse::Fail(
            Response::HTTP_BAD_REQUEST,
            'پرداخت ناموفق بود.'
        );
    }
}
