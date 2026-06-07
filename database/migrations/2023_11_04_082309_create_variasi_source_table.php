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
        Schema::create('variasi_source', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variasi_id')->nullable();
            $table->string('type');
            $table->string('path');
            $table->integer('is_featured')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variasi_source');
    }
};
