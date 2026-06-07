<?php

namespace App\Http\Resources\PANEL;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdukTimelineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'desc' => $this->desc,
            'tanggal' => date('Y-m-d', strtotime($this->w_awal)),
            'image' => $this->image ? url('v1/upload/' . $this->image) : null,
        ];
    }
}
