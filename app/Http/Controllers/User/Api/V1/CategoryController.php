<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Enums\ActivityStatus;
use App\Enums\PublicationStatus;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $categories = CategoryResource::collection(
                Category::query()
                    ->where('activity_status', ActivityStatus::ACTIVE->value)
                    ->whereNull('parent_id')
                    ->when(request()->filled('type'),
                        fn($q) => $q->where('type', request()->query('type'))
                    )
                    ->with('children')
                    ->get()
            );
            return ApiResponse::success('عملیات موفق', $categories);
        } catch (\Exception $exception) {
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در دریافت اطلاعات');
        }
    }

    public function show($slug): JsonResponse
    {
        try {
            $category = CategoryResource::make(
                Category::query()
                    ->where('slug', $slug)
                    ->with([
                        'children', 'products' => function ($q) {
                            $q->where('publication_status', PublicationStatus::PUBLISHED->value)
                                ->with('primaryImage');
                        }])
                    ->first()
            );
            return ApiResponse::success('عملیات موفق', $category);
        } catch (\Exception $exception) {
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در دریافت اطلاعات');
        }
    }
}
