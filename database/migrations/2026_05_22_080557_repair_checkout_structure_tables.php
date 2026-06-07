<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ORDERS
        |--------------------------------------------------------------------------
        */
        Schema::table('orders', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | SHIPPING ESTIMATION
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('orders', 'shipping_estimation')) {

                $table->string('shipping_estimation')
                    ->nullable()
                    ->after('shipping_cost');
            }

            /*
            |--------------------------------------------------------------------------
            | RECEIVER
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('orders', 'receiver_name')) {

                $table->string('receiver_name')
                    ->nullable()
                    ->after('shipping_estimation');
            }

            if (!Schema::hasColumn('orders', 'receiver_phone')) {

                $table->string('receiver_phone')
                    ->nullable()
                    ->after('receiver_name');
            }

            if (!Schema::hasColumn('orders', 'receiver_address')) {

                $table->text('receiver_address')
                    ->nullable()
                    ->after('receiver_phone');
            }

            if (!Schema::hasColumn('orders', 'receiver_province')) {

                $table->string('receiver_province')
                    ->nullable()
                    ->after('receiver_address');
            }

            if (!Schema::hasColumn('orders', 'receiver_city')) {

                $table->string('receiver_city')
                    ->nullable()
                    ->after('receiver_province');
            }

            if (!Schema::hasColumn('orders', 'receiver_district')) {

                $table->string('receiver_district')
                    ->nullable()
                    ->after('receiver_city');
            }

            /*
            |--------------------------------------------------------------------------
            | TIMESTAMP
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('orders', 'expired_at')) {

                $table->timestamp('expired_at')
                    ->nullable()
                    ->after('paid_at');
            }

            if (!Schema::hasColumn('orders', 'cancelled_at')) {

                $table->timestamp('cancelled_at')
                    ->nullable()
                    ->after('expired_at');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | ORDER ITEMS
        |--------------------------------------------------------------------------
        */
        Schema::table('order_items', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | SNAPSHOT PRODUCT
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('order_items', 'product_name')) {

                $table->string('product_name')
                    ->nullable()
                    ->after('product_id');
            }

            if (!Schema::hasColumn('order_items', 'product_image')) {

                $table->text('product_image')
                    ->nullable()
                    ->after('product_name');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | CART ITEMS
        |--------------------------------------------------------------------------
        */
        Schema::table('cart_items', function (Blueprint $table) {

            if (!Schema::hasColumn('cart_items', 'harga')) {

                $table->integer('harga')
                    ->default(0)
                    ->after('qty');
            }

            if (!Schema::hasColumn('cart_items', 'selected')) {

                $table->boolean('selected')
                    ->default(true)
                    ->after('harga');
            }

            if (!Schema::hasColumn('cart_items', 'catatan')) {

                $table->text('catatan')
                    ->nullable()
                    ->after('selected');
            }
        });
    }

    /**
     * Reverse migrations
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropColumn([

                'shipping_estimation',

                'receiver_name',

                'receiver_phone',

                'receiver_address',

                'receiver_province',

                'receiver_city',

                'receiver_district',

                'expired_at',

                'cancelled_at',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {

            $table->dropColumn([

                'product_name',

                'product_image',
            ]);
        });

        Schema::table('cart_items', function (Blueprint $table) {

            $table->dropColumn([

                'harga',

                'selected',

                'catatan',
            ]);
        });
    }
};