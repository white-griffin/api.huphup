<?php

namespace App\Http\Controllers\Provider\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Provider\Products\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\MediaService;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = ProductResource::collection(
                Product::query()
                    ->with(['activeVariations', 'images', 'categories', 'brand'])
                    ->paginate()
            );

            return ApiResponse::success('عملیات موفق', $products);
        } catch (\Exception $exception) {
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در دریافت اطلاعات');
        }
    }
    public function show(Product $product)
    {
        try {
            $product = ProductResource::make($product);
            return ApiResponse::Success('عملیات موفق', $product);
        } catch (\Exception $exception) {
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در دریافت اطلاعات');
        }
    }
    public function store()
    {
        $data = $this->productData();

        $variations = $data['variations'] ?? [];
        $categories = $data['categories'] ?? [];

        // حذف فیلدهایی که نباید وارد جدول products بشن
        $productData = Arr::except($data, ['variations', 'categories']);

        try {
            DB::transaction(function () use ($productData, $variations, $categories) {

                $product = Product::query()->create($productData);

                if (request()->hasFile('images')) {
                    $this->storeProductImages($product);
                }

                $this->setCategories($product, $categories);

                if (!empty($variations)) {
                    $this->syncVariations($product, $variations);
                }
            });
            return ApiResponse::success('عملیات موفق');
        } catch (\Exception $exception) {
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }
    public function update(Product $product)
    {
        $data = $this->productData($product);

        $variations = $data['variations'] ?? [];
        $categories = $data['categories'] ?? [];
        $productData = Arr::except($data, ['variations', 'categories']);

        try {
            DB::transaction(function () use ($product, $productData, $variations, $categories) {

                $product->update($productData);

                if (request()->hasFile('images')) {
                    $this->storeProductImages($product);
                }

                $this->setCategories($product, $categories);

                if (!empty($variations)) {
                    $this->syncVariations($product, $variations);
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
            'brand_id' => ['required'],
            'name' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => [$required, 'numeric'],
            'discount_price' => ['nullable', 'numeric'],
            'stock' => [$required, 'numeric'],
            'sku' => ['nullable', 'string', 'max:255'],
            'images' => ['sometimes', 'array'],
            'images.*' => ['image', 'max:2048'],
            'categories' => ['required', 'array'],
            'categories.*' => [
                'required',
                Rule::exists('categories', 'slug')
            ],
            'variations' => ['sometimes', 'array'],
            'variations.*.sku' => ['nullable', 'string', 'max:255'],
            'variations.*.price' => ['required_with:variations', 'numeric'],
            'variations.*.stock' => ['required_with:variations', 'integer'],
            'variations.*.is_active' => ['boolean'],
            'variations.*.attributes' => ['required_with:variations', 'array'],
        ], [
            'name.required' => 'نام محصول را وارد کنید',
            'name.max' => 'نام محصول طولانی تر از حد مجاز است',
            'price.required' => 'قیمت را وارد کنید',
            'price.numeric' => 'فرمت قیمت محصول باید عدد باشد',
            'stock.required' => 'تعداد موجودی را وارد کنید',
            'stock.numeric' => 'فرمت موجودی باید عدد باشد',
            'categories.required' => 'دسته بندی ها را انتخاب کنید',
        ]);

        $variations = $data['variations'] ?? [];
        unset($data['variations']);

        return ['product' => array_filter($data, fn($v) => !is_null($v)), 'variations' => $variations];

    }
    private function setCategories($product, $categories)
    {
        $categoryIds = Category::query()
            ->whereIn('slug', $categories)
            ->pluck('id')
            ->toArray();

        $product->categories()->sync($categoryIds);
    }
    private function syncVariations(Product $product, array $variations): void
    {
        // در update، variationهای قدیمی حذف میشن و جدید جایگزین میشن
        $product->variations()->delete();

        $product->variations()->createMany($variations);
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

        } catch (\Exception $exception) {
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
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
        } catch (\Exception $exception) {
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
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
        } catch (\Exception $exception) {
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
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
        } catch (\Exception $exception) {
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }


}
