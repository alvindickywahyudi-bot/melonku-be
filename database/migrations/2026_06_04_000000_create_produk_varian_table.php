

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
    Schema::create('produk_varian', function (Blueprint $table) {

        $table->id();

        $table->foreignId('produk_id')
            ->constrained('produk')
            ->cascadeOnDelete();

        $table->integer('berat');

        $table->integer('harga');

        $table->integer('stok');

        $table->timestamps();
    });

}
    
};