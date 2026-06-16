<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Enums\GenderType;
use App\Helpers\Api\ApiResponse;
use App\Http\Resources\V1\User\Pets\PetResource;
use App\Models\Pet;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PetController extends BaseController
{
    /**
     * return User's Pets
     * auth token needed
     * @return JsonResponse
     */
    public function getPets()
    {
        try {
            $pets = auth()->user()
                ->pets()
                ->with('species', 'breed')
                ->get();

            return ApiResponse::success('عملیات موفق', PetResource::collection($pets));
        } catch (\Exception $e) {
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }

    /**
     * return single Pet that id came from API
     * auth token needed
     * @param Pet $pet
     * @return JsonResponse
     */
    public function getPet(Pet $pet)
    {
        try {
            return ApiResponse::success('عملیات موفق', PetResource::make($pet));
        } catch (\Exception $e) {
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }

    /**
     * Store User's pet
     * auth token needed
     * @throws \Throwable
     */
    public function storePet()
    {
        $data = $this->petData();

        DB::beginTransaction();
        try {
            $user = auth()->user();

            $user->pets()->create($data);
            DB::commit();

            return ApiResponse::Success('عملیات موفق');
        } catch (\Exception $e) {
        }
        DB::rollBack();
        return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
    }


    /**
     * update User's pet that id came from API
     * @param Pet $pet
     * @return JsonResponse
     * @throws \Throwable
     */
    public function updatePet(Pet $pet)
    {
        $data = $this->petData($pet);

        DB::beginTransaction();
        try {
            $pet->update($data);
            DB::commit();
            return ApiResponse::Success('عملیات موفق');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }

    }

    public function deletePet(Pet $pet)
    {
        try {
            DB::beginTransaction();
            $pet->delete();
            DB::commit();
            return ApiResponse::Success('عملیات موفق');
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }

    /**
     * return Pet data to inner functions for CRUD
     * get data from request()
     * @return array
     */
    private function petData($pet = null)
    {
        $media = app(MediaService::class);
        $required = $pet ? 'sometimes' : 'required';
        $speciesId = request('species_id', $pet?->species_id);

        $data = request()->validate([
            'species_id' => [$required, 'integer', 'exists:species,id'],
            'breed_id' => [
                'nullable',
                'integer',
                Rule::exists('breeds', 'id')->where(fn($query) => $query->where('species_id', $speciesId)),
            ],
            'name' => [$required, 'string', 'max:100'],
            'gender_type' => ['nullable', Rule::in(array_map(fn(GenderType $type) => $type->value, GenderType::cases()))],
            'birth_date' => ['nullable', 'date'],
            'weight' => ['nullable', 'numeric', 'between:0,999.99'],
            'color' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'medical_records' => ['nullable'],
            'settings' => ['nullable'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ], [
            'species_id.required' => 'گونه حیوان را انتخاب کنید',
            'species_id.exists' => 'گونه انتخاب شده معتبر نیست',
            'breed_id.exists' => 'نژاد انتخاب شده معتبر نیست',
            'name.required' => 'نام حیوان را وارد کنید',
            'gender_type.in' => 'جنسیت انتخاب شده معتبر نیست',
            'birth_date.date' => 'تاریخ تولد معتبر نیست',
            'weight.numeric' => 'وزن باید عددی باشد',
            'weight.between' => 'وزن وارد شده معتبر نیست',
            'color.max' => 'رنگ حیوان بیش از حد مجاز است',
            'avatar.image' => 'فایل آواتار باید تصویر باشد',
            'avatar.mimes' => 'فرمت تصویر آواتار معتبر نیست',
            'avatar.max' => 'حجم تصویر آواتار نباید بیشتر از ۲ مگابایت باشد',
        ]);

        $data = array_filter(
            $data,
            fn($value) => !is_null($value)
        );

        if (request()->hasFile('avatar')) {
            $data['avatar'] = $media->replace(
                $pet?->avatar,
                request()->file('avatar'),
                'pet/avatars'
            );
        }

        return $data;
    }
}
