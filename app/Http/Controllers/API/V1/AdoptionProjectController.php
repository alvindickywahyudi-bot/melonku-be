<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use App\Http\Resources\PANEL\AdoptionProjectResource;
use App\Models\AdoptionProject;

class AdoptionProjectController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 🌱 LIST ADOPTION PROJECT
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {

            $projects = AdoptionProject::query()

                /*
                |--------------------------------------------------------------------------
                | ONLY ACTIVE
                |--------------------------------------------------------------------------
                */
                ->where('status', 1)

                /*
                |--------------------------------------------------------------------------
                | SEARCH
                |--------------------------------------------------------------------------
                */
                ->when($request->search, function ($q) use ($request) {

                    $q->where(
                        'nama',
                        'like',
                        '%' . $request->search . '%'
                    );
                })

                /*
                |--------------------------------------------------------------------------
                | LATEST
                |--------------------------------------------------------------------------
                */
                ->latest()

                /*
                |--------------------------------------------------------------------------
                | PAGINATION
                |--------------------------------------------------------------------------
                */
                ->paginate(
                    $request->limit ?? 10
                );

            return response()->json([

                'message' => 'List adoption project',

                'data' => $projects->map(function ($item) {

                    return [

                        /*
                        |--------------------------------------------------------------------------
                        | BASIC
                        |--------------------------------------------------------------------------
                        */
                        'id' => $item->id,

                        'nama' => $item->nama,

                        'slug' => $item->slug,

                        'deskripsi' => $item->deskripsi,

                        /*
                        |--------------------------------------------------------------------------
                        | ROI
                        |--------------------------------------------------------------------------
                        */
                        'roi_percent' => $item->roi_percent,

                        'durasi_hari' => $item->durasi_hari,

                        'proteksi_percent' => $item->proteksi_percent,

                        /*
                        |--------------------------------------------------------------------------
                        | SLOT
                        |--------------------------------------------------------------------------
                        */
                        'harga_slot' => $item->harga_slot,

                        'total_slot' => $item->total_slot,

                        'slot_tersedia' => $item->slot_tersedia,

                        /*
                        |--------------------------------------------------------------------------
                        | MEDIA
                        |--------------------------------------------------------------------------
                        */
                        'thumbnail_url' => $item->thumbnail_url,
                    ];
                }),

                'meta' => [

                    'current_page' => $projects->currentPage(),

                    'last_page' => $projects->lastPage(),

                    'per_page' => $projects->perPage(),

                    'total' => $projects->total(),
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
    | 🌱 DETAIL ADOPTION PROJECT
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        try {

            $project = AdoptionProject::query()

                ->where('status', 1)

                ->find($id);

            if (!$project) {

                return response()->json([

                    'message' => 'Project tidak ditemukan'

                ], 404);
            }

            return response()->json([

                'message' => 'Detail adoption project',

                'data' => [

                    /*
                    |--------------------------------------------------------------------------
                    | BASIC
                    |--------------------------------------------------------------------------
                    */
                    'id' => $project->id,

                    'nama' => $project->nama,

                    'slug' => $project->slug,

                    'deskripsi' => $project->deskripsi,

                    /*
                    |--------------------------------------------------------------------------
                    | ROI
                    |--------------------------------------------------------------------------
                    */
                    'roi_percent' => $project->roi_percent,

                    'durasi_hari' => $project->durasi_hari,

                    'proteksi_percent' => $project->proteksi_percent,

                    /*
                    |--------------------------------------------------------------------------
                    | SLOT
                    |--------------------------------------------------------------------------
                    */
                    'harga_slot' => $project->harga_slot,

                    'total_slot' => $project->total_slot,

                    'slot_tersedia' => $project->slot_tersedia,

                    /*
                    |--------------------------------------------------------------------------
                    | MEDIA
                    |--------------------------------------------------------------------------
                    */
                    'thumbnail_url' => $project->thumbnail_url,

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS
                    |--------------------------------------------------------------------------
                    */
                    'status' => $project->status,

                    /*
                    |--------------------------------------------------------------------------
                    | TIMESTAMP
                    |--------------------------------------------------------------------------
                    */
                    'created_at' => $project->created_at,
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
}