<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{


    /** Get User's profile
     * auth token needed
     * @return JsonResponse => Use App\Http\Resources\V1\User\ProfileResource
     */
    public function getProfile()
    {

        try {
            $user = auth()->user();
            return ApiResponse::Success('عملیات موفق', [
                ProfileResource::make($user)
            ]);
        } catch (\Exception $exception) {
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }

    /** Set user profile
     * auth token needed
     * @return JsonResponse
     * @throws \Throwable
     * @params from request : [ first_name,last_name,email,avatar,bith_date,national_code,gender_type,bio ]
     */
    public function updateProfile()
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $user->update([
                'first_name' => request('first_name'),
                'last_name' => request('last_name'),
                'email' => request('email'),
                //TODO: Set the current image address
                'avatar' => request('avatar'),
                'birth_date' => request('birth_date'),
                'national_code' => request('national_code'),
                'gender_type' => request('gender_type'),
                'bio' => request('bio'),
            ]);
            DB::commit();

            return ApiResponse::Success('عملیات موفق');
        }catch (\Exception $exception){
            DB::rollBack();

            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }
}
