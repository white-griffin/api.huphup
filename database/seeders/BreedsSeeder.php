<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BreedsSeeder extends Seeder
{
    public function run(): void
    {
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
    }
}
