<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $images = [

            'ghdhay-khshk-sg-royal-knyn-mini-adult' => [
                'products/adult-dog-dry-food.jpg',
            ],

            'ghdhay-khshk-grbh-rflks-adult' => [
                'products/adult-cat-dry-food.jpg',
            ],

            'tshoyghy-sg-ba-tam-mrgh' => [
                'products/dog-treat.jpg',
            ],

            'ghladh-chrmy-sg' => [
                'products/dog-collar.jpg',
            ],

            'asbab-bazy-top-sg' => [
                'products/dog-ball.jpg',
            ],

            'khak-grbh-soopr-klamp' => [
                'products/cat-litter.jpg',
            ],

        ];

        foreach ($images as $slug => $productImages) {

            $product = Product::query()
                ->where('slug', $slug)
                ->first();

            if (! $product) {
                continue;
            }

            foreach ($productImages as $index => $image) {

                $product->images()->updateOrCreate(
                    [
                        'name' => $image,
                    ],
                    [
                        'is_primary' => $index === 0,
                        'order' => $index + 1,
                    ]
                );
            }
        }
    }
}
