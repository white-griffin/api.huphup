<?php

namespace App\Http\Controllers\User\Api\V1\User;

use App\Enums\ActivityStatus;
use App\Enums\PlatformTypes;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\User\Api\V1\BaseController;
use App\Models\DeviceToken;
use App\Models\User;
use App\Services\Chat\ChatTokenService;
use App\Services\Referral\ReferralCodeService;
use App\Services\Referral\ReferralService;
use Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Ipe\Sdk\Facades\SmsIr;

class AuthController extends BaseController
{

    /**
     * Login Api User
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'mobile' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'referral_code' => ['nullable', 'string', 'max:32'],
        ], [
            'mobile.required' => 'شماره تماس را وارد کنید',
            'mobile.regex' => 'فرمت شماره تلفن صحیح نیست',
        ]);

        try {
            $user = User::query()
                ->firstOrCreate(
                    [
                        'mobile' => request('mobile')
                    ], [
                        'mobile' => request('mobile')
                    ]
                );

            $otp_code = Env::get('APP_ENV') == 'production' ? rand(100000, 999999) : '111111';
            $user->update(['otp_code' => $otp_code]);
            if (Env::get('APP_ENV') == 'production') {
                $sendOtp = $this->sendOtp($user->mobile, $otp_code);
                if ($sendOtp['code'] != 1) {

                    return ApiResponse::Fail(501, 'خطا در ارسال کد'
                        , $sendOtp);
                }
            }

            if (
                $user->wasRecentlyCreated &&
                $request->filled('referral_code')
            ) {
                Cache::put(
                    'pending_referral:' . $user->id,
                    strtoupper(trim($request->referral_code)),
                    now()->addMinutes(10)
                );
            }

            return ApiResponse::Success('رمز ارسال شد');

        } catch (\Exception $exception) {
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در برقراری ارتباط');
        }

    }

    /**
     * Login user and create auth tokens
     * @param Request $request
     * @return JsonResponse
     * @throws \Throwable
     */

    public function checkCode(
        Request $request,
        ReferralService $referralService,
        ReferralCodeService $referralCodeService
    ) {
        $validation = $request->validate([
            'mobile' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'otp_code' => ['required', 'digits:6'],
        ], [
            'mobile.required' => 'وارد کردن شماره موبایل الزامی است',
            'mobile.regex' => 'فرمت شماره تلفن صحیح نیست',
            'otp_code.required' => 'وارد کردن کد تایید الزامی است',
            'otp_code.digits' => 'کد تایید باید 6 رقمی باشد',
        ]);

        try {
            $user = DB::transaction(function () use (
                $validation,
                $referralCodeService
            ) {
                $user = User::query()
                    ->where('mobile', $validation['mobile'])
                    ->lockForUpdate()
                    ->first();

                if (!$user) {
                    throw ValidationException::withMessages([
                        'mobile' => 'کاربری با این شماره یافت نشد.',
                    ]);
                }

                if (
                    !$user->otp_code ||
                    $user->otp_code != $validation['otp_code']
                ) {
                    throw ValidationException::withMessages([
                        'otp_code' => 'کد تایید صحیح نیست.',
                    ]);
                }

                $user->activity_status = ActivityStatus::ACTIVE->value;
                $user->otp_code = null;
                $user->save();

                // تولید کد اختصاصی کاربر
                $referralCodeService->generate($user);

                return $user;
            }, 3);

        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'خطا در ورود به سیستم'
            );
        }

        // ثبت Referral خارج از Transaction ورود
        $referralCacheKey = 'pending_referral:' . $user->id;
        $pendingReferralCode = Cache::get($referralCacheKey);

        if ($pendingReferralCode) {
            try {
                $referralService->register(
                    $user,
                    $pendingReferralCode
                );

                Cache::forget($referralCacheKey);

            } catch (ValidationException $exception) {
                // کد نامعتبر یا شرایط غیرمجاز؛ دیگر نیازی به تلاش مجدد نیست.
                Cache::forget($referralCacheKey);
            } catch (\Throwable $exception) {
                // خطای موقت؛ کد در Cache باقی می‌ماند تا امکان تلاش مجدد باشد.
                report($exception);
            }
        }

        return ApiResponse::Success('با موفقیت وارد شدید', [
            'token' => $user->createToken('API TOKEN')->plainTextToken,
        ]);
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
        } catch (\Exception $exception) {
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }

    /**
     * Logout User and delete tokens
     * @return JsonResponse
     */
    public function logOut()
    {
        try {
            request()->user()->tokens()->delete();
            return ApiResponse::Success('با موفقیت خارج شدید');

        } catch (\Exception $e) {
            return ApiResponse::Fail(500, $e->getMessage());
        }
    }

    public function getChatJwtToken(
        Request          $request,
        ChatTokenService $chatTokenService
    ): JsonResponse
    {
        try {
            $token = $chatTokenService->getToken(
                $request->user()
            );

            return ApiResponse::Success('توکن دریافت شد', [
                'token' => $token,
            ]);
        } catch (\Exception $exception) {
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در برقراری ارتباط');
        }
    }


    public function setFcmToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_id' => ['required', 'string', 'max:255'],
                'token' => ['required', 'string'],
                'platform' => [
                    'required',
                    'integer',
                    Rule::in([
                        PlatformTypes::ANDROID,
                        PlatformTypes::IOS,
                        PlatformTypes::UNKNOWN,
                    ]),
                ],
            ]);

            $deviceToken = DeviceToken::query()
                ->updateOrCreate(
                    [
                        'user_id' => $request->user()->id,
                        'device_id' => $validated['device_id'],
                    ],
                    [
                        'token' => $validated['token'],
                        'platform' => $validated['platform'],
                        'last_seen_at' => now(),
                    ],
                );

            return ApiResponse::Success('عملیات موفق');
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }

    public function destroyFcmToken(Request $request,$deviceId)
    {
        try {
            DeviceToken::query()
                ->where('user_id', $request->user()->id)
                ->where('device_id', $deviceId)
                ->delete();

            return ApiResponse::Success('عملیات موفق');

        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());

        }
    }


}
