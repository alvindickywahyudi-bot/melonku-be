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
        Schema::create('adoption_projects', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | BASIC
            |--------------------------------------------------------------------------
            */
            $table->string('nama');

            $table->string('slug')
                ->unique();

            $table->text('deskripsi')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | INVESTMENT INFO
            |--------------------------------------------------------------------------
            */
            $table->integer('roi_percent')
                ->default(0);

            $table->integer('durasi_hari')
                ->default(0);

            $table->integer('proteksi_percent')
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | SLOT
            |--------------------------------------------------------------------------
            */
            $table->bigInteger('harga_slot')
                ->default(0);

            $table->integer('total_slot')
                ->default(0);

            $table->integer('slot_tersedia')
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | MEDIA
            |--------------------------------------------------------------------------
            */
            $table->string('thumbnail')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */
            $table->boolean('status')
                ->default(true);

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'adoption_projects'
        );
    }
};