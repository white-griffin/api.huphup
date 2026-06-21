<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessOffDaySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('business_off_days')->insert([
            [
                'business_id' => 1,
                'date' => now()->addDays(10)->toDateString(),
                'reason' => 'تعطیلی مناسبتی',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'business_id' => 2,
                'date' => now()->addDays(11)->toDateString(),
                'reason' => 'انبارگردانی',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
