<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';

    protected $fillable = [
        'kabupaten_id',
        'nama',
        'kode'
    ];

    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    // 🔗 ke kabupaten
    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class);
    }
}