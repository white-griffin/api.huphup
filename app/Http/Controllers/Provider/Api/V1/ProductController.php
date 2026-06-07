<?php

namespace App\Http\Controllers\Provider\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\MediaService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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
            'name' => [$required, 'string', 'max:255'],
            'price' => [$required, 'numeric'],
            'stock' => [$required, 'numeric'],
            'images' => ['sometimes','array'],
            'images.*' => ['image','max:2048']
        ],[
            'name.required' => 'نام محصول را وارد کنید',
            'name.max' => 'نام محصول طولانی تر از حد مجاز است',
            'price.required' => 'قیمت را وارد کنید',
            'price.numeric' => 'فرمت قیمت محصول باید عدد باشد',
            'stock.required' => 'تعداد موجودی را وارد کنید',
            'stock.numeric' => 'فرمت موجودی باید عدد باشد',
        ]);

        return array_filter(
            $data,
            fn($value) => !is_null($value)
        );
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

            $path = $media->store($image, 'product/images');

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

                    $path = $media->store($file, 'product/images');

                    $product->images()->create([
                        'name' => $path,
                        'order' => $lastOrder + $index + 1,
                        'is_primary' => !$hasPrimary && $index === 0
                    ]);
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

                $product->images()->update(['is_primary' => false]);

                $image->update(['is_primary' => true]);
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
