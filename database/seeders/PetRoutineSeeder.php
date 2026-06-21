<?php

namespace Database\Seeders;

use App\Enums\RoutineStatuses;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetRoutineSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pet_routines')->insert([
            [
                'pet_id' => 1,
                'routine_template_id' => 1,
                'interval_days' => 180,
                'start_date' => now()->toDateString(),
                'last_done_at' => now()->subMonths(5),
                'next_due_at' => now()->addMonth(),
                'notification_enabled' => true,
                'routine_status' => RoutineStatuses::UPCOMING->value,
                'settings' => json_encode(['notify_channel' => 'sms']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pet_id' => 2,
                'routine_template_id' => 2,
                'interval_days' => 30,
                'start_date' => now()->toDateString(),
                'last_done_at' => now()->subDays(20),
                'next_due_at' => now()->addDays(10),
                'notification_enabled' => true,
                'routine_status' => RoutineStatuses::DUE_SOON->value,
                'settings' => json_encode(['notify_channel' => 'push']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
