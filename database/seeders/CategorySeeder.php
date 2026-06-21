<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use App\Enums\CategoryTypes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'id' => 1,
                'parent_id' => null,
                'name' => 'غذای حیوانات',
                'slug' => 'pet-food',
                'image' => null,
                'type' => CategoryTypes::PRODUCT->value,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'parent_id' => null,
                'name' => 'خدمات سلامتی',
                'slug' => 'health-services',
                'image' => null,
                'type' => CategoryTypes::SERVICE->value,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
