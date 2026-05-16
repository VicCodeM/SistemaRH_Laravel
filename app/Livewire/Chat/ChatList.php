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

        // Buscar si ya existe un chat directo entre estos dos usuarios
        $room = ChatRoom::where('tipo', 'directo')
            ->where(function ($q) use ($user, $destinatario) {
                $q->where('direct_user_a_id', $user->id)->where('direct_user_b_id', $destinatario->id);
            })->orWhere(function ($q) use ($user, $destinatario) {
                $q->where('tipo', 'directo')
                  ->where('direct_user_a_id', $destinatario->id)->where('direct_user_b_id', $user->id);
            })->first();

        if (!$room) {
            $room = ChatRoom::create([
                'tipo'              => 'directo',
                'nombre'            => $destinatario->name,
                'creado_por'        => $user->id,
                'direct_user_a_id'  => $user->id,
                'direct_user_b_id'  => $destinatario->id,
            ]);
            $room->miembros()->attach([$user->id, $destinatario->id], ['joined_at' => now()]);
        }

        $this->redirect(route('chat.show', $room));
    }

    public function eliminarConversacion(int $roomId): void
    {
        $user = auth()->user();
        $room = ChatRoom::findOrFail($roomId);

        // Marcar como oculta para este usuario (soft delete personal)
        $room->miembros()->updateExistingPivot($user->id, [
            'hidden_at' => now(),
        ]);
    }

    public function render()
    {
        $user = auth()->user();

        $rooms = ChatRoom::whereHas('miembros', function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->whereNull('chat_room_members.hidden_at');
        })
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
                ->whereIn('rol', ['empresa', 'candidato'])
                ->orderBy('name')
                ->get();
        }

        return view('livewire.chat.chat-list', compact('rooms', 'usuariosSinChat'));
    }
}
