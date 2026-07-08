<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeOption;
use Illuminate\Database\Seeder;

class AttributeOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [

            'color' => [
                'قرمز',
                'آبی',
                'سبز',
                'زرد',
                'مشکی',
                'سفید',
                'طوسی',
                'قهوه‌ای',
            ],

            'weight' => [
                '500 گرم',
                '1 کیلوگرم',
                '2 کیلوگرم',
                '3 کیلوگرم',
                '5 کیلوگرم',
                '10 کیلوگرم',
                '15 کیلوگرم',
                '20 کیلوگرم',
            ],

            'size' => [
                'XS',
                'S',
                'M',
                'L',
                'XL',
                'XXL',
            ],

            'flavor' => [
                'مرغ',
                'گوشت',
                'ماهی',
                'بوقلمون',
                'بره',
                'اردک',
                'سالمون',
            ],

            'age' => [
                'توله',
                'جونیور',
                'بالغ',
                'مسن',
            ],

            'material' => [
                'پارچه',
                'پلاستیک',
                'سیلیکون',
                'فلز',
                'چرم',
                'استیل',
                'چوب',
            ],

            'breed' => [
                'همه نژادها',
                'نژاد کوچک',
                'نژاد متوسط',
                'نژاد بزرگ',
            ],

            'package-type' => [
                'کیسه',
                'قوطی',
                'کنسرو',
                'پاکت',
                'بسته',
            ],

        ];

        foreach ($options as $attributeSlug => $attributeOptions) {

            $attribute = Attribute::query()
                ->where('slug', $attributeSlug)
                ->first();

            if (! $attribute) {
                continue;
            }

            foreach ($attributeOptions as $index => $label) {

                AttributeOption::query()->updateOrCreate(
                    [
                        'attribute_id' => $attribute->id,
                        'slug' => str($label)->slug(),
                    ],
                    [
                        'label' => $label,
                        'value' => $label,
                        'sort_order' => $index + 1,
                    ]
                );
            }
        }
    }
}
