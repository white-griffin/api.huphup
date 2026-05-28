<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateCitySeeder extends Seeder
{
    public function run(): void
    {
        $country_path = 'database/sqls/countries.sql';
        $province_path = 'database/sqls/provinces.sql';
        $city_path = 'database/sqls/cities.sql';
        DB::unprepared(file_get_contents($country_path));
        DB::unprepared(file_get_contents($province_path));
        DB::unprepared(file_get_contents($city_path));

    }
}
