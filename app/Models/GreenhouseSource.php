<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GreenhouseSource extends Model
{
    use HasFactory;

    protected $table = 'greenhouse_source';

    protected $guarded = ['id'];
}
