<?php

namespace App\Http\Controllers\Provider\Api\V1;

use App\Enums\ActivityStatus;
use App\Helpers\Api\ApiResponse;
use App\Http\Resources\V1\User\CategoryResource;
use App\Models\Category;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends BaseController
{
    public function index()
    {
        try {
            $categories = CategoryResource::collection(
                Category::query()
                    ->where('activity_status',ActivityStatus::ACTIVE->value)
                    ->with(['children','parent'])
                    ->paginate()
            );
            return ApiResponse::success('عملیات موفق', $categories);
        } catch (\Exception $exception) {
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در دریافت اطلاعات');
        }
    }

    public function show($slug)
    {
        try {
            $category = CategoryResource::make(
                Category::query()
                    ->where('slug', $slug)
                    ->first()
            );
            return ApiResponse::success('عملیات موفق', $category);
        } catch (\Exception $exception) {
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در دریافت اطلاعات');
        }
    }

}
