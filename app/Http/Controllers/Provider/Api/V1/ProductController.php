<?php

namespace App\Http\Controllers\Provider\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Provider\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\MediaService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = ProductResource::collection(
                Product::query()->paginate()
            );

            return ApiResponse::success('عملیات موفق', $products);
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در دریافت اطلاعات');
        }
    }
    public function show(Product $product)
    {
        try {
            $product = ProductResource::make($product);
            return ApiResponse::Success('عملیات موفق', $product);
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در دریافت اطلاعات');
        }
    }

    public function store()
    {
        $data = $this->productData();
        try {
            DB::transaction(function () use ($data) {

                $product = Product::query()->create($data);

                if (request()->hasFile('images')) {
                    $this->storeProductImages($product);
                }

                $this->setCategories($product, $data['categories']);

            });
            return ApiResponse::success('عملیات موفق');
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }

    public function update(Product $product)
    {
        $data = $this->productData($product);

        try {
            DB::transaction(function () use ($product, $data) {

                $product->update($data);

                if (request()->hasFile('images')) {
                    $this->storeProductImages($product);
                }

                $this->setCategories($product, $data['categories']);
            });
            return ApiResponse::success('عملیات موفق');
        } catch (\Exception $exception) {
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }
    private function productData($product = null)
    {
        $required = $product ? 'sometimes' : 'required';
        $data = request()->validate([
            'brand_id' => ['required'],
            'name' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => [$required, 'numeric'],
            'discount_price' => ['nullable', 'numeric'],
            'stock' => [$required, 'numeric'],
            'sku' => ['nullable', 'string', 'max:255'],
            'attributes' => ['nullable', 'array'],
            'images' => ['sometimes','array'],
            'images.*' => ['image','max:2048'],
            'categories' => ['required','array'],
            'categories.*' => [
                'required',
                Rule::exists('categories','slug')
            ]
        ],[
            'name.required' => 'نام محصول را وارد کنید',
            'name.max' => 'نام محصول طولانی تر از حد مجاز است',
            'price.required' => 'قیمت را وارد کنید',
            'price.numeric' => 'فرمت قیمت محصول باید عدد باشد',
            'stock.required' => 'تعداد موجودی را وارد کنید',
            'stock.numeric' => 'فرمت موجودی باید عدد باشد',
            'categories.required' => 'دسته بندی ها را انتخاب کنید',
        ]);

        return array_filter(
            $data,
            fn($value) => !is_null($value)
        );
    }

    private function setCategories($product, $categories)
    {
        $categoryIds = Category::query()
            ->whereIn('slug', $categories)
            ->pluck('id')
            ->toArray();

        $product->categories()->sync($categoryIds);
    }

    /* {{ --- Image Controll Section --- }} */
    private function storeProductImages($product)
    {
        if (!request()->hasFile('images')) {
            return;
        }

        $media = app(MediaService::class);

        $images = [];

        $lastOrder = $product->images()->max('order') ?? -1;
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach (request()->file('images') as $index => $image) {

            $path = $media->upload($image, 'product/images');

            $images[] = [
                'name' => $path,
                'is_primary' => !$hasPrimary && $index === 0,
                'order' => $lastOrder + $index + 1
            ];
        }

        $product->images()->createMany($images);
    }
    public function uploadImages(Product $product)
    {
        request()->validate([
            'images' => 'required|array',
            'images.*' => 'image|max:2048'
        ]);

        try {
            DB::transaction(function () use ($product) {

                $media = app(MediaService::class);

                $lastOrder = $product->images()->max('order') ?? -1;

                $hasPrimary = $product->images()->where('is_primary', true)->exists();

                foreach (request()->file('images') as $index => $file) {

                    $path = $media->upload($file, 'product/images');

                    $product->images()->create([
                        'name' => $path,
                        'order' => $lastOrder + $index + 1,
                        'is_primary' => !$hasPrimary && $index === 0
                    ]);

                    $hasPrimary = true;
                }
            });

            return ApiResponse::success('تصاویر اضافه شد');

        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }
    public function deleteImage(Product $product, ProductImage $image)
    {
        abort_if($image->product_id !== $product->id, 403);

        try {
            DB::transaction(function () use ($image, $product) {

                $wasPrimary = $image->is_primary;

                $image->delete();

                if ($wasPrimary) {
                    $product->images()
                        ->orderBy('order')
                        ->first()?->update(['is_primary' => true]);
                }
            });

            return ApiResponse::success('تصویر حذف شد');
        }catch(\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }
    public function setPrimary(Product $product, ProductImage $image)
    {
        abort_if($image->product_id !== $product->id, 403);

        try {
            DB::transaction(function () use ($product, $image) {

                $image->update(['is_primary' => true]);

                $product->images()
                    ->whereKeyNot($image->id)
                    ->update(['is_primary' => false]);
            });

            return ApiResponse::success('تصویر شاخص تغییر کرد');
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }
    public function reorder()
    {
        request()->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer',
            'orders.*.order' => 'required|integer'
        ]);

        try {
            DB::transaction(function () {

                foreach (request('orders') as $item) {

                    ProductImage::query()
                        ->where('id', $item['id'])
                        ->update(['order' => $item['order']]);
                }
            });

            return ApiResponse::success('ترتیب بروزرسانی شد');
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }



}
