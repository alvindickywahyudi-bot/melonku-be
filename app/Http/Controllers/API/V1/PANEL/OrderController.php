<?php

namespace App\Http\Controllers\API\V1\PANEL;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Order;

class OrderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 📦 LIST ORDERS
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {

            $query = Order::query()

                ->with([
                    'user',
                    'items.produk',
'items.varian'
                ]);

            /*
            |--------------------------------------------------------------------------
            | 🔍 SEARCH
            |--------------------------------------------------------------------------
            */
            if ($request->search) {

                $search = $request->search;

                $query->where(function ($q) use ($search) {

                    $q->where('id', $search)

                        ->orWhere(
                            'midtrans_order_id',
                            'like',
                            '%' . $search . '%'
                        )

                        ->orWhere(
                            'receiver_name',
                            'like',
                            '%' . $search . '%'
                        )

                        ->orWhere(
                            'receiver_phone',
                            'like',
                            '%' . $search . '%'
                        );
                });
            }

            /*
            |--------------------------------------------------------------------------
            | 📌 FILTER STATUS
            |--------------------------------------------------------------------------
            */
            if ($request->status) {

                $query->where(
                    'status',
                    $request->status
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 💳 FILTER PAYMENT STATUS
            |--------------------------------------------------------------------------
            */
            if ($request->payment_status) {

                $query->where(
                    'payment_status',
                    $request->payment_status
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 📅 SORTING
            |--------------------------------------------------------------------------
            */
            $sort = $request->sort ?? 'created_at';

            $dir = $request->dir ?? 'desc';

            $orders = $query
                ->orderBy($sort, $dir)
                ->paginate($request->limit ?? 10);

            return response()->json([

                'message' => 'List orders',

                'data' => $orders

            ]);

        } catch (\Throwable $e) {

            Log::error($e->getMessage());

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
    | 📄 DETAIL ORDER
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        try {

            $order = Order::with([
                'user',
'items.produk',
'items.varian'
])
            ->where('midtrans_order_id', $id)
            ->first();

            if (!$order) {

                return response()->json([

                    'message' => 'Order tidak ditemukan'

                ], 404);
            }

            return response()->json([

                'message' => 'Detail order',

                'data' => $order

            ]);

        } catch (\Throwable $e) {

            Log::error($e->getMessage());

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
    | 🔄 UPDATE STATUS ORDER
    |--------------------------------------------------------------------------
    */
public function updateStatus(Request $request, $id)
{
    $request->validate([

        'status' => [
            'required',
            'in:pending,diproses,dikemas,dikirim,selesai,dibatalkan'
        ],

        'resi' => 'nullable|string|max:255',

        'admin_note' => 'nullable|string',
    ]);

    try {

        DB::beginTransaction();

$order = Order::with([
    'items.produk',
    'items.varian'
])

    ->where('midtrans_order_id', $id)

    ->first();

        if (!$order) {

            DB::rollBack();

            return response()->json([

                'message' => 'Order tidak ditemukan'

            ], 404);
        }

        $status = $request->status;

        /*
        |--------------------------------------------------------------------------
        | 💾 SAVE EXTRA DATA
        |--------------------------------------------------------------------------
        */
        $order->resi = $request->resi;

        $order->admin_note = $request->admin_note;

        /*
        |--------------------------------------------------------------------------
        | 🚚 DIKIRIM
        |--------------------------------------------------------------------------
        */
        if ($status === 'dikirim') {

            $order->shipped_at = now();
        }

        /*
        |--------------------------------------------------------------------------
        | ✅ SELESAI
        |--------------------------------------------------------------------------
        */
        if ($status === 'selesai') {

            $order->completed_at = now();
        }

        /*
        |--------------------------------------------------------------------------
        | ❌ DIBATALKAN
        |--------------------------------------------------------------------------
        */
        if ($status === 'dibatalkan') {

            $order->cancelled_at = now();

            /*
            |--------------------------------------------------------------------------
            | 🔄 RETURN STOCK
            |--------------------------------------------------------------------------
            */
            if ($order->status !== 'dibatalkan') {

foreach ($order->items as $item) {

    if ($item->varian) {

        $item->varian->increment(
            'stok',
            $item->qty
        );
    }
}
            }
        }

        $order->status = $status;

        $order->save();

        DB::commit();

        return response()->json([

            'message' => 'Status order berhasil diupdate',

            'data' => $order

        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error($e->getMessage());

        return response()->json([

            'message' => 'Internal Server Error',

            'errors' => [
                $e->getMessage()
            ]

        ], 500);
    }
}
}