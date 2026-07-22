<?php

namespace App\Http\Controllers\User\Api\V1\Order;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Discount\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CouponController extends Controller
{
    public function validateCoupon(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        $result = app(DiscountService::class)->validate(
            code: $data['code'],
            user: $request->user(),
            amount: $data['amount'],
        );

        if (! $result->valid) {
            return ApiResponse::Fail(Response::HTTP_UNPROCESSABLE_ENTITY,$result->message);
        }

        return ApiResponse::success('عملیات موفق',[
            'valid' => true,
            'code' => $result->coupon->code,
            'discount_amount' => $result->discountAmount,
            'final_amount' => max(0, $data['amount'] - $result->discountAmount),
            'message' => 'کد تخفیف معتبر است.',
        ]);
    }
}
