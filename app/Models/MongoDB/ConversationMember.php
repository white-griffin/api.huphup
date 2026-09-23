<?php

namespace App\Models\MongoDB;


use MongoDB\Laravel\Eloquent\Model;

class ConversationMember extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'conversationmembers';

    protected $guarded = [];

    protected $casts = [
        'joinedAt' => 'datetime',
        'leftAt' => 'datetime',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(
            Conversation::class,
            'conversationId',
            '_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            ChatUser::class,
            'userId',
            '_id'
        );
    }
}
