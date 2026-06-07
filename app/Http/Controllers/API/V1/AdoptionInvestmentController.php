<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use App\Models\AdoptionProject;
use App\Models\AdoptionInvestment;

class AdoptionInvestmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 🚀 INVEST
    |--------------------------------------------------------------------------
    */
    public function invest(Request $request)
    {
        try {

            $request->validate([

                'project_id' => 'required|exists:adoption_projects,id',

                'slot' => 'required|integer|min:1',

            ]);

            $project = AdoptionProject::findOrFail(
                $request->project_id
            );

            /*
            |--------------------------------------------------------------------------
            | SLOT VALIDATION
            |--------------------------------------------------------------------------
            */
            if (
                $request->slot >
                $project->slot_tersedia
            ) {

                return response()->json([

                    'message' => 'Slot tidak mencukupi'

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | CALCULATION
            |--------------------------------------------------------------------------
            */
            $modal =
                $project->harga_slot *
                $request->slot;

            $profit =
                ($modal * $project->roi_percent)
                / 100;

            $totalAkhir =
                $modal + $profit;

            /*
            |--------------------------------------------------------------------------
            | CREATE INVESTMENT
            |--------------------------------------------------------------------------
            */
            $investment =
                AdoptionInvestment::create([

                    'user_id' =>
                        auth()->id(),

                    'adoption_project_id' =>
                        $project->id,

                    'slot' =>
                        $request->slot,

                    'modal' =>
                        $modal,

                    'roi_percent' =>
                        $project->roi_percent,

                    'estimasi_profit' =>
                        $profit,

                    'total_akhir' =>
                        $totalAkhir,

                    'status' =>
                        'active',

                    'mulai_at' =>
                        now(),

                    'jatuh_tempo_at' =>
                        Carbon::now()->addDays(
                            $project->durasi_hari
                        ),

                ]);

            /*
            |--------------------------------------------------------------------------
            | REDUCE SLOT
            |--------------------------------------------------------------------------
            */
            $project->decrement(
                'slot_tersedia',
                $request->slot
            );

            return response()->json([

                'message' => 'Investasi berhasil',

                'data' => $investment

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
    | 🚀 MY INVESTMENT
    |--------------------------------------------------------------------------
    */
    public function myInvestment()
    {
        try {

            $investments =
                AdoptionInvestment::with('project')

                    ->where(
                        'user_id',
                        auth()->id()
                    )

                    ->latest()

                    ->get();

            return response()->json([

                'message' => 'My investment',

                'data' => $investments

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
    | 🚀 DASHBOARD INVESTASI
    |--------------------------------------------------------------------------
    */
    public function dashboard()
    {
        try {

            $user = auth()->user();

            $investments = AdoptionInvestment::where(
                'user_id',
                $user->id
            );

            $totalInvestasi =
                $investments->sum('modal');

            $totalProfit =
                $investments->sum(
                    'estimasi_profit'
                );

            $totalProject =
                $investments->count();

            $investasiAktif =
                AdoptionInvestment::where(
                    'user_id',
                    $user->id
                )
                ->where('status', 'active')
                ->count();

            $investasiSelesai =
                AdoptionInvestment::where(
                    'user_id',
                    $user->id
                )
                ->where('status', 'completed')
                ->count();

            return response()->json([

                'message' => 'Dashboard investasi',

                'data' => [

                    'total_investasi' =>
                        (int) $totalInvestasi,

                    'total_profit' =>
                        (int) $totalProfit,

                    'total_project' =>
                        (int) $totalProject,

                    'investasi_aktif' =>
                        (int) $investasiAktif,

                    'investasi_selesai' =>
                        (int) $investasiSelesai,

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