<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingCourier;

class ShippingCourierSeeder extends Seeder
{
    public function run(): void
    {
        ShippingCourier::truncate();

        ShippingCourier::create([
            'code' => 'jne',
            'name' => 'JNE',
            'is_active' => true,
        ]);

        ShippingCourier::create([
            'code' => 'jnt',
            'name' => 'J&T',
            'is_active' => true,
        ]);
    }
}