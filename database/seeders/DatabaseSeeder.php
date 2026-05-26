<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use App\Enums\GenderType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('species')->insert([
            [
                'id' => 1,
                'name_en' => 'dog',
                'name_fa' => 'سگ',
                'slug' => 'dog',
                'icon' => '🐶',
                'image' => null,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name_en' => 'cat',
                'name_fa' => 'گربه',
                'slug' => 'cat',
                'icon' => '🐱',
                'image' => null,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name_en' => 'bird',
                'name_fa' => 'پرنده',
                'slug' => 'bird',
                'icon' => '🐦',
                'image' => null,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('breeds')->insert([
            [
                'species_id' => 1,
                'name_en' => 'Golden Retriever',
                'name_fa' => 'گلدن رتریور',
                'slug' => 'golden-retriever',
                'description' => 'نژادی مهربان، باهوش و بسیار اجتماعی.',
                'image' => null,
                'characteristics' => json_encode([
                    'temperament' => 'friendly',
                    'size' => 'large',
                    'lifespan' => '10-12 years',
                ]),
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'species_id' => 1,
                'name_en' => 'German Shepherd',
                'name_fa' => 'ژرمن شپرد',
                'slug' => 'german-shepherd',
                'description' => 'سگی وفادار، قدرتمند و بسیار مناسب آموزش.',
                'image' => null,
                'characteristics' => json_encode([
                    'temperament' => 'loyal',
                    'size' => 'large',
                    'lifespan' => '9-13 years',
                ]),
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'species_id' => 2,
                'name_en' => 'Persian',
                'name_fa' => 'پرشین',
                'slug' => 'persian',
                'description' => 'گربه‌ای آرام، زیبا و مشهور به موهای بلند.',
                'image' => null,
                'characteristics' => json_encode([
                    'temperament' => 'calm',
                    'size' => 'medium',
                    'lifespan' => '12-17 years',
                ]),
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'species_id' => 2,
                'name_en' => 'Maine Coon',
                'name_fa' => 'مین‌کون',
                'slug' => 'maine-coon',
                'description' => 'گربه‌ای بزرگ‌جثه، مهربان و اجتماعی.',
                'image' => null,
                'characteristics' => json_encode([
                    'temperament' => 'gentle',
                    'size' => 'large',
                    'lifespan' => '12-15 years',
                ]),
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'species_id' => 3,
                'name_en' => 'African Grey',
                'name_fa' => 'کاسکو',
                'slug' => 'african-grey',
                'description' => 'طوطی خاکستری آفریقایی با هوش بسیار بالا.',
                'image' => null,
                'characteristics' => json_encode([
                    'temperament' => 'intelligent',
                    'size' => 'medium',
                    'lifespan' => '40-60 years',
                ]),
                'activity_status' => ActivityStatus::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

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
