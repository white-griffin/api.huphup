<?php

namespace Database\Seeders;

use App\Enums\MessageTypes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('messages')->insert([
            [
                'conversation_id' => 1,
                'sender_id' => 1,
                'body' => 'سلام، وضعیت نوبت من مشخص شده؟',
                'type' => MessageTypes::TEXT->value,
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'conversation_id' => 2,
                'sender_id' => 1,
                'body' => 'برای تغذیه روزانه گربه بالغ چه پیشنهادی دارید؟',
                'type' => MessageTypes::TEXT->value,
                'read_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);
    }
}
