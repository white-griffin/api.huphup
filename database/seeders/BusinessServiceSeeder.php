<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessServiceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('business_services')->insert([
            [
                'business_id' => 1,
                'service_id' => 1,
                'price' => 250000,
                'duration' => 30,
                'settings' => json_encode(['requires_appointment' => true]),
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'business_id' => 2,
                'service_id' => 2,
                'price' => 400000,
                'duration' => 60,
                'settings' => json_encode(['requires_appointment' => true, 'includes' => ['bath', 'nail_trim']]),
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
