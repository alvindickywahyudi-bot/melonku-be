<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\API\V1\CartController;
use App\Http\Controllers\API\V1\CheckoutController;

// AUTH
use App\Http\Controllers\API\V1\AuthController;

// OPTIONS
use App\Http\Controllers\API\V1\WilayahController;

// USER
use App\Http\Controllers\API\V1\UserProfileController;
// ADOPTION PROJECT
use App\Http\Controllers\API\V1\AdoptionProjectController;
use App\Http\Controllers\API\V1\AdoptionTransactionController;
use App\Http\Controllers\API\V1\AdoptionInvestmentController;
// PUBLIC
use App\Http\Controllers\API\V1\PUBLIC\ProdukController as ProdukPublicController;
use App\Http\Controllers\ProdukController as ProdukRefactorController;
use App\Http\Controllers\API\V1\PromoController as PromoPublicController;

// PANEL
use App\Http\Controllers\API\V1\PANEL\ProdukController;
use App\Http\Controllers\API\V1\PANEL\PromoController;
use App\Http\Controllers\API\V1\PANEL\GreenhouseController;
use App\Http\Controllers\API\V1\PANEL\DashboardController;
use App\Http\Controllers\API\V1\PANEL\OrderController;
use App\Http\Controllers\API\V1\PANEL\CustomerController;
use App\Http\Controllers\API\V1\PANEL\LaporanController;
use App\Http\Controllers\API\V1\OngkirController;
use App\Http\Controllers\API\V1\AiCustomerServiceController;
use App\Http\Controllers\API\V1\PANEL\OngkirController as PanelOngkirController;
use App\Http\Controllers\API\V1\PANEL\AdminNotificationController;
/*
|--------------------------------------------------------------------------
| 🌐 ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => response()->json([
    'message' => 'API is running 🚀'
]));

/*
|--------------------------------------------------------------------------
| 📁 FILE ACCESS
|--------------------------------------------------------------------------
*/
Route::get('/upload/{pathA}/{pathB}/{pathC?}', function (
    $pathA,
    $pathB,
    $pathC = null
) {

    $path = "{$pathA}/{$pathB}" . ($pathC ? "/{$pathC}" : '');

    if (!Storage::exists($path)) {

        return response()->json([
            'message' => 'File tidak ditemukan'
        ], 404);
    }

    $mime = Storage::mimeType($path);

    $allowed = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword',
        'application/vnd.ms-excel',
    ];

    if (!in_array($mime, $allowed)) {

        return response()->json([
            'message' => 'File tidak diizinkan'
        ], 403);
    }

    return response()->file(storage_path("app/{$path}"));
});

/*
|--------------------------------------------------------------------------
| 🔐 AUTH PUBLIC
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {

    // REGISTER
    Route::post('/register', [
        AuthController::class,
        'register'
    ]);

    // LOGIN USER
    Route::post('/login', [
        AuthController::class,
        'login'
    ]);

    // LOGIN GOOGLE
    Route::post('/google', [
        AuthController::class,
        'google'
    ]);

    // OPTIONAL SOCIALITE
    Route::get('/google/redirect', [
        AuthController::class,
        'redirectToGoogle'
    ]);

    Route::get('/google/callback', [
        AuthController::class,
        'handleGoogleCallback'
    ]);
});

/*
|--------------------------------------------------------------------------
| 🔐 AUTH PROTECTED
|--------------------------------------------------------------------------
*/
Route::prefix('auth')
    ->middleware('auth:api')
    ->group(function () {

        Route::get('/me', [
            AuthController::class,
            'me'
        ]);

        Route::post('/logout', [
            AuthController::class,
            'logout'
        ]);
    });

/*
|--------------------------------------------------------------------------
| 🛍 PUBLIC PRODUK
|--------------------------------------------------------------------------
*/
Route::prefix('produk')
    ->controller(ProdukRefactorController::class)
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | 📦 LIST PRODUK
        |--------------------------------------------------------------------------
        */
        Route::get('/', 'index');

        /*
        |--------------------------------------------------------------------------
        | 🔍 DETAIL PRODUK
        |--------------------------------------------------------------------------
        */
        Route::get('/{id}', 'show');
    });

