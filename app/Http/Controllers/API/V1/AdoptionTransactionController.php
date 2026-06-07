<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use App\Models\AdoptionProject;
use App\Models\AdoptionTransaction;

class AdoptionTransactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 💰 INVEST
    |--------------------------------------------------------------------------
    */
    public function invest(Request $request)
    {
        DB::beginTransaction();

        try {

            $request->validate([

                'project_id' => 'required|exists:adoption_projects,id',

                'slot' => 'required|integer|min:1',
            ]);

            $project = AdoptionProject::find(
                $request->project_id
            );

            /*
            |--------------------------------------------------------------------------
            | SLOT HABIS
            |--------------------------------------------------------------------------
            */
            if (
                $project->slot_tersedia
                < $request->slot
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
                $project->harga_slot
                * $request->slot;

            $profit =
                ($modal * $project->roi_percent)
                / 100;

            $totalAkhir =
                $modal + $profit;

            /*
            |--------------------------------------------------------------------------
            | CREATE TRANSACTION
            |--------------------------------------------------------------------------
            */
            $transaction = AdoptionTransaction::create([

                'user_id' => auth()->id(),

                'adoption_project_id' => $project->id,

                'slot' => $request->slot,

                'modal' => $modal,

                'roi_percent' => $project->roi_percent,

                'estimasi_profit' => $profit,

                'total_akhir' => $totalAkhir,

                'status' => 'active',

                'mulai_at' => now(),

                'jatuh_tempo_at' => Carbon::now()
                    ->addDays(
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

            DB::commit();

            return response()->json([

                'message' => 'Investasi berhasil',

                'data' => $transaction

            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

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
    | 📋 MY INVESTMENT
    |--------------------------------------------------------------------------
    */
    public function myInvestments()
    {
        try {

            $transactions = AdoptionTransaction::with([
                'project'
            ])

                ->where(
                    'user_id',
                    auth()->id()
                )

                ->latest()

                ->get();

            return response()->json([

                'message' => 'My investment',

                'data' => $transactions

            ]);

        } catch (\Throwable $e) {

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