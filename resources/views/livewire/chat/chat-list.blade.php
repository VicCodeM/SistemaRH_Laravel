<div>
    @foreach($rooms as $room)
        @php
            $ultimoMensaje = $room->mensajes->first();
            $activa = request()->routeIs('chat.show') && request()->route('room')?->id === $room->id;
            $noLeidos = $room->noLeidosPara(auth()->user());
        @endphp
        <a href="{{ route('chat.show', $room) }}" wire:navigate
            class="chat-room-link {{ $activa ? 'active' : '' }}">

            <div class="chat-room-avatar">
                {{ strtoupper(substr($room->nombre ?? 'C', 0, 1)) }}
                @if($noLeidos > 0)
                    <span class="badge-unread">{{ $noLeidos > 99 ? '99+' : $noLeidos }}</span>
                @endif
            </div>

            <div class="chat-room-info">
                <p class="chat-room-name">
                    {{ $room->nombre ?? 'Chat' }}
                    @if($room->tipo === 'grupal')
                        <span class="room-type">(grupo)</span>
                    @endif
                </p>
                @if($ultimoMensaje)
                    <p class="chat-room-meta">
                        {{ \Illuminate\Support\Str::limit($ultimoMensaje->contenido, 42) }}
                    </p>
                @else
                    <p class="chat-room-meta">Sin mensajes todavía</p>
                @endif
            </div>

            <div class="chat-room-time">
                @if($ultimoMensaje)
                    {{ $ultimoMensaje->created_at->diffForHumans(short: true) }}
                @endif
            </div>

            <div class="chat-room-actions"
                 onclick="event.preventDefault(); event.stopPropagation(); if(confirm('¿Eliminar esta conversación? Los mensajes seguirán guardados.')) { @this.eliminarConversacion({{ $room->id }}) }">
                <button title="Eliminar conversación">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </a>
    @endforeach

    @if($rooms->isEmpty())
        <p class="chat-list-empty">Sin conversaciones activas</p>
    @endif

    @if(auth()->user()->esAdmin() && $usuariosSinChat->isNotEmpty())
        <div class="chat-new-section">
            <p class="chat-new-label">Nueva conversación</p>
            @foreach($usuariosSinChat as $u)
                <button wire:click="iniciarChatConUsuario({{ $u->id }})" class="chat-new-user">
                    <div class="user-avatar">
                        {{ strtoupper(substr($u->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="user-name">{{ $u->name }}</p>
                        <p class="user-role">{{ ucfirst($u->rol) }}</p>
                    </div>
                </button>
            @endforeach
        </div>
    @endif
</div>