/*
|--------------------------------------------------------------------------
| 🌍 WILAYAH
|--------------------------------------------------------------------------
*/
Route::prefix('wilayah')
    ->controller(WilayahController::class)
    ->group(function () {

        // PROVINSI
        Route::get('/provinsi', 'provinsi');

        // KABUPATEN
        Route::get('/kabupaten/{id}', 'kabupaten');

        // KECAMATAN
        Route::get('/kecamatan/{id}', 'kecamatan');

        //DESA
        Route::get('/village/{id}', [WilayahController::class, 'village']);

        // GREENHOUSE OPTION
        Route::get('/greenhouse', 'greenhouse');

    });

/*
|--------------------------------------------------------------------------
| 👤 PROFILE
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    Route::prefix('profile')->group(function () {

        Route::get('/', [
            UserProfileController::class,
            'show'
        ]);

        Route::post('/', [
            UserProfileController::class,
            'update'
        ]);

        Route::post('/change-phone', [
            UserProfileController::class,
            'changePhone'
        ]);

        Route::post('/change-password', [
            UserProfileController::class,
            'changePassword'
        ]);

        Route::post('/connect-google', [
            UserProfileController::class,
            'connectGoogle'
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| 🎁 PUBLIC PROMO
|--------------------------------------------------------------------------
*/
Route::prefix('promo')
    ->controller(PromoPublicController::class)
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | 🖼️ BANNER PROMO
        |--------------------------------------------------------------------------
        */
        Route::get('/banner', 'banner');

        /*
        |--------------------------------------------------------------------------
        | LIST PROMO
        |--------------------------------------------------------------------------
        */
        Route::get('/', 'index');

        /*
        |--------------------------------------------------------------------------
        | CHECK PROMO VOUCHER
        |--------------------------------------------------------------------------
        */
        Route::post('/check', 'check');

        /*
        |--------------------------------------------------------------------------
        | DETAIL PROMO
        |--------------------------------------------------------------------------
        */
        Route::get('/{id}', 'show');
    });

/*
|--------------------------------------------------------------------------
| 👑 ADMIN LOGIN
|--------------------------------------------------------------------------
*/
Route::post('/admin/login', [
    AuthController::class,
    'adminLogin'
]);

/*
|--------------------------------------------------------------------------
| 👑 PANEL ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('panel')
    ->middleware(['auth:api', 'role:admin'])
    ->group(function () {
        
    

    Route::get(
        '/notifications',
        [AdminNotificationController::class, 'index']
    );

    Route::get(
        '/notifications/unread',
        [AdminNotificationController::class, 'unread']
    );

    Route::post(
        '/notifications/{id}/read',
        [AdminNotificationController::class, 'markRead']
    );

     

            /*
            |--------------------------------------------------------------------------
            | 📊 DASHBOARD
            |--------------------------------------------------------------------------
            */
            Route::get('/dashboard', [
                DashboardController::class,
                'index'
            ]);

            /*
            |--------------------------------------------------------------------------
            | 📦 PRODUK
            |--------------------------------------------------------------------------
            */
            Route::prefix('produk')->controller(ProdukController::class)->group(function () {

            Route::get('/', 'index');

            Route::post('/', 'store');

            Route::get('/{produk}', 'show');

            Route::put('/{produk}', 'update');

            Route::delete('/{produk}', 'destroy');

            Route::post('/{id}/restore', 'restore');

            Route::delete('/{id}/force-delete', 'forceDelete');
    
    
    //Notification

       /*
            |--------------------------------------------------------------------------
            | 🌱 ADOPTION PROJECT
            |--------------------------------------------------------------------------
            */
            Route::prefix('adoption-projects')
                ->controller(AdoptionProjectController::class)
                ->group(function () {

                    Route::get('/', 'index');

                    Route::post('/', 'store');

                    Route::get('/{id}', 'show');

                    Route::put('/{id}', 'update');

                    Route::delete('/{id}', 'destroy');
                });


        });
        
