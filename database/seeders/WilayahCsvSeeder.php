<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahCsvSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================
        // MATIKAN FOREIGN KEY
        // =========================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // =========================================
        // TRUNCATE DATA
        // =========================================
        DB::table('villages')->truncate();
        DB::table('kecamatan')->truncate();
        DB::table('kabupaten')->truncate();
        DB::table('provinsi')->truncate();

        // =========================================
        // HIDUPKAN LAGI FOREIGN KEY
        // =========================================
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // =========================================
        // PROVINSI
        // =========================================
        $this->importCSV('provinces.csv', function ($data) {

            DB::table('provinsi')->insert([
                'id' => (int) $data[0],
                'nama' => strtoupper(trim($data[1])),
                'kode' => (string) $data[0],
            ]);
        });

        echo "Provinsi: " . DB::table('provinsi')->count() . PHP_EOL;

        // =========================================
        // KABUPATEN
        // =========================================
        $this->importCSV('regencies.csv', function ($data) {

            DB::table('kabupaten')->insert([
                'id' => (int) $data[0],
                'provinsi_id' => (int) $data[1],
                'nama' => strtoupper(trim($data[2])),
                'type' => str_contains(strtolower($data[2]), 'kota')
                    ? 'kota'
                    : 'kabupaten',
                'kode' => (string) $data[0],
            ]);
        });

        echo "Kabupaten: " . DB::table('kabupaten')->count() . PHP_EOL;

        // =========================================
        // KECAMATAN
        // =========================================
        $this->importCSV('districts.csv', function ($data) {

            DB::table('kecamatan')->insert([
                'id' => (int) $data[0],
                'kabupaten_id' => (int) $data[1],
                'nama' => strtoupper(trim($data[2])),
                'kode' => (string) $data[0],
            ]);
        });

        echo "Kecamatan: " . DB::table('kecamatan')->count() . PHP_EOL;

        // =========================================
        // VILLAGES / DESA
        // =========================================
        $this->importCSV('villages.csv', function ($data) {

            DB::table('villages')->insert([
                'id' => (int) $data[0],
                'districts_id' => (int) $data[1],
                'nama' => strtoupper(trim($data[2])),
            ]);
        });

        echo "Villages: " . DB::table('villages')->count() . PHP_EOL;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER IMPORT CSV
    |--------------------------------------------------------------------------
    */
    private function importCSV($file, $callback): void
    {
        $path = storage_path("app/csv/$file");

        if (!file_exists($path)) {
            echo "File {$file} tidak ditemukan" . PHP_EOL;
            return;
        }

        $handle = fopen($path, 'r');

        if (!$handle) {
            echo "Gagal membuka file {$file}" . PHP_EOL;
            return;
        }

        // Skip Header
        fgetcsv($handle);

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {

            // Skip row kosong
            if (empty($data)) {
                continue;
            }

            // Validasi minimal kolom
            if (count($data) < 2) {
                continue;
            }

            // Skip jika ID bukan numeric
            if (!is_numeric($data[0])) {
                continue;
            }

            $callback($data);
        }

        fclose($handle);
    }
}