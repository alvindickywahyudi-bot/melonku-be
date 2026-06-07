<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | STATUS ORDER
        |--------------------------------------------------------------------------
        */
        DB::table('orders')

            ->where('status', 'processed')

            ->update([
                'status' => 'diproses'
            ]);

        DB::table('orders')

            ->where('status', 'packed')

            ->update([
                'status' => 'dikemas'
            ]);

        DB::table('orders')

            ->where('status', 'shipped')

            ->update([
                'status' => 'dikirim'
            ]);

        DB::table('orders')

            ->where('status', 'completed')

            ->update([
                'status' => 'selesai'
            ]);

        DB::table('orders')

            ->where('status', 'cancelled')

            ->update([
                'status' => 'dibatalkan'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ROLLBACK STATUS
        |--------------------------------------------------------------------------
        */
        DB::table('orders')

            ->where('status', 'diproses')

            ->update([
                'status' => 'processed'
            ]);

        DB::table('orders')

            ->where('status', 'dikemas')

            ->update([
                'status' => 'packed'
            ]);

        DB::table('orders')

            ->where('status', 'dikirim')

            ->update([
                'status' => 'shipped'
            ]);

        DB::table('orders')

            ->where('status', 'selesai')

            ->update([
                'status' => 'completed'
            ]);

        DB::table('orders')

            ->where('status', 'dibatalkan')

            ->update([
                'status' => 'cancelled'
            ]);
    }
};