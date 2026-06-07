<?php

namespace App\Http\Controllers\API\V1\PANEL;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;
use App\Models\Produk;
use App\Models\ProdukDetail;
use App\Models\ProdukSource;
use App\Models\ProdukVarian;

use App\Http\Resources\PANEL\ProdukResource;

class ProdukController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 📦 LIST PRODUK
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
$allowedSort = [
    'created_at',
    'nama',
    'harga',
    'stok'
];

$sort = in_array(
    $request->sort,
    $allowedSort
)
    ? $request->sort
    : 'created_at';        $dir  = $request->dir ?? 'desc';

        $query = Produk::query()
            ->with([
                'detail',
                'sources',
                'timeline',
                'greenhouse',
                'varian'
            ])
            ->when($request->search, function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            })
            ->orderBy($sort, $dir)
            ->paginate($request->limit ?? 10);

        return ProdukResource::collection($query);
    }

    /*
    |--------------------------------------------------------------------------
    | 📦 DETAIL PRODUK
    |--------------------------------------------------------------------------
    */
    public function show(Produk $produk)
    {
        $produk->load([
            'detail',
            'sources',
            'timeline',
            'greenhouse',
            'varian'
        ]);

        return response()->json([
            'message' => 'Detail produk',
            'data' => new ProdukResource($produk)
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 📦 CREATE PRODUK
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        
$validator = Validator::make($request->all(), [

    'greenhouse_id' => 'nullable|exists:greenhouse,id',

    'nama' => 'required|string|min:3|max:255',

    'slug' => 'nullable|string|max:255|unique:produk,slug',
    
    'harga' => 'required|numeric|min:0',
    
    'berat' => 'required|integer|min:1000',
    
    'stok' => 'required|integer|min:0',

    'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        
    'varian' => 'nullable|array',
    
    'varian.*.berat' =>
        'required_with:varian|integer|min:1000',
    
    'varian.*.harga' =>
        'required_with:varian|numeric|min:0',
    
    'varian.*.stok' =>
        'required_with:varian|integer|min:0',

    /*
    |--------------------------------------------------------------------------
    | DETAIL
    |--------------------------------------------------------------------------
    */
    'detail.short_description' => 'nullable|string|max:500',

    'detail.sweetness' => 'nullable|integer|min:1|max:10',

    'detail.juiciness' => 'nullable|integer|min:1|max:10',

    'detail.texture' => 'nullable|integer|min:1|max:10',

    'detail.sweetness_label' => 'nullable|string|max:100',

    'detail.juiciness_label' => 'nullable|string|max:100',

    'detail.texture_label' => 'nullable|string|max:100',

    'detail.serving_size' => 'nullable|string|max:100',

    'detail.calories' => 'nullable|string|max:100',

    'detail.vitamin_c' => 'nullable|string|max:100',

    'detail.potassium' => 'nullable|string|max:100',

    'detail.fiber' => 'nullable|string|max:100',

    'file_produk' => 'nullable|array',

'file_produk.*' =>
    'image|mimes:jpg,jpeg,png,webp|max:2048',
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
            | CREATE PRODUK
            |--------------------------------------------------------------------------
            */
            $produk = Produk::create([
                'user_id' => auth()->id(),

                'greenhouse_id' => $request->greenhouse_id,

                'nama' => $request->nama,

                'slug' => $request->slug
                    ? Str::slug($request->slug)
                    : Str::slug($request->nama),

                'gambar' => $request->gambar ? $request->gambar->store('produk', 'public') : null,

                'stok' => $request->stok,
                
                'berat' => $request->berat,

                'harga' => $request->harga,
            ]);

            /*
            |--------------------------------------------------------------------------
            | DETAIL
            |--------------------------------------------------------------------------
            */
            if ($request->detail) {

                ProdukDetail::create([
                    'produk_id' => $produk->id,

                    'short_description' => $request->detail['short_description'] ?? null,

                    'sweetness' => $request->detail['sweetness'] ?? null,

                    'juiciness' => $request->detail['juiciness'] ?? null,

                    'texture' => $request->detail['texture'] ?? null,

                    'sweetness_label' => $request->detail['sweetness_label'] ?? null,

                    'juiciness_label' => $request->detail['juiciness_label'] ?? null,

                    'texture_label' => $request->detail['texture_label'] ?? null,

                    'serving_size' => $request->detail['serving_size'] ?? null,

                    'calories' => $request->detail['calories'] ?? null,

                    'vitamin_c' => $request->detail['vitamin_c'] ?? null,

                    'potassium' => $request->detail['potassium'] ?? null,

                    'fiber' => $request->detail['fiber'] ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | HIDE REVIEW DULU
                    |--------------------------------------------------------------------------
                    */
                    'review_enabled' => 0,
                ]);
            }
                        
            /*
            |--------------------------------------------------------------------------
            | VARIAN PRODUK
            |--------------------------------------------------------------------------
            */
            if ($request->varian) {
            
                foreach ($request->varian as $item) {
            
                    ProdukVarian::create([
            
                        'produk_id' => $produk->id,
            
                        'berat' => $item['berat'],
            
                        'harga' => $item['harga'],
            
                        'stok' => $item['stok'],
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | FILE PRODUK
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('file_produk')) {

                foreach ($request->file_produk as $index => $file) {

                    $filename = Str::random(40) . '.' . $file->extension();

                    $path = $file->storeAs(
                        'produk',
                        $filename,
                        'public'
                    );

                    ProdukSource::create([
                        'produk_id' => $produk->id,

                        'type' => 'image',

                        'path' => $path,

                        'is_featured' => $index === 0 ? 1 : 0,
                    ]);
                }
            }

            DB::commit();

            $produk->load([
                'detail',
                'sources',
                'timeline',
                'greenhouse',
                'varian'
            ]);

            return response()->json([
                'message' => 'Produk berhasil dibuat',
                'data' => new ProdukResource($produk)
            ], 201);

        } catch (\Exception $e) {

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
    | ✏️ UPDATE PRODUK
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Produk $produk)
    {
        $request->validate([

    'harga' => 'nullable|numeric|min:0',

    'berat' => 'nullable|integer|min:1000',

    'stok' => 'nullable|integer|min:0',

]);
        DB::beginTransaction();

        try {

            $newGambar = $produk->gambar;
            if ($request->hasFile('gambar')) {

    /*
    |--------------------------------------------------------------------------
    | DELETE OLD IMAGE
    |--------------------------------------------------------------------------
    */
    if ($produk->gambar) {

        Storage::disk('public')
            ->delete($produk->gambar);
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD NEW IMAGE
    |--------------------------------------------------------------------------
    */
    $newGambar = $request->file('gambar')
        ->store('produk', 'public');
}

$produk->update([

    'nama' => $request->nama ?? $produk->nama,

    'slug' => $request->slug
        ? Str::slug($request->slug)
        : $produk->slug,

    'harga' => $request->harga ?? $produk->harga,

    'berat' => $request->berat ?? $produk->berat,

    'stok' => $request->stok ?? $produk->stok,

    'gambar' => $newGambar,
]);


/*
|--------------------------------------------------------------------------
| UPDATE VARIAN
|--------------------------------------------------------------------------
*/
if ($request->has('varian')) {

    $produk->varian()->delete();

    foreach ($request->varian as $item) {

        ProdukVarian::create([
            'produk_id' => $produk->id,
            'berat' => $item['berat'],
            'harga' => $item['harga'],
            'stok' => $item['stok'],
        ]);
    }
}
            /*
            |--------------------------------------------------------------------------
            | UPDATE DETAIL
            |--------------------------------------------------------------------------
            */
            if ($request->detail) {

                $produk->detail()->updateOrCreate(
                    [
                        'produk_id' => $produk->id
                    ],
                    [
                        'short_description' => $request->detail['short_description'] ?? null,

                        'sweetness' => $request->detail['sweetness'] ?? null,

                        'juiciness' => $request->detail['juiciness'] ?? null,

                        'texture' => $request->detail['texture'] ?? null,

                        'sweetness_label' => $request->detail['sweetness_label'] ?? null,

                        'juiciness_label' => $request->detail['juiciness_label'] ?? null,

                        'texture_label' => $request->detail['texture_label'] ?? null,

                        'serving_size' => $request->detail['serving_size'] ?? null,

                        'calories' => $request->detail['calories'] ?? null,

                        'vitamin_c' => $request->detail['vitamin_c'] ?? null,

                        'potassium' => $request->detail['potassium'] ?? null,

                        'fiber' => $request->detail['fiber'] ?? null,
                    ]
                );
            }

            DB::commit();

            $produk->load([
                'detail',
                'sources',
                'timeline',
                'greenhouse',
                'varian'
            ]);

            return response()->json([
                'message' => 'Produk berhasil diupdate',
                'data' => new ProdukResource($produk)
            ]);

        } catch (\Exception $e) {

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
    | 🗑 SOFT DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(Produk $produk)
    {
        $produk->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ♻️ RESTORE
    |--------------------------------------------------------------------------
    */
    public function restore($id)
    {
        $produk = Produk::onlyTrashed()->find($id);

        if (!$produk) {
            return response()->json([
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $produk->restore();

        return response()->json([
            'message' => 'Produk berhasil direstore'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ❌ FORCE DELETE
    |--------------------------------------------------------------------------
    */
    public function forceDelete($id)
    {
        DB::beginTransaction();

        try {

            $produk = Produk::onlyTrashed()->find($id);

            if (!$produk) {
                return response()->json([
                    'message' => 'Produk tidak ditemukan'
                ], 404);
            }

            /*
|--------------------------------------------------------------------------
| DELETE MAIN IMAGE
|--------------------------------------------------------------------------
*/
if ($produk->gambar) {

    Storage::disk('public')
        ->delete($produk->gambar);
}
            /*
            |--------------------------------------------------------------------------
            | DELETE FILE
            |--------------------------------------------------------------------------
            */
            
            foreach ($produk->sources as $source) {

                Storage::disk('public')->delete($source->path);

                $source->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE RELATION
            |--------------------------------------------------------------------------
            */
            $produk->detail()->delete();

            $produk->timeline()->delete();

            $produk->varian()->delete();

            /*
            |--------------------------------------------------------------------------
            | FORCE DELETE
            |--------------------------------------------------------------------------
            */
            $produk->forceDelete();

            DB::commit();

            return response()->json([
                'message' => 'Produk berhasil dihapus permanen'
            ]);

        } catch (\Exception $e) {

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

public function updateStock(
    Request $request,
    Produk $produk
)
{
    $request->validate([

        'stok' => 'required|integer|min:0'
    ]);

    $produk->update([

        'stok' => $request->stok
    ]);

    return response()->json([

        'message' => 'Stok berhasil diupdate',

        'data' => $produk
    ]);
}

}