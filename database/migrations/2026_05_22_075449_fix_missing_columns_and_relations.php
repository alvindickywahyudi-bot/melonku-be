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

            if (!Schema::hasColumn('orders', 'courier')) {

                $table->string('courier')
                    ->nullable();
            }

            if (!Schema::hasColumn('orders', 'shipping_service')) {

                $table->string('shipping_service')
                    ->nullable();
            }

            if (!Schema::hasColumn('orders', 'shipping_cost')) {

                $table->integer('shipping_cost')
                    ->default(0);
            }

            if (!Schema::hasColumn('orders', 'alamat_pengiriman')) {

                $table->text('alamat_pengiriman')
                    ->nullable();
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
        | USER ROLE FK
        |--------------------------------------------------------------------------
        */
Schema::table('user_role', function (Blueprint $table) {

    /*
    |--------------------------------------------------------------------------
    | ADD USER_ID
    |--------------------------------------------------------------------------
    */
    if (!Schema::hasColumn('user_role', 'user_id')) {

        $table->foreignId('user_id')
            ->nullable()
            ->after('id');
    }
});

Schema::table('user_role', function (Blueprint $table) {

    /*
    |--------------------------------------------------------------------------
    | FK USER
    |--------------------------------------------------------------------------
    */
    try {

        $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->cascadeOnDelete();

    } catch (\Throwable $e) {}

    /*
    |--------------------------------------------------------------------------
    | FK ROLE
    |--------------------------------------------------------------------------
    */
    try {

        $table->foreign('role_id')
            ->references('id')
            ->on('role')
            ->cascadeOnDelete();

    } catch (\Throwable $e) {}
});
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {

        Schema::table('orders', function (Blueprint $table) {

            $columns = [

                'status',
                'resi',
                'courier',
                'shipping_service',
                'shipping_cost',
                'alamat_pengiriman'
            ];

            foreach ($columns as $column) {

                if (Schema::hasColumn('orders', $column)) {

                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('user_profile', function (Blueprint $table) {

            try {

                $table->dropForeign([
                    'user_id'
                ]);

            } catch (\Throwable $e) {}

            if (Schema::hasColumn('user_profile', 'user_id')) {

                $table->dropColumn('user_id');
            }
        });

        Schema::table('user_role', function (Blueprint $table) {

            try {

                $table->dropForeign([
                    'user_id'
                ]);

            } catch (\Throwable $e) {}

            try {

                $table->dropForeign([
                    'role_id'
                ]);

            } catch (\Throwable $e) {}
        });
    }
};