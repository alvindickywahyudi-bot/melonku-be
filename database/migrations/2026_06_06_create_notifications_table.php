<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::create('notifications', function (Blueprint $table) {
    $table->id();

    $table->string('title');
    $table->text('message')->nullable();

    $table->string('type')->default('order');

    $table->foreignId('order_id')
        ->nullable()
        ->constrained('orders')
        ->nullOnDelete();

    $table->boolean('is_read')
        ->default(false);

    $table->timestamps();
});
    }

};