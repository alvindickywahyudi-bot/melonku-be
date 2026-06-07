<?php

namespace App\Models;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Produk;

use Illuminate\Support\Facades\DB;

class LaporanService
{
    public function generateDaily()
    {
        $tanggal = now()->toDateString();

        /*
        |--------------------------------------------------------------------------
        | 📊 LAPORAN HARIAN
        |--------------------------------------------------------------------------
        */
        $totalPenjualan = Order::whereDate(
                'created_at',
                $tanggal
            )
            ->where(
                'payment_status',
                'paid'
            )
            ->sum('total_price');

        $totalPesanan = Order::whereDate(
            'created_at',
            $tanggal
        )->count();

        $produkTerjual = OrderItem::whereDate(
            'created_at',
            $tanggal
        )->sum('qty');

        $customerBaru = User::whereDate(
            'created_at',
            $tanggal
        )->count();

        DB::table('laporan_harian')
            ->updateOrInsert(

                [
                    'tanggal' => $tanggal
                ],

                [

                    'total_penjualan' =>
                        $totalPenjualan,

                    'total_pesanan' =>
                        $totalPesanan,

                    'total_produk_terjual' =>
                        $produkTerjual,

                    'total_customer_baru' =>
                        $customerBaru,

                    'rata_rata_order' =>
                        $totalPesanan > 0
                            ? $totalPenjualan / $totalPesanan
                            : 0,

                    'updated_at' => now(),

                    'created_at' => now(),
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | 📈 LAPORAN PRODUK
        |--------------------------------------------------------------------------
        */
        $produk = Produk::all();

        foreach ($produk as $item) {

            $terjual = OrderItem::where(
                    'product_id',
                    $item->id
                )
                ->whereDate(
                    'created_at',
                    $tanggal
                )
                ->sum('qty');

            $revenue = OrderItem::where(
                    'product_id',
                    $item->id
                )
                ->whereDate(
                    'created_at',
                    $tanggal
                )
                ->sum(
                    DB::raw('qty * price')
                );

            DB::table('laporan_produk')
                ->updateOrInsert(

                    [

                        'tanggal' => $tanggal,

                        'produk_id' => $item->id,
                    ],

                    [

                        'total_terjual' =>
                            $terjual,

                        'total_revenue' =>
                            $revenue,

                        'updated_at' => now(),

                        'created_at' => now(),
                    ]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | 🌱 LAPORAN GREENHOUSE
        |--------------------------------------------------------------------------
        */
        $greenhouses = DB::table('greenhouse')->get();

        foreach ($greenhouses as $greenhouse) {

            $produkIds = Produk::where(
                'greenhouse_id',
                $greenhouse->id
            )->pluck('id');

            $totalProduk = $produkIds->count();

            $totalTerjual = OrderItem::whereIn(
                    'product_id',
                    $produkIds
                )
                ->whereDate(
                    'created_at',
                    $tanggal
                )
                ->sum('qty');

            $revenue = OrderItem::whereIn(
                    'product_id',
                    $produkIds
                )
                ->whereDate(
                    'created_at',
                    $tanggal
                )
                ->sum(
                    DB::raw('qty * price')
                );

            DB::table('laporan_greenhouse')
                ->updateOrInsert(

                    [

                        'tanggal' => $tanggal,

                        'greenhouse_id' =>
                            $greenhouse->id
                    ],

                    [

                        'total_produk' =>
                            $totalProduk,

                        'total_terjual' =>
                            $totalTerjual,

                        'total_revenue' =>
                            $revenue,

                        'updated_at' => now(),

                        'created_at' => now(),
                    ]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | 📦 STATUS ORDER
        |--------------------------------------------------------------------------
        */
        DB::table('laporan_order_status')
            ->updateOrInsert(

                [
                    'tanggal' => $tanggal
                ],

                [

                    'pending' => Order::whereDate(
                            'created_at',
                            $tanggal
                        )
                        ->where(
                            'status',
                            'pending'
                        )
                        ->count(),

                    'diproses' => Order::whereDate(
                            'created_at',
                            $tanggal
                        )
                        ->where(
                            'status',
                            'diproses'
                        )
                        ->count(),

                    'dikemas' => Order::whereDate(
                            'created_at',
                            $tanggal
                        )
                        ->where(
                            'status',
                            'dikemas'
                        )
                        ->count(),

                    'dikirim' => Order::whereDate(
                            'created_at',
                            $tanggal
                        )
                        ->where(
                            'status',
                            'dikirim'
                        )
                        ->count(),

                    'selesai' => Order::whereDate(
                            'created_at',
                            $tanggal
                        )
                        ->where(
                            'status',
                            'selesai'
                        )
                        ->count(),

                    'dibatalkan' => Order::whereDate(
                            'created_at',
                            $tanggal
                        )
                        ->where(
                            'status',
                            'dibatalkan'
                        )
                        ->count(),

                    'updated_at' => now(),

                    'created_at' => now(),
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | 💳 PAYMENT STATUS
        |--------------------------------------------------------------------------
        */
        DB::table('laporan_payment')
            ->updateOrInsert(

                [
                    'tanggal' => $tanggal
                ],

                [

                    'pending' => Order::whereDate(
                            'created_at',
                            $tanggal
                        )
                        ->where(
                            'payment_status',
                            'pending'
                        )
                        ->count(),

                    'paid' => Order::whereDate(
                            'created_at',
                            $tanggal
                        )
                        ->where(
                            'payment_status',
                            'paid'
                        )
                        ->count(),

                    'failed' => Order::whereDate(
                            'created_at',
                            $tanggal
                        )
                        ->where(
                            'payment_status',
                            'failed'
                        )
                        ->count(),

                    'expired' => Order::whereDate(
                            'created_at',
                            $tanggal
                        )
                        ->where(
                            'payment_status',
                            'expired'
                        )
                        ->count(),

                    'updated_at' => now(),

                    'created_at' => now(),
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | ⚠️ LAPORAN STOK
        |--------------------------------------------------------------------------
        */
        foreach ($produk as $item) {

            $status = 'aman';

            if ($item->stok <= 0) {

                $status = 'habis';

            } elseif ($item->stok <= 15) {

                $status = 'menipis';
            }

            DB::table('laporan_stok')
                ->updateOrInsert(

                    [
                        'produk_id' => $item->id
                    ],

                    [

                        'stok' => $item->stok,

                        'status' => $status,

                        'updated_at' => now(),

                        'created_at' => now(),
                    ]
                );
        }
    }
}