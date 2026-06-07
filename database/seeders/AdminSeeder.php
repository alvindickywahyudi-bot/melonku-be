<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Role;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ROLE ADMIN
        |--------------------------------------------------------------------------
        */
        $role = Role::updateOrCreate(

            [
                'nama' => 'admin'
            ],

            [
                'updated_at' => now()
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | ADMIN ACCOUNT
        |--------------------------------------------------------------------------
        */
        $admin = User::updateOrCreate(

            [
                'email' => 'admin@melonku.com'
            ],

            [

                'username' => 'admin',

                'phone' => '082232913406',

                'password' => Hash::make('admin123'),

                'is_active' => 1,

                'updated_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | USER ROLE
        |--------------------------------------------------------------------------
        */
        DB::table('user_role')->updateOrInsert(

            [
                'user_id' => $admin->id,

                'role_id' => $role->id,
            ],

            []
        );
    }
}