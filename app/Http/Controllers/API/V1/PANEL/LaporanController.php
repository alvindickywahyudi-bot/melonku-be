<?php

namespace App\Http\Controllers\API\V1\PANEL;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Produk;

class LaporanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 📊 DASHBOARD LAPORAN ADMIN
    |--------------------------------------------------------------------------
    */
    public function index()
{
    $period = request('period', 'weekly');
        try {

            /*
            |--------------------------------------------------------------------------
            | 💰 TOTAL REVENUE
            |--------------------------------------------------------------------------
            */
            $totalRevenue = Order::where(
                    'payment_status',
                    Order::PAYMENT_PAID
                )
                ->sum('total_price');

            /*
            |--------------------------------------------------------------------------
            | 📦 TOTAL ORDER
            |--------------------------------------------------------------------------
            */
            $totalOrders = Order::count();

            /*
            |--------------------------------------------------------------------------
            | 🍈 TOTAL PRODUK TERJUAL
            |--------------------------------------------------------------------------
            */
            $totalProdukTerjual = OrderItem::sum('qty');

            /*
            |--------------------------------------------------------------------------
            | 👥 TOTAL CUSTOMER
            |--------------------------------------------------------------------------
            */
            $totalCustomer = User::whereHas(
                    'roles',
                    function ($q) {

                        $q->where(
                            'nama',
                            'customer'
                        );
                    }
                )
                ->count();

            /*
            |--------------------------------------------------------------------------
            | 👤 CUSTOMER BARU
            |--------------------------------------------------------------------------
            */
            $customerBaru = User::whereHas(
                    'roles',
                    function ($q) {

                        $q->where(
                            'nama',
                            'customer'
                        );
                    }
                )
                ->whereDate(
                    'created_at',
                    '>=',
                    now()->subDays(30)
                )
                ->count();

            /*
            |--------------------------------------------------------------------------
            | 💳 AVG ORDER VALUE
            |--------------------------------------------------------------------------
            */
            $avgOrderValue =
                $totalOrders > 0
                    ? (int) (
                        $totalRevenue /
                        $totalOrders
                    )
                    : 0;

            /*
            |--------------------------------------------------------------------------
            | 📈 CHART PENJUALAN 7 HARI
            |--------------------------------------------------------------------------
            */
$chart = [];

if ($period === 'weekly') {

    for ($i = 6; $i >= 0; $i--) {

        $date = now()->subDays($i);

        $chart[] = [

            'label' => $date->translatedFormat('D'),

            'total' => Order::whereDate(
                'created_at',
                $date
            )
            ->where(
                'payment_status',
                Order::PAYMENT_PAID
            )
            ->sum('total_price'),
        ];
    }

} elseif ($period === 'monthly') {

    for ($i = 29; $i >= 0; $i--) {

        $date = now()->subDays($i);

        $chart[] = [

            'label' => $date->format('d'),

            'total' => Order::whereDate(
                'created_at',
                $date
            )
            ->where(
                'payment_status',
                Order::PAYMENT_PAID
            )
            ->sum('total_price'),
        ];
    }

} else {

    for ($i = 1; $i <= 12; $i++) {

        $chart[] = [

            'label' => Carbon::create()
                ->month($i)
                ->translatedFormat('M'),

            'total' => Order::whereYear(
                'created_at',
                now()->year
            )
            ->whereMonth(
                'created_at',
                $i
            )
            ->where(
                'payment_status',
                Order::PAYMENT_PAID
            )
            ->sum('total_price'),
        ];
    }
} {

                $date = now()->subDays($i);

                $orders = Order::whereDate(
                        'created_at',
                        $date
                    )
                    ->where(
                        'payment_status',
                        Order::PAYMENT_PAID
                    );

                $chart[] = [

                    'label' => Carbon::parse($date)
                        ->translatedFormat('D'),

                    'tanggal' => Carbon::parse($date)
                        ->format('Y-m-d'),

                    'total_order' =>
                        $orders->count(),

                    'revenue' =>
                        (int) $orders
                            ->sum('total_price'),
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | 🏆 TOP PRODUCTS
            |--------------------------------------------------------------------------
            */
            $topProducts = DB::table('order_items')

                ->join(
                    'produk',
                    'produk.id',
                    '=',
                    'order_items.product_id'
                )

                ->join(
                    'orders',
                    'orders.id',
                    '=',
                    'order_items.order_id'
                )

                ->where(
                    'orders.payment_status',
                    Order::PAYMENT_PAID
                )

                ->select(

                    'produk.id',

                    'produk.nama',

                    'produk.gambar',

                    DB::raw(
                        'SUM(order_items.qty) as total_terjual'
                    ),

                    DB::raw(
                        'SUM(
                            order_items.qty *
                            order_items.price
                        ) as revenue'
                    )
                )

                ->groupBy(
                    'produk.id',
                    'produk.nama',
                    'produk.gambar'
                )

                ->orderByDesc(
                    'total_terjual'
                )

                ->take(5)

                ->get();

            /*
            |--------------------------------------------------------------------------
            | 📦 ORDER STATUS
            |--------------------------------------------------------------------------
            */
            $orderStatus = [

                'pending' => Order::where(
                    'status',
                    Order::STATUS_PENDING
                )->count(),

                'diproses' => Order::where(
                    'status',
                    Order::STATUS_PROCESSED
                )->count(),

                'dikemas' => Order::where(
                    'status',
                    Order::STATUS_PACKED
                )->count(),

                'dikirim' => Order::where(
                    'status',
                    Order::STATUS_SHIPPED
                )->count(),

                'selesai' => Order::where(
                    'status',
                    Order::STATUS_COMPLETED
                )->count(),

                'dibatalkan' => Order::where(
                    'status',
                    Order::STATUS_CANCELLED
                )->count(),
            ];

            /*
            |--------------------------------------------------------------------------
            | 💳 PAYMENT STATUS
            |--------------------------------------------------------------------------
            */
            $paymentStatus = [

                'pending' => Order::where(
                    'payment_status',
                    Order::PAYMENT_PENDING
                )->count(),

                'paid' => Order::where(
                    'payment_status',
                    Order::PAYMENT_PAID
                )->count(),

                'failed' => Order::where(
                    'payment_status',
                    Order::PAYMENT_FAILED
                )->count(),

                'expired' => Order::where(
                    'payment_status',
                    Order::PAYMENT_EXPIRED
                )->count(),
            ];

            /*
            |--------------------------------------------------------------------------
            | 🌱 GREENHOUSE PERFORMANCE
            |--------------------------------------------------------------------------
            */
            $greenhousePerformance = DB::table('produk')

                ->join(
                    'greenhouse',
                    'greenhouse.id',
                    '=',
                    'produk.greenhouse_id'
                )

                ->leftJoin(
                    'order_items',
                    'order_items.product_id',
                    '=',
                    'produk.id'
                )

                ->leftJoin(
                    'orders',
                    'orders.id',
                    '=',
                    'order_items.order_id'
                )

                ->where(function ($query) {

                    $query

                        ->whereNull('orders.id')

                        ->orWhere(
                            'orders.payment_status',
                            Order::PAYMENT_PAID
                        );
                })

                ->select(

                    'greenhouse.id',

                    'greenhouse.nama',

                    DB::raw(
                        'COUNT(DISTINCT produk.id)
                        as total_produk'
                    ),

                    DB::raw(
                        'COALESCE(
                            SUM(order_items.qty),
                            0
                        ) as total_terjual'
                    ),

                    DB::raw(
                        'COALESCE(
                            SUM(
                                order_items.qty *
                                order_items.price
                            ),
                            0
                        ) as revenue'
                    )
                )

                ->groupBy(
                    'greenhouse.id',
                    'greenhouse.nama'
                )

                ->orderByDesc('revenue')

                ->get();

            /*
            |--------------------------------------------------------------------------
            | ⚠️ STOK MENIPIS
            |--------------------------------------------------------------------------
            */
            $stokMenipis = Produk::where(
                    'stok',
                    '<=',
                    10
                )
                ->orderBy('stok')

                ->take(10)

                ->get([

                    'id',

                    'nama',

                    'stok',

                    'harga',

                    'gambar'
                ]);

            /*
            |--------------------------------------------------------------------------
            | 📈 GROWTH REVENUE
            |--------------------------------------------------------------------------
            */
            $currentWeekRevenue = Order::whereBetween(
                    'created_at',
                    [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]
                )
                ->where(
                    'payment_status',
                    Order::PAYMENT_PAID
                )
                ->sum('total_price');

            $previousWeekRevenue = Order::whereBetween(
                    'created_at',
                    [
                        now()
                            ->subWeek()
                            ->startOfWeek(),

                        now()
                            ->subWeek()
                            ->endOfWeek()
                    ]
                )
                ->where(
                    'payment_status',
                    Order::PAYMENT_PAID
                )
                ->sum('total_price');

            $revenueGrowth = 0;

            if ($previousWeekRevenue > 0) {

                $revenueGrowth = round(

                    (
                        (
                            $currentWeekRevenue -
                            $previousWeekRevenue
                        )

                        /

                        $previousWeekRevenue
                    ) * 100,

                    1
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 📈 ORDER GROWTH
            |--------------------------------------------------------------------------
            */
            $currentWeekOrders = Order::whereBetween(
                    'created_at',
                    [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]
                )
                ->count();

            $previousWeekOrders = Order::whereBetween(
                    'created_at',
                    [
                        now()
                            ->subWeek()
                            ->startOfWeek(),

                        now()
                            ->subWeek()
                            ->endOfWeek()
                    ]
                )
                ->count();

            $orderGrowth = 0;

            if ($previousWeekOrders > 0) {

                $orderGrowth = round(

                    (
                        (
                            $currentWeekOrders -
                            $previousWeekOrders
                        )

                        /

                        $previousWeekOrders
                    ) * 100,

                    1
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 📈 CUSTOMER GROWTH
            |--------------------------------------------------------------------------
            */
            $currentWeekCustomer = User::whereDate(
                    'created_at',
                    '>=',
                    now()->startOfWeek()
                )
                ->count();

            $previousWeekCustomer = User::whereBetween(
                    'created_at',
                    [
                        now()
                            ->subWeek()
                            ->startOfWeek(),

                        now()
                            ->subWeek()
                            ->endOfWeek()
                    ]
                )
                ->count();

            $customerGrowth = 0;

            if ($previousWeekCustomer > 0) {

                $customerGrowth = round(

                    (
                        (
                            $currentWeekCustomer -
                            $previousWeekCustomer
                        )

                        /

                        $previousWeekCustomer
                    ) * 100,

                    1
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 🧾 TRANSAKSI TERBARU
            |--------------------------------------------------------------------------
            */
            $transactions = Order::latest()

                ->take(10)

                ->get()

                ->map(function ($item) {

                    return [

                        'id' =>
                            $item->midtrans_order_id,

                        'customer' =>
                            $item->receiver_name,

                        'total' =>
                            (int) $item->total_price,

                        'status' =>
                            $item->status,

                        'payment_status' =>
                            $item->payment_status,

                        'created_at' =>
                            $item->created_at
                                ? $item->created_at
                                    ->format('d M Y H:i')
                                : null,
                    ];
                });

            /*
            |--------------------------------------------------------------------------
            | 🚀 RESPONSE
            |--------------------------------------------------------------------------
            */
            return response()->json([

                'success' => true,

                'data' => [

                    /*
                    |--------------------------------------------------------------------------
                    | SUMMARY
                    |--------------------------------------------------------------------------
                    */
                    'stats' => [

                        'total_revenue' =>
                            (int) $totalRevenue,

                        'total_orders' =>
                            (int) $totalOrders,

                        'total_produk_terjual' =>
                            (int) $totalProdukTerjual,

                        'total_customer' =>
                            (int) $totalCustomer,

                        'customer_baru' =>
                            (int) $customerBaru,

                        'avg_order_value' =>
                            (int) $avgOrderValue,
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | GROWTH
                    |--------------------------------------------------------------------------
                    */
                    'growth' => [

                        'revenue_growth' =>
                            $revenueGrowth,

                        'order_growth' =>
                            $orderGrowth,

                        'customer_growth' =>
                            $customerGrowth,
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | CHART
                    |--------------------------------------------------------------------------
                    */
                    'chart' => $chart,

                    /*
                    |--------------------------------------------------------------------------
                    | TOP PRODUCTS
                    |--------------------------------------------------------------------------
                    */
                    'top_products' =>
                        $topProducts,

                    /*
                    |--------------------------------------------------------------------------
                    | ORDER STATUS
                    |--------------------------------------------------------------------------
                    */
                    'order_status' =>
                        $orderStatus,

                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT STATUS
                    |--------------------------------------------------------------------------
                    */
                    'payment_status' =>
                        $paymentStatus,

                    /*
                    |--------------------------------------------------------------------------
                    | GREENHOUSE
                    |--------------------------------------------------------------------------
                    */
                    'greenhouse_performance' =>
                        $greenhousePerformance,

                    /*
                    |--------------------------------------------------------------------------
                    | STOK MENIPIS
                    |--------------------------------------------------------------------------
                    */
                    'stok_menipis' =>
                        $stokMenipis,

                    /*
                    |--------------------------------------------------------------------------
                    | TRANSAKSI
                    |--------------------------------------------------------------------------
                    */
                    'transactions' =>
                        $transactions,
                ]
            ]);

        } catch (\Throwable $e) {

            Log::error($e);

            return response()->json([

                'success' => false,

                'message' => 'Internal Server Error',

                'errors' => [
                    $e->getMessage()
                ]
            ], 500);
        }
    }
}