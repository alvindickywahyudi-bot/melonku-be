<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukTimeline extends Model
{
    use HasFactory;

    protected $table = 'produk_timeline';

    protected $guarded = ['id'];
}