Route::get(
    '/ongkir',
    [PanelOngkirController::class, 'index']
);

Route::put(
    '/ongkir',
    [PanelOngkirController::class, 'update']
);

Route::patch(
    '/ongkir/courier/{id}',
    [PanelOngkirController::class, 'toggleCourier']
);

            /*
            |--------------------------------------------------------------------------
            | 🌱 GREENHOUSE
            |--------------------------------------------------------------------------
            */
            Route::prefix('greenhouse')
                ->controller(GreenhouseController::class)
                ->group(function () {

                    Route::get('/', 'index');

                    Route::post('/', 'store');

                    Route::get('/{greenhouse}', 'show');

                    Route::put('/{greenhouse}', 'update');

                    Route::delete('/{greenhouse}', 'destroy');

                    Route::post('/{id}/restore', 'restore');

                    Route::delete('/{id}/force-delete', 'forceDelete');
                });

            /*
            |--------------------------------------------------------------------------
            | 📦 ORDER MANAGEMENT
            |--------------------------------------------------------------------------
            */
            Route::prefix('orders')
                ->controller(OrderController::class)
                ->group(function () {

                    // LIST ORDER
                    Route::get('/', 'index');

                    // DETAIL ORDER
                    Route::get('/{id}', 'show');

                    // UPDATE STATUS
                    Route::post('/{id}/status', 'updateStatus');
                });

            /*
            |--------------------------------------------------------------------------
            | 👥 CUSTOMER MANAGEMENT
            |--------------------------------------------------------------------------
            */
            Route::prefix('customers')
                ->controller(CustomerController::class)
                ->group(function () {

                    // LIST CUSTOMER
                    Route::get('/', 'index');

                    // DETAIL CUSTOMER
                    Route::get('/{id}', 'show');

                    // NONAKTIFKAN
                    Route::post('/{id}/deactivate', 'deactivate');

                    // AKTIFKAN
                    Route::post('/{id}/activate', 'activate');
                });


                Route::get(
                    '/laporan',
                    [LaporanController::class, 'index']
                );



            /*
            |--------------------------------------------------------------------------
            | PROMO
            |--------------------------------------------------------------------------
            */
            Route::prefix('promo')
                ->controller(PromoController::class)
                ->group(function () {

                    Route::get('/', 'index');

                    Route::post('/', 'store');

                    Route::get('/{promo}', 'show');

                    Route::put('/{promo}', 'update');

                    Route::delete('/{promo}', 'destroy');

                    /*
                    |--------------------------------------------------------------------------
                    | 🎁 ASSIGN PRODUK KE PROMO
                    |--------------------------------------------------------------------------
                    */
                    Route::post(
                        '/{promo}/assign-product',
                        'assignProduct'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | ❌ REMOVE PRODUK DARI PROMO
                    |--------------------------------------------------------------------------
                    */
                    Route::post(
                        '/{promo}/remove-product',
                        'removeProduct'
                    );
                });  

    });

/*
|--------------------------------------------------------------------------
| 💳 PAYMENT CALLBACK
|--------------------------------------------------------------------------
*/

