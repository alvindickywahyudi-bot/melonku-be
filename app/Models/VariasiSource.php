<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariasiSource extends Model
{
    use HasFactory;

    protected $table = 'variasi_source';

    protected $guarded = ['id'];
}
