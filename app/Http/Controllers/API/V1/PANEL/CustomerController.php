<?php

namespace App\Http\Controllers\API\V1\PANEL;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

use App\Models\User;

class CustomerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 👥 LIST CUSTOMER
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {

            $query = User::query()

                ->whereHas('roles', function ($q) {

                    $q->where('nama', 'customer');

                })

                ->when($request->search, function ($q) use ($request) {

                    $q->where(function ($query) use ($request) {

                        $query->where('username', 'like', '%' . $request->search . '%')

                            ->orWhere('phone', 'like', '%' . $request->search . '%');

                    });

                })

                ->latest()

                ->paginate($request->limit ?? 10);

            return response()->json([

                'message' => 'List customer',

                'data' => $query

            ]);

        } catch (\Exception $e) {

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
    | 👤 DETAIL CUSTOMER
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        try {

            $customer = User::with([

                /*
                |--------------------------------------------------------------------------
                | ROLE
                |--------------------------------------------------------------------------
                */
                'roles',

                /*
                |--------------------------------------------------------------------------
                | PROFILE
                |--------------------------------------------------------------------------
                */
                'profile',

                /*
                |--------------------------------------------------------------------------
                | ORDERS
                |--------------------------------------------------------------------------
                */
                'orders' => function ($query) {

                    $query->latest();

                },

                /*
                |--------------------------------------------------------------------------
                | ORDER ITEMS
                |--------------------------------------------------------------------------
                */
                'orders.items.produk'

            ])

                ->whereHas('roles', function ($q) {

                    $q->where('nama', 'customer');

                })

                ->find($id);

            if (!$customer) {

                return response()->json([

                    'message' => 'Customer tidak ditemukan'

                ], 404);
            }

        return response()->json([

            'message' => 'Detail customer',

            'data' => [

                /*
                |--------------------------------------------------------------------------
                | BASIC
                |--------------------------------------------------------------------------
                */
                'id' => $customer->id,

                'nama' => $customer->profile?->nama
                    ?? $customer->username,

                'email' => $customer->email,

                'phone' => $customer->phone,

                'username' => $customer->username,

                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                */
                'status' => $customer->is_active
                    ? 'Aktif'
                    : 'Tidak Aktif',

                /*
                |--------------------------------------------------------------------------
                | ALAMAT
                |--------------------------------------------------------------------------
                */
                'alamat' => [

                    'detail' => $customer->profile?->alamat_detail

                ],

                /*
                |--------------------------------------------------------------------------
                | STATISTIK
                |--------------------------------------------------------------------------
                */
                'total_order' => $customer->orders->count(),

                'total_belanja' => $customer->orders->sum(
                    'total_price'
                ),

                /*
                |--------------------------------------------------------------------------
                | ORDERS
                |--------------------------------------------------------------------------
                */
                'orders' => $customer->orders->map(function ($order) {

                    return [

                        'id' => $order->id,

                        'invoice' => $order->midtrans_order_id
                            ?? 'INV-' . $order->id,

                        'tanggal' => $order->created_at
                            ? $order->created_at->format('d M Y')
                            : null,

                        'total' => $order->total_price,

                        'status' => ucfirst(
                            $order->status
                        ),
                    ];
                }),

            ]

        ]);

        } catch (\Exception $e) {

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
    | 🚫 NONAKTIFKAN CUSTOMER
    |--------------------------------------------------------------------------
    */
    public function deactivate($id)
    {
        try {

            $customer = User::whereHas('roles', function ($q) {

                $q->where('nama', 'customer');

            })->find($id);

            if (!$customer) {

                return response()->json([

                    'message' => 'Customer tidak ditemukan'

                ], 404);
            }

            $customer->update([

                'is_active' => 0

            ]);

            return response()->json([

                'message' => 'Customer berhasil dinonaktifkan'

            ]);

        } catch (\Exception $e) {

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
    | ✅ AKTIFKAN CUSTOMER
    |--------------------------------------------------------------------------
    */
    public function activate($id)
    {
        try {

            $customer = User::whereHas('roles', function ($q) {

                $q->where('nama', 'customer');

            })->find($id);

            if (!$customer) {

                return response()->json([

                    'message' => 'Customer tidak ditemukan'

                ], 404);
            }

            $customer->update([

                'is_active' => 1

            ]);

            return response()->json([

                'message' => 'Customer berhasil diaktifkan'

            ]);

        } catch (\Exception $e) {

            Log::error($e);

            return response()->json([

                'message' => 'Internal Server Error',

                'errors' => [
                    $e->getMessage()
                ]

            ], 500);
        }
    }
}