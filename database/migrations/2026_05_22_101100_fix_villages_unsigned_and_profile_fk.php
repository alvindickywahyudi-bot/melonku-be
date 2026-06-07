<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | FIX VILLAGES TYPE
        |--------------------------------------------------------------------------
        */
        DB::statement("
            ALTER TABLE villages
            MODIFY id BIGINT UNSIGNED NOT NULL
        ");

        DB::statement("
            ALTER TABLE villages
            MODIFY districts_id BIGINT UNSIGNED NOT NULL
        ");

        /*
        |--------------------------------------------------------------------------
        | FIX USER PROFILE VILLAGE ID
        |--------------------------------------------------------------------------
        */
        DB::statement("
            ALTER TABLE user_profile
            MODIFY village_id BIGINT UNSIGNED NULL
        ");

        /*
        |--------------------------------------------------------------------------
        | ADD FOREIGN KEY
        |--------------------------------------------------------------------------
        */
        Schema::table('user_profile', function (Blueprint $table) {

            try {

                $table->foreign('village_id')
                    ->references('id')
                    ->on('villages')
                    ->nullOnDelete();

            } catch (\Throwable $e) {}
        });
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::table('user_profile', function (Blueprint $table) {

            try {

                $table->dropForeign([
                    'village_id'
                ]);

            } catch (\Throwable $e) {}
        });
    }
};