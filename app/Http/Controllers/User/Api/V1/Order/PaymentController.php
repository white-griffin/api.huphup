<?php

namespace App\Http\Controllers\User\Api\V1\Order;

use App\Enums\PaymentGateways;
use App\Enums\PaymentStatuses;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\OrderVendor;
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
            'order_vendor_id' => ['required', 'integer'],
            'gateway' => ['required', Rule::enum(PaymentGateways::class)],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        $orderVendor = OrderVendor::query()
            ->whereKey($data['order_vendor_id'])
            ->whereHas('order', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->firstOrFail();

        $result = $this->paymentService->pay(
            payable: $orderVendor,
            gateway: PaymentGateways::from($data['gateway']),
            couponCode: $data['coupon_code'] ?? null,
        );

        return ApiResponse::Success('عملیات موفق', [
            'order_vendor' => $orderVendor->fresh([
                'order',
                'business',
                'payments',
            ]),
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
