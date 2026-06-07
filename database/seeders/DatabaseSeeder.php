<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

use Database\Seeders\AdminSeeder;
use Database\Seeders\WilayahCsvSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | WILAYAH
        |--------------------------------------------------------------------------
        */
        $this->call([
            WilayahCsvSeeder::class,
        ]);
                
        $this->call([
            ShippingCourierSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */
        $this->call([
            AdminSeeder::class,
        ]);
    }
}