<?php

namespace App\Http\Resources\PANEL;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdukVariasiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
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

            'produk_id' => $this->produk_id,

            'nama' => $this->nama,

            'desc' => $this->desc,

            'kode' => $this->kode,

            /*
            |--------------------------------------------------------------------------
            | 💰 STOCK & PRICE
            |--------------------------------------------------------------------------
            */
            'harga' => (int) $this->harga,

            'stok' => (int) $this->stok,

            /*
            |--------------------------------------------------------------------------
            | 📁 FILES
            |--------------------------------------------------------------------------
            */
            'variasi_file' => $this->whenLoaded(
                'source',
                function () {

                    return VariasiSourceResource::collection(
                        $this->source
                    );
                },
                []
            ),

            /*
            |--------------------------------------------------------------------------
            | 📅 TIMESTAMP
            |--------------------------------------------------------------------------
            */
            'created_at' => optional($this->created_at)
                ->format('Y-m-d H:i:s'),

            'updated_at' => optional($this->updated_at)
                ->format('Y-m-d H:i:s'),
        ];
    }
}