<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | DEFAULT ROLES
        |--------------------------------------------------------------------------
        */
        if (!DB::table('role')->where('nama', 'admin')->exists()) {

            DB::table('role')->insert([

                'nama' => 'admin',

                'created_at' => now(),

                'updated_at' => now(),
            ]);
        }

        if (!DB::table('role')->where('nama', 'customer')->exists()) {

            DB::table('role')->insert([

                'nama' => 'customer',

                'created_at' => now(),

                'updated_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | DEFAULT ADMIN
        |--------------------------------------------------------------------------
        */
        if (!DB::table('users')
            ->where('username', 'admin')
            ->exists()) {

            $adminId = DB::table('users')->insertGetId([

                'username' => 'admin',

                'email' => 'admin@melonku.com',

                'password' => Hash::make('password'),

                'is_active' => 1,

                'created_at' => now(),

                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | GET ADMIN ROLE
            |--------------------------------------------------------------------------
            */
            $adminRole = DB::table('role')
                ->where('nama', 'admin')
                ->first();

            /*
            |--------------------------------------------------------------------------
            | ATTACH ROLE
            |--------------------------------------------------------------------------
            */
            if ($adminRole) {

                DB::table('user_role')->insert([

                    'user_id' => $adminId,

                    'role_id' => $adminRole->id,
                ]);
            }
        }
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        //
    }
};