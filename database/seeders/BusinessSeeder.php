<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use App\Enums\BusinessTypes;
use App\Enums\VerificationStatuses;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('businesses')->insert([
            [
                'id' => 1,
                'provider_id' => 1,
                'business_type' => BusinessTypes::CLINIC->value,
                'name' => 'کلینیک حیوانات مهر',
                'license_code' => 'LIC-0001',
                'description' => 'کلینیک نمونه دامپزشکی برای داده‌های اولیه.',
                'logo' => null,
                'cover_image' => null,
                'phone' => '02111111111',
                'email' => 'clinic@example.com',
                'website' => 'https://clinic.example.com',
                'province_id' => 1,
                'city_id' => 1,
                'address' => 'خیابان نمونه، پلاک ۱',
                'postal_code' => '1234567890',
                'latitude' => 37.7588890,
                'longitude' => 45.9783330,
                'bank_name' => 'بانک نمونه',
                'bank_account_holder' => 'علی رضایی',
                'bank_card' => '1111222233334444',
                'bank_iban' => '12345678901234567890123456',
                'settings' => json_encode(['emergency_available' => true]),
                'verification_status' => VerificationStatuses::ACTIVE->value,
                'verified_at' => now(),
                'rejection_reason' => null,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'provider_id' => 2,
                'business_type' => BusinessTypes::SHOPPING->value,
                'name' => 'فروشگاه پت شاپ نارنج',
                'license_code' => 'LIC-0002',
                'description' => 'فروشگاه نمونه محصولات حیوانات خانگی.',
                'logo' => null,
                'cover_image' => null,
                'phone' => '02122222222',
                'email' => 'shop@example.com',
                'website' => 'https://shop.example.com',
                'province_id' => 1,
                'city_id' => 2,
                'address' => 'خیابان نمونه، پلاک ۲',
                'postal_code' => '1234567891',
                'latitude' => 37.9158330,
                'longitude' => 46.1236110,
                'bank_name' => 'بانک نمونه',
                'bank_account_holder' => 'سارا محمدی',
                'bank_card' => '5555666677778888',
                'bank_iban' => '22345678901234567890123456',
                'settings' => json_encode(['min_order' => 50000]),
                'verification_status' => VerificationStatuses::ACTIVE->value,
                'verified_at' => now(),
                'rejection_reason' => null,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
