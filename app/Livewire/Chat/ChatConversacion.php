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

    public function eliminarMensaje(int $mensajeId): void
    {
        $user = auth()->user();
        $mensaje = ChatMessage::findOrFail($mensajeId);

        // Solo el emisor o un admin puede eliminar
        if ($mensaje->sender_user_id !== $user->id && ! $user->esAdmin()) {
            abort(403, 'No puedes eliminar este mensaje.');
        }

        $mensaje->delete();
    }

    public function actualizarMensajes(): void
    {
        $this->marcarLeido();
    }

    private function marcarLeido(): void
    {
        $ultimoId = $this->room->mensajes()->max('id');
        if (! $ultimoId) {
            return;
        }

        // Solo escribir si realmente cambió (evita writes en cada sondeo)
        $miembro = $this->room->miembros()->where('user_id', auth()->id())->first();
        if ($miembro && (int) ($miembro->pivot->last_read_message_id ?? 0) === (int) $ultimoId) {
            return;
        }

        $this->room->miembros()->updateExistingPivot(auth()->id(), [
            'last_read_message_id' => $ultimoId,
        ]);
    }

    private function autorizarAcceso(): void
    {
        $user = auth()->user();

        // Admin puede ver cualquier sala
        if ($user->esAdmin()) return;

        // No-admin: debe ser miembro Y el chat debe incluir a un administrador
        $esMiembro = $this->room->miembros()->where('user_id', $user->id)->exists();
        abort_unless($esMiembro, 403, 'Esta conversación no es tuya.');

        $hayAdmin = $this->room->miembros()->where('rol', 'admin')->exists();
        abort_unless($hayAdmin, 403, 'Solo puedes conversar con el administrador.');
    }

    public function render()
    {
        $mensajes = $this->room->mensajes()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        $otroUsuario = null;
        $puedeEliminar = [];
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
