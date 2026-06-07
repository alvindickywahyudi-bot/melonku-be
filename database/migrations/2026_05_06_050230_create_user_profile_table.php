<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 🔥 jangan create kalau sudah ada
        if (Schema::hasTable('user_profile')) {
            return;
        }

        Schema::create('user_profile', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('nama')->nullable();

            $table->text('bio')->nullable();

            $table->enum('gender', ['L', 'P'])->nullable();

            $table->string('tempat_lahir')->nullable();

            $table->date('tgl_lahir')->nullable();

            $table->text('alamat')->nullable();

            $table->unsignedBigInteger('provinsi_id')->nullable();

            $table->unsignedBigInteger('kabupaten_id')->nullable();

            $table->unsignedBigInteger('kecamatan_id')->nullable();

            $table->decimal('lat', 10, 7)->nullable();

            $table->decimal('lng', 10, 7)->nullable();

            $table->string('foto')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profile');
    }
};