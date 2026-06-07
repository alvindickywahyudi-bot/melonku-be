<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produk extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | 🧱 TABLE
    |--------------------------------------------------------------------------
    */
    protected $table = 'produk';

    /*
    |--------------------------------------------------------------------------
    | 🛡️ MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */
protected $fillable = [

    'nama',
    'slug',
    'harga',
    'stok',
    'berat',
    'gambar',
    'seller_id',
    'greenhouse_id',
];

    /*
    |--------------------------------------------------------------------------
    | 🧠 APPENDS
    |--------------------------------------------------------------------------
    */
    protected $appends = [
        'gambar_url'
    ];

    /*
    |--------------------------------------------------------------------------
    | 🙈 HIDDEN
    |--------------------------------------------------------------------------
    */
    protected $hidden = [
        'deleted_at'
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔄 CASTS
    |--------------------------------------------------------------------------
    */
protected $casts = [

    'harga' => 'integer',
    'stok' => 'integer',
    'berat' => 'integer',
];
    /*
    |--------------------------------------------------------------------------
    | 🔗 RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | 👤 SELLER
    |--------------------------------------------------------------------------
    */
    public function seller()
    {
        return $this->belongsTo(
            User::class,
            'seller_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🌱 GREENHOUSE
    |--------------------------------------------------------------------------
    */
    public function greenhouse()
    {
        return $this->belongsTo(
            Greenhouse::class,
            'greenhouse_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🖼️ PRODUCT FILES / IMAGES
    |--------------------------------------------------------------------------
    */
    public function sources()
    {
        return $this->hasMany(
            ProdukSource::class,
            'produk_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | 📈 TIMELINE
    |--------------------------------------------------------------------------
    */
    public function timeline()
    {
        return $this->hasMany(
            ProdukTimeline::class,
            'produk_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 📄 DETAIL
    |--------------------------------------------------------------------------
    */
    public function detail()
    {
        return $this->hasOne(
            ProdukDetail::class,
            'produk_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🛒 ORDER ITEMS
    |--------------------------------------------------------------------------
    */
    public function orderItems()
    {
        return $this->hasMany(
            OrderItem::class,
            'product_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 ACCESSOR
    |--------------------------------------------------------------------------
    */
    public function getGambarUrlAttribute()
    {
        if (!$this->gambar) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | SUDAH FULL URL
        |--------------------------------------------------------------------------
        */
        if (str_starts_with($this->gambar, 'http')) {

            return $this->gambar;
        }

        return asset(
            'storage/' . ltrim($this->gambar, '/')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🔍 SEARCH
    |--------------------------------------------------------------------------
    */
    public function scopeSearch($query, $search)
    {
        if (!$search) {

            return $query;
        }

        return $query->where(function ($q) use ($search) {

            $q->where(
                'nama',
                'like',
                "%{$search}%"
            )

            ->orWhere(
                'slug',
                'like',
                "%{$search}%"
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | 🎯 PROMO
    |--------------------------------------------------------------------------
    */
    public function promo()
    {
        return $this->belongsToMany(
            Promo::class,
            'promo_produk',
            'produk_id',
            'promo_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🎁 ACTIVE PROMO
    |--------------------------------------------------------------------------
    */
    public function activePromo()
    {
        return $this->belongsToMany(
            Promo::class,
            'promo_produk',
            'produk_id',
            'promo_id'
        )->active();
    }

    /*
    |--------------------------------------------------------------------------
    | 🖼️ FEATURED IMAGE
    |--------------------------------------------------------------------------
    */
    public function featuredSource()
    {
        return $this->hasOne(
            ProdukSource::class,
            'produk_id'
        )->where('is_featured', 1);
    }

public function varian()
{
    return $this->hasMany(
        ProdukVarian::class,
        'produk_id'
    );
}


    /*
    |--------------------------------------------------------------------------
    | ⭐ REVIEWS
    |--------------------------------------------------------------------------
    */
    public function reviews()
    {
        return $this->hasMany(
            ProductReview::class,
            'produk_id'
        );
    }


}