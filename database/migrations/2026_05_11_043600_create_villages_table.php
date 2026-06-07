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
        Schema::create('villages', function (Blueprint $table) {

            $table->bigInteger('id')->primary();

            $table->bigInteger('districts_id');

            $table->string('nama');

            $table->timestamps();

            // optional foreign key
            // $table->foreign('districts_id')
            //     ->references('id')
            //     ->on('kecamatan')
            //     ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};