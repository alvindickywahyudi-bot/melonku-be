<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Str;

class AdoptionProject extends Model
{
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */
    protected $table = 'adoption_projects';

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    */
    protected $fillable = [

        'nama',
        'slug',
        'deskripsi',

        'roi_percent',
        'durasi_hari',
        'proteksi_percent',

        'harga_slot',
        'total_slot',
        'slot_tersedia',

        'thumbnail',

        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | APPEND
    |--------------------------------------------------------------------------
    */
    protected $appends = [

        'thumbnail_url'
    ];

    /*
    |--------------------------------------------------------------------------
    | CAST
    |--------------------------------------------------------------------------
    */
    protected $casts = [

        'status' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | AUTO SLUG
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {

            $item->slug = Str::slug(
                $item->nama
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | THUMBNAIL URL
    |--------------------------------------------------------------------------
    */
    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail
            ? asset('storage/' . $this->thumbnail)
            : null;
    }

    public function transactions()
    {
        return $this->hasMany(
            AdoptionTransaction::class
        );
    }
}