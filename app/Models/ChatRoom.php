<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChatRoom extends Model
{
    protected $fillable = [
        'tipo', 'nombre', 'creado_por', 'direct_user_a_id', 'direct_user_b_id'
    ];

    public static function tipos(): array
    {
        return CatalogoOpcion::opciones('chat_room_tipos', [
            'directo' => 'Chat directo',
            'grupal'  => 'Grupo',
        ]);
    }

    public static function tipoLabel(?string $tipo): string
    {
        return self::tipos()[$tipo] ?? ucfirst((string) $tipo);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'chat_room_id');
    }

    public function miembros(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_room_members', 'chat_room_id', 'user_id')
            ->withPivot(['joined_at', 'last_read_message_id', 'hidden_at'])
            ->withTimestamps();
    }
}
