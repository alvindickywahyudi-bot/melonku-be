<?php

namespace App\Http\Controllers\API\V1\PANEL;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use Carbon\Carbon;
use App\Models\Promo;
use App\Models\Produk;
use App\Models\Greenhouse;
use App\Models\User;
use App\Models\Order;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 📊 DASHBOARD ADMIN
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)    {

        
        try {
            $type = $request->type ?? 'weekly';
            $month = $request->month ?? now()->month;
$year  = $request->year ?? now()->year;
            /*
            |--------------------------------------------------------------------------
            | 🎁 PROMO AKTIF
            |--------------------------------------------------------------------------
            */
$promoAktif = Promo::where(
    'status',
    1
)
->latest()
->take(5)
->get();
            /*
            |--------------------------------------------------------------------------
            | TOTAL MASTER DATA
            |--------------------------------------------------------------------------
            */
            $totalProduk = Produk::count();


            $totalGreenhouse = Greenhouse::count();

            /*
            |--------------------------------------------------------------------------
            | TOTAL CUSTOMER
            |--------------------------------------------------------------------------
            */
            $totalCustomer = User::whereHas('roles', function ($query) {

                $query->where('nama', 'customer');

            })->count();

            /*
            |--------------------------------------------------------------------------
            | TOTAL ADMIN
            |--------------------------------------------------------------------------
            */
            $totalAdmin = User::whereHas('roles', function ($query) {

                $query->where('nama', 'admin');

            })->count();

            /*
            |--------------------------------------------------------------------------
            | TOTAL STOK
            |--------------------------------------------------------------------------
            */
            $totalStok = Produk::sum('stok');

            /*
            |--------------------------------------------------------------------------
            | TOTAL NILAI PRODUK
            |--------------------------------------------------------------------------
            */
            $totalNilaiProduk = Produk::select(
                DB::raw('SUM(harga * stok) as total')
            )->value('total');

            /*
            |--------------------------------------------------------------------------
            | TOTAL ORDER
            |--------------------------------------------------------------------------
            */
            $totalOrders = Order::count();

            /*
            |--------------------------------------------------------------------------
            | TOTAL REVENUE
            |--------------------------------------------------------------------------
            */
            $totalRevenue = Order::where(
                    'payment_status',
                    Order::PAYMENT_PAID
                )
                ->sum('total_price');

            /*
            |--------------------------------------------------------------------------
            | TOTAL PRODUK TERJUAL
            |--------------------------------------------------------------------------
            */
            $totalProdukTerjual = DB::table('order_items')
                ->sum('qty');

            /*
            |--------------------------------------------------------------------------
            | CUSTOMER BARU
            |--------------------------------------------------------------------------
            */
$customerBaru = User::whereHas('roles', function ($query) {

        $query->where('nama', 'customer');

    })
    ->whereMonth('created_at', $month)
    ->whereYear('created_at', $year)
    ->count();

            /*
            |--------------------------------------------------------------------------
            | PRODUK TERBARU
            |--------------------------------------------------------------------------
            */
            $produkTerbaru = Produk::latest()
                ->take(5)
                ->get([
                    'id',
                    'nama',
                    'harga',
                    'stok',
                    'gambar',
                    'created_at'
                ]);

            /*
            |--------------------------------------------------------------------------
            | STOK MENIPIS
            |--------------------------------------------------------------------------
            */
            $stokMenipis = Produk::where('stok', '<=', 15)
                ->orderBy('stok', 'asc')
                ->take(5)
                ->get([
                    'id',
                    'nama',
                    'stok',
                    'harga',
                    'gambar'
                ]);

            /*
            |--------------------------------------------------------------------------
            | PESANAN TERBARU
            |--------------------------------------------------------------------------
            */
            $pesananTerbaru = Order::with([

                    'user.profile',

                    'items.product',

                    'items.variasi'

                ])
                ->latest()
                ->take(5)
                ->get();

            $topProducts = DB::table('order_items')

                ->join(
                    'orders',
                    'orders.id',
                    '=',
                    'order_items.order_id'
                )

                ->join(
                    'produk',
                    'produk.id',
                    '=',
                    'order_items.product_id'
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
                        'COALESCE(
    SUM(order_items.qty * order_items.price),
    0
) as revenue'
                    )
                )

                ->groupBy(

                    'produk.id',

                    'produk.nama',

                    'produk.gambar'
                )

                ->orderByDesc('total_terjual')

                ->take(5)

                ->get();


                $revenueSummary = [

                'today' => Order::whereDate(
                    'created_at',
                    today()
                )
                ->where(
                    'payment_status',
                    Order::PAYMENT_PAID
                )
                ->sum('total_price'),

                'this_week' => Order::whereBetween(
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
                ->sum('total_price'),

                'this_month' => Order::whereMonth(
                    'created_at',
                    now()->month
                )
                ->where(
                    'payment_status',
                    Order::PAYMENT_PAID
                )
                ->sum('total_price'),
                ];
/*
|--------------------------------------------------------------------------
| 📈 CHART PENJUALAN
|--------------------------------------------------------------------------
*/
if ($type === 'monthly') {

    /*
    |--------------------------------------------------------------------------
    | MONTHLY RAW
    |--------------------------------------------------------------------------
    */
    $chartRaw = Order::select(

            DB::raw('MONTH(created_at) as bulan'),

            DB::raw('COUNT(*) as total_order'),

            DB::raw('SUM(total_price) as total_revenue')

        )
        ->where(
            'payment_status',
            Order::PAYMENT_PAID
        )
        ->whereYear(
            'created_at',
            now()->year
        )
        ->groupBy('bulan')
        ->orderBy('bulan')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | FORMAT MONTHLY
    |--------------------------------------------------------------------------
    */
    $months = collect(range(1, 12));

    $chartPenjualan = $months->map(function ($month)
    use ($chartRaw) {

        $found = $chartRaw->firstWhere(
            'bulan',
            $month
        );

        return [

            'label' => Carbon::create()
                ->month($month)
                ->translatedFormat('M'),

            'total_order' =>
                (int) ($found->total_order ?? 0),

            'total_revenue' =>
                (int) ($found->total_revenue ?? 0),
        ];
    });

} else {

    /*
    |--------------------------------------------------------------------------
    | WEEKLY RAW
    |--------------------------------------------------------------------------
    */
    $chartRaw = Order::select(

            DB::raw('DATE(created_at) as tanggal'),

            DB::raw('COUNT(*) as total_order'),

            DB::raw('SUM(total_price) as total_revenue')

        )
        ->where(
            'payment_status',
            Order::PAYMENT_PAID
        )
        ->whereDate(
            'created_at',
            '>=',
            now()->subDays(6)
        )
        ->groupBy('tanggal')
        ->orderBy('tanggal')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | FORMAT WEEKLY
    |--------------------------------------------------------------------------
    */
    $days = collect();

    for ($i = 6; $i >= 0; $i--) {

        $date = now()->subDays($i);

        $days->push([

            'date' => $date->format('Y-m-d'),

            'label' => $date->translatedFormat('D'),
        ]);
    }

    $chartPenjualan = $days->map(function ($day)
    use ($chartRaw) {

        $found = $chartRaw->firstWhere(
            'tanggal',
            $day['date']
        );

        return [

            'label' => $day['label'],

            'total_order' =>
                (int) ($found->total_order ?? 0),

            'total_revenue' =>
                (int) ($found->total_revenue ?? 0),
        ];
    });
}
            /*
            |--------------------------------------------------------------------------
            | ORDER STATUS
            |--------------------------------------------------------------------------
            */
            $orderStatus = [

                'pending' => Order::where(
                    'status',
                    Order::STATUS_PENDING
                )->count(),

                'processed' => Order::where(
                    'status',
                    Order::STATUS_PROCESSED
                )->count(),

                'packed' => Order::where(
                    'status',
                    Order::STATUS_PACKED
                )->count(),

                'shipped' => Order::where(
                    'status',
                    Order::STATUS_SHIPPED
                )->count(),

                'completed' => Order::where(
                    'status',
                    Order::STATUS_COMPLETED
                )->count(),

                'cancelled' => Order::where(
                    'status',
                    Order::STATUS_CANCELLED
                )->count(),
            ];

            /*
            |--------------------------------------------------------------------------
            | PAYMENT STATUS
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
    | 📦 SALES BY CATEGORY
    |--------------------------------------------------------------------------
    */
    $salesByCategory = DB::table('order_items')

        ->join(
            'orders',
            'orders.id',
            '=',
            'order_items.order_id'
        )

        ->join(
            'produk',
            'produk.id',
            '=',
            'order_items.product_id'
        )

        ->select(

            DB::raw("
                COALESCE(
                    produk.kategori,
                    'Tanpa Kategori'
                ) as kategori
            "),
            DB::raw(
                'SUM(order_items.qty) as total_terjual'
            ),

            DB::raw(
                'COALESCE(
    SUM(order_items.qty * order_items.price),
    0
) as revenue'
            )
        )

        ->where(
            'orders.payment_status',
            Order::PAYMENT_PAID
        )

        ->groupBy('produk.kategori')

        ->orderByDesc('revenue')

        ->get();


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

            ->select(

                'greenhouse.id',

                'greenhouse.nama',

                DB::raw(
                    'COUNT(DISTINCT produk.id) as total_produk'
                ),

                DB::raw(
                    'SUM(order_items.qty) as total_terjual'
                ),

                DB::raw(
                    'COALESCE(
    SUM(order_items.qty * order_items.price),
    0
) as revenue'
                )
            )

            ->where(function ($query) {

                $query

                    ->whereNull('orders.id')

                    ->orWhere(
                        'orders.payment_status',
                        Order::PAYMENT_PAID
                    );
            })

            ->groupBy(
                'greenhouse.id',
                'greenhouse.nama'
            )

            ->orderByDesc('revenue')

            ->get();



