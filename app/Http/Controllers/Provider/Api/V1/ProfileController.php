<?php

namespace App\Http\Controllers\Provider\Api\V1;


use App\Helpers\Api\ApiResponse;
use App\Http\Requests\Provider\Api\V1\Profile\UpdateProfileRequest;
use App\Http\Resources\V1\Provider\ProfileResource;
use App\Services\MediaService;

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
            $media = app(MediaService::class);
            $provider = request()->user('provider');
            $data = $request->validated();
            $data = array_filter(
                $data,
                fn($value) => !is_null($value)
            );

            if (request()->hasFile('avatar')) {
                $data['avatar'] = $media->replace(
                    $provider->avatar,
                    request()->file('avatar'),
                    'providers/avatars'
                );
            }
            $profile = $provider->update($data);
            return ApiResponse::success('عملیات موفق');
        }catch (\Exception $exception){
            return ApiResponse::Fail(500,$exception->getMessage());
        }
    }
}
