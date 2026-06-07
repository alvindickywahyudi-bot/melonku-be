<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingCourier;
use App\Models\ShippingSetting;

class ShippingSeeder extends Seeder
{
    public function run(): void
    {
        ShippingSetting::firstOrCreate(
            ['id' => 1],
            [
                'origin_city_name' => 'Surabaya',
                'origin_province_name' => 'Jawa Timur',
                'warehouse_name' => 'Gudang Melonku Pusat',
            ]
        );

        ShippingCourier::firstOrCreate(
            ['code' => 'jne'],
            [
                'name' => 'JNE',
                'is_active' => true,
            ]
        );

        ShippingCourier::firstOrCreate(
            ['code' => 'jnt'],
            [
                'name' => 'J&T',
                'is_active' => true,
            ]
        );
    }
}