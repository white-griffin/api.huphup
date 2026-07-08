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
        $this->call([
            StateCitySeeder::class,

            AdminSeeder::class,
            UserSeeder::class,
            ProviderSeeder::class,
            ProviderDocumentSeeder::class,

            BusinessSeeder::class,

            ServiceSeeder::class,
            BusinessServiceSeeder::class,

            SpeciesSeeder::class,
            BreedsSeeder::class,
            PetSeeder::class,
            UserAddressSeeder::class,

            CategorySeeder::class,
            BrandSeeder::class,

            AttributeSeeder::class,
            AttributeOptionSeeder::class,

            ProductSeeder::class,
            ProductVariationSeeder::class,
            ProductVariationAttributeSeeder::class,
            ProductImageSeeder::class,

            BusinessScheduleSeeder::class,
            ScheduleBreakSeeder::class,
            BusinessOffDaySeeder::class,

            AppointmentSeeder::class,

            ConversationSeeder::class,
            ConversationParticipantSeeder::class,
            MessageSeeder::class,

            RoutineTemplateSeeder::class,
            PetRoutineSeeder::class,
            RoutineActionSeeder::class,
        ]);
    }
}
