<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\PetResource;
use App\Models\Pet;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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
            $pets = auth()->user()->pets;

            return ApiResponse::success('عملیات موفق', PetResource::collection($pets));
        }catch (\Exception $e) {
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
        }catch (\Exception $e) {
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
        DB::beginTransaction();
        try {
            $user = auth()->user();

            $user->pets()->create($this->petData());
            DB::commit();

            return ApiResponse::Success('عملیات موفق');
        }catch (\Exception $e) {}
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

        DB::beginTransaction();
        try {
            $pet->update($this->petData($pet));
            DB::commit();
            return ApiResponse::Success('عملیات موفق');
        }catch (\Exception $e) {
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
        $data =  array_filter(
            request()->only([
                'species_id',
                'breed_id',
                'name',
                'gender_type',
                'weight',
                'color',
                'medical_records',
                'setting',
                'bio'
            ]),
            fn($value) => !is_null($value)
        );

        if (request()->hasFile('avatar') ) {
            $data['avatar'] = $media->replace(
                $pet?->avatar,
                request()->file('avatar'),
                'pet/avatars'
            );
        }

        return $data;
    }
}
