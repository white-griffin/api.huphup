<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpeciesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('species')->insert([
            [
                'id' => 1,
                'name_en' => 'dog',
                'name_fa' => 'سگ',
                'slug' => 'dog',
                'icon' => '🐶',
                'image' => null,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name_en' => 'cat',
                'name_fa' => 'گربه',
                'slug' => 'cat',
                'icon' => '🐱',
                'image' => null,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name_en' => 'bird',
                'name_fa' => 'پرنده',
                'slug' => 'bird',
                'icon' => '🐦',
                'image' => null,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
