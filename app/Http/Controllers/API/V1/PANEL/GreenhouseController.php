<?php

namespace App\Http\Controllers\API\V1\PANEL;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Provinsi;
use App\Models\Kabupaten;
use App\Models\Greenhouse;
use App\Models\GreenhouseSource;

use App\Http\Resources\PANEL\GreenhouseResource;

class GreenhouseController extends Controller
{
    
    public function index(Request $request)
    {
        list($sort, $dir) = explode(':', $request->sort ?? 'created_at:desc');

        $query = Greenhouse::search($request->search)->orderBy($sort, $dir)->paginate($request->limit ?? 10);

        return GreenhouseResource::collection($query);
    }

    public function show(Greenhouse $greenhouse)
    {
        return new GreenhouseResource($greenhouse);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $rules = [
            'nama' => 'required|min:3|max:100',
            'desc' => 'nullable|min:3|max:300',
            'alamat' => 'nullable|min:3|max:300',
            'provinsi' => ['required', Rule::in( Provinsi::select('id')->pluck('id')->all() )],
            'kabupaten' => ['required', Rule::in( Kabupaten::select('id')->pluck('id')->all() )],
            'lat' => 'nullable',
            'lng' => 'nullable',
        ];

        if($request->file_greenhouse) {
            $rules['file_greenhouse.*'] = 'required|mimes:doc,docx,PDF,pdf,jpg,jpeg,png|max:10240';
        }

        $request->validate($rules);

        try{
            DB::beginTransaction();

            $greenhouse = Greenhouse::create([
                'user_id' => $user->id,
                'nama' => $request->nama,
                'desc' => $request->desc,
                'alamat' => $request->alamat,
                'provinsi_id' => $request->provinsi,
                'kabupaten_id' => $request->kabupaten,
                'lat' => $request->lat,
                'lng' => $request->lng,
            ]);

            if ($request->file_greenhouse) { 

                foreach ($request->file('file_greenhouse') as $file) {
                    if ($file->isValid()) {
                        // list($originalFilename, $ext) = explode('.', $file->getClientOriginalName());
                        $filename = Str::random(30).'.'.$file->extension();
                        $fileUpload = $file->storeAs('file-greenhouse', $filename);

                        $greenhouse->source()->create([
                            'type' => $file->getClientMimeType(),
                            'path' => $fileUpload
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Berhasil', 'data' => new GreenhouseResource($greenhouse)], 200);
        }catch(\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Internal Server Error', 'errors' => [$e->getMessage()]], 500);
        }
    }

    public function update(Request $request, Greenhouse $greenhouse)
    {
        $user = $request->user();

        $rules = [
            'nama' => 'required|min:3|max:100',
            'desc' => 'nullable|min:3|max:300',
            'alamat' => 'nullable|min:3|max:300',
            'provinsi' => ['required', Rule::in( Provinsi::select('id')->pluck('id')->all() )],
            'kabupaten' => ['required', Rule::in( Kabupaten::select('id')->pluck('id')->all() )],
            'lat' => 'nullable',
            'lng' => 'nullable',
        ];

        if($request->file_greenhouse) {
            $rules['file_greenhouse.*'] = 'required|mimes:doc,docx,PDF,pdf,jpg,jpeg,png|max:10240';
        }

        $request->validate($rules);

        try{
            DB::beginTransaction();

            $greenhouse->update([
                'user_id' => $user->id,
                'nama' => $request->nama,
                'desc' => $request->desc,
                'alamat' => $request->alamat,
                'provinsi_id' => $request->provinsi,
                'kabupaten_id' => $request->kabupaten,
                'lat' => $request->lat,
                'lng' => $request->lng,
            ]);

            // hapus file greenhouse
            if($request->ids_delete_file) {
                foreach($request->ids_delete_file as $item) {
                    $sourceGreenhouse = GreenhouseSource::where('id', $item)->first();

                    if(!$sourceGreenhouse) {
                        DB::rollBack();
                        return response()->json(['status' => 'error', 'message' => 'File id '.$item.' Not Found', 'errors' => ['Not Found']], 404);
                    }

                    Storage::delete($sourceGreenhouse->path);

                    $sourceGreenhouse->delete();
                }
            }

            if ($request->file_greenhouse) { 

                foreach ($request->file('file_greenhouse') as $file) {
                    if ($file->isValid()) {
                        // list($originalFilename, $ext) = explode('.', $file->getClientOriginalName());
                        $filename = Str::random(30).'.'.$file->extension();
                        $fileUpload = $file->storeAs('file-greenhouse', $filename);

                        $greenhouse->source()->create([
                            'type' => $file->getClientMimeType(),
                            'path' => $fileUpload
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Berhasil', 'data' => new GreenhouseResource($greenhouse)], 200);
        }catch(\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Internal Server Error', 'errors' => [$e->getMessage()]], 500);
        }
    }

    public function restore($id)
    {
        try{
            DB::beginTransaction();

            $greenhouse = Greenhouse::onlyTrashed()->find($id);

            if(!$greenhouse) {
                DB::rollBack();
                return response()->json(['message' => 'Not Found', 'errors' => ['Not Found']], 404);
            }

            $greenhouse->restore();

            DB::commit();
            return response()->json(['message' => 'Berhasil', 'data' => new GreenhouseResource($greenhouse)], 200);
        }catch(\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Internal Server Error', 'errors' => [$e->getMessage()]], 500);
        }
    }

    public function destroy(Greenhouse $greenhouse)
    {
        if (!$greenhouse->delete())
            return response()->json(['message' => 'Internal Server Error', 'errors' => ['Internal Server Error']], 500);

        return response()->json(['message' => 'Berhasil hapus data', 'data' => new GreenhouseResource($greenhouse)], 200);
    }

    public function forceDelete($id)
    {
        try{
            DB::beginTransaction();

            $greenhouse = Greenhouse::onlyTrashed()->find($id);

            if(!$greenhouse) {
                DB::rollBack();
                return response()->json(['message' => 'Not Found', 'errors' => ['Not Found']], 404);
            }

            $greenhouse->source()->delete();
            $greenhouse->forceDelete();

            DB::commit();
            return response()->json(['message' => 'Berhasil', 'data' => new GreenhouseResource($greenhouse)], 200);
        }catch(\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Internal Server Error', 'errors' => [$e->getMessage()]], 500);
        }
    }
}