/*
|--------------------------------------------------------------------------
| 📈 GROWTH COMPARISON
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

$customerGrowthPercent = 0;

if ($previousWeekCustomer > 0) {

    $customerGrowthPercent = round(

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
            | ANALYTICS
            |--------------------------------------------------------------------------
            */
            $analytics = [

                /*
                |--------------------------------------------------------------------------
                | CONVERSION RATE
                |--------------------------------------------------------------------------
                */
                'conversion_rate' => 84,

                /*
                |--------------------------------------------------------------------------
                | TOTAL CHECKOUT
                |--------------------------------------------------------------------------
                */
                'total_checkout' => $totalOrders,


                'revenue_growth' => $revenueGrowth,
                'customer_growth' => $customerGrowthPercent,
                'order_growth' => $orderGrowth,
                /*
                |--------------------------------------------------------------------------
                | PELANGGAN AKTIF
                |--------------------------------------------------------------------------
                */
                'pelanggan_aktif' => User::whereHas(
                    'orders'
                )->count(),

                /*
                |--------------------------------------------------------------------------
                | AVG ORDER VALUE
                |--------------------------------------------------------------------------
                */
                'average_order_value' => $totalOrders > 0
                    ? (int) ($totalRevenue / $totalOrders)
                    : 0,
            ];

            /*
            |--------------------------------------------------------------------------
            | AKTIVITAS
            |--------------------------------------------------------------------------
            */
            $aktivitas = [

                [
                    'title' => 'Produk baru ditambahkan',
                    'description' => 'Melon premium berhasil ditambahkan',
                    'time' => '5 menit lalu'
                ],

                [
                    'title' => 'Pesanan baru masuk',
                    'description' => 'Order baru menunggu pembayaran',
                    'time' => '12 menit lalu'
                ],

                [
                    'title' => 'Penjualan meningkat',
                    'description' => '+18% dibanding minggu lalu',
                    'time' => '1 jam lalu'
                ],
            ];

                /*
                |--------------------------------------------------------------------------
                | 🔔 NOTIFICATIONS
                |--------------------------------------------------------------------------
                */
                $notifications = [];

                /*
                |--------------------------------------------------------------------------
                | STOK KRITIS
                |--------------------------------------------------------------------------
                */
                if ($stokMenipis->count() > 0) {

                    $notifications[] = [

                        'type' => 'warning',

                        'title' => 'Stok Menipis',

                        'message' =>
                            $stokMenipis->count() .
                            ' produk perlu restock'
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | PAYMENT PENDING
                |--------------------------------------------------------------------------
                */
                $pendingPayment = Order::where(
                    'payment_status',
                    Order::PAYMENT_PENDING
                )->count();

                if ($pendingPayment > 0) {

                    $notifications[] = [

                        'type' => 'info',

                        'title' => 'Pembayaran Pending',

                        'message' =>
                            $pendingPayment .
                            ' pembayaran belum selesai'
                    ];
                }
            /*
            |--------------------------------------------------------------------------
            | DASHBOARD RESPONSE
            |--------------------------------------------------------------------------
            */
            return response()->json([

                'success' => true,

                'message' => 'Dashboard admin',

                'data' => [

                    /*
                    |--------------------------------------------------------------------------
                    | SUMMARY
                    |--------------------------------------------------------------------------
                    */
                    'summary' => [

                        'total_produk' => (int) $totalProduk,

                        'total_greenhouse' => (int) $totalGreenhouse,

                        'total_customer' => (int) $totalCustomer,

                        'total_admin' => (int) $totalAdmin,

                        'total_stok' => (int) $totalStok,

                        'total_nilai_produk' => (int) $totalNilaiProduk,

                        'total_orders' => (int) $totalOrders,

                        'total_revenue' => (int) $totalRevenue,

                        'total_produk_terjual' => (int) $totalProdukTerjual,

                        'customer_baru' => (int) $customerBaru,
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | CHART PENJUALAN
                    |--------------------------------------------------------------------------
                    */
                    'chart_penjualan' => $chartPenjualan,

                    /*
                    |--------------------------------------------------------------------------
                    | ORDER STATUS
                    |--------------------------------------------------------------------------
                    */
                    'order_status' => $orderStatus,

                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT STATUS
                    |--------------------------------------------------------------------------
                    */
                    'payment_status' => $paymentStatus,

                    /*
                    |--------------------------------------------------------------------------
                    | PRODUK TERBARU
                    |--------------------------------------------------------------------------
                    */
                    'produk_terbaru' => $produkTerbaru,
                    /*
                    |--------------------------------------------------------------------------
                    | PROMO AKTIF
                    |--------------------------------------------------------------------------
                    */
                    'promo_aktif' => $promoAktif,

                    /*
                    |--------------------------------------------------------------------------
                    | STOK MENIPIS
                    |--------------------------------------------------------------------------
                    */
                    'stok_menipis' => $stokMenipis,

                    /*
                    |--------------------------------------------------------------------------
                    | PESANAN TERBARU
                    |--------------------------------------------------------------------------
                    */
                    'pesanan_terbaru' => $pesananTerbaru,

                    /*
                    |--------------------------------------------------------------------------
                    | ANALYTICS
                    |--------------------------------------------------------------------------
                    */
                    'analytics' => $analytics,

                    /*
                    |--------------------------------------------------------------------------
                    | AKTIVITAS
                    |--------------------------------------------------------------------------
                    */
                    'aktivitas' => $aktivitas,

                    'top_products' => $topProducts,

                    'revenue_summary' => $revenueSummary,

                    'customer_growth' => $customerGrowthPercent,

                    'notifications' => $notifications,

                    'sales_by_category' => $salesByCategory,

                    'greenhouse_performance' => $greenhousePerformance,
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