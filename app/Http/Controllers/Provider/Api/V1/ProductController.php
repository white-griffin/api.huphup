<?php

namespace App\Http\Controllers\Provider\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Provider\Products\ProductResource;
use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\MediaService;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = ProductResource::collection(
                Product::query()
                    ->with([
                        'activeVariations.variationAttributes.attribute',
                        'activeVariations.variationAttributes.option',
                        'images',
                        'categories',
                        'brand',
                    ])
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
            $showProduct = ProductResource::make(
                $product->load([
                    'activeVariations.variationAttributes.attribute',
                    'activeVariations.variationAttributes.option',
                    'images',
                    'categories',
                    'brand',
                ])
            );
            return ApiResponse::Success('عملیات موفق', $showProduct);
        } catch (\Exception $exception) {
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در دریافت اطلاعات');
        }
    }

    public function store()
    {
        $data = $this->productData();

        $variations = $data['variations'];
        $categories = $data['categories'];

        $productData = Arr::except($data, [
            'variations',
            'categories',
        ]);

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

        $variations = $data['variations'];
        $categories = $data['categories'];

        $productData = Arr::except($data, [
            'variations',
            'categories',
        ]);

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
            'images' => ['sometimes', 'array'],
            'images.*' => ['image', 'max:2048'],
            'categories' => ['required', 'array'],
            'categories.*' => [
                'required',
                Rule::exists('categories', 'slug')
            ],
            'variations' => ['required', 'array', 'min:1'],

            'variations.*.price' => ['required', 'numeric'],
            'variations.*.discount_price' => ['nullable', 'numeric'],
            'variations.*.stock' => ['required', 'integer'],
            'variations.*.sku' => ['nullable', 'string', 'max:255'],
            'variations.*.is_default' => ['required', 'boolean'],
            'variations.*.activity_status' => ['integer'],

            'variations.*.attributes' => ['required', 'array', 'min:1'],

            'variations.*.attributes.*.attribute_id' => [
                'required',
                Rule::exists('attributes', 'id'),
            ],

            'variations.*.attributes.*.attribute_option_id' => [
                'required',
                Rule::exists('attribute_options', 'id'),
            ],
        ], [
            'name.required' => 'نام محصول را وارد کنید',
            'name.max' => 'نام محصول طولانی تر از حد مجاز است',
            'categories.required' => 'دسته بندی ها را انتخاب کنید',
        ]);

        $variations = $data['variations'] ?? [];
        unset($data['variations']);

        $data['variations'] = $variations;

        return array_filter($data, fn($v) => !is_null($v));

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
        $signatures = collect();

        $defaultCount = collect($variations)
            ->where('is_default', true)
            ->count();

        if ($defaultCount !== 1) {
            throw ValidationException::withMessages([
                'variations' => 'Exactly one default variation is required.',
            ]);
        }

        $validOptions = AttributeOption::query()
            ->pluck('attribute_id', 'id');

        foreach ($variations as $variationData) {

            $attributes = $variationData['attributes'] ?? [];
            unset($variationData['attributes']);

            if (count($attributes) !== collect($attributes)->pluck('attribute_id')->unique()->count()) {
                throw ValidationException::withMessages([
                    'variations' => 'Duplicate attributes in one variation.',
                ]);
            }

            $signature = collect($attributes)
                ->sortBy('attribute_id')
                ->map(fn ($item) => $item['attribute_id'] . ':' . $item['attribute_option_id'])
                ->implode('|');

            if ($signatures->contains($signature)) {
                throw ValidationException::withMessages([
                    'variations' => 'Duplicate variation.',
                ]);
            }

            $signatures->push($signature);

            foreach ($attributes as $attribute) {

                $optionId = $attribute['attribute_option_id'];
                $attributeId = $attribute['attribute_id'];

                if (
                    !isset($validOptions[$optionId]) ||
                    (int) $validOptions[$optionId] !== (int) $attributeId
                ) {
                    throw ValidationException::withMessages([
                        'variations' => 'Attribute option is invalid.',
                    ]);
                }
            }
        }

        $product->variations()->delete();

        foreach ($variations as $variationData) {

            $attributes = $variationData['attributes'] ?? [];
            unset($variationData['attributes']);

            $variation = $product->variations()->create($variationData);

            $variation->attributes()->createMany(
                collect($attributes)
                    ->map(fn ($attribute) => [
                        'attribute_id' => $attribute['attribute_id'],
                        'attribute_option_id' => $attribute['attribute_option_id'],
                    ])
                    ->toArray()
            );
        }
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
