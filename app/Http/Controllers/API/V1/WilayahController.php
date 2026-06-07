<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;

use App\Models\Provinsi;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Village;

class WilayahController extends Controller
{
    public function provinsi()
    {
        return Provinsi::select('id', 'nama')->get();
    }

    public function kabupaten($id)
    {
        return Kabupaten::where('provinsi_id', $id)
            ->select('id', 'nama')
            ->get();
    }

    public function kecamatan($id)
    {
        return Kecamatan::where('kabupaten_id', $id)
            ->select('id', 'nama')
            ->get();
    }

    public function village($id)
    {
        return Village::where('districts_id', $id)
            ->select('id', 'nama')
            ->get();
    }
}