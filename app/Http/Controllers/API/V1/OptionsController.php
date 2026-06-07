<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Greenhouse;
use App\Models\ProdukVariasi;

use App\Http\Resources\BaseOptionResource;

class OptionsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 🌱 GREENHOUSE
    |--------------------------------------------------------------------------
    */
    public function greenhouse(Request $request)
    {
        $search = $request->search;

        $query = Greenhouse::query()
            ->select('id', 'nama as text')
            ->search($search);

        return BaseOptionResource::collection(
            $query->paginate($request->limit ?? 100)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 📦 VARIASI
    |--------------------------------------------------------------------------
    */
    public function variasi(Request $request)
    {
        $search = $request->search;

        $query = ProdukVariasi::query()
            ->select('id', 'nama as text')
            ->search($search);

        return BaseOptionResource::collection(
            $query->paginate($request->limit ?? 100)
        );
    }
}