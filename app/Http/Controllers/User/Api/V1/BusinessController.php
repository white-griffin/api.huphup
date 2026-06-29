<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Enums\ActivityStatus;
use App\Enums\VerificationStatuses;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\BusinessResource;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        try {
            $businesses = BusinessResource::collection(
                Business::query()
                    ->where('verification_status', VerificationStatuses::ACTIVE->value)
                    ->where('activity_status', ActivityStatus::ACTIVE->value)
                    ->when($request->type, function ($q) use ($request) {
                        $q->where('business_type', $request->type);
                    })
                    ->cursorPaginate(10)
            );
            return ApiResponse::success('عملیات موفق', $businesses);
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage());
        }
    }


    public function show(Business $business)
    {
        try {
            return ApiResponse::success('عملیات موفق', BusinessResource::make($business));
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage());
        }
    }
}
