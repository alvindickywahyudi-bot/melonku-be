<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id',
        'product_id',
        'produk_varian_id',
        'qty',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔗 RELATION CART
    |--------------------------------------------------------------------------
    */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔗 RELATION PRODUCT
    |--------------------------------------------------------------------------
    */
    public function produk()
    {
        return $this->belongsTo(
            Produk::class,
            'product_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🔗 RELATION VARIAN
    |--------------------------------------------------------------------------
    */
    public function varian()
    {
        return $this->belongsTo(
            ProdukVarian::class,
            'produk_varian_id'
        );
    }
}