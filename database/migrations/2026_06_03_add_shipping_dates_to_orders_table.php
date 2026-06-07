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
        | ORDERS
        |--------------------------------------------------------------------------
        */
        Schema::table('orders', function (Blueprint $table) {

            if (!Schema::hasColumn('orders', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('resi');
            }

            if (!Schema::hasColumn('orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('shipped_at');
            }

        });

        /*
        |--------------------------------------------------------------------------
        | PRODUK
        |--------------------------------------------------------------------------
        */
        Schema::table('produk', function (Blueprint $table) {

            if (!Schema::hasColumn('produk', 'berat')) {
                $table->integer('berat')
                    ->default(1000)
                    ->after('harga');
            }

        });

        /*
        |--------------------------------------------------------------------------
        | CART ITEMS
        |--------------------------------------------------------------------------
        */
        Schema::table('cart_items', function (Blueprint $table) {

            if (!Schema::hasColumn('cart_items', 'selected_weight')) {
                $table->integer('selected_weight')
                    ->default(1000)
                    ->after('qty');
            }

        });
        
/*
|--------------------------------------------------------------------------
| ORDER ITEMS
|--------------------------------------------------------------------------
*/
Schema::table('order_items', function (Blueprint $table) {

    if (!Schema::hasColumn('order_items', 'selected_weight')) {

        $table->integer('selected_weight')
            ->default(1000)
            ->after('price');
    }

});
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ORDERS
        |--------------------------------------------------------------------------
        */
        Schema::table('orders', function (Blueprint $table) {

            if (Schema::hasColumn('orders', 'shipped_at')) {
                $table->dropColumn('shipped_at');
            }

            if (Schema::hasColumn('orders', 'completed_at')) {
                $table->dropColumn('completed_at');
            }

        });

        /*
        |--------------------------------------------------------------------------
        | PRODUK
        |--------------------------------------------------------------------------
        */
        Schema::table('produk', function (Blueprint $table) {

            if (Schema::hasColumn('produk', 'berat')) {
                $table->dropColumn('berat');
            }

        });

        /*
        |--------------------------------------------------------------------------
        | CART ITEMS
        |--------------------------------------------------------------------------
        */
        Schema::table('cart_items', function (Blueprint $table) {

            if (Schema::hasColumn('cart_items', 'selected_weight')) {
                $table->dropColumn('selected_weight');
            }

        });
        
/*
|--------------------------------------------------------------------------
| ORDER ITEMS
|--------------------------------------------------------------------------
*/
Schema::table('order_items', function (Blueprint $table) {

    if (Schema::hasColumn('order_items', 'selected_weight')) {

        $table->dropColumn('selected_weight');
    }

});
    }
};