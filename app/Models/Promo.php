<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Promo extends Model
{
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | 🧱 TABLE
    |--------------------------------------------------------------------------
    */
    protected $table = 'promo';

    /*
    |--------------------------------------------------------------------------
    | 🛡️ MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */
    protected $fillable = [

        'nama',

        'slug',

        'deskripsi',

        'kode_promo',

        'tipe',

        'diskon',

        'minimal_belanja',

        'maksimal_diskon',

        'banner',

        'status',

        'tanggal_mulai',

        'tanggal_selesai',
        'is_flashsale',
        'flashsale_stock',
        'flashsale_limit',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔄 CASTS
    |--------------------------------------------------------------------------
    */
    protected $casts = [

        'diskon' => 'float',

        'minimal_belanja' => 'float',

        'maksimal_diskon' => 'float',

        'status' => 'boolean',

        'tanggal_mulai' => 'datetime',

        'tanggal_selesai' => 'datetime',
        'is_flashsale' => 'boolean',
        'flashsale_stock' => 'integer',
        'flashsale_limit' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔗 RELATION PRODUK
    |--------------------------------------------------------------------------
    */
    public function produk()
    {
        return $this->belongsToMany(
            Produk::class,
            'promo_produk',
            'promo_id',
            'produk_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ ACTIVE PROMO SCOPE
    |--------------------------------------------------------------------------
    */
    public function scopeActive(Builder $query)
    {
        return $query

            /*
            |--------------------------------------------------------------------------
            | STATUS ACTIVE
            |--------------------------------------------------------------------------
            */
            ->where('status', true)

            /*
            |--------------------------------------------------------------------------
            | START DATE
            |--------------------------------------------------------------------------
            */
            ->where(function ($q) {

                $q->whereNull('tanggal_mulai')

                    ->orWhere(
                        'tanggal_mulai',
                        '<=',
                        now()
                    );
            })

            /*
            |--------------------------------------------------------------------------
            | END DATE
            |--------------------------------------------------------------------------
            */
            ->where(function ($q) {

                $q->whereNull('tanggal_selesai')

                    ->orWhere(
                        'tanggal_selesai',
                        '>=',
                        now()
                    );
            });
    }

    /*
    |--------------------------------------------------------------------------
    | 🏷️ ACCESSOR LABEL
    |--------------------------------------------------------------------------
    */
    public function getLabelAttribute(): string
    {
        if ($this->tipe === 'percent') {

            return 'DISKON ' .
                (int) $this->diskon . '%';
        }

        return 'HEMAT Rp ' .
            number_format(
                $this->diskon,
                0,
                ',',
                '.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | 🖼️ ACCESSOR BANNER URL
    |--------------------------------------------------------------------------
    */
    public function getBannerUrlAttribute(): ?string
    {
        if (!$this->banner) {

            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | SUDAH FULL URL
        |--------------------------------------------------------------------------
        */
        if (
            str_starts_with($this->banner, 'http')
        ) {

            return $this->banner;
        }

        return asset(
            'storage/' . ltrim($this->banner, '/')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🔥 CHECK EXPIRED
    |--------------------------------------------------------------------------
    */
    public function isExpired(): bool
    {
        if (!$this->tanggal_selesai) {

            return false;
        }

        return now()->greaterThan(
            $this->tanggal_selesai
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🚀 CHECK STARTED
    |--------------------------------------------------------------------------
    */
    public function hasStarted(): bool
    {
        if (!$this->tanggal_mulai) {

            return true;
        }

        return now()->greaterThanOrEqualTo(
            $this->tanggal_mulai
        );
    }
}