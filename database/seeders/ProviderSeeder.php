<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use App\Enums\GenderType;
use App\Enums\VerificationStatuses;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('providers')->insert([
            [
                'id' => 1,
                'first_name' => 'علی',
                'last_name' => 'رضایی',
                'national_code' => '0012345678',
                'father_name' => 'حسن',
                'birth_date' => '1988-03-12',
                'gender_type' => GenderType::MALE->value,
                'mobile' => '09120000001',
                'mobile_verified_at' => now(),
                'email' => 'provider1@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'two_factor_status' => ActivityStatus::INACTIVE->value,
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
                'remember_token' => null,
                'province_id' => 1,
                'city_id' => 1,
                'postal_code' => '1234567890',
                'address' => 'آدرس نمونه ارائه‌دهنده اول',
                'shahkar_verified' => true,
                'verification_status' => VerificationStatuses::ACTIVE->value,
                'verified_at' => now(),
                'rejection_reason' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'first_name' => 'سارا',
                'last_name' => 'محمدی',
                'national_code' => '0012345679',
                'father_name' => 'رضا',
                'birth_date' => '1991-07-21',
                'gender_type' => GenderType::FEMALE->value,
                'mobile' => '09120000002',
                'mobile_verified_at' => now(),
                'email' => 'provider2@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'two_factor_status' => ActivityStatus::INACTIVE->value,
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
                'remember_token' => null,
                'province_id' => 1,
                'city_id' => 2,
                'postal_code' => '1234567891',
                'address' => 'آدرس نمونه ارائه‌دهنده دوم',
                'shahkar_verified' => true,
                'verification_status' => VerificationStatuses::ACTIVE->value,
                'verified_at' => now(),
                'rejection_reason' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
