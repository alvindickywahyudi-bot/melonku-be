<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukDetail extends Model
{
    protected $table = 'produk_detail';

    protected $fillable = [
        'produk_id',
        'sweetness',
        'juiciness',
        'texture',
        'rating'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}