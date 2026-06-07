<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $table = 'user_profile';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'user_id',

        'nama',

        // alamat lengkap
        'alamat_detail',

        // wilayah
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'village_id',

        // maps
        'lat',
        'lng',

    ];

    /*
    |--------------------------------------------------------------------------
    | CAST
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'tgl_lahir' => 'date',

        'lat' => 'float',
        'lng' => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    // Profile milik user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Provinsi
    public function provinsi()
    {
        return $this->belongsTo(
            Provinsi::class,
            'provinsi_id'
        );
    }

    // Kabupaten
    public function kabupaten()
    {
        return $this->belongsTo(
            Kabupaten::class,
            'kabupaten_id'
        );
    }

    // Kecamatan
    public function kecamatan()
    {
        return $this->belongsTo(
            Kecamatan::class,
            'kecamatan_id'
        );
    }

    // Village / Desa
    public function village()
    {
        return $this->belongsTo(
            Village::class,
            'village_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */

    // Label gender
    public function getGenderLabelAttribute()
    {
        return match ($this->gender) {
            'L' => 'Laki-Laki',
            'P' => 'Perempuan',
            default => '-',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER ALAMAT
    |--------------------------------------------------------------------------
    */

    // Full alamat otomatis
    public function getFullAddressAttribute()
    {
        return collect([
            $this->alamat_detail,
            optional($this->village)->nama,
            optional($this->kecamatan)->nama,
            optional($this->kabupaten)->nama,
            optional($this->provinsi)->nama,
        ])
        ->filter()
        ->implode(', ');
    }
}