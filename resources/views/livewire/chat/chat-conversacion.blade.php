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
            <textarea id="chat-textarea" wire:model="mensaje" rows="1" autocomplete="off"
                placeholder="Escribe un mensaje..." class="chat-input-textarea"></textarea>
            <button type="submit" class="chat-input-btn">Enviar</button>
        </form>
        @error('mensaje') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
    </div>

    @script
    <script>
        const cont = document.getElementById('chat-mensajes');
        const ta = document.getElementById('chat-textarea');
        let cerca = true;   // ¿el usuario está viendo el final?
        let listo = false;  // evita sonar al cargar la conversación

        function abajo() { if (cont) cont.scrollTop = cont.scrollHeight; }

        function sonar() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator(), g = ctx.createGain();
                osc.connect(g); g.connect(ctx.destination);
                osc.frequency.value = 750; g.gain.value = 0.04;
                osc.start(); osc.stop(ctx.currentTime + 0.06);
            } catch (e) {}
        }

        if (cont) {
            cont.addEventListener('scroll', () => {
                cerca = (cont.scrollHeight - cont.scrollTop - cont.clientHeight) < 80;
            });

            requestAnimationFrame(abajo);
            setTimeout(() => { listo = true; }, 600);

            // Detecta mensajes nuevos (por envío o por el sondeo) sin parpadeos
            new MutationObserver((muts) => {
                let mio = false, ajeno = false;
                muts.forEach(m => m.addedNodes.forEach(n => {
                    if (n.nodeType === 1 && n.classList && n.classList.contains('chat-bubble-wrap')) {
                        if (n.classList.contains('mine')) mio = true;
                        else ajeno = true;
                    }
                }));

                if (!mio && !ajeno) return;
                if (ajeno && listo) sonar();
                if (mio || cerca) abajo();      // siempre baja con lo que yo escribo
                if (mio && ta) ta.focus();      // re-enfoca para seguir escribiendo
            }).observe(cont, { childList: true });
        }

        // Enter envía · Shift+Enter hace salto de línea
        if (ta) {
            ta.focus();
            ta.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (ta.value.trim() !== '') $wire.enviar();
                }
            });
        }
    </script>
    @endscript
</div>
