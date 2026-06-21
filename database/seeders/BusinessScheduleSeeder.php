<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use App\Enums\DaysOfWeek;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessScheduleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('business_schedules')->insert([
            [
                'id' => 1,
                'business_id' => 1,
                'day_of_week' => DaysOfWeek::SATURDAY->value,
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'slot_duration' => 30,
                'capacity' => 2,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'business_id' => 2,
                'day_of_week' => DaysOfWeek::SUNDAY->value,
                'start_time' => '10:00:00',
                'end_time' => '18:00:00',
                'slot_duration' => 60,
                'capacity' => 1,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
