<?php

namespace App\Http\Controllers\API\V1\PANEL;

use App\Http\Controllers\Controller;

use App\Http\Resources\PANEL\AdoptionProjectResource;

use App\Models\AdoptionProject;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;

class AdoptionProjectController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 📋 LIST PROJECT
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {

            $projects = AdoptionProject::query()

                ->when($request->search, function ($q) use ($request) {

                    $q->where(
                        'nama',
                        'like',
                        '%' . $request->search . '%'
                    );
                })

                ->latest()

                ->paginate($request->limit ?? 10);

            return response()->json([

                'message' => 'List adoption project',

                'data' => AdoptionProjectResource::collection($projects),

                'meta' => [

                    'current_page' => $projects->currentPage(),

                    'last_page' => $projects->lastPage(),

                    'per_page' => $projects->perPage(),

                    'total' => $projects->total(),
                ]
            ]);

        } catch (\Throwable $e) {

            Log::error($e);

            return $this->serverError($e);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 👁 DETAIL PROJECT
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        try {

            $project = AdoptionProject::find($id);

            if (!$project) {

                return response()->json([

                    'message' => 'Project tidak ditemukan'

                ], 404);
            }

            return response()->json([

                'message' => 'Detail adoption project',

                'data' => new AdoptionProjectResource($project)

            ]);

        } catch (\Throwable $e) {

            Log::error($e);

            return $this->serverError($e);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ➕ CREATE PROJECT
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $validated = $this->validateProject($request);

            /*
            |--------------------------------------------------------------------------
            | UPLOAD THUMBNAIL
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('thumbnail')) {

                $validated['thumbnail'] = $this->uploadThumbnail(
                    $request->file('thumbnail')
                );
            }

            $validated['slug'] = Str::slug($validated['nama']);

            $validated['slot_tersedia']
                = $validated['total_slot'];

            $project = AdoptionProject::create($validated);

            DB::commit();

            return response()->json([

                'message' => 'Project berhasil dibuat',

                'data' => new AdoptionProjectResource($project)

            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error($e);

            return $this->serverError($e);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ✏ UPDATE PROJECT
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $project = AdoptionProject::find($id);

            if (!$project) {

                return response()->json([

                    'message' => 'Project tidak ditemukan'

                ], 404);
            }

            $validated = $this->validateProject($request);

            /*
            |--------------------------------------------------------------------------
            | REPLACE THUMBNAIL
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('thumbnail')) {

                if ($project->thumbnail) {

                    Storage::delete($project->thumbnail);
                }

                $validated['thumbnail']
                    = $this->uploadThumbnail(
                        $request->file('thumbnail')
                    );
            }

            $validated['slug']
                = Str::slug($validated['nama']);

            $project->update($validated);

            DB::commit();

            return response()->json([

                'message' => 'Project berhasil diupdate',

                'data' => new AdoptionProjectResource($project)

            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error($e);

            return $this->serverError($e);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🗑 DELETE PROJECT
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $project = AdoptionProject::find($id);

            if (!$project) {

                return response()->json([

                    'message' => 'Project tidak ditemukan'

                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE THUMBNAIL
            |--------------------------------------------------------------------------
            */
            if ($project->thumbnail) {

                Storage::delete($project->thumbnail);
            }

            $project->delete();

            DB::commit();

            return response()->json([

                'message' => 'Project berhasil dihapus'

            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error($e);

            return $this->serverError($e);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ VALIDATION
    |--------------------------------------------------------------------------
    */
    private function validateProject(Request $request)
    {
        return $request->validate([

            'nama' => 'required|string|max:255',

            'deskripsi' => 'nullable|string',

            'roi_percent' => 'required|numeric|min:1',

            'durasi_hari' => 'required|integer|min:1',

            'proteksi_percent' => 'required|numeric|min:1',

            'harga_slot' => 'required|numeric|min:1',

            'total_slot' => 'required|integer|min:1',

            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🖼 UPLOAD THUMBNAIL
    |--------------------------------------------------------------------------
    */
    private function uploadThumbnail($file)
    {
        $filename = time()
            . '_'
            . Str::random(10)
            . '.'
            . $file->getClientOriginalExtension();

        return $file->storeAs(
            'adoption',
            $filename
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ❌ SERVER ERROR RESPONSE
    |--------------------------------------------------------------------------
    */
    private function serverError($e)
    {
        return response()->json([

            'message' => 'Internal Server Error',

            'errors' => [
                $e->getMessage()
            ]

        ], 500);
    }
}