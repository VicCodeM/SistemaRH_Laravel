<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ChatRoomMember extends Pivot
{
    protected $table = 'chat_room_members';

    protected $fillable = [
        'chat_room_id', 'user_id', 'last_read_message_id', 'hidden_at'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'hidden_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
