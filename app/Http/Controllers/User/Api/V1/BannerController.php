<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Enums\BannerPlacement;
use App\Http\Resources\V1\User\BannerResource;
use App\Services\Banner\BannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BannerController extends Controller
{
    public function index(
        BannerPlacement $placement,
        BannerService $bannerService,
    ): JsonResponse
    {
        try {

            $banners = $bannerService->getForPlacement($placement);
            return ApiResponse::success('عملیات موفق',BannerResource::collection($banners));

        }catch (\Exception $exception){

            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage());
        }
    }
}
