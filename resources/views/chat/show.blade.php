<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Chat</h1>
        <p class="page-subtitle">{{ $room->nombre ?? 'Conversación' }}</p>
    </x-slot>

    <div class="card fade-in" style="display:grid; grid-template-columns:280px 1fr; height:580px; overflow:hidden; padding:0;">

        {{-- Panel izquierdo: lista de chats --}}
        <div style="border-right:1px solid var(--border); overflow-y:auto; padding:12px;">
            <p style="font-size:11px; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; padding:4px 8px; margin-bottom:8px;">Conversaciones</p>
            <livewire:chat.chat-list :room-id="$room->id" />
        </div>

        {{-- Panel derecho: conversación activa --}}
        <div style="display:flex; flex-direction:column; overflow:hidden;">
            {{-- Header del chat --}}
            <div style="padding:14px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:50%; background: var(--accent); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:600; font-size:14px; flex-shrink:0;">
                    {{ strtoupper(substr($room->nombre ?? 'C', 0, 1)) }}
                </div>
                <div>
                    <p style="font-weight:600; font-size:14px; margin:0;">{{ $room->nombre ?? 'Chat' }}</p>
                    <p style="font-size:12px; color:var(--text-muted); margin:0;">
                        {{ $room->tipo === 'grupal' ? 'Grupo' : 'Chat directo' }}
                        <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background: var(--success); margin-left:4px; vertical-align:middle;"></span>
                    </p>
                </div>
            </div>

            <livewire:chat.chat-conversacion :room="$room" />
        </div>

    </div>
</x-app-layout>
