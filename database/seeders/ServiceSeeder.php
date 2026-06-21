<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('services')->insert([
            [
                'id' => 1,
                'name' => 'معاینه دامپزشکی',
                'name_en' => 'Veterinary Checkup',
                'description' => 'معاینه عمومی حیوان خانگی.',
                'icon' => 'stethoscope',
                'image' => null,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'آرایش و شستشو',
                'name_en' => 'Grooming',
                'description' => 'شستشو و آرایش حیوان خانگی.',
                'icon' => 'scissors',
                'image' => null,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