Route::post('/payment/notification', [
    CheckoutController::class,
    'notification'
]);

    /*
    |--------------------------------------------------------------------------
    | 🛒 CUSTOMER AREA
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'auth:api',
        'role:customer'
    ])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | CART
        |--------------------------------------------------------------------------
        */
        Route::prefix('cart')->group(function () {

            Route::get('/', [
                CartController::class,
                'index'
            ]);

            Route::post('/add', [
                CartController::class,
                'add'
            ]);

            Route::post('/update', [
                CartController::class,
                'update'
            ]);

            Route::delete('/remove/{id}', [
                CartController::class,
                'remove'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | CHECKOUT
        |--------------------------------------------------------------------------
        */
        Route::post('/checkout', [
            CheckoutController::class,
            'checkout'
        ]);

        Route::get('/checkout/pay/{orderId}', [
            CheckoutController::class,
            'pay'
        ]);

        /*
        |--------------------------------------------------------------------------
        | MY ORDERS
        |--------------------------------------------------------------------------
        */
        Route::get('/my-orders', [
            CheckoutController::class,
            'myOrders'
        ]);

        /*
        |--------------------------------------------------------------------------
        | 💰 ADOPTION INVESTMENT
        |--------------------------------------------------------------------------
        */

        Route::prefix('adoption')->group(function () {

            /*
            |--------------------------------------------------------------------------
            | BUY SLOT
            |--------------------------------------------------------------------------
            */
            Route::post('/invest', [
                AdoptionTransactionController::class,
                'invest'
            ]);

            /*
            |--------------------------------------------------------------------------
            | MY INVESTMENT
            |--------------------------------------------------------------------------
            */
            Route::get('/my-investments', [
                AdoptionTransactionController::class,
                'myInvestments'
            ]);
        });

    });

/*
|--------------------------------------------------------------------------
| 📦 ORDER DETAIL
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:api',
    'role:customer,admin,kurir'
])->group(function () {

    Route::get('/order/{id}', [
        CheckoutController::class,
        'detail'
    ]);
});

/*
|--------------------------------------------------------------------------
| 🚚 KURIR
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:api',
    'role:kurir'
])->group(function () {

    Route::get('/deliveries', [
        CheckoutController::class,
        'myDeliveries'
    ]);
});

/*
|--------------------------------------------------------------------------
| 🚚 ONGKIR
|--------------------------------------------------------------------------
*/
Route::post('/ongkir', [
    OngkirController::class,
    'cost'
]);

Route::get('/ongkir/couriers', [
    OngkirController::class,
    'couriers'
]);

/*
|--------------------------------------------------------------------------
| AI CUSTOMER SERVICE
|--------------------------------------------------------------------------
*/

    Route::post('/ai/chat', [
        AiCustomerServiceController::class,
        'chat'
    ]);

Route::put(

    '/panel/produk/{produk}/stock',

    [ProdukController::class, 'updateStock']
);


/*
|--------------------------------------------------------------------------
| 🌱 ADOPTION PROJECT
|--------------------------------------------------------------------------
*/
Route::prefix('adoption-projects')
    ->controller(AdoptionProjectController::class)
    ->group(function () {

        Route::get('/', 'index');

        Route::get('/{id}', 'show');
    });

Route::middleware('auth:api')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | 🌱 ADOPTION INVESTMENT
    |--------------------------------------------------------------------------
    */

    Route::prefix('adoption')->group(function () {

        Route::get(
            '/projects',
            [AdoptionProjectController::class, 'index']
        );

        Route::post(
            '/invest',
            [AdoptionInvestmentController::class, 'invest']
        );

        Route::get(
            '/my-investments',
            [AdoptionInvestmentController::class, 'myInvestment']
        );

        /*
        |--------------------------------------------------------------------------
        | 🚀 DASHBOARD
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/dashboard',
            [AdoptionInvestmentController::class, 'dashboard']
        );

    });

});
Route::get('/test-origin', function () {
    return response()->json([
        'origin' => config('services.rajaongkir.origin')
    ]);
});
Route::get('/test-ongkir', function () {

    $controller = app(\App\Http\Controllers\API\V1\OngkirController::class);

    $request = new \Illuminate\Http\Request([
        'destination' => 3522180,
        'weight' => 1000,
        'courier' => 'jne'
    ]);

    return $controller->cost($request);
});
Route::get('/test-search-dander', function () {

    $response = Http::withHeaders([
        'key' => config('services.rajaongkir.api_key')
    ])->get(
        config('services.rajaongkir.base_url')
        . '/destination/domestic-destination',
        [
            'search' => 'Dander'
        ]
    );

    return $response->json();
});
Route::get('/ongkir/search-destination', [
    OngkirController::class,
    'searchDestination'
]);

