<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatHistory extends Model
{
    protected $table = 'ai_chat_histories';

    protected $fillable = [

        'user_id',

        'role',

        'message',
    ];

    /*
    |--------------------------------------------------------------------------
    | 👤 USER
    |--------------------------------------------------------------------------
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }
}