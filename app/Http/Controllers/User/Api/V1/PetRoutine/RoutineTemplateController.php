<?php

namespace App\Http\Controllers\User\Api\V1\PetRoutine;

use App\Enums\ActivityStatus;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\User\Api\V1\BaseController;
use App\Http\Resources\V1\User\PetRoutines\RoutineTemplateResource;
use App\Models\RoutineTemplate;
use Illuminate\Http\Response;

class RoutineTemplateController extends BaseController
{

    /**
     * @description Show Active Templates List
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $templates = RoutineTemplateResource::collection(
                RoutineTemplate::query()
                    ->where('activity_status', ActivityStatus::ACTIVE->value)
                    ->where('species_id', request()->species_id)
                    ->get()
            );

            return ApiResponse::Success('لیست قالب‌های روتین دریافت شد', $templates);
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در دریافت اطلاعات');
        }
    }


    /**
     * @description Show Single Template
     * @param RoutineTemplate $routine_template
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(RoutineTemplate $routine_template)
    {
        try {

            return ApiResponse::Success(
                'عملیات موفق',
                RoutineTemplateResource::make($routine_template)
            );

        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در دریافت اطلاعات');
        }

    }
}
