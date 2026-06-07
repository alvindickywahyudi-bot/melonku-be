<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {

            $table->foreignId('produk_varian_id')
                ->nullable()
                ->after('product_id')
                ->constrained('produk_varian')
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {

            $table->dropForeign([
                'produk_varian_id'
            ]);

            $table->dropColumn(
                'produk_varian_id'
            );

        });
    }
};