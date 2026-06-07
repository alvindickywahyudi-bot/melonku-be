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
        Schema::table('user_profile', function (Blueprint $table) {

            // village/desa
            $table->bigInteger('village_id')
                ->nullable()
                ->after('kecamatan_id');

            // detail alamat lengkap
            $table->text('alamat_detail')
                ->nullable()
                ->after('alamat');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profile', function (Blueprint $table) {

            $table->dropColumn('village_id');

            $table->dropColumn('alamat_detail');

        });
    }
};