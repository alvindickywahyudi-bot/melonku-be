<?php

namespace App\Http\Resources\PANEL;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | 🆔 BASIC
            |--------------------------------------------------------------------------
            */
            'id' => (int) $this->id,

            'nama' => $this->nama,

            'slug' => $this->slug,

            'deskripsi' => $this->deskripsi,

            'kode_promo' => $this->kode_promo,

            /*
            |--------------------------------------------------------------------------
            | 🎁 PROMO
            |--------------------------------------------------------------------------
            */
            'tipe' => $this->tipe,

            'diskon' => (float) $this->diskon,

            'label' => $this->label,

            /*
            |--------------------------------------------------------------------------
            | 💰 PRICE RULE
            |--------------------------------------------------------------------------
            */
            'minimal_belanja' => (float) (
                $this->minimal_belanja ?? 0
            ),

            'maksimal_diskon' => $this->maksimal_diskon
                ? (float) $this->maksimal_diskon
                : null,

            /*
            |--------------------------------------------------------------------------
            | ⚡ FLASH SALE
            |--------------------------------------------------------------------------
            */
            'is_flashsale' => (bool) (
                $this->is_flashsale ?? false
            ),

            'flashsale_stock' => $this->flashsale_stock
                ? (int) $this->flashsale_stock
                : null,

            'flashsale_limit' => $this->flashsale_limit
                ? (int) $this->flashsale_limit
                : null,

            /*
            |--------------------------------------------------------------------------
            | 🖼️ BANNER
            |--------------------------------------------------------------------------
            */
            'banner' => $this->banner,

            'banner_url' => $this->banner_url,

            /*
            |--------------------------------------------------------------------------
            | 📌 STATUS
            |--------------------------------------------------------------------------
            */
            'status' => (bool) $this->status,

            'is_expired' => $this->isExpired(),

            'has_started' => $this->hasStarted(),

            /*
            |--------------------------------------------------------------------------
            | 📅 DATE
            |--------------------------------------------------------------------------
            */
            'tanggal_mulai' => $this->tanggal_mulai
                ? $this->tanggal_mulai
                    ->format('Y-m-d H:i:s')
                : null,

            'tanggal_selesai' => $this->tanggal_selesai
                ? $this->tanggal_selesai
                    ->format('Y-m-d H:i:s')
                : null,

            /*
            |--------------------------------------------------------------------------
            | 📦 RELATION
            |--------------------------------------------------------------------------
            */
            'jumlah_produk' => $this->whenLoaded(
                'produk',
                fn () => $this->produk->count(),
                0
            ),

            /*
            |--------------------------------------------------------------------------
            | 🚫 SAFE RELATION
            |--------------------------------------------------------------------------
            */
            'produk' => $this->whenLoaded(
                'produk',
                function () {

                    return $this->produk->map(
                        function ($item) {

                            return [

                                'id' => (int) $item->id,

                                'nama' => $item->nama,

                                'slug' => $item->slug,

                                'harga' => (int) $item->harga,

                                'stok' => (int) $item->stok,

                                'gambar' => $item->gambar_url,
                            ];
                        }
                    );
                },
                []
            ),

            /*
            |--------------------------------------------------------------------------
            | 🕒 TIMESTAMP
            |--------------------------------------------------------------------------
            */
            'created_at' => $this->created_at
                ? $this->created_at
                    ->format('Y-m-d H:i:s')
                : null,

            'updated_at' => $this->updated_at
                ? $this->updated_at
                    ->format('Y-m-d H:i:s')
                : null,
        ];
    }
}