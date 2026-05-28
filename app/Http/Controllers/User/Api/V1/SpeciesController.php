<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Enums\ActivityStatus;
use App\Helpers\Api\ApiResponse;
use App\Http\Resources\V1\User\SpeciesResource;
use App\Models\Species;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SpeciesController extends BaseController
{
    /**
     * return Species List
     * @return JsonResponse
     */
    public function getSpecies()
    {
        try {
            $species = Species::query()
                ->where('activity_status',ActivityStatus::ACTIVE)
                ->orderBy('order')
                ->get();

            return ApiResponse::success('عملیات موفق',SpeciesResource::collection($species));
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }
}
