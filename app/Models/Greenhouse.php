<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Greenhouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'greenhouse';

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id');
    }

    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class, 'greenhouse_id');
    }

    public function source(): HasMany
    {
        return $this->hasMany(GreenhouseSource::class, 'greenhouse_id');
    }

    public function scopeSearch($query, $search)
    {
        if($search) {
            $query->whereIn('id',
                DB::table('greenhouse')
                ->select('greenhouse.id')
                ->where('greenhouse.nama', 'like', '%'.$search. '%')
                ->orWhere('greenhouse.desc', 'like', '%'.$search. '%')
                ->orWhere('greenhouse.alamat', 'like', '%'.$search. '%')
                ->pluck('id')
                ->all()
            );
        }

        return $query;
    }
}
