<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 🌐 ROOT (ONLY HEALTH CHECK)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return response()->json([
        'message' => 'API is running 🚀'
    ]);
});

/*
|--------------------------------------------------------------------------
| 🧪 OPTIONAL TEST (BOLEH DIHAPUS JIKA PRODUCTION)
|--------------------------------------------------------------------------
*/
Route::prefix('test')->group(function () {

    Route::get('/', function () {
        return response()->json([
            'message' => 'Test route OK'
        ]);
    });

});