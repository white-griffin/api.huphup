<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatuses;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('appointments')->insert([
            [
                'business_id' => 1,
                'user_id' => 1,
                'service_id' => 1,
                'pet_id' => 1,
                'date' => now()->addDays(2)->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '09:30:00',
                'service_price' => 250000,
                'service_duration' => 30,
                'status' => AppointmentStatuses::CONFIRMED->value,
                'notes' => 'معاینه عمومی رکس',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'business_id' => 2,
                'user_id' => 1,
                'service_id' => 2,
                'pet_id' => 2,
                'date' => now()->addDays(3)->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'service_price' => 400000,
                'service_duration' => 60,
                'status' => AppointmentStatuses::PENDING->value,
                'notes' => 'آرایش و شستشوی لونا',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
