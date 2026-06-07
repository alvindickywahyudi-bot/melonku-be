<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdoptionInvestment extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',

        'adoption_project_id',

        'slot',

        'modal',

        'roi_percent',

        'estimasi_profit',

        'total_akhir',

        'status',

        'mulai_at',

        'jatuh_tempo_at',

    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function project()
    {
        return $this->belongsTo(
            AdoptionProject::class,
            'adoption_project_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }
}