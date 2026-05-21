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

    /**
     * Cuenta mensajes no leídos por un usuario en esta sala.
     * Usa la relación eager-loaded si ya está cargada.
     */
    public function noLeidosPara(User $user): int
    {
        $member = $this->relationLoaded('miembros')
            ? $this->miembros->firstWhere('id', $user->id)
            : $this->miembros()->where('user_id', $user->id)->first();

        if (! $member) {
            return 0;
        }

        $lastRead = $member->pivot->last_read_message_id ?? 0;

        return $this->mensajes()
            ->where('id', '>', $lastRead)
            ->where('sender_user_id', '!=', $user->id)
            ->count();
    }

    /**
     * Total de no leídos de TODAS las salas del usuario en UNA sola query.
     */
    public static function totalNoLeidosPara(int $userId): int
    {
        return (int) \Illuminate\Support\Facades\DB::selectOne("
            SELECT COALESCE(SUM(cnt), 0) AS total FROM (
                SELECT COUNT(*) AS cnt
                FROM chat_messages cm
                JOIN chat_room_members crm
                    ON crm.chat_room_id = cm.chat_room_id AND crm.user_id = ?
                WHERE cm.id > crm.last_read_message_id
                  AND cm.sender_user_id != ?
            ) t
        ", [$userId, $userId])->total;
    }
}
