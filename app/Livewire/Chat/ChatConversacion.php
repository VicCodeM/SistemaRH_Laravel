<?php

namespace App\Livewire\Chat;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use Livewire\Component;

class ChatConversacion extends Component
{
    public ChatRoom $room;
    public string $mensaje = '';

    public function mount(ChatRoom $room): void
    {
        $this->room = $room;
        $this->autorizarAcceso();
        $this->marcarLeido();
    }

    public function enviar(): void
    {
        $this->validate(['mensaje' => 'required|string|max:2000']);

        $user = auth()->user();

        ChatMessage::create([
            'chat_room_id'   => $this->room->id,
            'sender_user_id' => $user->id,
            'sender_role'    => $user->rol,
            'tipo'           => 'texto',
            'contenido'      => $this->mensaje,
        ]);

        $this->room->touch();
        $this->marcarLeido();
        $this->mensaje = '';
    }

    public function actualizarMensajes(): void
    {
        $this->marcarLeido();
    }

    private function marcarLeido(): void
    {
        $ultimoMensaje = $this->room->mensajes()->latest()->first();
        if ($ultimoMensaje) {
            $this->room->miembros()->updateExistingPivot(auth()->id(), [
                'last_read_message_id' => $ultimoMensaje->id,
            ]);
        }
    }

    private function autorizarAcceso(): void
    {
        $user = auth()->user();
        $esMiembro = $this->room->miembros()->where('user_id', $user->id)->exists();

        // Admin puede ver cualquier sala
        if ($user->esAdmin()) return;

        abort_unless($esMiembro, 403);
    }

    public function render()
    {
        $mensajes = $this->room->mensajes()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        $otroUsuario = null;
        if ($this->room->tipo === 'directo') {
            $user = auth()->user();
            $otroId = $this->room->direct_user_a_id === $user->id
                ? $this->room->direct_user_b_id
                : $this->room->direct_user_a_id;
            $otroUsuario = \App\Models\User::find($otroId);
        }

        return view('livewire.chat.chat-conversacion', compact('mensajes', 'otroUsuario'));
    }
}
