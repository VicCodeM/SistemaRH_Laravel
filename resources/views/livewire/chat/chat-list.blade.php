<div wire:poll.3s.visible>
    {{-- Barra superior: iniciar conversación --}}
    @if(auth()->user()->esAdmin())
        <div class="chat-new-top">
            <button type="button" wire:click="$toggle('mostrarNuevos')"
                    class="chat-new-toggle {{ $mostrarNuevos ? 'abierto' : '' }}">
                <span><span class="chat-new-plus">＋</span> Nueva conversación</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     style="transition:transform .2s; {{ $mostrarNuevos ? 'transform:rotate(180deg);' : '' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            @if($mostrarNuevos)
                <input type="text" wire:model.live.debounce.300ms="buscarUsuario"
                       placeholder="Buscar empresa, candidato o interno..." class="chat-new-search" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                <div class="chat-new-list">
                    @forelse($usuariosSinChat as $u)
                        <button type="button" wire:click="iniciarChatConUsuario({{ $u->id }})" class="chat-new-user">
                            <x-avatar :src="$u->avatar_url" :nombre="$u->name" :tamano="36" />
                            <div>
                                <p class="user-name">{{ $u->name }}</p>
                                <p class="user-role">{{ ucfirst($u->rol) }}</p>
                            </div>
                        </button>
                    @empty
                        <p class="chat-new-empty">No hay personas para mostrar.</p>
                    @endforelse
                </div>
            @endif
        </div>
    @else
        <div class="chat-new-top">
            <button wire:click="iniciarChatConAdmin" class="chat-new-user">
                <span class="chat-new-icon">🛟</span>
                <div>
                    <p class="user-name">Escribir al administrador</p>
                    <p class="user-role">Soporte y dudas</p>
                </div>
            </button>
        </div>
    @endif

    {{-- Lista de conversaciones --}}
    @foreach($rooms as $room)
        @php
            $otro = $room->tipo === 'directo'
                ? $room->miembros->firstWhere('id', '!=', auth()->id())
                : null;
            $nombre = $otro?->name ?? ($room->nombre ?? 'Chat');
            $ultimoMensaje = $room->mensajes->first();
            $activa = request()->routeIs('chat.show') && request()->route('room')?->id === $room->id;
            $noLeidos = $room->noLeidosPara(auth()->user());
        @endphp
        <div class="chat-room-link {{ $activa ? 'active' : '' }}">
            <a href="{{ route('chat.show', $room) }}" class="chat-room-main">
                <div class="chat-room-av">
                    <x-avatar :src="$otro?->avatar_url" :nombre="$nombre" :tamano="46" />
                    @if($otro && $otro->estaEnLinea())
                        <span class="chat-online-dot" title="En línea"></span>
                    @endif
                    @if($noLeidos > 0)
                        <span class="badge-unread">{{ $noLeidos > 99 ? '99+' : $noLeidos }}</span>
                    @endif
                </div>
                <div class="chat-room-info">
                    <p class="chat-room-name">
                        {{ $nombre }}
                        @if($room->tipo === 'grupal')<span class="room-type">(grupo)</span>@endif
                    </p>
                    <p class="chat-room-meta {{ $noLeidos > 0 ? 'sin-leer' : '' }}">
                        {{ $ultimoMensaje ? \Illuminate\Support\Str::limit($ultimoMensaje->contenido, 40) : 'Sin mensajes todavía' }}
                    </p>
                </div>
            </a>

            <div class="chat-room-side">
                @if($ultimoMensaje)
                    <span class="chat-room-time">{{ $ultimoMensaje->created_at->locale('es')->diffForHumans(short: true) }}</span>
                @endif
                @if(auth()->user()->esAdmin())
                    <button type="button" class="chat-room-del" title="Eliminar conversación"
                            wire:click="eliminarConversacion({{ $room->id }})"
                            wire:confirm="¿Eliminar esta conversación para siempre? Se borrarán todos los mensajes y no se puede deshacer.">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    @endforeach

    @if($rooms->isEmpty())
        <p class="chat-list-empty">Sin conversaciones todavía</p>
    @endif
</div>
