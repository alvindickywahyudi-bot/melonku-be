<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_settings', function (Blueprint $table) {

            $table->id();

            $table->string('origin_city_name')
                ->default('Bojonegoro');

            $table->string('origin_province_name')
                ->default('Jawa Timur');

            $table->string('warehouse_name')
                ->default('Greenhouse');

            $table->string('rajaongkir_api_key')
                ->nullable();

            $table->boolean('is_rajaongkir_active')
                ->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_settings');
    }
};