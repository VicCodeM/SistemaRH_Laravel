<div class="chat-fab-wrap" data-noleidos="{{ $noLeidosTotal }}"
    data-estado="{{ $abierto && $conv ? 'conv' : ($abierto ? 'lista' : 'cerrado') }}">
    {{-- Botón flotante --}}
    <button type="button" class="chat-fab {{ $abierto ? 'abierto' : '' }} {{ (!$abierto && $noLeidosTotal > 0) ? 'tiene-nuevos' : '' }}" wire:click="toggle" title="Mensajes">
        @if($abierto)
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
        @else
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
            @if($noLeidosTotal > 0)
                <span class="chat-fab-badge">{{ $noLeidosTotal > 99 ? '99+' : $noLeidosTotal }}</span>
            @endif
        @endif
    </button>

    @if($abierto)
        <div class="chat-fab-panel">
            @if($conv)
                @php $room = $conv['room']; $mensajes = $conv['mensajes']; $otro = $conv['otro']; @endphp

                {{-- Encabezado conversación --}}
                <div class="chat-fab-header">
                    <button type="button" wire:click="volver" class="chat-fab-back" title="Volver">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <x-avatar :src="$otro['usuario']?->avatar_url" :nombre="$otro['usuario']?->name ?? ($room->nombre ?? 'Chat')" :tamano="34" />
                    <div class="chat-fab-htext">
                        <p class="chat-fab-name">{{ $otro['usuario']?->name ?? ($room->nombre ?? 'Chat') }}</p>
                        <p class="chat-fab-status">
                            @if($otro['escribiendo'])
                                <span class="chat-typing-label">escribiendo…</span>
                            @elseif($otro['enLinea'])
                                <span class="status-dot"></span> en línea
                            @elseif($otro['ultimaVez'])
                                <span class="status-dot offline"></span> {{ $otro['ultimaVez'] }}
                            @elseif($otro['usuario'])
                                {{ ucfirst($otro['usuario']->rol) }}
                            @endif
                        </p>
                    </div>
                    <button type="button" wire:click="toggle" class="chat-fab-close" title="Cerrar">&times;</button>
                </div>

                {{-- Mensajes --}}
                <div id="fab-mensajes" class="chat-fab-mensajes">
                    @php $fechaAnterior = null; $emisorAnterior = null; @endphp
                    @forelse($mensajes as $msg)
                        @php
                            $esMio = $msg->sender_user_id === auth()->id();
                            $fechaMsg = $msg->created_at->startOfDay();
                            $mismoEmisor = ($emisorAnterior === $msg->sender_user_id);
                            $leido = $otro['leyoHasta'] >= $msg->id;
                            $puedeBorrar = $esMio || auth()->user()->esAdmin();
                        @endphp

                        @if($fechaAnterior === null || !$fechaAnterior->equalTo($fechaMsg))
                            @php $fechaAnterior = $fechaMsg; $emisorAnterior = null; $mismoEmisor = false; @endphp
                            <div class="chat-date-separator"><span>
                                @if($fechaMsg->isToday()) Hoy
                                @elseif($fechaMsg->isYesterday()) Ayer
                                @else {{ $fechaMsg->translatedFormat('d \d\e F') }}
                                @endif
                            </span></div>
                        @endif

                        <div class="chat-bubble-wrap {{ $esMio ? 'mine' : 'theirs' }}">
                            @if(!$esMio)
                                @if($mismoEmisor)
                                    <span class="chat-bubble-av-spacer" style="width:26px;"></span>
                                @else
                                    <x-avatar :src="$msg->sender?->avatar_url" :nombre="$msg->sender?->name ?? 'Usuario'" :tamano="26" class="chat-bubble-av" />
                                @endif
                            @endif
                            <div class="chat-bubble">
                                <div class="chat-bubble-text">{{ $msg->contenido }}</div>
                                <div class="chat-bubble-time">
                                    {{ $msg->created_at->format('H:i') }}
                                    @if($esMio)
                                        <span class="chat-ticks {{ $leido ? 'leido' : '' }}">
                                            <svg viewBox="0 0 16 11" width="15" height="10" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 6.2 3.6 8.8 8.1 3.2"/><path d="M6.4 8.8 10.9 3.2"/></svg>
                                        </span>
                                    @endif
                                </div>
                                @if($puedeBorrar)
                                    <div class="chat-msg-actions">
                                        <button type="button" wire:click="eliminarMensaje({{ $msg->id }})" wire:confirm="¿Eliminar este mensaje?" title="Eliminar">×</button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($loop->last && $esMio && $leido)
                            <div class="chat-visto">Visto</div>
                        @endif

                        @php $emisorAnterior = $msg->sender_user_id; @endphp
                    @empty
                        <div style="flex:1; display:flex; align-items:center; justify-content:center; text-align:center;">
                            <p style="color:var(--text-muted); font-size:13px;">Sé el primero en escribir</p>
                        </div>
                    @endforelse

                    @if($otro['escribiendo'])
                        <div class="chat-typing-wrap">
                            <x-avatar :src="$otro['usuario']?->avatar_url" :nombre="$otro['usuario']?->name ?? 'Usuario'" :tamano="26" class="chat-bubble-av" />
                            <div class="chat-typing-bubble"><span></span><span></span><span></span></div>
                        </div>
                    @endif
                </div>

                {{-- Input --}}
                <div class="chat-input-area">
                    <form id="fab-form" wire:submit="enviar" class="chat-input-form">
                        <textarea id="fab-textarea" wire:model="mensaje" rows="1" autocomplete="off"
                            placeholder="Escribe un mensaje..." class="chat-input-textarea" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX"></textarea>
                        <button type="submit" class="chat-input-btn" title="Enviar">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                        </button>
                    </form>
                </div>
            @else
                {{-- Encabezado lista --}}
                <div class="chat-fab-header">
                    <span class="chat-fab-title">Mensajes</span>
                    <button type="button" wire:click="toggle" class="chat-fab-close" title="Cerrar">&times;</button>
                </div>

                <div class="chat-fab-mensajes chat-fab-lista">
                    {{-- Iniciar conversación --}}
                    @if(auth()->user()->esAdmin())
                        <div class="chat-new-top">
                            <button type="button" wire:click="$toggle('mostrarNuevos')" class="chat-new-toggle {{ $mostrarNuevos ? 'abierto' : '' }}">
                                <span><span class="chat-new-plus">＋</span> Nueva conversación</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transition:transform .2s; {{ $mostrarNuevos ? 'transform:rotate(180deg);' : '' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            @if($mostrarNuevos)
                                <input type="text" wire:model.live.debounce.300ms="buscarUsuario" placeholder="Buscar persona..." class="chat-new-search" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                                <div class="chat-new-list">
                                    @forelse($usuariosSinChat as $u)
                                        <button type="button" wire:click="iniciarChatConUsuario({{ $u->id }})" class="chat-new-user">
                                            <x-avatar :src="$u->avatar_url" :nombre="$u->name" :tamano="34" />
                                            <div><p class="user-name">{{ $u->name }}</p><p class="user-role">{{ ucfirst($u->rol) }}</p></div>
                                        </button>
                                    @empty
                                        <p class="chat-new-empty">No hay personas para mostrar.</p>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="chat-new-top">
                            <button type="button" wire:click="iniciarChatConAdmin" class="chat-new-user">
                                <span class="chat-new-icon">🛟</span>
                                <div><p class="user-name">Escribir al administrador</p><p class="user-role">Soporte y dudas</p></div>
                            </button>
                        </div>
                    @endif

                    {{-- Conversaciones --}}
                    @forelse($rooms as $room)
                        @php
                            $otroU = $room->tipo === 'directo' ? $room->miembros->firstWhere('id', '!=', auth()->id()) : null;
                            $nombre = $otroU?->name ?? ($room->nombre ?? 'Chat');
                            $ultimo = $room->mensajes->first();
                            $noLeidos = $room->noLeidosPara(auth()->user());
                        @endphp
                        <div class="chat-room-link" wire:click="abrirRoom({{ $room->id }})" style="cursor:pointer;">
                            <div class="chat-room-main">
                                <div class="chat-room-av">
                                    <x-avatar :src="$otroU?->avatar_url" :nombre="$nombre" :tamano="42" />
                                    @if($otroU && $otroU->estaEnLinea())<span class="chat-online-dot"></span>@endif
                                    @if($noLeidos > 0)<span class="badge-unread">{{ $noLeidos > 99 ? '99+' : $noLeidos }}</span>@endif
                                </div>
                                <div class="chat-room-info">
                                    <p class="chat-room-name">{{ $nombre }}</p>
                                    <p class="chat-room-meta {{ $noLeidos > 0 ? 'sin-leer' : '' }}">
                                        {{ $ultimo ? \Illuminate\Support\Str::limit($ultimo->contenido, 32) : 'Sin mensajes todavía' }}
                                    </p>
                                </div>
                            </div>
                            <div class="chat-room-side">
                                @if($ultimo)<span class="chat-room-time">{{ $ultimo->created_at->locale('es')->diffForHumans(short: true) }}</span>@endif
                                @if(auth()->user()->esAdmin())
                                    <button type="button" class="chat-room-del" title="Eliminar"
                                            wire:click.stop="eliminarConversacion({{ $room->id }})"
                                            wire:confirm="¿Eliminar esta conversación para siempre?">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="chat-list-empty">Sin conversaciones todavía</p>
                    @endforelse
                </div>
            @endif
        </div>
    @endif

    @script
    <script>
        const snd = window.rhSonido || {};
        let fabPrevNoLeidos = -1;
        let fabPrevMsgCount = -1;
        let fabListo = false;

        function fabScroll() {
            const c = document.getElementById('fab-mensajes');
            if (c) c.scrollTop = c.scrollHeight;
        }

        function fabBindTextarea() {
            const ta = document.getElementById('fab-textarea');
            if (!ta || ta.dataset.bound) return;
            ta.dataset.bound = '1';
            ta.focus();

            ta.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    fabEnviarRapido();
                }
            });

            const form = document.getElementById('fab-form');
            if (form) form.addEventListener('submit', (e) => {
                e.preventDefault();
                fabEnviarRapido();
            });

            let ping = 0;
            ta.addEventListener('input', () => {
                const n = Date.now();
                if (n - ping > 2000) { ping = n; $wire.escribiendo(); }
            });
        }

        function fabEnviarRapido() {
            const ta = document.getElementById('fab-textarea');
            if (!ta) return;
            const txt = ta.value.trim();
            if (txt === '') return;
            fabOptimisticSend(txt);
            ta.value = '';
            if (snd.enviado) snd.enviado();
            $wire.set('mensaje', txt);
            $wire.enviar();
        }

        function fabOptimisticSend(txt) {
            const cont = document.getElementById('fab-mensajes');
            if (!cont) return;
            const hora = new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit', hour12: false });
            const wrap = document.createElement('div');
            wrap.className = 'chat-bubble-wrap mine fab-optimistic';
            wrap.innerHTML = '<div class="chat-bubble"><div class="chat-bubble-text">' + txt.replace(/&/g,'&amp;').replace(/</g,'&lt;') + '</div><div class="chat-bubble-time">' + hora + '</div></div>';
            cont.appendChild(wrap);
            cont.scrollTop = cont.scrollHeight;
        }

        // ── Notificaciones de escritorio ──
        if ('Notification' in window && Notification.permission === 'default') {
            // Pedir permiso tras primera interacción del usuario
            document.addEventListener('click', function _perm() {
                Notification.requestPermission();
                document.removeEventListener('click', _perm);
            }, { once: true });
        }

        function fabNotificarEscritorio(cantidad) {
            if (!('Notification' in window) || Notification.permission !== 'granted') return;
            if (!document.hidden) return; // solo si la pestaña está oculta
            const n = new Notification('Nuevo mensaje', {
                body: cantidad > 1 ? 'Tienes ' + cantidad + ' mensajes sin leer' : 'Tienes un mensaje nuevo',
                icon: document.querySelector('link[rel="icon"]')?.href || '/favicon.ico',
                tag: 'chat-rh', // reemplaza notificaciones anteriores
                silent: true,   // el sonido ya lo maneja el chat
            });
            n.onclick = () => { window.focus(); n.close(); };
            setTimeout(() => n.close(), 6000);
        }

        function fabCheckSonidos() {
            const wrap = document.querySelector('.chat-fab-wrap');
            if (!wrap) return;

            // ── Detectar nuevos no leídos (badge) ──
            const nuevoNoLeidos = parseInt(wrap.dataset.noleidos || '0', 10);
            if (fabListo && nuevoNoLeidos > fabPrevNoLeidos && fabPrevNoLeidos >= 0) {
                // Hay nuevos mensajes: ¿estoy viendo la conversación o no?
                const enConv = !!document.getElementById('fab-mensajes') && !document.querySelector('.chat-fab-lista');
                if (enConv) {
                    // Estoy en la conversación: sonido de "recibido"
                    if (snd.recibido) snd.recibido();
                } else {
                    // Estoy en la lista o cerrado: sonido de "notificación"
                    if (snd.notificacion) snd.notificacion();
                    fabNotificarEscritorio(nuevoNoLeidos);
                }
            }
            fabPrevNoLeidos = nuevoNoLeidos;

            // ── Detectar nuevo mensaje en conversación abierta ──
            const cont = document.getElementById('fab-mensajes');
            if (cont) {
                const msgs = cont.querySelectorAll('.chat-bubble-wrap.theirs:not(.fab-optimistic)');
                const count = msgs.length;
                if (fabListo && count > fabPrevMsgCount && fabPrevMsgCount >= 0) {
                    if (snd.recibido) snd.recibido();
                }
                fabPrevMsgCount = count;
            } else {
                fabPrevMsgCount = -1;
            }

            // ── Detectar "escribiendo" ──
            const typing = document.querySelector('.chat-fab-wrap .chat-typing-wrap');
            if (typing && !typing.dataset.sonado) {
                typing.dataset.sonado = '1';
                if (fabListo && snd.escribiendo) snd.escribiendo();
            }
        }

        Livewire.hook('morphed', ({ component }) => {
            if (component.id !== $wire.id) return;
            document.querySelectorAll('.fab-optimistic').forEach(el => el.remove());
            fabScroll();
            fabBindTextarea();
            fabCheckSonidos();
            if (!fabListo) setTimeout(() => { fabListo = true; }, 800);
        });

        // Primer render
        requestAnimationFrame(() => {
            fabScroll();
            fabBindTextarea();
            fabCheckSonidos();
            setTimeout(() => { fabListo = true; }, 800);
        });

        // ── Sondeo adaptativo (reemplaza wire:poll) ──────────────────
        // Conv abierta: 500ms-5s · Lista: 3s · Cerrado: 8s · Tab oculta: pausa
        let fabSondeoTimer = null;
        let fabSondeoActividad = Date.now();
        let fabSondeoThrottle = 0;

        function fabGetDelay() {
            const wrap = document.querySelector('.chat-fab-wrap');
            const estado = wrap ? wrap.dataset.estado : 'cerrado';

            if (estado === 'conv') {
                const idle = Date.now() - fabSondeoActividad;
                return idle < 15000 ? 500 : idle < 60000 ? 2000 : 5000;
            }
            if (estado === 'lista') return 3000;
            return 4000; // cerrado: solo badge
        }

        function fabSondeoPoll() {
            $wire.$refresh()
                .then(() => fabSondeoSchedule())
                .catch(() => setTimeout(fabSondeoSchedule, 5000));
        }

        function fabSondeoSchedule() {
            if (fabSondeoTimer) clearTimeout(fabSondeoTimer);
            // Tab oculta: sondeo lento (15s) para seguir detectando mensajes y notificar
            const delay = document.hidden ? 15000 : fabGetDelay();
            fabSondeoTimer = setTimeout(fabSondeoPoll, delay);
        }

        function fabSondeoTouch() {
            const now = Date.now();
            if (now - fabSondeoThrottle < 2000) return;
            fabSondeoThrottle = now;
            const estabaInactivo = (now - fabSondeoActividad) > 15000;
            fabSondeoActividad = now;
            if (estabaInactivo) { clearTimeout(fabSondeoTimer); fabSondeoPoll(); }
        }

        document.addEventListener('mousemove', fabSondeoTouch, { passive: true });
        document.addEventListener('keydown', fabSondeoTouch);
        document.addEventListener('visibilitychange', () => {
            clearTimeout(fabSondeoTimer);
            if (document.hidden) {
                fabSondeoSchedule(); // sondeo lento (15s) para notificaciones de escritorio
            } else {
                fabSondeoActividad = Date.now();
                fabSondeoPoll(); // volver a velocidad normal
            }
        });

        fabSondeoSchedule();
    </script>
    @endscript
</div>
