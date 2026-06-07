<?php

namespace App\Http\Controllers\API\V1\PANEL;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;

use App\Models\Promo;

use App\Http\Resources\PANEL\PromoResource;

class PromoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 📋 LIST PROMO
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Promo::query()
            ->with('produk')
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | 🔍 SEARCH
        |--------------------------------------------------------------------------
        */
        if ($request->search) {

            $query->where(function ($q) use ($request) {

                $q->where(
                    'nama',
                    'like',
                    '%' . $request->search . '%'
                )

                ->orWhere(
                    'kode_promo',
                    'like',
                    '%' . $request->search . '%'
                );
            });
        }

        $promo = $query->paginate(
            $request->limit ?? 10
        );

        return PromoResource::collection($promo);
    }

    /*
    |--------------------------------------------------------------------------
    | 📄 DETAIL PROMO
    |--------------------------------------------------------------------------
    */
    public function show(Promo $promo)
    {
        $promo->load('produk');

        return response()->json([

            'message' => 'Detail promo',

            'data' => new PromoResource($promo)

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ➕ CREATE PROMO
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'nama' => 'required|string|max:255',

            'kode_promo' => 'nullable|string|max:255|unique:promo,kode_promo',

            'deskripsi' => 'nullable|string',

            'tipe' => 'required|in:percent,nominal',

            'diskon' => 'required|numeric|min:0',

            'minimal_belanja' => 'nullable|numeric|min:0',

            'maksimal_diskon' => 'nullable|numeric|min:0',

            'status' => 'nullable|boolean',

            'tanggal_mulai' => 'nullable|date',

            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',

            'banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'produk_id' => 'nullable|array',

            'produk_id.*' => 'exists:produk,id',

            'is_flashsale' => 'nullable|boolean',

            'flashsale_stock' => 'nullable|integer|min:0',

            'flashsale_limit' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {

            return response()->json([

                'message' => 'Validation Error',

                'errors' => $validator->errors()

            ], 422);
        }

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | UPLOAD BANNER
            |--------------------------------------------------------------------------
            */
            $bannerPath = null;

            if ($request->hasFile('banner')) {

                $bannerPath = $request->file('banner')
                    ->store('promo', 'public');
            }

            /*
            |--------------------------------------------------------------------------
            | CREATE PROMO
            |--------------------------------------------------------------------------
            */
            $promo = Promo::create([

                'nama' => $request->nama,

                'slug' => Str::slug($request->nama),

                'deskripsi' => $request->deskripsi,

                'kode_promo' => $request->kode_promo,

                'tipe' => $request->tipe,

                'diskon' => $request->diskon,

                'minimal_belanja' => $request->minimal_belanja ?? 0,

                'maksimal_diskon' => $request->maksimal_diskon,

                'banner' => $bannerPath,

                'status' => $request->status ?? 1,

                'tanggal_mulai' => $request->tanggal_mulai,

                'tanggal_selesai' => $request->tanggal_selesai,

                'is_flashsale' => $request->is_flashsale ?? false,

                'flashsale_stock' => $request->flashsale_stock,

                'flashsale_limit' => $request->flashsale_limit,
            ]);

            /*
            |--------------------------------------------------------------------------
            | RELASI PRODUK
            |--------------------------------------------------------------------------
            */
            if ($request->produk_id) {

                $promo->produk()
                    ->attach($request->produk_id);
            }

            DB::commit();

            $promo->load('produk');

            return response()->json([

                'message' => 'Promo berhasil dibuat',

                'data' => new PromoResource($promo)

            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error($e);

            return response()->json([

                'message' => 'Internal Server Error',

                'errors' => [
                    $e->getMessage()
                ]

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ✏️ UPDATE PROMO
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Promo $promo)
    {
        $validator = Validator::make($request->all(), [

            'nama' => 'nullable|string|max:255',

            'kode_promo' => 'nullable|string|max:255|unique:promo,kode_promo,' . $promo->id,

            'deskripsi' => 'nullable|string',

            'tipe' => 'nullable|in:percent,nominal',

            'diskon' => 'nullable|numeric|min:0',

            'minimal_belanja' => 'nullable|numeric|min:0',

            'maksimal_diskon' => 'nullable|numeric|min:0',

            'status' => 'nullable|boolean',

            'tanggal_mulai' => 'nullable|date',

            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',

            'banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'produk_id' => 'nullable|array',

            'produk_id.*' => 'exists:produk,id',

            'is_flashsale' => 'nullable|boolean',

            'flashsale_stock' => 'nullable|integer|min:0',

            'flashsale_limit' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {

            return response()->json([

                'message' => 'Validation Error',

                'errors' => $validator->errors()

            ], 422);
        }

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | DEFAULT BANNER
            |--------------------------------------------------------------------------
            */
            $bannerPath = $promo->banner;

            /*
            |--------------------------------------------------------------------------
            | UPDATE BANNER
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('banner')) {

                if ($promo->banner) {

                    Storage::disk('public')
                        ->delete($promo->banner);
                }

                $bannerPath = $request->file('banner')
                    ->store('promo', 'public');
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE PROMO
            |--------------------------------------------------------------------------
            */
            $promo->update([

                'nama' => $request->has('nama')
                    ? $request->nama
                    : $promo->nama,

                'slug' => $request->has('nama')
                    ? Str::slug($request->nama)
                    : $promo->slug,

                'deskripsi' => $request->has('deskripsi')
                    ? $request->deskripsi
                    : $promo->deskripsi,

                'kode_promo' => $request->has('kode_promo')
                    ? $request->kode_promo
                    : $promo->kode_promo,

                'tipe' => $request->has('tipe')
                    ? $request->tipe
                    : $promo->tipe,

                'diskon' => $request->has('diskon')
                    ? $request->diskon
                    : $promo->diskon,

                'minimal_belanja' => $request->has('minimal_belanja')
                    ? $request->minimal_belanja
                    : $promo->minimal_belanja,

                'maksimal_diskon' => $request->has('maksimal_diskon')
                    ? $request->maksimal_diskon
                    : $promo->maksimal_diskon,

                'status' => $request->has('status')
                    ? $request->status
                    : $promo->status,

                'tanggal_mulai' => $request->has('tanggal_mulai')
                    ? $request->tanggal_mulai
                    : $promo->tanggal_mulai,

                'tanggal_selesai' => $request->has('tanggal_selesai')
                    ? $request->tanggal_selesai
                    : $promo->tanggal_selesai,

                'banner' => $bannerPath,

                'is_flashsale' => $request->has('is_flashsale')
                    ? $request->is_flashsale
                    : $promo->is_flashsale,

                'flashsale_stock' => $request->has('flashsale_stock')
                    ? $request->flashsale_stock
                    : $promo->flashsale_stock,

                'flashsale_limit' => $request->has('flashsale_limit')
                    ? $request->flashsale_limit
                    : $promo->flashsale_limit,
            ]);

            /*
            |--------------------------------------------------------------------------
            | UPDATE RELASI PRODUK
            |--------------------------------------------------------------------------
            */
            if ($request->produk_id) {

                $promo->produk()
                    ->sync($request->produk_id);
            }

            DB::commit();

            $promo->load('produk');

            return response()->json([

                'message' => 'Promo berhasil diupdate',

                'data' => new PromoResource($promo)

            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error($e);

            return response()->json([

                'message' => 'Internal Server Error',

                'errors' => [
                    $e->getMessage()
                ]

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🗑 DELETE PROMO
    |--------------------------------------------------------------------------
    */
    public function destroy(Promo $promo)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | DELETE BANNER
            |--------------------------------------------------------------------------
            */
            if ($promo->banner) {

                Storage::disk('public')
                    ->delete($promo->banner);
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE RELASI PRODUK
            |--------------------------------------------------------------------------
            */
            $promo->produk()->detach();

            /*
            |--------------------------------------------------------------------------
            | DELETE PROMO
            |--------------------------------------------------------------------------
            */
            $promo->delete();

            DB::commit();

            return response()->json([

                'message' => 'Promo berhasil dihapus'

            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error($e);

            return response()->json([

                'message' => 'Internal Server Error',

                'errors' => [
                    $e->getMessage()
                ]

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🎁 ASSIGN PRODUK
    |--------------------------------------------------------------------------
    */
    public function assignProduct(
        Request $request,
        Promo $promo
    )
    {
        $validator = Validator::make(
            $request->all(),
            [

                'produk_id' => 'required|array',

                'produk_id.*' => 'exists:produk,id',
            ]
        );

        if ($validator->fails()) {

            return response()->json([

                'message' => 'Validation Error',

                'errors' => $validator->errors()

            ], 422);
        }

        $promo->produk()->syncWithoutDetaching(
            $request->produk_id
        );

        $promo->load('produk');

        return response()->json([

            'message' => 'Produk berhasil ditambahkan ke promo',

            'data' => new PromoResource($promo)
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ❌ REMOVE PRODUK
    |--------------------------------------------------------------------------
    */
    public function removeProduct(
        Request $request,
        Promo $promo
    )
    {
        $validator = Validator::make(
            $request->all(),
            [

                'produk_id' => 'required|array',

                'produk_id.*' => 'exists:produk,id',
            ]
        );

        if ($validator->fails()) {

            return response()->json([

                'message' => 'Validation Error',

                'errors' => $validator->errors()

            ], 422);
        }

        $promo->produk()->detach(
            $request->produk_id
        );

        $promo->load('produk');

        return response()->json([

            'message' => 'Produk berhasil dihapus dari promo',

            'data' => new PromoResource($promo)
        ]);
    }


/*
|--------------------------------------------------------------------------
| 🖼️ BANNER PROMO
|--------------------------------------------------------------------------
*/
public function banner()
{
    $promo = Promo::query()

        ->active()

        ->whereNotNull('banner')

        ->latest()

        ->get();

    return response()->json([

        'message' => 'Banner promo',

        'data' => $promo->map(function ($item) {

            return [

                'id' => $item->id,

                'nama' => $item->nama,

                'label' => $item->label,

                'banner_url' => $item->banner_url,
            ];
        })
    ]);
}


}