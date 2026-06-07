<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        */
        Schema::table('users', function (Blueprint $table) {

            if (!Schema::hasColumn('users', 'google_id')) {

                $table->string('google_id')
                    ->nullable()
                    ->unique()
                    ->after('email');
            }

            if (!Schema::hasColumn('users', 'phone')) {

                $table->string('phone')
                    ->nullable()
                    ->after('google_id');
            }

            if (!Schema::hasColumn('users', 'is_active')) {

                $table->integer('is_active')
                    ->default(1)
                    ->after('password');
            }

            if (!Schema::hasColumn('users', 'phone_verified_at')) {

                $table->timestamp('phone_verified_at')
                    ->nullable()
                    ->after('is_active');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | USER PROFILE
        |--------------------------------------------------------------------------
        */
        Schema::table('user_profile', function (Blueprint $table) {

            if (!Schema::hasColumn('user_profile', 'user_id')) {

                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | USER ROLE
        |--------------------------------------------------------------------------
        */
/*
|--------------------------------------------------------------------------
| USER ROLE
|--------------------------------------------------------------------------
*/
if (Schema::hasColumn('user_role', 'user_id')) {

    Schema::table('user_role', function (Blueprint $table) {

        try {

            $table->dropForeign([
                'user_id'
            ]);

        } catch (\Throwable $e) {}
    });

    Schema::table('user_role', function (Blueprint $table) {

        $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->cascadeOnDelete();
    });
}

if (Schema::hasColumn('user_role', 'role_id')) {

    Schema::table('user_role', function (Blueprint $table) {

        try {

            $table->dropForeign([
                'role_id'
            ]);

        } catch (\Throwable $e) {}
    });

    Schema::table('user_role', function (Blueprint $table) {

        $table->foreign('role_id')
            ->references('id')
            ->on('role')
            ->cascadeOnDelete();
    });
}

        /*
        |--------------------------------------------------------------------------
        | PRODUK
        |--------------------------------------------------------------------------
        */
        Schema::table('produk', function (Blueprint $table) {

            $table->integer('stok')
                ->default(0)
                ->change();

            $table->integer('harga')
                ->default(0)
                ->change();
        });

        /*
        |--------------------------------------------------------------------------
        | PRODUK DETAIL
        |--------------------------------------------------------------------------
        */
        Schema::table('produk_detail', function (Blueprint $table) {

            if (!Schema::hasColumn('produk_detail', 'short_description')) {

                $table->text('short_description')
                    ->nullable()
                    ->after('produk_id');
            }

            if (!Schema::hasColumn('produk_detail', 'long_description')) {

                $table->longText('long_description')
                    ->nullable()
                    ->after('short_description');
            }

            if (!Schema::hasColumn('produk_detail', 'sweetness_label')) {

                $table->string('sweetness_label')
                    ->nullable()
                    ->after('sweetness');
            }

            if (!Schema::hasColumn('produk_detail', 'juiciness_label')) {

                $table->string('juiciness_label')
                    ->nullable()
                    ->after('juiciness');
            }

            if (!Schema::hasColumn('produk_detail', 'texture_label')) {

                $table->string('texture_label')
                    ->nullable()
                    ->after('texture');
            }

            if (!Schema::hasColumn('produk_detail', 'serving_size')) {

                $table->string('serving_size')
                    ->nullable();
            }

            if (!Schema::hasColumn('produk_detail', 'calories')) {

                $table->string('calories')
                    ->nullable();
            }

            if (!Schema::hasColumn('produk_detail', 'vitamin_c')) {

                $table->string('vitamin_c')
                    ->nullable();
            }

            if (!Schema::hasColumn('produk_detail', 'potassium')) {

                $table->string('potassium')
                    ->nullable();
            }

            if (!Schema::hasColumn('produk_detail', 'fiber')) {

                $table->string('fiber')
                    ->nullable();
            }

            if (!Schema::hasColumn('produk_detail', 'storage_instruction')) {

                $table->text('storage_instruction')
                    ->nullable();
            }

            if (!Schema::hasColumn('produk_detail', 'origin_farm')) {

                $table->string('origin_farm')
                    ->nullable();
            }

            if (!Schema::hasColumn('produk_detail', 'harvest_age')) {

                $table->string('harvest_age')
                    ->nullable();
            }

            if (!Schema::hasColumn('produk_detail', 'review_enabled')) {

                $table->boolean('review_enabled')
                    ->default(false)
                    ->after('rating');
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

        /*
        |--------------------------------------------------------------------------
        | ORDERS
        |--------------------------------------------------------------------------
        */
        Schema::table('orders', function (Blueprint $table) {

            if (!Schema::hasColumn('orders', 'status')) {

                $table->string('status')
                    ->default('pending')
                    ->after('total_price');
            }

            if (!Schema::hasColumn('orders', 'resi')) {

                $table->string('resi')
                    ->nullable()
                    ->after('payment_status');
            }

            if (!Schema::hasColumn('orders', 'expired_at')) {

                $table->timestamp('expired_at')
                    ->nullable();
            }

            if (!Schema::hasColumn('orders', 'cancelled_at')) {

                $table->timestamp('cancelled_at')
                    ->nullable();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | ORDER ITEMS
        |--------------------------------------------------------------------------
        */
        Schema::table('order_items', function (Blueprint $table) {

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
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        //
    }
};