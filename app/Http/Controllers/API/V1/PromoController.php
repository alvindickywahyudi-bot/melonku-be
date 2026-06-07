<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;

use App\Models\Promo;
use App\http\Resources\PANEL\PromoResource;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 🎁 LIST PROMO ACTIVE
    |--------------------------------------------------------------------------
    */
public function index(Request $request)
{
    $promo = Promo::query()

        /*
        |--------------------------------------------------------------------------
        | ACTIVE ONLY
        |--------------------------------------------------------------------------
        */
        // ->active()

        /*
        |--------------------------------------------------------------------------
        | RELATION
        |--------------------------------------------------------------------------
        */
        ->with('produk')

        /*
        |--------------------------------------------------------------------------
        | FILTER SEARCH
        |--------------------------------------------------------------------------
        */
        ->when($request->search, function ($q) use ($request) {

            $q->where(
                'nama',
                'like',
                '%' . $request->search . '%'
            );
        })

        /*
        |--------------------------------------------------------------------------
        | LATEST
        |--------------------------------------------------------------------------
        */
        ->latest()

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */
        ->paginate(
            $request->limit ?? 10
        );

    return response()->json([

        'message' => 'List promo aktif',

        'data' => PromoResource::collection($promo),

        'meta' => [

            'current_page' => $promo->currentPage(),

            'last_page' => $promo->lastPage(),

            'per_page' => $promo->perPage(),

            'total' => $promo->total(),
        ]
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | 🎁 DETAIL PROMO
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $promo = Promo::query()

            ->active()

            ->with('produk')

            ->find($id);

        if (!$promo) {

            return response()->json([

                'message' => 'Promo tidak ditemukan'

            ], 404);
        }

        return response()->json([

            'message' => 'Detail promo',

            'data' => [

                'id' => $promo->id,

                'nama' => $promo->nama,

                'slug' => $promo->slug,

                'deskripsi' => $promo->deskripsi,

                'label' => $promo->label,

                'banner_url' => $promo->banner_url,

                'tanggal_mulai' => $promo->tanggal_mulai,

                'tanggal_selesai' => $promo->tanggal_selesai,

                'produk' => $promo->produk
            ]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🎁 CHECK PROMO VOUCHER
    |--------------------------------------------------------------------------
    */
    public function check(Request $request)
    {
        $request->validate([
            'kode_promo' => 'required|string',
        ]);

        $code = strtoupper($request->kode_promo);

        $promo = Promo::whereRaw('UPPER(kode_promo) = ?', [$code])
            ->active()
            ->first();

        if (!$promo) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher tidak ditemukan atau tidak aktif.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil digunakan',
            'data' => [
                'id' => $promo->id,
                'nama' => $promo->nama,
                'kode_promo' => $promo->kode_promo,
                'tipe' => $promo->tipe,
                'diskon' => (int) $promo->diskon,
                'minimal_belanja' => (int) $promo->minimal_belanja,
                'maksimal_diskon' => (int) $promo->maksimal_diskon,
                'status' => (bool) $promo->status,
                'tanggal_mulai' => $promo->tanggal_mulai ? $promo->tanggal_mulai->toDateString() : null,
                'tanggal_selesai' => $promo->tanggal_selesai ? $promo->tanggal_selesai->toDateString() : null,
            ]
        ]);
    }
}