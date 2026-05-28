<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Enums\ActivityStatus;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\BreedResource;
use App\Models\Breed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BreedsController extends BaseController
{
    /**
     * return Breeds List
     * @return JsonResponse
     */
    public function getBreeds()
    {
        try {
            $breeds = Breed::query()
                ->where('activity_status',ActivityStatus::ACTIVE)
                ->get();

            return ApiResponse::success('عملیات موفق',BreedResource::collection($breeds));
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }
}
