<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    use HasFactory;

    protected $table = 'provinsi';

    protected $guarded = ['id'];

    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function kabupaten()
    {
        return $this->hasMany(Kabupaten::class, 'provinsi_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where('nama', 'like', "%{$search}%");
        }

        return $query;
    }


/*
|--------------------------------------------------------------------------
| 🔥 SCOPE ACTIVE
|--------------------------------------------------------------------------
*/
public function scopeActive($query)
{
    return $query

        ->where('status', 1)

        ->where(function ($q) {

            $q->whereNull('tanggal_mulai')
              ->orWhere(
                  'tanggal_mulai',
                  '<=',
                  now()
              );
        })

        ->where(function ($q) {

            $q->whereNull('tanggal_selesai')
              ->orWhere(
                  'tanggal_selesai',
                  '>=',
                  now()
              );
 } );
}

}