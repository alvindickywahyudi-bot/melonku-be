<?php

namespace App\Http\Resources\PANEL;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdoptionProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | BASIC
            |--------------------------------------------------------------------------
            */
            'id' => $this->id,

            'nama' => $this->nama,

            'slug' => $this->slug,

            'deskripsi' => $this->deskripsi,

            /*
            |--------------------------------------------------------------------------
            | INVESTMENT
            |--------------------------------------------------------------------------
            */
            'roi_percent' => (float) $this->roi_percent,

            'durasi_hari' => $this->durasi_hari,

            'proteksi_percent' => (float) $this->proteksi_percent,

            /*
            |--------------------------------------------------------------------------
            | SLOT
            |--------------------------------------------------------------------------
            */
            'harga_slot' => (int) $this->harga_slot,

            'total_slot' => (int) $this->total_slot,

            'slot_tersedia' => (int) $this->slot_tersedia,

            /*
            |--------------------------------------------------------------------------
            | THUMBNAIL
            |--------------------------------------------------------------------------
            */
            'thumbnail' => $this->thumbnail,

            'thumbnail_url' => $this->thumbnail
                ? asset('storage/' . $this->thumbnail)
                : null,

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */
            'is_available' => $this->slot_tersedia > 0,

            /*
            |--------------------------------------------------------------------------
            | TIMESTAMP
            |--------------------------------------------------------------------------
            */
            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,
        ];
    }
}