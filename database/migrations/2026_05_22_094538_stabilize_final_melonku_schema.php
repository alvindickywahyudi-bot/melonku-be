<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | PRODUK
        |--------------------------------------------------------------------------
        */
        Schema::table('produk', function (Blueprint $table) {

            if (!Schema::hasColumn('produk', 'slug')) {

                $table->string('slug')
                    ->nullable()
                    ->after('nama');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | UNIQUE SLUG
        |--------------------------------------------------------------------------
        */
        try {

            Schema::table('produk', function (Blueprint $table) {

                $table->unique('slug');
            });

        } catch (\Throwable $e) {}

        /*
        |--------------------------------------------------------------------------
        | FK USER
        |--------------------------------------------------------------------------
        */
        try {

            Schema::table('produk', function (Blueprint $table) {

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });

        } catch (\Throwable $e) {}

        /*
        |--------------------------------------------------------------------------
        | FK GREENHOUSE
        |--------------------------------------------------------------------------
        */
        try {

            Schema::table('produk', function (Blueprint $table) {

                $table->foreign('greenhouse_id')
                    ->references('id')
                    ->on('greenhouse')
                    ->nullOnDelete();
            });

        } catch (\Throwable $e) {}

        /*
        |--------------------------------------------------------------------------
        | PRODUK REQUIRED
        |--------------------------------------------------------------------------
        */
        Schema::table('produk', function (Blueprint $table) {

            $table->string('nama')
                ->nullable(false)
                ->change();

            $table->integer('stok')
                ->default(0)
                ->change();

            $table->integer('harga')
                ->default(0)
                ->change();
        });
    }

    public function down(): void
    {
        //
    }
};