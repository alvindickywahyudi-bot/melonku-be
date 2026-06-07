<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Kurir extends Model
{
    use HasFactory;

    protected $table = 'kurir';

    protected $guarded = ['id'];
}
