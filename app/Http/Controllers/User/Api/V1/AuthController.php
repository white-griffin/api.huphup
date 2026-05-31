<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Enums\ActivityStatus;
use App\Helpers\Api\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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

            $otp_code = mt_rand(10000, 99999);
            $user->update(['otp_code' => $otp_code]);
            $user->tokens()->delete();
            $sendOtp = $this->sendOtp($user->mobile, $otp_code);
            if ($sendOtp['code'] != 1){

                return ApiResponse::Fail(501,'خطا در ارسال کد'
                    ,$sendOtp);
            }

            return ApiResponse::Success('رمز ارسال شد');

        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در برقراری ارتباط');
        }

    }

    /**
     * Login user and create auth tokens
     * @param Request $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function checkCode(Request $request)
    {
        $validation = $request->validate([
            'mobile'   => 'required',
            'otp_code' => 'required',
        ], [
            'mobile.required'   => 'وارد کردن شماره موبایل الزامی است',
            'otp_code.required' => 'وارد کردن کد تایید الزامی است',
        ]);


        $user = User::query()->where('mobile', $validation['mobile'])->first();

        if (! $user) {
            return ApiResponse::Fail(Response::HTTP_NOT_FOUND, 'کاربری با این شماره یافت نشد');
        }

        if ($user->otp_code != $validation['otp_code']) {
            return ApiResponse::Fail(Response::HTTP_UNAUTHORIZED, 'کد تایید صحیح نیست');
        }

        DB::beginTransaction();
        try {
            $user->activity_status = ActivityStatus::ACTIVE->value;
            $user->save();
            $user->tokens()->delete();

            DB::commit();

            return ApiResponse::Success('با موفقیت وارد شدید', [
                'token' => $user->createToken('API TOKEN')->plainTextToken,
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
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
            return ApiResponse::Fail(500,$e->getMessage());
        }
    }



}
