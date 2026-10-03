<?php

namespace App\Models\MongoDB;

use MongoDB\Laravel\Eloquent\Model;

class Conversation extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'conversations';

    protected $guarded = [];

    protected $casts = [
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
        'closedAt' => 'datetime',
    ];

    public function members()
    {
        return $this->hasMany(
            ConversationMember::class,
            'conversationId',
            '_id'
        );
    }

    public function messages()
    {
        return $this->hasMany(
            Message::class,
            'conversationId',
            '_id'
        )->orderBy('createdAt');
    }

    public function creator()
    {
        return $this->belongsTo(
            ChatUser::class,
            'createdBy',
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

    public function scopeSupport($query)
    {
        return $query->where('context', 'SUPPORT');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'OPEN');
    }

}
