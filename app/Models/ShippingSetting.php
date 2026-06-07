<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingSetting extends Model
{
    protected $fillable = [

        'origin_city_id',

        'origin_city_name',

        'origin_province_name',

        'warehouse_name',

        'rajaongkir_api_key',

        'is_rajaongkir_active',
    ];
}