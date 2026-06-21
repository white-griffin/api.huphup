<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoutineActionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('routine_actions')->insert([
            [
                'routine_template_id' => 1,
                'target_type' => 'service',
                'target_id' => 1,
                'priority' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'routine_template_id' => 2,
                'target_type' => 'product',
                'target_id' => 2,
                'priority' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
