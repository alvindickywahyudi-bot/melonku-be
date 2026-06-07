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
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('greenhouse_id')->nullable();
            $table->unsignedBigInteger('variasi_id')->nullable();
            $table->string('nama')->nullable();
            $table->longText('desc')->nullable();
            $table->enum('kondisi', ['baru', 'bekas'])->nullable();
            $table->string('size')->nullable();
            $table->string('panjang')->nullable();
            $table->string('lebar')->nullable();
            $table->string('tinggi')->nullable();
            $table->string('stok')->nullable();
            $table->string('harga')->nullable();
            $table->dateTime('w_tanam')->nullable();
            $table->dateTime('w_panen')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('qr_string')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
