<?php

namespace App\Http\Resources\PANEL;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdukResource extends JsonResource
{
    /**
     * Transform resource into array
     */
    public function toArray(Request $request): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | 🧾 BASIC INFO
            |--------------------------------------------------------------------------
            */
            'id' => $this->id,

            'nama' => $this->nama,

            'slug' => $this->slug,

            'desc' => $this->desc,

            'kondisi' => $this->kondisi,

            /*
            |--------------------------------------------------------------------------
            | 📦 DIMENSION
            |--------------------------------------------------------------------------
            */
            'size' => $this->size,

            'panjang' => (int) $this->panjang,

            'lebar' => (int) $this->lebar,

            'tinggi' => (int) $this->tinggi,


            /*
            |--------------------------------------------------------------------------
            | 💰 STOCK & PRICE
            |--------------------------------------------------------------------------
            */
            'stok' => (int) $this->stok,
            
            'harga' => (int) $this->harga,
            
            'berat' => (int) $this->berat,

            /*
            |--------------------------------------------------------------------------
            | 👤 RELATION IDS
            |--------------------------------------------------------------------------
            */
            'user_id' => $this->user_id,

            'seller_id' => $this->seller_id,

            'greenhouse_id' => $this->greenhouse_id,

            'variasi_id' => $this->variasi_id,

            /*
            |--------------------------------------------------------------------------
            | 🖼️ IMAGE
            |--------------------------------------------------------------------------
            */
            'gambar' => $this->imageUrl(),

            /*
            |--------------------------------------------------------------------------
            | 🧬 TRACKING
            |--------------------------------------------------------------------------
            */
            'serial_number' => $this->serial_number,

            'qr_string' => $this->qr_string,

            /*
            |--------------------------------------------------------------------------
            | 📅 DATE
            |--------------------------------------------------------------------------
            */
            'w_tanam' => $this->formatDate($this->w_tanam),

            'w_panen' => $this->formatDate($this->w_panen),

            /*
            |--------------------------------------------------------------------------
            | 🌱 GREENHOUSE
            |--------------------------------------------------------------------------
            */
            'greenhouse' => $this->whenLoaded(
                'greenhouse',
                fn() => new GreenhouseResource(
                    $this->greenhouse
                )
            ),
            
            /*
            |--------------------------------------------------------------------------
            | 📦 VARIAN
            |--------------------------------------------------------------------------
            */
            'varian' => $this->whenLoaded(
                'varian',
                function () {
            
                    return $this->varian->map(function ($item) {
            
                        return [
            
                            'id' => $item->id,
            
                            'berat' => (int) $item->berat,
            
                            'harga' => (int) $item->harga,
            
                            'stok' => (int) $item->stok,
                        ];
                    });
                },
                []
            ),
            /*
            |--------------------------------------------------------------------------
            | 📁 PRODUCT FILES
            |--------------------------------------------------------------------------
            */
            'produk_file' => $this->whenLoaded(
                'sources',
                function () {

                    return $this->sources->map(function ($file) {

                        return [

                            'id' => $file->id,

                            'type' => $file->type,

                            'path' => $file->path,

                            'url' => $file->path
                                ? asset(
                                    'storage/' .
                                    ltrim($file->path, '/')
                                )
                                : null,

                            'is_featured' =>
                                (bool) $file->is_featured,
                        ];
                    });
                },
                []
            ),

            /*
            |--------------------------------------------------------------------------
            | 🕓 TIMELINE
            |--------------------------------------------------------------------------
            */
            'produk_timeline' => $this->whenLoaded(
                'timeline',
                fn() => ProdukTimelineResource::collection(
                    $this->timeline
                ),
                []
            ),

            /*
            |--------------------------------------------------------------------------
            | ⏱ TIMESTAMP
            |--------------------------------------------------------------------------
            */
            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 🖼️ IMAGE URL
    |--------------------------------------------------------------------------
    */
    private function imageUrl(): ?string
    {
        if (!$this->gambar) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | ALREADY FULL URL
        |--------------------------------------------------------------------------
        */
        if (
            str_starts_with($this->gambar, 'http://') ||
            str_starts_with($this->gambar, 'https://')
        ) {
            return $this->gambar;
        }

        /*
        |--------------------------------------------------------------------------
        | STORAGE URL
        |--------------------------------------------------------------------------
        */
        return asset(
            'storage/' . ltrim($this->gambar, '/')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 📅 FORMAT DATE
    |--------------------------------------------------------------------------
    */
    private function formatDate($date): ?string
    {
        if (!$date) {
            return null;
        }

        return date(
            'Y-m-d',
            strtotime($date)
        );
    }
}