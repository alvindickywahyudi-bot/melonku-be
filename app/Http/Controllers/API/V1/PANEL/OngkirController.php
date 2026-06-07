<?php

namespace App\Http\Controllers\API\V1\PANEL;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ShippingSetting;
use App\Models\ShippingCourier;

class OngkirController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET SETTING ONGKIR
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $setting = ShippingSetting::first();

$couriers = ShippingCourier::whereIn(
        'code',
        ['jne', 'jnt']
    )
    ->orderBy('name')
    ->get();

        return response()->json([
            'message' => 'Data ongkir',

            'data' => [
                'setting' => $setting,
                'couriers' => $couriers,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE SETTING ONGKIR
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $request->validate([
            'origin_city_id' =>
                'nullable|string|max:50',

            'origin_city_name' =>
                'required|string|max:255',

            'origin_province_name' =>
                'required|string|max:255',

            'warehouse_name' =>
                'required|string|max:255',

            'rajaongkir_api_key' =>
                'nullable|string',

            'is_rajaongkir_active' =>
                'nullable|boolean',
        ]);

        $setting = ShippingSetting::first();

        if (!$setting) {

            $setting = ShippingSetting::create([
                'origin_city_id' => '5667',
                'origin_city_name' => 'Bojonegoro',
                'origin_province_name' => 'Jawa Timur',
                'warehouse_name' => 'Gudang Melonku Pusat',
            ]);
        }

        $setting->update([
            'origin_city_id' =>
                $request->origin_city_id ?? $setting->origin_city_id,

            'origin_city_name' =>
                $request->origin_city_name,

            'origin_province_name' =>
                $request->origin_province_name,

            'warehouse_name' =>
                $request->warehouse_name,

            'rajaongkir_api_key' =>
                $request->rajaongkir_api_key,

            'is_rajaongkir_active' =>
                $request->is_rajaongkir_active ?? $setting->is_rajaongkir_active ?? false,
        ]);

        return response()->json([
            'message' => 'Pengaturan ongkir berhasil diperbarui',
            'data' => $setting->fresh(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE KURIR
    |--------------------------------------------------------------------------
    */
    public function toggleCourier($id)
    {
        $courier = ShippingCourier::findOrFail($id);
            if (!in_array($courier->code, ['jne', 'jnt'])) {
                abort(404);
            }
        $courier->update([
            'is_active' => !$courier->is_active,
        ]);

        return response()->json([
            'message' => 'Status kurir berhasil diperbarui',
            'data' => $courier->fresh(),
        ]);
    }
}