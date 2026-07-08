<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Seeder;

class ProductVariationSeeder extends Seeder
{
    public function run(): void
    {
        ProductVariation::query()->delete();

        foreach (Product::all() as $product) {

            switch ($product->slug) {

                case 'ghdhay-khshk-sg-royal-knyn-mini-adult':

                    ProductVariation::create([
                        'product_id' => $product->id,
                        'price' => 650000,
                        'discount_price' => 620000,
                        'stock' => 15,
                        'sku' => 'RC-MINI-1KG',
                        'is_default' => true,
                        'activity_status' => ActivityStatus::ACTIVE->value,
                    ]);

                    ProductVariation::create([
                        'product_id' => $product->id,
                        'price' => 1200000,
                        'discount_price' => 1150000,
                        'stock' => 8,
                        'sku' => 'RC-MINI-2KG',
                        'is_default' => false,
                        'activity_status' => ActivityStatus::ACTIVE->value,
                    ]);

                    break;

                case 'ghdhay-khshk-grbh-rflks-adult':

                    ProductVariation::create([
                        'product_id' => $product->id,
                        'price' => 520000,
                        'discount_price' => 490000,
                        'stock' => 10,
                        'sku' => 'REF-CAT-1KG',
                        'is_default' => true,
                        'activity_status' => ActivityStatus::ACTIVE->value,
                    ]);

                    ProductVariation::create([
                        'product_id' => $product->id,
                        'price' => 980000,
                        'discount_price' => 940000,
                        'stock' => 6,
                        'sku' => 'REF-CAT-2KG',
                        'is_default' => false,
                        'activity_status' => ActivityStatus::ACTIVE->value,
                    ]);

                    break;

                case 'tshoyghy-sg-ba-tam-mrgh':

                    ProductVariation::create([
                        'product_id' => $product->id,
                        'price' => 180000,
                        'discount_price' => null,
                        'stock' => 20,
                        'sku' => 'DOG-TREAT-CHICKEN',
                        'is_default' => true,
                        'activity_status' => ActivityStatus::ACTIVE->value,
                    ]);

                    break;

                case 'ghladh-chrmy-sg':

                    ProductVariation::create([
                        'product_id' => $product->id,
                        'price' => 350000,
                        'discount_price' => null,
                        'stock' => 12,
                        'sku' => 'COLLAR-M',
                        'is_default' => true,
                        'activity_status' => ActivityStatus::ACTIVE->value,
                    ]);

                    ProductVariation::create([
                        'product_id' => $product->id,
                        'price' => 390000,
                        'discount_price' => null,
                        'stock' => 9,
                        'sku' => 'COLLAR-L',
                        'is_default' => false,
                        'activity_status' => ActivityStatus::ACTIVE->value,
                    ]);

                    break;

                case 'asbab-bazy-top-sg':

                    ProductVariation::create([
                        'product_id' => $product->id,
                        'price' => 120000,
                        'discount_price' => null,
                        'stock' => 30,
                        'sku' => 'BALL-RED',
                        'is_default' => true,
                        'activity_status' => ActivityStatus::ACTIVE->value,
                    ]);

                    ProductVariation::create([
                        'product_id' => $product->id,
                        'price' => 120000,
                        'discount_price' => null,
                        'stock' => 25,
                        'sku' => 'BALL-BLUE',
                        'is_default' => false,
                        'activity_status' => ActivityStatus::ACTIVE->value,
                    ]);

                    break;

                case 'khak-grbh-soopr-klamp':

                    ProductVariation::create([
                        'product_id' => $product->id,
                        'price' => 240000,
                        'discount_price' => 220000,
                        'stock' => 18,
                        'sku' => 'CAT-LITTER-10',
                        'is_default' => true,
                        'activity_status' => ActivityStatus::ACTIVE->value,
                    ]);

                    ProductVariation::create([
                        'product_id' => $product->id,
                        'price' => 420000,
                        'discount_price' => 390000,
                        'stock' => 7,
                        'sku' => 'CAT-LITTER-20',
                        'is_default' => false,
                        'activity_status' => ActivityStatus::ACTIVE->value,
                    ]);

                    break;
            }
        }
    }
}
