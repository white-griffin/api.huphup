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
            SpeciesSeeder::class,
            BreedsSeeder::class,
            PetSeeder::class,
        ]);
    }
}
