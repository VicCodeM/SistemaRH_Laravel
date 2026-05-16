<div>
    @foreach($rooms as $room)
        @php
            $ultimoMensaje = $room->mensajes->first();
            $activa = request()->routeIs('chat.show') && request()->route('room')?->id === $room->id;
            $noLeidos = $room->noLeidosPara(auth()->user());
        @endphp
        <a href="{{ route('chat.show', $room) }}" wire:navigate
            style="display:flex; align-items:center; gap:12px; padding:12px 14px; text-decoration:none; border-radius:8px; margin-bottom:4px; {{ $activa ? 'background: var(--accent-light);' : '' }} transition: background .15s;"
            onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='{{ $activa ? 'var(--accent-light)' : 'transparent' }}'">

            <div style="width:38px; height:38px; border-radius:50%; background: var(--accent); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:600; font-size:15px; flex-shrink:0; position:relative;">
                {{ strtoupper(substr($room->nombre ?? 'C', 0, 1)) }}
                @if($noLeidos > 0)
                    <span style="position:absolute; top:-2px; right:-2px; min-width:16px; height:16px; background:var(--danger); color:#fff; font-size:9px; font-weight:700; border-radius:8px; display:flex; align-items:center; justify-content:center; padding:0 4px;">
                        {{ $noLeidos > 99 ? '99+' : $noLeidos }}
                    </span>
                @endif
            </div>
            <div style="min-width:0; flex:1;">
                <p style="font-weight:500; font-size:14px; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color: {{ $activa ? 'var(--accent)' : 'inherit' }};">
                    {{ $room->nombre ?? 'Chat' }}
                    @if($room->tipo === 'grupal')
                        <span style="font-size:11px; color:var(--text-muted);">(grupo)</span>
                    @endif
                </p>
                <p style="font-size:12px; color:var(--text-muted); margin:0;">
                    {{ \App\Models\ChatRoom::tipoLabel($room->tipo) }}
                </p>
                @if($ultimoMensaje)
                    <p style="font-size:12px; color:var(--text-muted); margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ \Illuminate\Support\Str::limit($ultimoMensaje->contenido, 40) }}
                    </p>
                @else
                    <p style="font-size:12px; color:var(--text-muted); margin:0;">Sin mensajes todavía</p>
                @endif
            </div>
        </a>
    @endforeach

    @if($rooms->isEmpty())
        <p style="font-size:13px; color:var(--text-muted); text-align:center; padding:20px 0;">Sin conversaciones activas</p>
    @endif

    @if(auth()->user()->esAdmin() && $usuariosSinChat->isNotEmpty())
        <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--border);">
            <p style="font-size:11px; color:var(--text-muted); font-weight:500; text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px;">Nueva conversación</p>
            @foreach($usuariosSinChat as $u)
                <button wire:click="iniciarChatConUsuario({{ $u->id }})"
                    style="display:flex; align-items:center; gap:10px; width:100%; padding:8px 10px; background:transparent; border:none; cursor:pointer; border-radius:8px; text-align:left;"
                    onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='transparent'">
                    <div style="width:32px; height:32px; border-radius:50%; background: var(--surface-2); display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:600; color:var(--text-muted); flex-shrink:0;">
                        {{ strtoupper(substr($u->name, 0, 1)) }}
                    </div>
                    <div>
                        <p style="font-size:13px; font-weight:500; margin:0;">{{ $u->name }}</p>
                        <p style="font-size:11px; color:var(--text-muted); margin:0;">{{ ucfirst($u->rol) }}</p>
                    </div>
                </button>
            @endforeach
        </div>
    @endif
</div>
