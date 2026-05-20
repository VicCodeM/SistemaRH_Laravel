<?php

namespace App\Livewire\Chat;

use App\Models\ChatRoom;
use App\Models\User;
use Livewire\Component;

class ChatList extends Component
{
    public ?int $roomSeleccionadaId = null;

    public function mount(?int $roomId = null): void
    {
        $this->roomSeleccionadaId = $roomId;
    }

    public function iniciarChatConUsuario(int $userId): void
    {
        $user = auth()->user();
        abort_unless($user->esAdmin(), 403);

        $destinatario = User::findOrFail($userId);
        $this->abrirOcrearChat($user, $destinatario);
    }

    /**
     * Un usuario NO-admin abre (o crea) su chat con el administrador.
     * Soporte centralizado: los demás solo hablan con el admin.
     */
    public function iniciarChatConAdmin(): void
    {
        $user = auth()->user();
        abort_if($user->esAdmin(), 403, 'El administrador inicia los chats desde la lista.');

        $admin = User::where('rol', 'admin')->where('estado', 'activo')->orderBy('id')->first();
        abort_unless($admin, 404, 'No hay un administrador disponible por ahora.');

        $this->abrirOcrearChat($user, $admin);
    }

    /**
     * Busca el chat directo entre dos usuarios o lo crea. Redirige a él.
     */
    private function abrirOcrearChat(User $a, User $b): void
    {
        $room = ChatRoom::where('tipo', 'directo')
            ->where(function ($q) use ($a, $b) {
                $q->where('direct_user_a_id', $a->id)->where('direct_user_b_id', $b->id);
            })->orWhere(function ($q) use ($a, $b) {
                $q->where('tipo', 'directo')
                  ->where('direct_user_a_id', $b->id)->where('direct_user_b_id', $a->id);
            })->first();

        if (! $room) {
            $room = ChatRoom::create([
                'tipo'              => 'directo',
                'nombre'            => $b->name,
                'creado_por'        => $a->id,
                'direct_user_a_id'  => $a->id,
                'direct_user_b_id'  => $b->id,
            ]);
            $room->miembros()->attach([$a->id, $b->id], ['joined_at' => now()]);
        } else {
            // Si la había ocultado, reaparece al volver a abrirla
            $room->miembros()->updateExistingPivot($a->id, ['hidden_at' => null]);
        }

        $this->redirect(route('chat.show', $room));
    }

    public function eliminarConversacion(int $roomId): void
    {
        $user = auth()->user();
        $room = ChatRoom::findOrFail($roomId);

        // Solo un miembro (o el admin) puede borrarla
        abort_unless(
            $user->esAdmin() || $room->miembros()->where('user_id', $user->id)->exists(),
            403,
            'No puedes eliminar esta conversación.'
        );

        // Borrado real: mensajes y miembros se eliminan en cascada
        $room->delete();

        // Si estabas viendo esta conversación, vuelve a la bandeja
        if ($this->roomSeleccionadaId === $roomId) {
            $this->redirect(route('chat.index'));
        }
    }

    public function render()
    {
        $user = auth()->user();

        $rooms = ChatRoom::whereHas('miembros', fn ($q) => $q->where('user_id', $user->id))
            ->with(['mensajes' => fn ($q) => $q->latest()->limit(1), 'creador'])
            ->latest('updated_at')
            ->get();

        // Para admin: usuarios sin chat directo para poder iniciar uno nuevo
        $usuariosSinChat = collect();
        if ($user->esAdmin()) {
            $idsConChat = ChatRoom::whereHas('miembros', fn ($q) => $q->where('user_id', $user->id))
                ->where('tipo', 'directo')
                ->get()
                ->flatMap(fn ($r) => [$r->direct_user_a_id, $r->direct_user_b_id])
                ->filter(fn ($id) => $id !== $user->id)
                ->unique();

            $usuariosSinChat = User::whereNotIn('id', $idsConChat)
                ->where('id', '!=', $user->id)
                ->whereIn('rol', ['empresa', 'candidato', 'interno'])
                ->orderBy('name')
                ->get();
        }

        return view('livewire.chat.chat-list', compact('rooms', 'usuariosSinChat'));
    }
}
