<?php

namespace App\Http\Controllers\Provider\Api\V1;

use App\Enums\ActivityStatus;
use App\Helpers\Api\ApiResponse;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Ipe\Sdk\Facades\SmsIr;

class AuthController extends BaseController
{

    public function login(Request $request)
    {

         $request->validate([
            'mobile' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'password' => ['required', 'string'],
        ],[
            'mobile.required' => 'شماره تماس را وارد کنید',
            'mobile.regex' => 'فرمت شماره تلفن صحیح نیست',
            'password.required' => 'رمز عبور را وارد کنید',
            'password.regex' => 'فرمت رمز عبور صحیح نیست',
        ]);

        $key = 'login:' . $request->mobile . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            return ApiResponse::Fail(Response::HTTP_TOO_MANY_REQUESTS,"تعداد تلاش بیش از حد مجاز است. {$seconds} ثانیه دیگر تلاش کنید.");
        }

        try {
            $provider = Provider::query()
                ->where('mobile', $request->mobile)
                ->first();

            if (! $provider || ! Hash::check($request->password, $provider->password)) {
                RateLimiter::hit($key, 300); // 5 دقیقه
                return ApiResponse::Fail(Response::HTTP_UNPROCESSABLE_ENTITY,'شماره موبایل یا پسورد اشتباه است.');
            }

            RateLimiter::clear($key);

            if ($provider->two_factor_status == ActivityStatus::ACTIVE->value) {
                $code = (string) random_int(100000, 999999);

                $provider->update([
                    'two_factor_code' => $code,
                    'two_factor_expires_at' => now()->addMinutes(2),
                ]);

                $this->sendOtp($provider->mobile, $code);


                return ApiResponse::Success('کد تایید ارسال شد');
            }

            return ApiResponse::Success('با موفقیت وارد شدید', [
                'token' => $provider->createToken('API TOKEN')->plainTextToken,
            ]);

        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در ورود');
        }
    }


    public function verify2fa(Request $request)
    {
        $validation = $request->validate([
            'mobile' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'otp_code' => ['required', 'digits:6'],
        ], [
            'mobile.required' => 'وارد کردن شماره موبایل الزامی است',
            'mobile.regex' => 'فرمت شماره تلفن صحیح نیست',
            'otp_code.required' => 'وارد کردن کد تایید الزامی است',
            'otp_code.digits' => 'کد تایید باید 6 رقمی باشد',
        ]);

        $key = 'provider-2fa:' . $request->mobile . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return ApiResponse::Fail(
                Response::HTTP_TOO_MANY_REQUESTS,
                "تعداد تلاش بیش از حد مجاز است. {$seconds} ثانیه دیگر تلاش کنید."
            );
        }

        try {

            $provider = Provider::query()->where('mobile', $request->mobile)->first();
            if (! $provider) {
                return ApiResponse::Fail(Response::HTTP_NOT_FOUND,'کاربر پیدا نشد');
            }

            if (
                ! $provider->two_factor_code ||
                ! $provider->two_factor_expires_at ||
                now()->greaterThan($provider->two_factor_expires_at) ||
                $provider->two_factor_code !== $request->otp_code
            ) {
                return ApiResponse::Fail(Response::HTTP_UNPROCESSABLE_ENTITY,'کد تایید نامعتبر یا منقضی شده است.');
            }

            RateLimiter::clear($key);
            $provider->update([
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
            ]);


            return ApiResponse::Success('با موفقیت وارد شدید', [
                'token' => $provider->createToken('API TOKEN')->plainTextToken,
            ]);
        }catch (\Exception $exception){
            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'خطا در تایید کد'
            );
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
            $lineNumber = "9982008664";
            $templateId = 595494; // شناسه الگو
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

    public function logOut()
    {
        try {
            request()->user()->tokens()->delete();
            return ApiResponse::Success('با موفقیت خارج شدید');

        } catch (\Exception $e) {
            return ApiResponse::Fail(500,$e->getMessage());
        }
    }

}
