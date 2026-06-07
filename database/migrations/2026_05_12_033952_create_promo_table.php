<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promo', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | BASIC
            |--------------------------------------------------------------------------
            */
            $table->string('nama');

            $table->string('slug')->unique();

            $table->text('deskripsi')->nullable();

            $table->string('kode_promo')
                ->nullable()
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | DISCOUNT
            |--------------------------------------------------------------------------
            */
            $table->enum('tipe', [
                'percent',
                'nominal'
            ]);

            $table->decimal(
                'diskon',
                12,
                2
            );

            $table->decimal(
                'minimal_belanja',
                12,
                2
            )->default(0);

            $table->decimal(
                'maksimal_diskon',
                12,
                2
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | BANNER
            |--------------------------------------------------------------------------
            */
            $table->string('banner')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */
            $table->boolean('status')
                ->default(1);

            /*
            |--------------------------------------------------------------------------
            | DATE
            |--------------------------------------------------------------------------
            */
            $table->timestamp('tanggal_mulai')
                ->nullable();

            $table->timestamp('tanggal_selesai')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo');
    }
};