<?php

namespace App\Models\MongoDB;

use MongoDB\Laravel\Eloquent\Model;

class Message extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'messages';

    protected $guarded = [];

    protected $casts = [
        'readBy' => 'array',
        'editedAt' => 'datetime',
        'deletedAt' => 'datetime',
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

    public function sender()
    {
        return $this->belongsTo(
            ChatUser::class,
            'senderId',
            '_id'
        );
    }

//    public function replyTo()
//    {
//        return $this->belongsTo(
//            Message::class,
//            'replyTo',
//            '_id'
//        );
//    }
}
