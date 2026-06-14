<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationParticipant extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'joined_at'    => 'datetime',
        'last_read_at' => 'datetime',
    ];
}
