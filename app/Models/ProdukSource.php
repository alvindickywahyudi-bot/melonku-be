<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukSource extends Model
{
    protected $table = 'produk_source';

    protected $fillable = [
        'produk_id',
        'type',
        'path',
        'is_featured'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR (URL GAMBAR)
    |--------------------------------------------------------------------------
    */

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }
}