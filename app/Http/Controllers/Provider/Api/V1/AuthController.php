<?php

namespace App\Http\Controllers\Provider\Api\V1;

use App\Enums\ActivityStatus;
use App\Helpers\Api\ApiResponse;
use App\Http\Resources\V1\Provider\ProfileResource;
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
                'profile' => ProfileResource::make($provider)
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


    public function toggle2fa(Request $request)
    {
        try {
            $provider = $request->user('provider');

            $code = (string) random_int(100000, 999999);

            $provider->update([
                'two_factor_code' => $code,
                'two_factor_expires_at' => now()->addMinutes(2),
            ]);

            $this->sendOtp($provider->mobile, $code);

            return ApiResponse::Success('کد تایید ارسال شد');

        } catch (\Exception $exception) {
            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'خطا در ارسال کد تایید'
            );
        }
    }

    public function verifyToggle2fa(Request $request)
    {
        $request->validate([
            'otp_code' => ['required', 'digits:6'],
        ], [
            'otp_code.required' => 'وارد کردن کد تایید الزامی است',
            'otp_code.digits' => 'کد تایید باید 6 رقمی باشد',
        ]);

        $provider = $request->user('provider');

        try {

            if (
                ! $provider->two_factor_code ||
                ! $provider->two_factor_expires_at ||
                now()->greaterThan($provider->two_factor_expires_at) ||
                $provider->two_factor_code !== $request->otp_code
            ) {
                return ApiResponse::Fail(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    'کد تایید نامعتبر یا منقضی شده است.'
                );
            }

            $isActive = $provider->two_factor_status == ActivityStatus::ACTIVE->value;

            $provider->update([
                'two_factor_status' => $isActive
                    ? ActivityStatus::INACTIVE->value
                    : ActivityStatus::ACTIVE->value,

                'two_factor_code' => null,
                'two_factor_expires_at' => null,
            ]);

            return ApiResponse::Success(
                $isActive
                    ? 'ورود دو مرحله‌ای غیرفعال شد'
                    : 'ورود دو مرحله‌ای فعال شد',
                [
                    'two_factor_status' => $isActive
                        ? ActivityStatus::INACTIVE->value
                        : ActivityStatus::ACTIVE->value,
                ]
            );

        } catch (\Exception $exception) {
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
            request()->user('provider')->tokens()->delete();
            return ApiResponse::Success('با موفقیت خارج شدید');

        } catch (\Exception $e) {
            return ApiResponse::Fail(500,$e->getMessage());
        }
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'رمز عبور فعلی را وارد کنید',
            'password.required' => 'رمز عبور جدید را وارد کنید',
            'password.min' => 'رمز عبور جدید باید حداقل 8 کاراکتر باشد',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور جدید مطابقت ندارد',
        ]);

        try {
            $provider = $request->user('provider');

            if (! Hash::check($request->current_password, $provider->password)) {
                return ApiResponse::Fail(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    'رمز عبور فعلی اشتباه است'
                );
            }

            // اگر 2FA فعال نیست، مستقیم پسورد تغییر کند
            if ($provider->two_factor_status != ActivityStatus::ACTIVE->value) {
                $provider->update([
                    'password' => Hash::make($request->password),
                ]);

                return ApiResponse::Success('رمز عبور با موفقیت تغییر کرد');
            }

            // ذخیره موقت پسورد جدید تا زمان تایید OTP
            cache()->put(
                'change-password:' . $provider->id,
                Hash::make($request->password),
                now()->addMinutes(2)
            );

            $code = (string) random_int(100000, 999999);

            $provider->update([
                'two_factor_code' => $code,
                'two_factor_expires_at' => now()->addMinutes(2),
            ]);

            $this->sendOtp($provider->mobile, $code);

            return ApiResponse::Success('کد تایید ارسال شد');

        } catch (\Exception $exception) {
            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'خطا در تغییر رمز عبور'
            );
        }
    }

    public function verifyChangePassword(Request $request)
    {
        $request->validate([
            'otp_code' => ['required', 'digits:6'],
        ], [
            'otp_code.required' => 'وارد کردن کد تایید الزامی است',
            'otp_code.digits' => 'کد تایید باید 6 رقمی باشد',
        ]);

        try {
            $provider = $request->user('provider');

            if (
                ! $provider->two_factor_code ||
                ! $provider->two_factor_expires_at ||
                now()->greaterThan($provider->two_factor_expires_at) ||
                $provider->two_factor_code != $request->otp_code
            ) {
                return ApiResponse::Fail(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    'کد تایید نامعتبر یا منقضی شده است.'
                );
            }

            $newPassword = cache()->pull(
                'change-password:' . $provider->id
            );

            if (! $newPassword) {
                return ApiResponse::Fail(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    'درخواست تغییر رمز عبور منقضی شده است.'
                );
            }

            $provider->update([
                'password' => $newPassword,
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
            ]);

            return ApiResponse::Success(
                'رمز عبور با موفقیت تغییر کرد'
            );

        } catch (\Exception $exception) {
            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'خطا در تایید کد'
            );
        }
    }
}
