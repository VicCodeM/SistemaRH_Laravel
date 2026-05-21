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

        return $user ? ChatRoom::totalNoLeidosPara($user->id) : 0;
    }

    public function render()
    {
        // Latido de presencia (se actualiza como mucho cada 15s)
        auth()->user()?->tocarPresencia();

        return view('livewire.chat.chat-notificaciones');
    }
}
