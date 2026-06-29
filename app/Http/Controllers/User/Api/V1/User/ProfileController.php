<?php

namespace App\Http\Controllers\User\Api\V1\User;

use App\Enums\GenderType;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\User\Api\V1\BaseController;
use App\Http\Resources\V1\User\ProfileResource;
use App\Models\UserAddress;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            return ApiResponse::Success('عملیات موفق', ProfileResource::make($user));
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
        $user = auth()->user();
        $data = $this->profileData($user);

        DB::beginTransaction();
        try {
            $user->update($data);
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
        $data = $this->addressData();

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $user->addresses()->create($data);
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
        $this->authorizeAddressOwner($address);

        $data = $this->addressData(true);

        DB::beginTransaction();
        try {
            $address->update($data);
            DB::commit();
            return ApiResponse::Success('عملیات موفق');
        } catch (\Exception $exception) {
            DB::rollBack();
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }


    /** Delete User's Address
     * auth token needed
     * @param UserAddress $address
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteAddress(UserAddress $address)
    {
        DB::beginTransaction();
        try {
            $this->authorizeAddressOwner($address);

            $address->delete();
            DB::commit();
            return ApiResponse::Success('عملیات موفق');
        } catch (\Exception $exception) {
            DB::rollBack();
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');

        }
    }


    private function authorizeAddressOwner(UserAddress $address): void
    {
        abort_if($address->user_id !== auth()->id(), Response::HTTP_FORBIDDEN);
    }

    /**
     * get ProfileData form request ()
     * @params $user , $media
     * @return array
     */
    private function profileData($user)
    {
        $media = app(MediaService::class);
        $data = request()->validate([
            'first_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'birth_date' => ['nullable', 'date'],
            'national_code' => ['nullable', 'digits:10', Rule::unique('users', 'national_code')->ignore($user->id)],
            'gender_type' => ['nullable', Rule::in(array_map(fn (GenderType $type) => $type->value, GenderType::cases()))],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ], [
            'email.email' => 'فرمت ایمیل صحیح نیست',
            'email.unique' => 'این ایمیل قبلا ثبت شده است',
            'national_code.digits' => 'کد ملی باید ۱۰ رقم باشد',
            'national_code.unique' => 'این کد ملی قبلا ثبت شده است',
            'gender_type.in' => 'جنسیت انتخاب شده معتبر نیست',
            'avatar.image' => 'فایل آواتار باید تصویر باشد',
            'avatar.mimes' => 'فرمت تصویر آواتار معتبر نیست',
            'avatar.max' => 'حجم تصویر آواتار نباید بیشتر از ۲ مگابایت باشد',
            'latitude.between' => 'عرض جغرافیایی معتبر نیست',
            'longitude.between' => 'طول جغرافیایی معتبر نیست',
        ]);

        $data = array_filter(
            $data,
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
    private function addressData(bool $isUpdate = false)
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        $data = request()->validate([
            'province_id' => [$required, 'integer', 'exists:provinces,id'],
            'city_id' => [$required, 'integer', 'exists:cities,id'],
            'address' => [$required, 'string', 'max:1000'],
            'postal_code' => ['nullable', 'digits:10'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ], [
            'province_id.required' => 'استان را انتخاب کنید',
            'province_id.exists' => 'استان انتخاب شده معتبر نیست',
            'city_id.required' => 'شهر را انتخاب کنید',
            'city_id.exists' => 'شهر انتخاب شده معتبر نیست',
            'address.required' => 'آدرس را وارد کنید',
            'postal_code.digits' => 'کد پستی باید ۱۰ رقم باشد',
            'latitude.between' => 'عرض جغرافیایی معتبر نیست',
            'longitude.between' => 'طول جغرافیایی معتبر نیست',
        ]);

        return array_filter(
            $data,
            fn($value) => !is_null($value)
        );

    }
}
