<?php

namespace Database\Seeders;

use App\Enums\GenderType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pets')->insert([
            [
                'user_id' => 1,
                'species_id' => 1,
                'breed_id' => 1,
                'name' => 'رکس',
                'gender_type' => GenderType::MALE->value,
                'birth_date' => '2021-05-10',
                'weight' => 28.40,
                'color' => 'طلایی',
                'avatar' => null,
                'medical_records' => json_encode([
                    ['date' => '2024-01-10', 'note' => 'واکسیناسیون کامل'],
                ]),
                'settings' => json_encode([
                    'public_profile' => true,
                ]),
                'bio' => 'سگ مهربان و پرانرژی.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'species_id' => 2,
                'breed_id' => 3,
                'name' => 'لونا',
                'gender_type' => GenderType::FEMALE->value,
                'birth_date' => '2022-08-15',
                'weight' => 4.10,
                'color' => 'کرم',
                'avatar' => null,
                'medical_records' => json_encode([
                    ['date' => '2024-02-05', 'note' => 'معاینه دوره‌ای'],
                ]),
                'settings' => json_encode([
                    'public_profile' => true,
                ]),
                'bio' => 'گربه‌ای آرام و دوست‌داشتنی.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
