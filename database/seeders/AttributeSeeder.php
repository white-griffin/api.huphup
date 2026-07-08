<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use App\Models\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [

            [
                'name' => 'رنگ',
                'slug' => 'color',
                'is_filterable' => true,
                'display_order' => 1,
            ],

            [
                'name' => 'وزن',
                'slug' => 'weight',
                'is_filterable' => true,
                'display_order' => 2,
            ],

            [
                'name' => 'سایز',
                'slug' => 'size',
                'is_filterable' => true,
                'display_order' => 3,
            ],

            [
                'name' => 'طعم',
                'slug' => 'flavor',
                'is_filterable' => true,
                'display_order' => 4,
            ],

            [
                'name' => 'سن',
                'slug' => 'age',
                'is_filterable' => true,
                'display_order' => 5,
            ],

            [
                'name' => 'جنس',
                'slug' => 'material',
                'is_filterable' => true,
                'display_order' => 6,
            ],

            [
                'name' => 'نژاد',
                'slug' => 'breed',
                'is_filterable' => true,
                'display_order' => 7,
            ],

            [
                'name' => 'نوع بسته بندی',
                'slug' => 'package-type',
                'is_filterable' => true,
                'display_order' => 8,
            ],

        ];

        foreach ($attributes as $attribute) {

            Attribute::query()->updateOrCreate(
                ['slug' => $attribute['slug']],
                [
                    'name' => $attribute['name'],
                    'is_filterable' => $attribute['is_filterable'],
                    'display_order' => $attribute['display_order'],
                    'activity_status' => ActivityStatus::ACTIVE->value,
                ]
            );
        }
    }
}
