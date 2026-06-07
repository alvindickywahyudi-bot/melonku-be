<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    protected $fillable = [

        'user_id',

        'produk_id',

        'order_id',

        'rating',

        'review',

        'foto',

        'is_hidden'
    ];

    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUK
    |--------------------------------------------------------------------------
    */
    public function produk()
    {
        return $this->belongsTo(
            Produk::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ORDER
    |--------------------------------------------------------------------------
    */
    public function order()
    {
        return $this->belongsTo(
            Order::class
        );
    }
}