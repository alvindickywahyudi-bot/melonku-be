<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/*
|--------------------------------------------------------------------------
| ✅ IMPORT YANG BENAR
|--------------------------------------------------------------------------
*/
use App\Models\LaporanService;

class GenerateLaporanHarian extends Command
{
    protected $signature =
        'laporan:generate';

    protected $description =
        'Generate laporan harian otomatis';

    public function handle(
        LaporanService $laporanService
    )
    {
        $laporanService->generateDaily();

        $this->info(
            'Laporan harian berhasil dibuat 🚀'
        );
    }
}