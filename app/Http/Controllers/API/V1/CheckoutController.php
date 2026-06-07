<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Notification as MidtransNotification;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 🛒 CHECKOUT
    |--------------------------------------------------------------------------
    */
    public function checkout(Request $request)
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | 🔥 VALIDASI PROFILE WAJIB LENGKAP
        |--------------------------------------------------------------------------
        */
        $user->load('profile');

        $profile = $user->profile;

        if (! $profile) {

            return response()->json([

                'success' => false,

                'message' => 'Profil tidak ditemukan',

            ], 422);
        }

        $request->validate([

            'receiver_name' => 'required|string|max:255',

            'receiver_phone' => 'required|string|max:20',

            'receiver_address' => 'required|string',

            'receiver_province' => 'required|string|max:255',

            'receiver_city' => 'required|string|max:255',

            'receiver_district' => 'required|string|max:255',

            'courier' => 'required|string|max:100',

            'shipping_service' => 'required|string|max:100',

            'shipping_cost' => 'required|integer|min:0',

            'shipping_estimation' => 'nullable|string|max:100',

            'kode_promo' => 'nullable|string',

            'promo_code' => 'nullable|string',

            'voucher_code' => 'nullable|string',
        ]);

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | GET CART
            |--------------------------------------------------------------------------
            */
            $cart = Cart::with([
                'items.produk.sources',
                'items.varian',
            ])
                ->where('user_id', $user->id)
                ->first();

            if (! $cart || $cart->items->isEmpty()) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Cart kosong',
                ], 400);
            }

            $subtotal = 0;

            /*
            |--------------------------------------------------------------------------
            | VALIDASI STOK
            |--------------------------------------------------------------------------
            */
            foreach ($cart->items as $item) {

                $produk = $item->produk;

                if (! $produk) {

                    DB::rollBack();

                    return response()->json([

                        'success' => false,

                        'message' => 'Produk tidak ditemukan',

                    ], 404);
                }

                if (! $item->varian) {

                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Varian produk tidak ditemukan',
                    ], 404);
                }

                if ($item->varian->stok < $item->qty) {

                    DB::rollBack();

                    return response()->json([

                        'success' => false,

                        'message' => "Stok {$produk->nama} tidak cukup",

                    ], 422);
                }

                $hargaPerItem =
                    (int) $item->varian->harga;
                $subtotal +=
                    ($hargaPerItem * $item->qty);
            }

            /*
            |--------------------------------------------------------------------------
            | TOTAL WITH VOUCHER / PROMO DISCOUNT
            |--------------------------------------------------------------------------
            */
            $discount = 0;
            $appliedVoucherCode = null;
            $voucherCodeInput = $request->kode_promo ?? $request->promo_code ?? $request->voucher_code;
            if ($voucherCodeInput) {
                $promo = \App\Models\Promo::whereRaw('UPPER(kode_promo) = ?', [strtoupper($voucherCodeInput)])
                    ->active()
                    ->first();

                if ($promo) {
                    if ($subtotal >= $promo->minimal_belanja) {
                        if ($promo->tipe === 'percent' || $promo->tipe === 'persen' || $promo->tipe === 'percentage') {
                            $discount = (int) round(($subtotal * $promo->diskon) / 100);
                            if ($promo->maksimal_diskon && $discount > $promo->maksimal_diskon) {
                                $discount = (int) $promo->maksimal_diskon;
                            }
                        } else {
                            $discount = (int) $promo->diskon;
                        }
                        $appliedVoucherCode = $promo->kode_promo;
                    }
                }
            }

            $shippingCost = (int) $request->shipping_cost;
            $grandTotal = max(0, $subtotal + $shippingCost - $discount);

            /*
            |--------------------------------------------------------------------------
            | CREATE ORDER
            |--------------------------------------------------------------------------
            */
            $order = Order::create([

                'user_id' => $user->id,

                'total_price' => $grandTotal,

                'voucher_code' => $appliedVoucherCode,

                'voucher_discount' => $discount,

                'status' => Order::STATUS_PENDING,

                'payment_status' => Order::PAYMENT_PENDING,

                'courier' => $request->courier,

                'shipping_service' => $request->shipping_service,

                'shipping_cost' => $shippingCost,

                'shipping_estimation' => $request->shipping_estimation,

                'receiver_name' => $request->receiver_name,

                'receiver_phone' => $request->receiver_phone,

                'receiver_address' => $request->receiver_address,

                'receiver_province' => $request->receiver_province,

                'receiver_city' => $request->receiver_city,

                'receiver_district' => $request->receiver_district,
            ]);

            Notification::create([
                'title' => 'Pesanan Baru',
                'message' => 'Pesanan #'.$order->id.' masuk',
                'type' => 'order',
                'order_id' => $order->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | CREATE ORDER ITEMS
            |--------------------------------------------------------------------------
            */
            foreach ($cart->items as $item) {

                $produk = $item->produk;

                $image = optional(
                    $produk->sources->first()
                )->path;
                OrderItem::create([

                    'order_id' => $order->id,

                    'product_id' => $produk->id,

                    'product_name' => $produk->nama,

                    'product_image' => $image,

                    'qty' => (int) $item->qty,

                    'price' => (int) $item->varian->harga,

                    'produk_varian_id' => $item->produk_varian_id,

                    'selected_weight' => (int) $item->varian->berat,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | MIDTRANS ORDER ID
            |--------------------------------------------------------------------------
            */
            $midtransOrderId = $this->generateMidtransOrderId($order);

            $order->update([
                'midtrans_order_id' => $midtransOrderId,
            ]);

            /*
            |--------------------------------------------------------------------------
            | GENERATE SNAP TOKEN
            |--------------------------------------------------------------------------
            */
            $snapToken = Snap::getSnapToken(
                $this->buildMidtransParams($order)
            );

            /*
            |--------------------------------------------------------------------------
            | CLEAR CART
            |--------------------------------------------------------------------------
            */
            $cart->items()->delete();

            DB::commit();

            return response()->json([

                'success' => true,

                'message' => 'Checkout berhasil 🚀',

                'data' => [

                    'order_id' => $order->id,

                    'midtrans_order_id' => $order->midtrans_order_id,

                    'total_price' => (int) $order->total_price,

                    'snap_token' => $snapToken,
                ],
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error($e->getMessage());

            return response()->json([

                'success' => false,

                'message' => 'Checkout gagal',

                'errors' => [
                    $e->getMessage(),
                ],

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 💳 RETRY PAYMENT
    |--------------------------------------------------------------------------
    */
    public function pay($orderId, Request $request)
    {
        $user = $request->user();

        $order = Order::with([
            'items.produk',
            'items.varian',
        ])->find($orderId);

        if (! $order) {

            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        if (
            $order->user_id !== $user->id
            && ! $user->hasRole('admin')
        ) {

            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        try {

            $snapToken = Snap::getSnapToken(
                $this->buildMidtransParams($order)
            );

            return response()->json([

                'success' => true,

                'snap_token' => $snapToken,
            ]);

        } catch (\Throwable $e) {

            Log::error($e->getMessage());

            return response()->json([

                'success' => false,

                'message' => 'Gagal generate payment',

                'errors' => [
                    $e->getMessage(),
                ],

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🔔 MIDTRANS CALLBACK
    |--------------------------------------------------------------------------
    */
    public function notification()
    {
        Log::info('MIDTRANS CALLBACK HIT');

        try {

            $notif = new MidtransNotification();
            Log::info('MIDTRANS DATA', [
                'order_id' => $notif->order_id,
                'transaction_status' => $notif->transaction_status,
                'payment_type' => $notif->payment_type,
            ]);

        } catch (\Throwable $e) {

            return response()->json([

                'success' => false,

                'message' => 'Invalid payload',

            ], 400);
        }

        $orderId = $this->extractOrderId(
            $notif->order_id
        );

        $order = Order::with([
            'items.produk',
        ])->find($orderId);

        if (! $order) {

            return response()->json([

                'success' => false,

                'message' => 'Order tidak ditemukan',

            ], 404);
        }

        DB::beginTransaction();

        try {

            switch ($notif->transaction_status) {

                /*
                |--------------------------------------------------------------------------
                | PAYMENT SUCCESS
                |--------------------------------------------------------------------------
                */
                case 'settlement':

                    /*
                    |--------------------------------------------------------------------------
                    | PREVENT DOUBLE CALLBACK
                    |--------------------------------------------------------------------------
                    */
                    if (
                        $order->payment_status !== Order::PAYMENT_PAID
                    ) {

                        foreach ($order->items as $item) {

                            $produk = $item->produk;

                            if ($produk) {

                                if ($item->varian) {

                                    $item->varian->decrement(
                                        'stok',
                                        $item->qty
                                    );
                                }
                            }
                        }
                    }

                    $order->update([

                        'payment_status' => Order::PAYMENT_PAID,

                        'status' => Order::STATUS_PROCESSED,

                        'paid_at' => now(),
                    ]);

                    break;

                    /*
                    |--------------------------------------------------------------------------
                    | PENDING
                    |--------------------------------------------------------------------------
                    */
                case 'pending':

                    $order->update([

                        'payment_status' => Order::PAYMENT_PENDING,
                    ]);

                    break;

                    /*
                    |--------------------------------------------------------------------------
                    | EXPIRE
                    |--------------------------------------------------------------------------
                    */
                case 'expire':

                    $order->update([

                        'payment_status' => Order::PAYMENT_EXPIRED,

                        'status' => Order::STATUS_CANCELLED,

                        'expired_at' => now(),
                    ]);

                    break;

                    /*
                    |--------------------------------------------------------------------------
                    | CANCEL
                    |--------------------------------------------------------------------------
                    */
                case 'cancel':

                    $order->update([

                        'payment_status' => Order::PAYMENT_FAILED,

                        'status' => Order::STATUS_CANCELLED,

                        'cancelled_at' => now(),
                    ]);

                    break;
            }

            DB::commit();

            return response()->json([

                'success' => true,
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error($e->getMessage());

            return response()->json([

                'success' => false,

                'message' => 'Callback gagal',

                'errors' => [
                    $e->getMessage(),
                ],

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 📦 MY ORDERS
    |--------------------------------------------------------------------------
    */
    public function myOrders(Request $request)
    {
        $user = $request->user();

        $orders = Order::with([
            'items',
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return response()->json([

            'success' => true,

            'data' => $orders,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 📄 DETAIL ORDER
    |--------------------------------------------------------------------------
    */
    public function detail($id, Request $request)
    {
        $user = $request->user();

        $order = Order::with([
            'items',
        ])->find($id);

        if (! $order) {

            return response()->json([

                'success' => false,

                'message' => 'Order tidak ditemukan',

            ], 404);
        }

        if (
            $order->user_id !== $user->id
            && ! $user->hasRole('admin')
        ) {

            return response()->json([

                'success' => false,

                'message' => 'Forbidden',

            ], 403);
        }

        return response()->json([

            'success' => true,

            'data' => $order,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔧 HELPERS
    |--------------------------------------------------------------------------
    */
    private function generateMidtransOrderId(Order $order): string
    {
        return 'ORDER-'.$order->id.'-'.Str::uuid();
    }

    private function extractOrderId(string $midtransId): ?int
    {
        if (
            preg_match(
                '/ORDER-(\d+)-/',
                $midtransId,
                $match
            )
        ) {

            return (int) $match[1];
        }

        return null;
    }

    private function buildMidtransParams(Order $order): array
    {
        $items = [];

        foreach ($order->items as $item) {

            $selectedWeight =
                (int) ($item->selected_weight ?: 1000);

            $hargaPerItem =
                (int) $item->price;

            $items[] = [

                'id' => (string) $item->id,

                'price' => (int) round($hargaPerItem),

                'quantity' => (int) $item->qty,

                'name' => $item->product_name.
                    ' ('.
                    ($selectedWeight / 1000).
                    ' Kg)',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | SHIPPING COST
        |--------------------------------------------------------------------------
        */
        $items[] = [

            'id' => 'SHIPPING',

            'price' => (int) $order->shipping_cost,

            'quantity' => 1,

            'name' => 'Ongkir '.strtoupper($order->courier),
        ];

        return [

            'transaction_details' => [

                'order_id' => $order->midtrans_order_id,

                'gross_amount' => (int) $order->total_price,
            ],

            'item_details' => $items,

            'customer_details' => [

                'first_name' => $order->receiver_name,

                'phone' => $order->receiver_phone,
            ],
        ];
    }
}