<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // cek dulu sebelum buat
            if (!Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable();
            }

            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $drop = [];

            if (Schema::hasColumn('users', 'name')) {
                $drop[] = 'name';
            }

            if (Schema::hasColumn('users', 'google_id')) {
                $drop[] = 'google_id';
            }

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};