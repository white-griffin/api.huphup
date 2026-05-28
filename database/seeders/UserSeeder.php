<?php

namespace Database\Seeders;

use App\Enums\GenderType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->create([
            'first_name' => 'محمدامین',
            'last_name' => 'زنگوئی',
            'mobile' => '09391937554',
            'email' => 'mohamadamn.zanguee@gmail.com',
            'password' => Hash::make('password'),
            'national_code' => '0924231602',
            'gender_type' => GenderType::MALE->value,
        ]);
    }
}
