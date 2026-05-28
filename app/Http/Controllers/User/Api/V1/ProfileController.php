<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\ProfileResource;
use App\Models\UserAddress;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProfileController extends BaseController
{


    /** Get User's profile
     * auth token needed
     * @return JsonResponse => Use App\Http\Resources\V1\User\ProfileResource
     */
    public function getProfile()
    {

        try {
            $user = auth()->user();
            return ApiResponse::Success('عملیات موفق', ProfileResource::make($user->with('addresses')));
        } catch (\Exception $exception) {
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }

    /** Set user profile
     * auth token needed
     * @return JsonResponse
     * @throws \Throwable
     * @params from profileData funtion
     */
    public function updateProfile()
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $user->update($this->profileData($user));
            DB::commit();

            return ApiResponse::Success('عملیات موفق');
        } catch (\Exception $exception) {
            DB::rollBack();
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }

    /** add Address for user
     * auth token needed
     * @return JsonResponse
     * @throws \Throwable
     * @params from addressData Function
     */
    public function addAddress()
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $user->addresses()->create($this->addressData());
            DB::commit();
            return ApiResponse::Success('عملیات موفق');
        } catch (\Exception $exception) {
            DB::rollBack();
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }


    /** Updating User's address
     * auth token needed
     * @param UserAddress $address
     * @return JsonResponse
     * @throws \Throwable
     */
    public function updateAddress(UserAddress $address)
    {
        DB::beginTransaction();
        try {
            $address->update($this->addressData());
            DB::commit();
            return ApiResponse::Success('عملیات موفق');
        } catch (\Exception $exception) {
            DB::rollBack();
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }


    /** Delete User's Address
     * auth token needed
     * @param $address
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteAddress($address)
    {
        DB::beginTransaction();
        try {
            $address->delete();
            DB::commit();
            return ApiResponse::Success('عملیات موفق');
        } catch (\Exception $exception) {
            DB::rollBack();
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');

        }
    }


    /**
     * get ProfileData form request ()
     * @params $user , $media
     * @return array
     */
    private function profileData($user)
    {
        $media = app(MediaService::class);
        $data = array_filter(
            request()->only([
                'first_name',
                'last_name',
                'email',
                'birth_date',
                'national_code',
                'gender_type',
                'bio'
            ]),
            fn($value) => !is_null($value)
        );

        if (request()->hasFile('avatar')) {
            $data['avatar'] = $media->replace(
                $user->avatar,
                request()->file('avatar'),
                'users/avatars'
            );
        }
        return $data;
    }

    /**
     * get AddressData from request()
     * @return array
     * @params from request : [ province_id,city_id,postal_code,address,latitude,longitude ]
     */
    private function addressData()
    {
        return array_filter(
            request()->only([
                'province_id',
                'city_id',
                'postal_code',
                'address',
                'latitude',
                'longitude',
            ]),
            fn($value) => !is_null($value)
        );

    }
}
