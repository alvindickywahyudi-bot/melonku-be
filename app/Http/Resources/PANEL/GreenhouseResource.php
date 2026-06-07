<?php

namespace App\Http\Resources\PANEL;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GreenhouseResource extends JsonResource
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

            'user_id' => $this->user_id,

            'nama' => $this->nama,

            'desc' => $this->desc,

            'alamat' => $this->alamat,

            /*
            |--------------------------------------------------------------------------
            | 🌍 LOCATION
            |--------------------------------------------------------------------------
            */
            'provinsi_id' => $this->provinsi_id,

            'provinsi' => optional(
                $this->provinsi
            )->nama,

            'kabupaten_id' => $this->kabupaten_id,

            'kabupaten' => optional(
                $this->kabupaten
            )->nama,

            'kecamatan_id' => $this->kecamatan_id,

            'kecamatan' => optional(
                $this->kecamatan
            )->nama,

            /*
            |--------------------------------------------------------------------------
            | 📍 COORDINATE
            |--------------------------------------------------------------------------
            */
            'lat' => $this->lat,

            'lng' => $this->lng,

            /*
            |--------------------------------------------------------------------------
            | 📁 FILES
            |--------------------------------------------------------------------------
            */
            'greenhouse_file' => $this->whenLoaded(
                'source',
                function () {

                    return GreenhouseSourceResource::collection(
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