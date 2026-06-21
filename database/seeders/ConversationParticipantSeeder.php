<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConversationParticipantSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('conversation_participants')->insert([
            [
                'conversation_id' => 1,
                'user_id' => 1,
                'joined_at' => now(),
                'last_read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'conversation_id' => 2,
                'user_id' => 1,
                'joined_at' => now(),
                'last_read_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
