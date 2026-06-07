<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | FLASH SALE
            |--------------------------------------------------------------------------
            */
            $table->boolean('is_flashsale')
                ->default(false);

            $table->integer('flashsale_stock')
                ->nullable();

            $table->integer('flashsale_limit')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('promo', function (Blueprint $table) {

            $table->dropColumn([
                'is_flashsale',
                'flashsale_stock',
                'flashsale_limit'
            ]);
        });
    }
};