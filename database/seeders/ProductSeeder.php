<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use App\Enums\PublicationStatus;
use App\Models\Brand;
use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::query()->first();

        if (! $business) {
            return;
        }

        $products = [

            [
                'name' => 'غذای خشک سگ رویال کنین Mini Adult',
                'brand' => 'Royal Canin',
                'categories' => ['dog-food'],
                'description' => 'غذای خشک مناسب سگ‌های نژاد کوچک.',
            ],

            [
                'name' => 'غذای خشک گربه رفلکس Adult',
                'brand' => 'Reflex',
                'categories' => ['cat-food'],
                'description' => 'غذای کامل گربه بالغ.',
            ],

            [
                'name' => 'تشویقی سگ با طعم مرغ',
                'brand' => 'Reflex',
                'categories' => ['dog-treat'],
                'description' => 'تشویقی مناسب آموزش سگ.',
            ],

            [
                'name' => 'قلاده چرمی سگ',
                'brand' => 'Trixie',
                'categories' => ['dog-collar'],
                'description' => 'قلاده چرمی مقاوم.',
            ],

            [
                'name' => 'اسباب بازی توپ سگ',
                'brand' => 'Trixie',
                'categories' => ['dog-toy'],
                'description' => 'توپ لاستیکی مقاوم.',
            ],

            [
                'name' => 'خاک گربه سوپر کلامپ',
                'brand' => 'Catsan',
                'categories' => ['cat-litter'],
                'description' => 'خاک گربه بدون گرد و غبار.',
            ],

        ];

        foreach ($products as $item) {

            $brand = Brand::query()
                ->where('name', $item['brand'])
                ->first();

            if (! $brand) {
                continue;
            }

            $product = Product::query()->updateOrCreate(

                [
                    'slug' => Str::slug($item['name']),
                ],

                [
                    'business_id' => $business->id,
                    'brand_id' => $brand->id,

                    'name' => $item['name'],
                    'description' => $item['description'],

                    'publication_status' => PublicationStatus::PUBLISHED->value,
                    'activity_status' => ActivityStatus::ACTIVE->value,
                ]

            );

            $categoryIds = Category::query()
                ->whereIn('slug', $item['categories'])
                ->pluck('id');

            $product->categories()->sync($categoryIds);
        }
    }
}
