<?php

namespace Database\Seeders;

use App\Enums\AccessStatuses;
use App\Enums\ActivityStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('conversations')->insert([
            [
                'id' => 1,
                'type' => AccessStatuses::PRIVATE->value,
                'name' => 'پشتیبانی سفارش',
                'image' => null,
                'created_by' => 1,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'type' => AccessStatuses::PUBLIC->value,
                'name' => 'راهنمای نگهداری حیوانات',
                'image' => null,
                'created_by' => 1,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);
    }
}
