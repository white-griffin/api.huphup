<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use App\Enums\RoutineCategoryTypes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoutineTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('routine_templates')->insert([
            [
                'id' => 1,
                'title' => 'واکسیناسیون دوره‌ای',
                'species_id' => 1,
                'routine_category' => RoutineCategoryTypes::HEALTH->value,
                'default_interval_days' => 180,
                'reminder_days_before' => 7,
                'image' => null,
                'description' => 'یادآوری واکسیناسیون دوره‌ای سگ.',
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'title' => 'برنامه شستشو',
                'species_id' => 2,
                'routine_category' => RoutineCategoryTypes::CARE->value,
                'default_interval_days' => 30,
                'reminder_days_before' => 3,
                'image' => null,
                'description' => 'یادآوری شستشو و رسیدگی به گربه.',
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
