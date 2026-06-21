<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserAddressSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user_addresses')->insert([
            [
                'user_id' => 1,
                'province_id' => 1,
                'city_id' => 1,
                'address' => 'آدرس نمونه کاربر، پلاک ۱',
                'postal_code' => '1234567890',
                'latitude' => 37.7588890,
                'longitude' => 45.9783330,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'province_id' => 1,
                'city_id' => 2,
                'address' => 'آدرس دوم کاربر، پلاک ۲',
                'postal_code' => '1234567891',
                'latitude' => 37.9158330,
                'longitude' => 46.1236110,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
