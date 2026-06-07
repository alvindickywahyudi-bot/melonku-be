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
        | 📊 LAPORAN HARIAN
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('laporan_harian')) {

            Schema::create('laporan_harian', function (Blueprint $table) {

                $table->id();

                $table->date('tanggal');

                $table->bigInteger('total_penjualan')
                    ->default(0);

                $table->integer('total_pesanan')
                    ->default(0);

                $table->integer('total_produk_terjual')
                    ->default(0);

                $table->integer('total_customer_baru')
                    ->default(0);

                $table->bigInteger('rata_rata_order')
                    ->default(0);

                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 📈 LAPORAN PRODUK
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('laporan_produk')) {
            Schema::create('laporan_produk', function (Blueprint $table) {

            $table->id();

            $table->foreignId('produk_id')
                ->nullable()
                ->constrained('produk')
                ->nullOnDelete();

            $table->date('tanggal');

            $table->integer('total_terjual')
                ->default(0);

            $table->bigInteger('total_revenue')
                ->default(0);

            $table->timestamps();
        });
        }
        /*
        |--------------------------------------------------------------------------
        | 🌱 LAPORAN GREENHOUSE
        |--------------------------------------------------------------------------
        */
    if (!Schema::hasTable('laporan_greenhouse')) {
        Schema::create('laporan_greenhouse', function (Blueprint $table) {

            $table->id();

            $table->foreignId('greenhouse_id')
                ->nullable()
                ->constrained('greenhouse')
                ->nullOnDelete();

            $table->date('tanggal');

            $table->integer('total_produk')
                ->default(0);

            $table->integer('total_terjual')
                ->default(0);

            $table->bigInteger('total_revenue')
                ->default(0);

            $table->timestamps();
        });
    }
        /*
        |--------------------------------------------------------------------------
        | 📦 LAPORAN STATUS ORDER
        |--------------------------------------------------------------------------
        */
    if (!Schema::hasTable('laporan_order_status')) {
        Schema::create('laporan_order_status', function (Blueprint $table) {

            $table->id();

            $table->date('tanggal');

            $table->integer('pending')
                ->default(0);

            $table->integer('diproses')
                ->default(0);

            $table->integer('dikemas')
                ->default(0);

            $table->integer('dikirim')
                ->default(0);

            $table->integer('selesai')
                ->default(0);

            $table->integer('dibatalkan')
                ->default(0);

            $table->timestamps();
        });
    }
        /*
        |--------------------------------------------------------------------------
        | 💳 LAPORAN PAYMENT
        |--------------------------------------------------------------------------
        */
    if (!Schema::hasTable('laporan_payment')) {
        Schema::create('laporan_payment', function (Blueprint $table) {

            $table->id();

            $table->date('tanggal');

            $table->integer('pending')
                ->default(0);

            $table->integer('paid')
                ->default(0);

            $table->integer('failed')
                ->default(0);

            $table->integer('expired')
                ->default(0);

            $table->timestamps();
        });
    }

        /*
        |--------------------------------------------------------------------------
        | ⚠️ LAPORAN STOK
        |--------------------------------------------------------------------------
        */
    if (!Schema::hasTable('laporan_stok')) {
        Schema::create('laporan_stok', function (Blueprint $table) {

            $table->id();

            $table->foreignId('produk_id')
                ->nullable()
                ->constrained('produk')
                ->nullOnDelete();

            $table->integer('stok')
                ->default(0);

            $table->enum('status', [

                'aman',
                'menipis',
                'habis'

            ])->default('aman');

            $table->timestamps();
        });
    }
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_stok');

        Schema::dropIfExists('laporan_payment');

        Schema::dropIfExists('laporan_order_status');

        Schema::dropIfExists('laporan_greenhouse');

        Schema::dropIfExists('laporan_produk');

        Schema::dropIfExists('laporan_harian');
    }
};