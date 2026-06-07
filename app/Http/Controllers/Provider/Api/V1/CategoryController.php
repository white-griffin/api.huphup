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
                Category::query()->paginate()
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

    /**
     * @throws \Throwable
     */
    public function store()
    {
        $data = $this->categoryData();

        try {

            DB::transaction(function () use ($data) {
                Category::query()->create($data);
            });

            return ApiResponse::success('عملیات موفق');

        } catch (\Exception $exception) {
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }

    /**
     * @throws \Throwable
     */
    public function update(Category $category)
    {
        $data = $this->categoryData($category);

        try {
            DB::transaction(function () use ($category, $data) {
                $category->update($data);
            });
            return ApiResponse::success('عملیات موفق');
        } catch (\Exception $exception) {
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }


    private function categoryData($category = null)
    {
        $media = app(MediaService::class);
        $required = $category ? 'sometimes' : 'required';
        $data = request()->validate([
            'name' => [$required, 'string', 'max:100'],
            'parent' => [
                'nullable',
                Rule::exists('categories', 'id')
                    ->where('business_id', business()->id)
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'name.required' => 'نام دسته بندی را وارد کنید',
            'name.max' => 'نام دسته بندی طولانی تر از حد مجاز است',
            'image.image' => 'فایل تصویر باید تصویر باشد',
            'image.mimes' => 'فرمت تصویر معتبر نیست',
            'image.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد',
        ]);

        $data = array_filter(
            $data,
            fn($value) => !is_null($value)
        );

        if (request()->hasFile('image')) {
            $data['image'] = $media->replace(
                $category?->image,
                request()->file('image'),
                'category/images'
            );
        }

        return $data;
    }
}
