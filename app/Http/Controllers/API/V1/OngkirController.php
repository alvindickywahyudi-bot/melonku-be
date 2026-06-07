<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\ShippingCourier;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OngkirController extends Controller
{
    
    
    
    /*
    |--------------------------------------------------------------------------
    | 🚚 CHECK SHIPPING COST
    |--------------------------------------------------------------------------
    */
    public function cost(Request $request)
    {
$request->validate([

    'destination' => 'required|integer',

    'weight' => 'required|integer|min:1',

    'courier' => 'required|string',
]);

$courier = ShippingCourier::where(
    'code',
    strtolower($request->courier)
)->first();

if (!$courier || !$courier->is_active) {

    return response()->json([
        'success' => false,
        'message' => 'Kurir tidak aktif'
    ], 422);
}

$shippingSetting = \App\Models\ShippingSetting::first();
$origin = $shippingSetting->origin_city_id ?? config('services.rajaongkir.origin');

Log::info('ORIGIN ID', [
    'origin' => $origin
]);


try {

    $originCheck = Http::withHeaders([
        'key' => config('services.rajaongkir.api_key'),
        'Accept' => 'application/json',
    ])->get(
        config('services.rajaongkir.base_url')
        . '/destination/district/' . $origin
    );

    Log::info('ORIGIN LOOKUP', [
        'origin_id' => $origin,
        'response' => $originCheck->json()
    ]);

} catch (\Throwable $e) {

    Log::error('ORIGIN LOOKUP ERROR', [
        'message' => $e->getMessage()
    ]);

}

Log::info('ONGKIR REQUEST', [
    'origin' => $origin,
    'destination' => $request->destination,
    'weight' => $request->weight,
    'courier' => $request->courier,
]);
        try {

            /*
            |--------------------------------------------------------------------------
            | 🔥 REQUEST RAJAONGKIR
            |--------------------------------------------------------------------------
            */
            $response = Http::withHeaders([

                'key' => config('services.rajaongkir.api_key'),

                'Accept' => 'application/json',

            ])->asForm()->post(

                config('services.rajaongkir.base_url')
                . '/calculate/domestic-cost',

                [

                    'origin' => $origin,

                    'destination' => $request->destination,

                    'weight' => $request->weight,

                    'courier' => strtolower($request->courier),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | ❌ FAILED RESPONSE
            |--------------------------------------------------------------------------
            */
            if (!$response->successful()) {

                Log::error('RajaOngkir Error', [

                    'status' => $response->status(),

                    'body' => $response->body(),
                ]);

                return response()->json([

                    'success' => false,

                    'message' => 'Gagal mengambil ongkir',

                    'errors' => $response->json(),

                ], $response->status());
            }

            $result = $response->json();
Log::info('RAJAONGKIR RESULT', [
    'origin' => $origin,
    'destination' => $request->destination,
    'response' => $result,
]);
            /*
            |--------------------------------------------------------------------------
            | ✅ SUCCESS
            |--------------------------------------------------------------------------
            */
            return response()->json([

                'success' => true,

                'message' => 'Berhasil mengambil ongkir',

                'data' => $result,
            ]);

        } catch (\Throwable $e) {

            Log::error('Ongkir Error: ' . $e->getMessage());

            return response()->json([

                'success' => false,

                'message' => 'Internal Server Error',

                'errors' => [
                    $e->getMessage()
                ]

            ], 500);
        }
        
        
    }
public function searchDestination(Request $request)
{
    try {

Log::info('SEARCH DESTINATION', [
    'keyword' => $request->keyword,
    'base_url' => config('services.rajaongkir.base_url'),
]);

Log::info('PENGIRIM', [
    'origin_id' => config('services.rajaongkir.origin'),
    'origin_name' => ''
]);


        $response = Http::withHeaders([
            'key' => config('services.rajaongkir.api_key')
        ])->get(
            config('services.rajaongkir.base_url')
            . '/destination/domestic-destination',
            [
                'search' => $request->keyword,
                'limit' => 10,
            ]
        );

        Log::info('RAJAONGKIR RESPONSE', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return response()->json(
            $response->json()
        );

    } catch (\Throwable $e) {

        Log::error('SEARCH DESTINATION ERROR', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}
public function couriers()
{
    return response()->json([
        'success' => true,
        'data' => ShippingCourier::where(
            'is_active',
            true
        )
        ->whereIn('code', [
            'jne',
            'jnt'
        ])
        ->orderBy('name')
        ->get()
    ]);
}

}