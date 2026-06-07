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
        | GREENHOUSE
        |--------------------------------------------------------------------------
        */
        DB::table('greenhouse')->updateOrInsert(

            [
                'nama' => 'Greenhouse Fujisawa'
            ],

            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $greenhouse = DB::table('greenhouse')

            ->where('nama', 'Greenhouse Fujisawa')

            ->first();

        /*
        |--------------------------------------------------------------------------
        | ROLE ADMIN
        |--------------------------------------------------------------------------
        */
        DB::table('role')->updateOrInsert(

            [
                'nama' => 'admin'
            ],

            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | ROLE CUSTOMER
        |--------------------------------------------------------------------------
        */
        DB::table('role')->updateOrInsert(

            [
                'nama' => 'customer'
            ],

            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $adminRole = DB::table('role')

            ->where('nama', 'admin')

            ->first();

        /*
        |--------------------------------------------------------------------------
        | USER ADMIN
        |--------------------------------------------------------------------------
        */
        DB::table('users')->updateOrInsert(

            [
                'email' => 'admin@melonku.com'
            ],

            [
                'username' => 'admin',

                'password' => Hash::make('admin123'),

                'is_active' => 1,

                'created_at' => now(),

                'updated_at' => now(),
            ]
        );

        $admin = DB::table('users')

            ->where('email', 'admin@melonku.com')

            ->first();

        /*
        |--------------------------------------------------------------------------
        | USER ROLE
        |--------------------------------------------------------------------------
        */
        DB::table('user_role')->updateOrInsert(

            [
                'user_id' => $admin->id,
                'role_id' => $adminRole->id,
            ],

            []
        );

        /*
        |--------------------------------------------------------------------------
        | PRODUK
        |--------------------------------------------------------------------------
        */
        DB::table('produk')->updateOrInsert(

            [
                'slug' => 'melon-fujisawa-premium'
            ],

            [

                'user_id' => $admin->id,

                'greenhouse_id' => $greenhouse->id,

                'nama' => 'Melon Fujisawa Premium',

                'gambar' => 'produk/fujisawa.jfif',

                'desc' =>
                    'Melon premium Jepang dengan rasa sangat manis dan tekstur lembut.',

                'kondisi' => 'baru',

                'stok' => 120,

                'harga' => 85000,

                'created_at' => now(),

                'updated_at' => now(),
            ]
        );

        $produk = DB::table('produk')

            ->where('slug', 'melon-fujisawa-premium')

            ->first();

        /*
        |--------------------------------------------------------------------------
        | PRODUK DETAIL
        |--------------------------------------------------------------------------
        */
        DB::table('produk_detail')->updateOrInsert(

            [
                'produk_id' => $produk->id
            ],

            [

                'short_description' =>
                    'Melon premium super manis',

                'long_description' =>
                    'Melon Fujisawa merupakan melon premium kualitas ekspor dengan rasa manis alami dan aroma khas Jepang.',

                'sweetness' => 10,

                'sweetness_label' => 'Super Sweet',

                'juiciness' => 9,

                'juiciness_label' => 'Very Juicy',

                'texture' => 8,

                'texture_label' => 'Soft Texture',

                'serving_size' => '100g',

                'calories' => '36 kcal',

                'vitamin_c' => '45%',

                'potassium' => '210mg',

                'fiber' => '2g',

                'storage_instruction' =>
                    'Simpan di suhu dingin',

                'origin_farm' =>
                    'Greenhouse Fujisawa',

                'harvest_age' => '75 Hari',

                'review_enabled' => 1,

                'created_at' => now(),

                'updated_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | PRODUK SOURCE
        |--------------------------------------------------------------------------
        */
        DB::table('produk_source')->updateOrInsert(

            [
                'produk_id' => $produk->id,
                'path' => 'produk/fujisawa.jfif',
            ],

            [

                'type' => 'image',

                'is_featured' => 1,

                'created_at' => now(),

                'updated_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | PRODUK TIMELINE
        |--------------------------------------------------------------------------
        */
        DB::table('produk_timeline')->updateOrInsert(

            [
                'produk_id' => $produk->id,
                'judul' => 'Penanaman Benih',
            ],

            [

                'desc' =>
                    'Benih melon mulai ditanam di greenhouse Fujisawa.',

                'w_awal' => now(),

                'w_akhir' => now()->addDays(7),

                'image' => 'timeline/tanam.jpg',

                'created_at' => now(),

                'updated_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | PROMO
        |--------------------------------------------------------------------------
        */
        DB::table('promo')->updateOrInsert(

            [
                'slug' => 'flash-sale-melon'
            ],

            [

                'nama' => 'Flash Sale Melon',

                'deskripsi' =>
                    'Diskon spesial melon premium',

                'tipe' => 'percent',

                'diskon' => 20,

                'minimal_belanja' => 0,

                'status' => 1,

                'tanggal_mulai' => now(),

                'tanggal_selesai' => now()->addDays(7),

                'is_flashsale' => 1,

                'flashsale_stock' => 50,

                'flashsale_limit' => 2,

                'created_at' => now(),

                'updated_at' => now(),
            ]
        );

        $promo = DB::table('promo')

            ->where('slug', 'flash-sale-melon')

            ->first();

        /*
        |--------------------------------------------------------------------------
        | PROMO PRODUK
        |--------------------------------------------------------------------------
        */
        DB::table('promo_produk')->updateOrInsert(

            [
                'promo_id' => $promo->id,

                'produk_id' => $produk->id,
            ],

            [

                'created_at' => now(),

                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        //
    }
};