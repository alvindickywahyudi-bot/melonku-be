<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Produk;
use App\Models\ProdukVarian;

class CartController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 🛒 LIST CART
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {

            $user = $request->user();

$cart = Cart::with([
    'items.produk.sources',
    'items.produk.greenhouse',
    'items.produk.promo',
    'items.varian',
])->firstOrCreate([
    'user_id' => $user->id
]);

$items = $cart->items

    ->filter(function ($item) {
        return !empty($item->produk);
    })

    ->map(function ($item) {

        $produk = $item->produk;

        $image = null;

        if ($produk && $produk->sources && $produk->sources->count()) {
            $image = $produk->sources->first()->path;
        }

        $hargaAsli = (int) ($produk->harga ?? 0);

        $promo = null;

        if ($produk && $produk->promo) {

            $promo = $produk->promo
                ->filter(function ($p) {

                    return $p->status &&
                        (!$p->tanggal_mulai || $p->tanggal_mulai <= now()) &&
                        (!$p->tanggal_selesai || $p->tanggal_selesai >= now());

                })
                ->sortByDesc('is_flashsale')
                ->first();
        }

        $diskonNominal = 0;

        if ($promo) {

            if ($promo->tipe === 'percent') {

                $diskonNominal =
                    ($hargaAsli * $promo->diskon) / 100;

            } else {

                $diskonNominal =
                    (int) $promo->diskon;
            }
        }

        $hargaFinal = max(
            0,
            $hargaAsli - $diskonNominal
        );
        
$varian = $item->varian ?? null;
$hargaPerItem = (int) ($varian->harga ?? $hargaFinal);
$selectedWeight = (int) ($varian->berat ?? 1000);
        
        return [
        
            'id' => $item->id,
        
            'qty' => $item->qty,
        
            'selected_weight' => $selectedWeight,
        
            'subtotal' => $hargaPerItem * $item->qty,

            'produk' => [

                'id' => $produk ? $produk->id : null,

                'nama' => $produk ? $produk->nama : null,

                'harga_asli' => $hargaAsli,

                'harga_final' => $hargaFinal,

                'diskon_nominal' => $diskonNominal,

                'promo_label' => $promo
                    ? $promo->nama
                    : null,

                'stok' => $produk ? $produk->stok : null,

                'image' => $image
                    ? url('/api/v1/upload/' . $image)
                    : null,

                'greenhouse' => (
                    $produk &&
                    $produk->greenhouse
                )
                    ? $produk->greenhouse->nama
                    : null,
                                                    
                'produk_varian_id' => $item->produk_varian_id,
                
                'selected_weight' => $selectedWeight,
                
                'harga_per_item' => (int) $hargaPerItem,
            ]
        ];
    });

            $total = $items->sum('subtotal');

            return response()->json([

                'success' => true,

                'message' => 'Cart berhasil diambil',

                'data' => [

                    'cart_id' => $cart->id,

                    'total' => $total,

                    'items' => $items
                ]
            ]);

        } catch (\Throwable $e) {

            Log::error($e->getMessage());

            return response()->json([

                'success' => false,

                'message' => 'Internal Server Error',

                'errors' => [
                    $e->getMessage()
                ]

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ➕ ADD TO CART
    |--------------------------------------------------------------------------
    */
    public function add(Request $request)
    {
$request->validate([
    'product_id' => 'required|exists:produk,id',
    'produk_varian_id' => 'required|exists:produk_varian,id',
    'qty' => 'required|integer|min:1',
]);
        try {

            DB::beginTransaction();

            $user = $request->user();

            $produk = Produk::find(
                $request->product_id
            );

            if (!$produk) {

                return response()->json([

                    'success' => false,

                    'message' => 'Produk tidak ditemukan'

                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | CHECK STOCK
            |--------------------------------------------------------------------------
            */
$varian = ProdukVarian::find(
    $request->produk_varian_id
);

if (!$varian) {

    return response()->json([
        'success' => false,
        'message' => 'Varian tidak ditemukan'
    ], 404);
}

if ($varian->stok < $request->qty) {

    return response()->json([

        'success' => false,

        'message' => 'Stok produk tidak cukup'

    ], 422);
}
            

            /*
            |--------------------------------------------------------------------------
            | FIND CART
            |--------------------------------------------------------------------------
            */
            $cart = Cart::firstOrCreate([

                'user_id' => $user->id
            ]);

            /*
            |--------------------------------------------------------------------------
            | FIND ITEM
            |--------------------------------------------------------------------------
            */
$item = CartItem::where('cart_id', $cart->id)
    ->where('product_id', $request->product_id)
    ->where('produk_varian_id', $request->produk_varian_id)
    ->first();
            /*
            |--------------------------------------------------------------------------
            | UPDATE EXISTING ITEM
            |--------------------------------------------------------------------------
            */
            if ($item) {

                $newQty =
                    $item->qty + $request->qty;

                if ($newQty > $varian->stok) {

                    return response()->json([

                        'success' => false,

                        'message' => 'Jumlah melebihi stok'

                    ], 422);
                }

                $item->update([

                    'qty' => $newQty
                ]);

            } else {

                /*
                |--------------------------------------------------------------------------
                | CREATE NEW ITEM
                |--------------------------------------------------------------------------
                */
CartItem::create([
    'cart_id' => $cart->id,
    'product_id' => $request->product_id,
    'produk_varian_id' => $request->produk_varian_id,
    'qty' => $request->qty,
]);
            }

            DB::commit();

            return response()->json([

                'success' => true,

                'message' => 'Produk berhasil ditambahkan ke cart'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error($e->getMessage());

            return response()->json([

                'success' => false,

                'message' => 'Internal Server Error',

                'errors' => [
                    $e->getMessage()
                ]

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ✏️ UPDATE QTY CART
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'qty' => 'required|integer|min:1'
        ]);

        try {

            DB::beginTransaction();

            $user = $request->user();

$item = CartItem::with([

    'produk',

    'varian',

    'cart'

])->find($request->cart_item_id);

            if (!$item) {

                return response()->json([

                    'success' => false,

                    'message' => 'Cart item tidak ditemukan'

                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | SECURITY
            |--------------------------------------------------------------------------
            */
            if ($item->cart->user_id !== $user->id) {

                return response()->json([

                    'success' => false,

                    'message' => 'Unauthorized'

                ], 403);
            }

/*
|--------------------------------------------------------------------------
| STOCK CHECK
|--------------------------------------------------------------------------
*/
if ($request->qty > $item->varian->stok) {

    return response()->json([

        'success' => false,

        'message' => 'Qty melebihi stok'

    ], 422);
}

            /*
            |--------------------------------------------------------------------------
            | UPDATE
            |--------------------------------------------------------------------------
            */
            $item->update([

                'qty' => $request->qty
            ]);

            DB::commit();

            return response()->json([

                'success' => true,

                'message' => 'Qty cart berhasil diupdate'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error($e->getMessage());

            return response()->json([

                'success' => false,

                'message' => 'Internal Server Error',

                'errors' => [
                    $e->getMessage()
                ]

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ❌ REMOVE ITEM
    |--------------------------------------------------------------------------
    */
    public function remove(Request $request, $id)
    {
        try {

            DB::beginTransaction();

            $user = $request->user();

            $item = CartItem::with(

                'cart'

            )->find($id);

            if (!$item) {

                return response()->json([

                    'success' => false,

                    'message' => 'Cart item tidak ditemukan'

                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | SECURITY
            |--------------------------------------------------------------------------
            */
            if ($item->cart->user_id !== $user->id) {

                return response()->json([

                    'success' => false,

                    'message' => 'Unauthorized'

                ], 403);
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE
            |--------------------------------------------------------------------------
            */
            $item->delete();

            DB::commit();

            return response()->json([

                'success' => true,

                'message' => 'Item berhasil dihapus dari cart'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error($e->getMessage());

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