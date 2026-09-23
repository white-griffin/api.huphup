<?php

namespace App\Models\MongoDB;

use MongoDB\Laravel\Eloquent\Model;

class ChatUser extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'users';

    protected $guarded = [];

    protected $casts = [
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public function memberships()
    {
        return $this->hasMany(
            ConversationMember::class,
            'userId',
            '_id'
        );
    }



    public function sentMessages()
    {
        return $this->hasMany(
            Message::class,
            'senderId',
            '_id'
        );
    }
}
