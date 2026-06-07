<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    use HasFactory;

    protected $table = 'kabupaten';

    protected $guarded = ['id'];

    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    public function kecamatan()
    {
        return $this->hasMany(Kecamatan::class, 'kabupaten_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeByProvinsi($query, $provinsiId)
    {
        if ($provinsiId) {
            $query->where('provinsi_id', $provinsiId);
        }

        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where('nama', 'like', "%{$search}%");
        }

        return $query;
    }
}