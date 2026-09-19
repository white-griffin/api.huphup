<?php

namespace App\Http\Controllers\Provider\Api\V1\Product;

use App\Enums\ActivityStatus;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Provider\Api\V1\BaseController;
use App\Http\Resources\V1\Provider\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Response;

class CategoryController extends BaseController
{
    public function index()
    {
        try {
            $categories = Category::query()
                ->where(
                    'activity_status',
                    ActivityStatus::ACTIVE->value
                )
                ->with([
                    'activeChildren',
                    'parent',
                ])
                ->paginate();

            $categoryMap = Category::query()
                ->get([
                    'id',
                    'parent_id',
                    'name',
                ])
                ->keyBy('id');

            $categories->getCollection()->transform(
                function ($category) use ($categoryMap) {
                    $names = [];
                    $current = $category;

                    while ($current) {
                        $names[] = $current->name;

                        $current = $categoryMap->get(
                            $current->parent_id
                        );
                    }

                    $category->breadcrumb = implode(
                        ' > ',
                        array_reverse($names)
                    );

                    return $category;
                }
            );

            return ApiResponse::success(
                'عملیات موفق',
                CategoryResource::collection($categories)
            );
        } catch (\Exception $exception) {
            report($exception);

            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'خطا در دریافت اطلاعات'
            );
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
