<?php

namespace App\Livewire\Chat;

use App\Models\ChatRoom;
use Livewire\Component;

/**
 * Badge de notificaciones de chat para la navbar.
 * Muestra el total de mensajes no leídos en todas las conversaciones.
 */
class ChatNotificaciones extends Component
{
    public function getNoLeidosProperty(): int
    {
        $user = auth()->user();
        if (! $user) {
            return 0;
        }

        return ChatRoom::whereHas('miembros', fn ($q) => $q->where('user_id', $user->id))
            ->get()
            ->sum(function ($room) use ($user) {
                $member = $room->miembros()->where('user_id', $user->id)->first();
                if (! $member) {
                    return 0;
                }

                $lastRead = $member->pivot->last_read_message_id ?? 0;

                return $room->mensajes()
                    ->where('id', '>', $lastRead)
                    ->where('sender_user_id', '!=', $user->id)
                    ->count();
            });
    }

    public function render()
    {
        return view('livewire.chat.chat-notificaciones');
    }
}
