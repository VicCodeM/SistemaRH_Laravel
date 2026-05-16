<div wire:poll.1500ms.visible="actualizarMensajes" style="display:flex; flex-direction:column; height:100%;">
    {{-- Encabezado --}}
    <div class="chat-conv-header">
        <div class="header-avatar">
            {{ strtoupper(substr($room->nombre ?? 'C', 0, 1)) }}
        </div>
        <div class="header-info">
            <p class="header-name">{{ $room->nombre ?? 'Chat' }}</p>
            <p class="header-status">
                {{ \App\Models\ChatRoom::tipoLabel($room->tipo) }}
                @if($otroUsuario)
                    · {{ $otroUsuario->name }}
                @endif
                <span class="status-dot"></span>
            </p>
        </div>
    </div>

    {{-- Mensajes --}}
    <div id="chat-mensajes">
        @php $fechaAnterior = null; @endphp
        @forelse($mensajes as $msg)
            @php
                $esMio = $msg->sender_user_id === auth()->id();
                $fechaMsg = $msg->created_at->startOfDay();
                $puedeBorrar = $esMio || auth()->user()->esAdmin();
            @endphp

            @if($fechaAnterior === null || !$fechaAnterior->equalTo($fechaMsg))
                @php $fechaAnterior = $fechaMsg; @endphp
                <div class="chat-date-separator">
                    <span>
                        @if($fechaMsg->isToday()) Hoy
                        @elseif($fechaMsg->isYesterday()) Ayer
                        @else {{ $fechaMsg->translatedFormat('d \d\e F') }}
                        @endif
                    </span>
                </div>
            @endif

            <div class="chat-bubble-wrap {{ $esMio ? 'mine' : 'theirs' }}" style="position:relative;">
                <div class="chat-bubble">
                    @if(!$esMio)
                        <p class="chat-bubble-sender">{{ $msg->sender?->name ?? 'Usuario' }}</p>
                    @endif
                    <div>{{ $msg->contenido }}</div>
                    <div class="chat-bubble-time">
                        {{ $msg->created_at->format('H:i') }}
                        @if($esMio && $loop->last)
                            <span class="chat-check">✓</span>
                        @endif
                    </div>
                </div>
                @if($puedeBorrar)
                    <div class="chat-msg-actions">
                        <button onclick="if(confirm('¿Eliminar este mensaje?')) { @this.eliminarMensaje({{ $msg->id }}) }"
                            title="Eliminar mensaje">
                            ×
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div style="flex:1; display:flex; align-items:center; justify-content:center;">
                <div style="text-align:center;">
                    <p style="color:var(--text-muted); font-size:14px; margin:0 0 4px;">Sé el primero en escribir</p>
                    <p style="color:var(--text-muted); font-size:12px; margin:0; opacity:0.7;">Los mensajes se guardan de forma segura</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Input --}}
    <div class="chat-input-area">
        <form wire:submit="enviar" class="chat-input-form">
            <textarea wire:model="mensaje" rows="1" placeholder="Escribe un mensaje..."
                class="chat-input-textarea"
                onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();$wire.enviar();}"></textarea>
            <button type="submit" class="chat-input-btn">
                Enviar
            </button>
        </form>
        @error('mensaje') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
    </div>
</div>

<script>
(function() {
    const contenedor = document.getElementById('chat-mensajes');
    let ultimoMensajeId = null;

    function estaAlFinal() {
        if (!contenedor) return true;
        return (contenedor.scrollHeight - contenedor.scrollTop - contenedor.clientHeight) < 60;
    }

    function scrollAlFinal() {
        if (contenedor) contenedor.scrollTop = contenedor.scrollHeight;
    }

    function reproducirSonido() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 750;
            gain.gain.value = 0.04;
            osc.start();
            osc.stop(ctx.currentTime + 0.06);
        } catch(e) {}
    }

    document.addEventListener('DOMContentLoaded', scrollAlFinal);

    document.addEventListener('livewire:updated', () => {
        const mensajes = contenedor?.querySelectorAll('.chat-bubble-wrap');
        const ultimo = mensajes?.[mensajes.length - 1];
        const nuevoId = ultimo?.querySelector('.chat-bubble')?.dataset?.id;

        if (ultimo) {
            const esMio = ultimo.classList.contains('mine');
            if (!esMio && ultimoMensajeId !== null) {
                reproducirSonido();
            }
        }

        if (estaAlFinal()) scrollAlFinal();
        ultimoMensajeId = nuevoId;
    });
})();
</script>
