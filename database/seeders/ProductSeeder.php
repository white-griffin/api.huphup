<?php

namespace Database\Seeders;

use App\Enums\PublicationStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'id' => 1,
                'business_id' => 2,
                'brand_id' => 1,
                'name' => 'غذای خشک سگ بالغ',
                'slug' => 'adult-dog-dry-food',
                'description' => 'غذای خشک مناسب سگ‌های بالغ.',
                'price' => 950000,
                'discount_price' => 890000,
                'stock' => 20,
                'sku' => 'DOG-FOOD-001',
                'publication_status' => PublicationStatus::PUBLISHED->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'business_id' => 2,
                'brand_id' => 2,
                'name' => 'غذای خشک گربه بالغ',
                'slug' => 'adult-cat-dry-food',
                'description' => 'غذای خشک مناسب گربه‌های بالغ.',
                'price' => 780000,
                'discount_price' => null,
                'stock' => 15,
                'sku' => 'CAT-FOOD-001',
                'publication_status' => PublicationStatus::PUBLISHED->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
