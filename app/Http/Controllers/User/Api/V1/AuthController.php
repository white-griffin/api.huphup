<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Ipe\Sdk\Facades\SmsIr;

class AuthController extends BaseController
{

    /**
     * Login Api User
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'mobile' => 'required|numeric|min:7',
        ],[
            'mobile.required' => 'شماره تماس را وارد کنید',
            'mobile.min' => 'شماره تماس را کامل وارد کنید',
            'mobile.numeric' => 'فرمت شماره تلفن صحیح نیست ',
        ]);

        try {
            $user = User::query()
                ->firstOrCreate(
                [
                    'mobile' => request('mobile')
                ],[
                    'mobile' => request('mobile')
                ]
            );

            $otp_code = mt_rand(1000, 9999);
            $user->update(['otp_code' => $otp_code]);
            $user->tokens()->delete();
            $sendOtp = $this->sendOtp($user->mobile, $otp_code);
            if ($sendOtp['code'] != 1){

                return ApiResponse::Fail(501,'خطا در ارسال کد'
                    ,$sendOtp);
            }

            return ApiResponse::Success('رمز ارسال شد');

        }catch (\Exception $exception){
            return ApiResponse::Fail(500,'خطا در برقراری ارتباط');
        }

    }


    /**
     * Sending OTP code using SMS
     * @param $mobile
     * @param $otpCode
     * @return array => Provider's status code and message
     */
    private function sendOtp($mobile, $otpCode)
    {

        try {
            $templateId = 123456; // شناسه الگو
            $parameters = [
                [
                    "name" => "CODE",
                    "value" => $otpCode
                ]
            ];

            $response = SmsIr::verifySend($mobile, $templateId, $parameters);

            return [
                'code' => $response->status,
                'message' => $response->message,
            ];
        }catch (\Exception $exception){
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }

}
