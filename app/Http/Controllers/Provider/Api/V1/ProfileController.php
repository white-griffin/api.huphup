<?php

namespace App\Http\Controllers\Provider\Api\V1;


use App\Helpers\Api\ApiResponse;
use App\Http\Requests\Provider\Api\V1\Profile\UpdateProfileRequest;
use App\Http\Resources\V1\Provider\ProfileResource;

class ProfileController extends BaseController
{
    public function getProfile()
    {
        try {
           $profile = ProfileResource::make(request()->user('provider'));
           return ApiResponse::success('', $profile);
        }catch (\Exception $exception){
            return ApiResponse::Fail(500,$exception->getMessage());
        }
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $profile = request()->user('provider')
                ->update($request->validated());
            return ApiResponse::success('عملیات موفق');
        }catch (\Exception $exception){
            return ApiResponse::Fail(500,$exception->getMessage());
        }
    }
}
