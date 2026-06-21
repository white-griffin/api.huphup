<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductVariationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_variations')->insert([
            [
                'product_id' => 1,
                'price' => 950000,
                'discount_price' => 890000,
                'stock' => 10,
                'sku' => 'DOG-FOOD-001-2KG',
                'attributes' => json_encode(['وزن' => '۲ کیلوگرم']),
                'is_default' => true,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 2,
                'price' => 780000,
                'discount_price' => null,
                'stock' => 8,
                'sku' => 'CAT-FOOD-001-2KG',
                'attributes' => json_encode(['وزن' => '۲ کیلوگرم']),
                'is_default' => true,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
