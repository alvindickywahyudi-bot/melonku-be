<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdoptionTransaction extends Model
{
    protected $fillable = [

        'user_id',

        'adoption_project_id',

        /*
        |--------------------------------------------------------------------------
        | INVESTMENT
        |--------------------------------------------------------------------------
        */
        'slot',

        'modal',

        'roi_percent',

        'estimasi_profit',

        'total_akhir',

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */
        'status',

        /*
        |--------------------------------------------------------------------------
        | DATE
        |--------------------------------------------------------------------------
        */
        'mulai_at',

        'jatuh_tempo_at',
    ];

    protected $casts = [

        'roi_percent' => 'float',

        'mulai_at' => 'datetime',

        'jatuh_tempo_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(
            AdoptionProject::class,
            'adoption_project_id'
        );
    }
}