<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleBreakSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('schedule_breaks')->insert([
            [
                'schedule_id' => 1,
                'start_time' => '13:00:00',
                'end_time' => '14:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'schedule_id' => 2,
                'start_time' => '14:00:00',
                'end_time' => '15:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
