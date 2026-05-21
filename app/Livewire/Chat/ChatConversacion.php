<?php

namespace App\Livewire\Chat;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ChatConversacion extends Component
{
    public int $roomId = 0;
    public string $mensaje = '';

    public function mount(ChatRoom $room): void
    {
        if (! $this->puedeAcceder($room)) {
            $this->redirect(route('chat.index'));
            $this->skipRender();

            return;
        }

        $this->roomId = $room->id;
        auth()->user()?->tocarPresencia();
        $this->marcarLeido($room);
    }

    public function enviar(): void
    {
        $room = $this->sala();
        if (! $room) { $this->irABandeja(); return; }

        $this->validate(['mensaje' => 'required|string|max:2000']);

        $user = auth()->user();

        ChatMessage::create([
            'chat_room_id'   => $room->id,
            'sender_user_id' => $user->id,
            'sender_role'    => $user->rol,
            'tipo'           => 'texto',
            'contenido'      => $this->mensaje,
        ]);

        $room->touch();
        $this->marcarLeido($room);
        Cache::forget($this->typingKey($user->id)); // dejar de "escribir"
        $this->mensaje = '';
    }

    /**
     * Marca que este usuario está escribiendo (se borra solo a los 4s).
     */
    public function escribiendo(): void
    {
        Cache::put($this->typingKey(auth()->id()), true, now()->addSeconds(4));
    }

    public function eliminarMensaje(int $mensajeId): void
    {
        $user = auth()->user();
        $mensaje = ChatMessage::find($mensajeId);
        if (! $mensaje) {
            return;
        }

        if ($mensaje->sender_user_id !== $user->id && ! $user->esAdmin()) {
            return; // sin permiso, ignorar silenciosamente
        }

        $mensaje->delete();
    }

    public function actualizarMensajes(): void
    {
        $room = $this->sala();
        if (! $room) { $this->irABandeja(); return; }

        auth()->user()?->tocarPresencia();
        $this->marcarLeido($room);
    }

    /**
     * Carga la sala por id (null si ya fue eliminada).
     */
    private function sala(): ?ChatRoom
    {
        return $this->roomId ? ChatRoom::find($this->roomId) : null;
    }

    /**
     * La sala ya no existe: regresar a la bandeja sin reventar.
     */
    private function irABandeja(): void
    {
        $this->redirect(route('chat.index'));
        $this->skipRender();
    }

    private function typingKey(int $userId): string
    {
        return "chat_typing_{$this->roomId}_{$userId}";
    }

    private function marcarLeido(ChatRoom $room): void
    {
        $ultimoId = $room->mensajes()->max('id');
        if (! $ultimoId) {
            return;
        }

        // Solo escribir si realmente cambió (evita writes en cada sondeo)
        $miembro = $room->miembros()->where('user_id', auth()->id())->first();
        if ($miembro && (int) ($miembro->pivot->last_read_message_id ?? 0) === (int) $ultimoId) {
            return;
        }

        $room->miembros()->updateExistingPivot(auth()->id(), [
            'last_read_message_id' => $ultimoId,
        ]);
    }

    /**
     * Verifica acceso SIN abort (evita overlay de Livewire).
     */
    private function puedeAcceder(ChatRoom $room): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->esAdmin()) {
            return true;
        }

        return $room->miembros()->where('user_id', $user->id)->exists()
            && $room->miembros()->where('rol', 'admin')->exists();
    }

    public function render()
    {
        $room = $this->sala();
        if (! $room) {
            // La sala fue eliminada mientras la veías: regresa a la bandeja
            $this->redirect(route('chat.index'));

            return '<div></div>';
        }

        $mensajes = $room->mensajes()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        $otroUsuario = null;
        $otroLeyoHasta = 0;       // hasta qué mensaje leyó el otro (palomitas)
        $otroEscribiendo = false; // ¿el otro está escribiendo?
        $otroEnLinea = false;     // ¿el otro está conectado ahora?
        $otroUltimaVez = null;    // "hace 5 minutos"

        if ($room->tipo === 'directo') {
            $user = auth()->user();
            $otroId = $room->direct_user_a_id === $user->id
                ? $room->direct_user_b_id
                : $room->direct_user_a_id;
            $otroUsuario = User::find($otroId);

            if ($otroUsuario) {
                $pivotOtro = $room->miembros()->where('user_id', $otroUsuario->id)->first();
                $otroLeyoHasta = (int) ($pivotOtro?->pivot->last_read_message_id ?? 0);
                $otroEscribiendo = (bool) Cache::get($this->typingKey($otroUsuario->id));
                $otroEnLinea = $otroUsuario->estaEnLinea();
                $otroUltimaVez = $otroUsuario->ultimaVezTexto();
            }
        }

        return view('livewire.chat.chat-conversacion', compact(
            'room', 'mensajes', 'otroUsuario', 'otroLeyoHasta', 'otroEscribiendo', 'otroEnLinea', 'otroUltimaVez'
        ));
    }
}
