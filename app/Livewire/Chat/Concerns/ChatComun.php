<?php

namespace App\Livewire\Chat\Concerns;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Lógica compartida del chat (conversación de página y widget flotante).
 */
trait ChatComun
{
    protected function claveEscribiendo(int $roomId, int $userId): string
    {
        return "chat_typing_{$roomId}_{$userId}";
    }

    /**
     * Crea un mensaje, actualiza la sala y marca como leído.
     */
    protected function registrarMensaje(ChatRoom $room, string $contenido): void
    {
        $user = auth()->user();

        ChatMessage::create([
            'chat_room_id'   => $room->id,
            'sender_user_id' => $user->id,
            'sender_role'    => $user->rol,
            'tipo'           => 'texto',
            'contenido'      => $contenido,
        ]);

        $room->touch();
        $this->marcarLeidoEn($room);
        Cache::forget($this->claveEscribiendo($room->id, $user->id));
    }

    protected function marcarLeidoEn(ChatRoom $room): void
    {
        $ultimoId = $room->mensajes()->max('id');
        if (! $ultimoId) {
            return;
        }

        $miembro = $room->miembros()->where('user_id', auth()->id())->first();
        if ($miembro && (int) ($miembro->pivot->last_read_message_id ?? 0) === (int) $ultimoId) {
            return;
        }

        $room->miembros()->updateExistingPivot(auth()->id(), [
            'last_read_message_id' => $ultimoId,
        ]);
    }

    /**
     * Datos del "otro" usuario en un chat directo: presencia, leído, escribiendo.
     *
     * @return array{usuario:?User, leyoHasta:int, escribiendo:bool, enLinea:bool, ultimaVez:?string}
     */
    protected function datosOtro(ChatRoom $room): array
    {
        $vacio = ['usuario' => null, 'leyoHasta' => 0, 'escribiendo' => false, 'enLinea' => false, 'ultimaVez' => null];

        if ($room->tipo !== 'directo') {
            return $vacio;
        }

        $userId = auth()->id();
        $otroId = $room->direct_user_a_id === $userId
            ? $room->direct_user_b_id
            : $room->direct_user_a_id;

        $otro = User::find($otroId);
        if (! $otro) {
            return $vacio;
        }

        $pivot = $room->miembros()->where('user_id', $otro->id)->first();

        return [
            'usuario'     => $otro,
            'leyoHasta'   => (int) ($pivot?->pivot->last_read_message_id ?? 0),
            'escribiendo' => (bool) Cache::get($this->claveEscribiendo($room->id, $otro->id)),
            'enLinea'     => $otro->estaEnLinea(),
            'ultimaVez'   => $otro->ultimaVezTexto(),
        ];
    }

    /**
     * Busca el chat directo entre dos usuarios o lo crea.
     */
    protected function abrirOcrearChat(User $a, User $b): ChatRoom
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
                'tipo'             => 'directo',
                'nombre'           => $b->name,
                'creado_por'       => $a->id,
                'direct_user_a_id' => $a->id,
                'direct_user_b_id' => $b->id,
            ]);
            $room->miembros()->attach([$a->id, $b->id], ['joined_at' => now()]);
        }

        return $room;
    }
}
