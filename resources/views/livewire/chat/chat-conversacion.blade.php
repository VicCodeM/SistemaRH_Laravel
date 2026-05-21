<div wire:poll.1500ms.visible="actualizarMensajes" style="display:flex; flex-direction:column; height:100%;">
    {{-- Encabezado --}}
    <div class="chat-conv-header">
        <a href="{{ route('chat.index') }}" class="chat-back-btn" title="Volver a conversaciones">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
        <x-avatar :src="$otroUsuario?->avatar_url" :nombre="$otroUsuario?->name ?? ($room->nombre ?? 'Chat')" :tamano="42" />
        <div class="header-info">
            <p class="header-name">{{ $otroUsuario?->name ?? ($room->nombre ?? 'Chat') }}</p>
            <p class="header-status">
                @if($otroEscribiendo)
                    <span class="chat-typing-label">escribiendo…</span>
                @elseif($otroEnLinea)
                    <span class="status-dot"></span> en línea
                @elseif($otroUltimaVez)
                    <span class="status-dot offline"></span> últ. vez {{ $otroUltimaVez }}
                @elseif($otroUsuario)
                    {{ ucfirst($otroUsuario->rol) }}
                @else
                    {{ \App\Models\ChatRoom::tipoLabel($room->tipo) }}
                @endif
            </p>
        </div>
    </div>

    {{-- Mensajes --}}
    <div id="chat-mensajes">
        @php $fechaAnterior = null; $emisorAnterior = null; @endphp
        @forelse($mensajes as $msg)
            @php
                $esMio = $msg->sender_user_id === auth()->id();
                $fechaMsg = $msg->created_at->startOfDay();
                $puedeBorrar = $esMio || auth()->user()->esAdmin();
                $mismoEmisor = ($emisorAnterior === $msg->sender_user_id);
                $leido = $otroLeyoHasta >= $msg->id;
            @endphp

            @if($fechaAnterior === null || !$fechaAnterior->equalTo($fechaMsg))
                @php $fechaAnterior = $fechaMsg; $emisorAnterior = null; $mismoEmisor = false; @endphp
                <div class="chat-date-separator">
                    <span>
                        @if($fechaMsg->isToday()) Hoy
                        @elseif($fechaMsg->isYesterday()) Ayer
                        @else {{ $fechaMsg->translatedFormat('d \d\e F') }}
                        @endif
                    </span>
                </div>
            @endif

            <div class="chat-bubble-wrap {{ $esMio ? 'mine' : 'theirs' }}">
                {{-- Avatar del otro (solo en el primero de una racha) --}}
                @if(!$esMio)
                    @if($mismoEmisor)
                        <span class="chat-bubble-av-spacer"></span>
                    @else
                        <x-avatar :src="$msg->sender?->avatar_url" :nombre="$msg->sender?->name ?? 'Usuario'" :tamano="30" class="chat-bubble-av" />
                    @endif
                @endif

                <div class="chat-bubble">
                    @if(!$esMio && $room->tipo === 'grupal' && !$mismoEmisor)
                        <p class="chat-bubble-sender">{{ $msg->sender?->name ?? 'Usuario' }}</p>
                    @endif
                    <div class="chat-bubble-text">{{ $msg->contenido }}</div>
                    <div class="chat-bubble-time">
                        {{ $msg->created_at->format('H:i') }}
                        @if($esMio)
                            <span class="chat-ticks {{ $leido ? 'leido' : '' }}" title="{{ $leido ? 'Leído' : 'Entregado' }}">
                                <svg viewBox="0 0 16 11" width="16" height="11" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 6.2 3.6 8.8 8.1 3.2"/>
                                    <path d="M6.4 8.8 10.9 3.2"/>
                                </svg>
                            </span>
                        @endif
                    </div>

                    @if($puedeBorrar)
                        <div class="chat-msg-actions">
                            <button type="button" wire:click="eliminarMensaje({{ $msg->id }})"
                                wire:confirm="¿Eliminar este mensaje?" title="Eliminar mensaje">×</button>
                        </div>
                    @endif
                </div>
            </div>

            @if($loop->last && $esMio && $leido)
                <div class="chat-visto">Visto</div>
            @endif

            @php $emisorAnterior = $msg->sender_user_id; @endphp
        @empty
            <div style="flex:1; display:flex; align-items:center; justify-content:center;">
                <div style="text-align:center;">
                    <p style="color:var(--text-muted); font-size:14px; margin:0 0 4px;">Sé el primero en escribir</p>
                    <p style="color:var(--text-muted); font-size:12px; margin:0; opacity:0.7;">Los mensajes se guardan de forma segura</p>
                </div>
            </div>
        @endforelse

        {{-- Indicador "escribiendo…" --}}
        @if($otroEscribiendo)
            <div class="chat-typing-wrap">
                <x-avatar :src="$otroUsuario?->avatar_url" :nombre="$otroUsuario?->name ?? 'Usuario'" :tamano="30" class="chat-bubble-av" />
                <div class="chat-typing-bubble"><span></span><span></span><span></span></div>
            </div>
        @endif
    </div>

    {{-- Input --}}
    <div class="chat-input-area">
        <form id="chat-form" wire:submit="enviar" class="chat-input-form">
            <textarea id="chat-textarea" wire:model="mensaje" rows="1" autocomplete="off"
                placeholder="Escribe un mensaje..." class="chat-input-textarea"></textarea>
            <button type="submit" class="chat-input-btn" title="Enviar (Enter)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
            </button>
        </form>
        @error('mensaje') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
    </div>

    @script
    <script>
        const cont = document.getElementById('chat-mensajes');
        const ta = document.getElementById('chat-textarea');
        let cerca = true;
        let listo = false;
        let ultimoPing = 0;
        const snd = window.rhSonido || {};

        function abajo() { if (cont) cont.scrollTop = cont.scrollHeight; }

        // Muestra el mensaje inmediatamente sin esperar al servidor
        function chatOptimisticSend(txt) {
            if (!cont) return;
            const hora = new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit', hour12: false });
            const wrap = document.createElement('div');
            wrap.className = 'chat-bubble-wrap mine chat-optimistic';
            wrap.innerHTML = '<div class="chat-bubble"><div class="chat-bubble-text">' + txt.replace(/&/g,'&amp;').replace(/</g,'&lt;') + '</div><div class="chat-bubble-time">' + hora + '</div></div>';
            cont.appendChild(wrap);
            abajo();
        }

        if (cont) {
            cont.addEventListener('scroll', () => {
                cerca = (cont.scrollHeight - cont.scrollTop - cont.clientHeight) < 90;
            });

            requestAnimationFrame(abajo);
            setTimeout(() => { listo = true; }, 600);

            new MutationObserver((muts) => {
                let mio = false, ajeno = false, escribiendo = false;
                muts.forEach(m => m.addedNodes.forEach(n => {
                    if (n.nodeType !== 1 || !n.classList) return;
                    if (n.classList.contains('chat-bubble-wrap') && !n.classList.contains('chat-optimistic')) {
                        n.classList.contains('mine') ? (mio = true) : (ajeno = true);
                    } else if (n.classList.contains('chat-typing-wrap')) {
                        escribiendo = true;
                    }
                }));

                // Limpiar burbujas optimistas cuando llega la respuesta real
                if (mio) document.querySelectorAll('.chat-optimistic').forEach(el => el.remove());

                if (ajeno && listo && snd.recibido) snd.recibido();
                if (escribiendo && listo && snd.escribiendo) snd.escribiendo();
                if (mio || escribiendo || cerca) abajo();
                if (mio && ta) ta.focus();
            }).observe(cont, { childList: true });
        }

        function chatEnviarRapido() {
            if (!ta) return;
            const txt = ta.value.trim();
            if (txt === '') return;
            chatOptimisticSend(txt);
            ta.value = '';
            if (snd.enviado) snd.enviado();
            $wire.set('mensaje', txt);
            $wire.enviar();
        }

        if (ta) {
            ta.focus();
            ta.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    chatEnviarRapido();
                }
            });
            ta.addEventListener('input', () => {
                const ahora = Date.now();
                if (ahora - ultimoPing > 2000) {
                    ultimoPing = ahora;
                    $wire.escribiendo();
                }
            });
        }

        const chatForm = document.getElementById('chat-form');
        if (chatForm) chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            chatEnviarRapido();
        });
    </script>
    @endscript
</div>
