<div wire:poll.1500ms.visible="actualizarMensajes" style="display:flex; flex-direction:column; height:100%;">
    {{-- Encabezado de la conversación --}}
    <div style="padding:14px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:12px; background:var(--surface);">
        <div style="width:36px; height:36px; border-radius:50%; background:var(--accent); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:600; font-size:14px;">
            {{ strtoupper(substr($room->nombre ?? 'C', 0, 1)) }}
        </div>
        <div>
            <p style="font-weight:600; font-size:14px; margin:0;">{{ $room->nombre ?? 'Chat' }}</p>
            <p style="font-size:12px; color:var(--text-muted); margin:0;">
                {{ \App\Models\ChatRoom::tipoLabel($room->tipo) }}
                @if($otroUsuario)
                    · {{ $otroUsuario->name }}
                @endif
            </p>
        </div>
    </div>

    {{-- Área de mensajes --}}
    <div id="chat-mensajes" style="flex:1; overflow-y:auto; padding:20px; display:flex; flex-direction:column; gap:4px;">
        @php
            $fechaAnterior = null;
        @endphp
        @forelse($mensajes as $msg)
            @php
                $esMio = $msg->sender_user_id === auth()->id();
                $fechaMsg = $msg->created_at->startOfDay();
            @endphp

            {{-- Separador de fecha --}}
            @if($fechaAnterior === null || !$fechaAnterior->equalTo($fechaMsg))
                @php $fechaAnterior = $fechaMsg; @endphp
                <div style="display:flex; align-items:center; justify-content:center; margin:12px 0;">
                    <span style="font-size:11px; color:var(--text-muted); background:var(--surface-2); padding:4px 12px; border-radius:12px;">
                        @if($fechaMsg->isToday())
                            Hoy
                        @elseif($fechaMsg->isYesterday())
                            Ayer
                        @else
                            {{ $fechaMsg->translatedFormat('d \d\e F') }}
                        @endif
                    </span>
                </div>
            @endif

            <div class="chat-mensaje-item" data-id="{{ $msg->id }}" data-sender="{{ $msg->sender_user_id }}" style="display:flex; {{ $esMio ? 'justify-content:flex-end' : 'justify-content:flex-start' }};">
                <div style="max-width:70%;">
                    @if(!$esMio)
                        <p style="font-size:11px; color:var(--text-muted); margin:0 0 3px 4px;">
                            {{ $msg->sender?->name ?? 'Usuario' }} · {{ ucfirst($msg->sender_role) }}
                        </p>
                    @endif
                    <div style="padding:10px 14px; border-radius:{{ $esMio ? '16px 16px 4px 16px' : '16px 16px 16px 4px' }};
                        background: {{ $esMio ? 'var(--accent)' : 'var(--surface-2)' }};
                        color: {{ $esMio ? '#fff' : 'inherit' }};
                        font-size:14px; line-height:1.5;">
                        {{ $msg->contenido }}
                    </div>
                    <p style="font-size:11px; color:var(--text-muted); margin:3px 4px 0; text-align:{{ $esMio ? 'right' : 'left' }};">
                        {{ $msg->created_at->format('H:i') }}
                        @if($esMio && $loop->last)
                            <span style="margin-left:4px;">✓</span>
                        @endif
                    </p>
                </div>
            </div>
        @empty
            <div style="flex:1; display:flex; align-items:center; justify-content:center;">
                <p style="color:var(--text-muted); font-size:14px;">Sé el primero en escribir un mensaje.</p>
            </div>
        @endforelse
    </div>

    {{-- Formulario de envío --}}
    <div style="padding:16px 20px; border-top:1px solid var(--border); background: var(--surface);">
        <form wire:submit="enviar" style="display:flex; gap:10px; align-items:flex-end;">
            <textarea wire:model="mensaje" rows="1" placeholder="Escribe un mensaje..."
                style="flex:1; padding:10px 14px; border:1px solid var(--border); border-radius:12px; font-size:14px; background: var(--surface-2); resize:none; max-height:120px; overflow-y:auto;"
                onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();$wire.enviar();}"></textarea>
            <button type="submit"
                style="padding:10px 16px; background: var(--accent); color:#fff; border:none; border-radius:12px; cursor:pointer; font-size:14px; white-space:nowrap; font-weight:500;">
                Enviar
            </button>
        </form>
        @error('mensaje') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
    </div>
</div>

{{-- Script para scroll inteligente y sonido --}}
<script>
(function() {
    const contenedor = document.getElementById('chat-mensajes');
    let ultimoMensajeId = null;

    // Determinar si el usuario está al final del scroll
    function estaAlFinal() {
        if (!contenedor) return true;
        return (contenedor.scrollHeight - contenedor.scrollTop - contenedor.clientHeight) < 50;
    }

    // Scroll al final
    function scrollAlFinal() {
        if (contenedor) {
            contenedor.scrollTop = contenedor.scrollHeight;
        }
    }

    // Sonido sutil al recibir mensaje
    function reproducirSonido() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 800;
            gain.gain.value = 0.05;
            osc.start();
            osc.stop(ctx.currentTime + 0.08);
        } catch(e) {}
    }

    // Inicial: scroll al final
    document.addEventListener('DOMContentLoaded', scrollAlFinal);

    // Al actualizar Livewire
    document.addEventListener('livewire:updated', () => {
        const mensajes = contenedor?.querySelectorAll('.chat-mensaje-item');
        const ultimo = mensajes?.[mensajes.length - 1];
        const nuevoId = ultimo?.getAttribute('data-id');

        if (ultimo) {
            const esMio = ultimo.getAttribute('data-sender') == {{ auth()->id() }};

            // Si hay mensaje nuevo ajeno, sonido
            if (!esMio && ultimoMensajeId !== null && nuevoId !== ultimoMensajeId) {
                reproducirSonido();
            }
        }

        // Solo auto-scroll si estaba al final
        if (estaAlFinal()) {
            scrollAlFinal();
        }

        ultimoMensajeId = nuevoId;
    });
})();
</script>
