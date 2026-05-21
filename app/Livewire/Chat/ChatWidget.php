<?php

namespace App\Livewire\Chat;

use App\Livewire\Chat\Concerns\ChatComun;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

/**
 * Chat flotante disponible en todas las páginas.
 * Reutiliza la lógica del chat normal vía el trait ChatComun.
 */
class ChatWidget extends Component
{
    use ChatComun;

    public bool $abierto = false;
    public ?int $roomId = null;        // conversación abierta dentro del panel
    public string $mensaje = '';
    public bool $mostrarNuevos = false;
    public string $buscarUsuario = '';

    public function toggle(): void
    {
        $this->abierto = ! $this->abierto;
    }

    public function abrirRoom(int $roomId): void
    {
        $room = ChatRoom::find($roomId);
        if (! $room || ! $this->puedeVer($room)) {
            $this->roomId = null;

            return;
        }

        $this->roomId = $roomId;
        $this->marcarLeidoEn($room);
    }

    public function volver(): void
    {
        $this->roomId = null;
        $this->mensaje = '';
    }

    public function enviar(): void
    {
        $room = $this->roomActual();
        if (! $room) {
            $this->roomId = null;

            return;
        }

        $this->validate(['mensaje' => 'required|string|max:2000']);
        $this->registrarMensaje($room, $this->mensaje);
        $this->mensaje = '';
    }

    public function escribiendo(): void
    {
        if ($this->roomId) {
            Cache::put($this->claveEscribiendo($this->roomId, auth()->id()), true, now()->addSeconds(4));
        }
    }

    public function eliminarMensaje(int $mensajeId): void
    {
        $user = auth()->user();
        $mensaje = ChatMessage::find($mensajeId);
        if (! $mensaje) {
            return;
        }

        if ($mensaje->sender_user_id !== $user->id && ! $user->esAdmin()) {
            return;
        }

        $mensaje->delete();
    }

    public function eliminarConversacion(int $roomId): void
    {
        if (! auth()->user()?->esAdmin()) {
            return;
        }

        ChatRoom::find($roomId)?->delete();
        if ($this->roomId === $roomId) {
            $this->roomId = null;
        }
    }

    public function iniciarChatConUsuario(int $userId): void
    {
        $user = auth()->user();
        if (! $user?->esAdmin()) {
            return;
        }

        $destinatario = User::find($userId);
        if (! $destinatario) {
            return;
        }

        $room = $this->abrirOcrearChat($user, $destinatario);
        $this->roomId = $room->id;
        $this->mostrarNuevos = false;
        $this->buscarUsuario = '';
        $this->marcarLeidoEn($room);
    }

    public function iniciarChatConAdmin(): void
    {
        $user = auth()->user();
        if (! $user || $user->esAdmin()) {
            return;
        }

        $admin = User::where('rol', 'admin')->where('estado', 'activo')->orderBy('id')->first();
        if (! $admin) {
            return;
        }

        $room = $this->abrirOcrearChat($user, $admin);
        $this->roomId = $room->id;
        $this->marcarLeidoEn($room);
    }

    private function roomActual(): ?ChatRoom
    {
        return $this->roomId ? ChatRoom::find($this->roomId) : null;
    }

    private function puedeVer(ChatRoom $room): bool
    {
        $user = auth()->user();
        if ($user->esAdmin()) {
            return true;
        }

        return $room->miembros()->where('user_id', $user->id)->exists()
            && $room->miembros()->where('rol', 'admin')->exists();
    }

    public function render()
    {
        $user = auth()->user();
        $user->tocarPresencia();

        $rooms = collect();
        $conv = null;
        $usuariosSinChat = collect();

        // ── Panel cerrado: solo badge (1 query rápida) ──
        if (! $this->abierto) {
            $noLeidosTotal = ChatRoom::totalNoLeidosPara($user->id);

            return view('livewire.chat.chat-widget', compact('rooms', 'noLeidosTotal', 'conv', 'usuariosSinChat'));
        }

        // ── Conversación abierta: mensajes + datos del otro ──
        if ($this->roomId) {
            $room = $this->roomActual();
            if ($room) {
                $conv = [
                    'room'     => $room,
                    'mensajes' => $room->mensajes()->with('sender')->orderBy('created_at')->get(),
                    'otro'     => $this->datosOtro($room),
                ];
            } else {
                $this->roomId = null;
            }
            $noLeidosTotal = ChatRoom::totalNoLeidosPara($user->id);

            return view('livewire.chat.chat-widget', compact('rooms', 'noLeidosTotal', 'conv', 'usuariosSinChat'));
        }

        // ── Lista abierta: cargar rooms + usuarios sin chat ──
        $rooms = ChatRoom::whereHas('miembros', fn ($q) => $q->where('user_id', $user->id))
            ->with(['mensajes' => fn ($q) => $q->latest()->limit(1), 'miembros'])
            ->latest('updated_at')
            ->get();

        $noLeidosTotal = $rooms->sum(fn ($room) => $room->noLeidosPara($user));

        if ($user->esAdmin() && $this->mostrarNuevos) {
            $idsConChat = $rooms->where('tipo', 'directo')
                ->flatMap(fn ($r) => [$r->direct_user_a_id, $r->direct_user_b_id])
                ->filter(fn ($id) => $id !== $user->id)
                ->unique();

            $usuariosSinChat = User::whereNotIn('id', $idsConChat)
                ->where('id', '!=', $user->id)
                ->whereIn('rol', ['empresa', 'candidato', 'interno'])
                ->when($this->buscarUsuario !== '', fn ($q) => $q->where('name', 'like', '%'.$this->buscarUsuario.'%'))
                ->orderBy('name')
                ->limit(30)
                ->get();
        }

        return view('livewire.chat.chat-widget', compact('rooms', 'noLeidosTotal', 'conv', 'usuariosSinChat'));
    }
}
