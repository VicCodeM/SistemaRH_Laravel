<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'es' ? 'es' : str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', ($sitio['sitio_nombre'] ?? config('app.name', 'SistemaRH')) . (auth()->check() ? ' — Panel ' . ucfirst(auth()->user()->rol) : ''))</title>
        @if(!empty($sitio['sitio_favicon']))
            @php $favExt = pathinfo($sitio['sitio_favicon'], PATHINFO_EXTENSION); @endphp
            <link rel="icon" type="{{ $favExt === 'svg' ? 'image/svg+xml' : ($favExt === 'ico' ? 'image/x-icon' : 'image/' . $favExt) }}" href="{{ asset('storage/' . $sitio['sitio_favicon']) }}">
        @else
            <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head-scripts')
        @livewireStyles
    </head>
    <body>
        {{-- Barra de progreso AJAX --}}
        <div id="rh-prog"></div>

        {{-- Botón mobile --}}
        <button class="sidebar-toggle" onclick="rhSidebarToggle()" aria-label="Menú">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
        </button>
        <div id="sidebar-backdrop" class="sidebar-backdrop" onclick="rhSidebarToggle(false)"></div>

        <div class="app-layout">
            @include('layouts.navigation')
            <main class="main-content">
                @isset($header)
                    <div class="page-header fade-in">
                        {{ $header }}
                    </div>
                @endisset
                <div class="fade-in">{{ $slot }}</div>
            </main>
        </div>
        @livewireScripts
        <script>
        // Evitar que Livewire muestre el overlay feo de error.
        // Recarga silenciosamente ante cualquier error de componente o sesión.
        document.addEventListener('livewire:init', () => {
            let reloading = false;
            function recargar() {
                if (reloading) return;
                reloading = true;
                window.location.reload();
            }

            // Errores de componentes Livewire (poll, acciones, etc.)
            Livewire.hook('request', ({ fail }) => {
                fail(({ status, preventDefault }) => {
                    preventDefault();
                    // Solo 419 (sesión expirada) recarga para renovar la sesión.
                    // Otros errores transitorios (poll, red) NO recargan → no cierran el chat.
                    if (status === 419) {
                        recargar();
                        return;
                    }
                    // Limpiar cualquier estado visual pegado, sin recargar
                    document.body.style.overflow = '';
                    document.body.style.pointerEvents = '';
                    document.body.style.opacity = '';
                });
            });

            // Listener global: cualquier componente Livewire puede disparar toast
            Livewire.on('notificacion', (data) => {
                const params = Array.isArray(data) ? data[0] : data;
                if (params && params.mensaje) {
                    rhToast(params.mensaje, params.tipo || 'info');
                }
            });
        });

        // Limpiar cualquier estado visual pegado al cargar la página
        window.addEventListener('pageshow', () => {
            document.body.style.overflow = '';
            document.body.style.pointerEvents = '';
            document.body.style.opacity = '';
            document.querySelectorAll('[data-livewire-navigate-loading]').forEach(el => el.remove());
        });
        </script>

        {{-- Modal global --}}
        <div id="rh-modal-overlay"
             onclick="rhModalClose()"
             style="pointer-events:none; opacity:0; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,.45); backdrop-filter:blur(4px); padding:24px; overflow-y:auto;">
            <div id="rh-modal-box"
                 onclick="event.stopPropagation()"
                 style="position:relative; background:#ffffff; border:1px solid var(--border); border-radius:16px; width:700px; max-width:96vw; max-height:88vh; overflow-y:auto; margin:40px auto; box-shadow:0 24px 60px rgba(0,0,0,.18); transform:scale(.95) translateY(-12px); opacity:0;">
                <div id="rh-modal-loading" style="padding:60px; text-align:center; color:var(--text-muted); font-size:14px;">
                    <svg style="width:28px;height:28px;margin:0 auto 12px;display:block;animation:spin .8s linear infinite;" fill="none" viewBox="0 0 24 24"><circle style="opacity:.2" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/><path style="opacity:.8" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                    Cargando...
                </div>
                <div id="rh-modal-content"></div>
            </div>
        </div>

        {{-- Contenedor de toasts --}}
        <div id="rh-toasts"></div>

        {{-- Chat flotante (visible en todas las páginas excepto la página de chat) --}}
        @auth
            @unless(request()->routeIs('chat.*'))
                <livewire:chat.chat-widget />
            @endunless
        @endauth

        <script>
        // ── Progress bar ──────────────────────────────────────────────
        const _prog = document.getElementById('rh-prog');
        const RHP = {
            start() {
                _prog.style.transition = 'none';
                _prog.style.width = '0';
                _prog.style.opacity = '1';
                _prog.classList.add('running');
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    _prog.style.transition = 'width .5s ease';
                    _prog.style.width = '65%';
                }));
            },
            done() {
                _prog.classList.remove('running');
                _prog.style.transition = 'width .2s ease, opacity .3s ease .15s';
                _prog.style.width = '100%';
                setTimeout(() => { _prog.style.opacity = '0'; setTimeout(() => { _prog.style.width = '0'; _prog.style.transition = 'none'; }, 350); }, 200);
            }
        };

        // Disparar en submit de formularios.
        // Se difiere a un microtask para saber si el envio realmente procede:
        // si un onsubmit="return confirm(...)" se cancela, no arrancamos la barra ni el boton.
        document.addEventListener('submit', e => {
            queueMicrotask(() => {
                // defaultPrevented sin manejo SPA = confirm cancelado u otra cancelacion: no hacer nada.
                if (e.defaultPrevented && !e.__rhSpaHandled) return;
                RHP.start();
                const btn = e.target.querySelector('[type=submit]');
                if (btn && !btn.hasAttribute('data-no-load')) {
                    btn.classList.add('btn-loading');
                    // Safety: si el submit fue cancelado (ej. confirm() = false),
                    // el boton sigue en el DOM y la pagina no navega.
                    // En ese caso, quitamos el estado visual tras un breve lapso.
                    setTimeout(() => {
                        if (document.contains(btn)) {
                            btn.classList.remove('btn-loading');
                            RHP.done();
                        }
                    }, 300);
                }
            });
        }, true);

        // ── SPA para formularios POST de acción: enviar sin recargar ───
        // Conserva el scroll y deja que el toast del flash se muestre solo.
        let _spaScrollY = null;
        document.addEventListener('submit', function(e) {
            // Respeta cualquier cancelacion previa (ej. onsubmit="return confirm(...)").
            // Si el usuario dio "Cancelar", el evento ya viene con defaultPrevented y NO debemos enviar.
            if (e.defaultPrevented) return;

            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;

            // Exclusiones de seguridad
            if (form.hasAttribute('data-no-spa')) return;
            if (form.hasAttribute('wire:submit')) return;            // forms Livewire
            if ((form.enctype || '').includes('multipart')) return;  // subida de archivos

            const metodo = (form.getAttribute('method') || 'get').toUpperCase();
            if (metodo !== 'POST') return;                            // las GET (filtros) quedan igual

            let accion;
            try {
                accion = new URL(form.action || window.location.href, window.location.origin);
                if (accion.origin !== window.location.origin) return; // solo mismo origen
            } catch { return; }

            // No tocar autenticación/sesión
            if (/\/(login|logout|register|password|confirm-password|email)/.test(accion.pathname)) return;

            e.preventDefault();
            e.__rhSpaHandled = true;
            _spaScrollY = window.scrollY;
            RHP.start();

            fetch(accion.href, {
                method: 'POST',
                body: new FormData(form),
                // Cabecera propia: el middleware RespuestaSpa devuelve { redirect } y conserva el flash.
                // Accept text/html: que la validación/errores se manejen como redirect (no 422 JSON).
                headers: { 'X-RH-SPA': '1', 'Accept': 'text/html' },
            })
            .then(async (response) => {
                let destino = null;
                try {
                    const data = await response.clone().json();
                    if (data && data.redirect) destino = data.redirect;
                } catch (_) { /* no vino JSON: usamos la pagina actual */ }

                if (!destino) destino = window.location.href;

                // Solo conservamos el scroll si seguimos en la MISMA pagina (acciones tipo toggle).
                // Si el servidor redirige a otra pagina (ej. la lista tras guardar), no lo restauramos.
                try {
                    if (new URL(destino, window.location.origin).pathname !== window.location.pathname) {
                        _spaScrollY = null;
                    }
                } catch (_) {}

                window.Livewire ? Livewire.navigate(destino) : (window.location.href = destino);
            })
            .catch(() => {
                _spaScrollY = null;
                form.submit();                       // si falla la red, envío normal
            });
        });

        // Completar al cargar la página (post-redirect)
        window.addEventListener('pageshow', () => RHP.done());

        function rhSidebarToggle(forceOpen) {
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');

            if (!sidebar) return;

            const isMobile = window.matchMedia('(max-width: 768px)').matches;
            if (!isMobile) {
                sidebar.classList.remove('open');
                backdrop?.classList.remove('show');
                document.body.classList.remove('sidebar-open');
                return;
            }

            const open = typeof forceOpen === 'boolean' ? forceOpen : !sidebar.classList.contains('open');
            sidebar.classList.toggle('open', open);
            backdrop?.classList.toggle('show', open);
            document.body.classList.toggle('sidebar-open', open);
        }

        // Hook Livewire navigate (con safety net si la navegación falla)
        let _navTimeout = null;
        document.addEventListener('livewire:navigate', () => {
            RHP.start();
            rhSidebarToggle(false);
            clearTimeout(_navTimeout);
            // Si en 8s no llega 'navigated', limpiar estado visual
            _navTimeout = setTimeout(() => {
                RHP.done();
                document.body.style.overflow = '';
                document.body.style.pointerEvents = '';
            }, 8000);
        });
        document.addEventListener('livewire:navigated', () => {
            clearTimeout(_navTimeout);
            setTimeout(() => RHP.done(), 80);
            // Conservar el scroll tras una acción POST (no saltar arriba)
            if (_spaScrollY !== null) {
                const y = _spaScrollY;
                _spaScrollY = null;
                requestAnimationFrame(() => window.scrollTo(0, y));
            }
        });

        // ── Toast system ──────────────────────────────────────────────
        function rhToast(msg, type) {
            type = type || 'success';
            const icons = {
                success: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                error:   '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>',
                warning: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>',
                info:    '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>',
            };
            const t = document.createElement('div');
            t.className = 'rh-toast rh-toast-' + type;
            t.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:18px;height:18px;flex-shrink:0;">' + (icons[type] || icons.success) + '</svg>'
                        + '<span style="flex:1;line-height:1.4;">' + msg + '</span>'
                        + '<button onclick="rhDismissToast(this.parentElement)" style="background:none;border:none;cursor:pointer;opacity:.5;font-size:18px;line-height:1;padding:0;color:inherit;">&times;</button>';
            document.getElementById('rh-toasts').appendChild(t);
            const tid = setTimeout(() => rhDismissToast(t), 5500);
            t._tid = tid;
        }
        function rhDismissToast(t) {
            if (!t || !t.parentElement) return;
            clearTimeout(t._tid);
            t.classList.add('out');
            setTimeout(() => t.remove(), 320);
        }

        // Auto-dismiss inline alerts después de 5s
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => {
                el.style.transition = 'opacity .4s ease, transform .4s ease';
                el.style.opacity = '0';
                el.style.transform = 'translateY(-6px)';
                setTimeout(() => el.remove(), 420);
            });
        }, 5000);

        // ── Modal system ──────────────────────────────────────────────
        function rhModal(url) {
            const overlay = document.getElementById('rh-modal-overlay');
            const loading = document.getElementById('rh-modal-loading');
            const content = document.getElementById('rh-modal-content');
            loading.style.display = 'block';
            content.innerHTML = '';
            overlay.classList.add('rh-open');
            document.body.style.overflow = 'hidden';
            RHP.start();
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
                .then(r => { if (!r.ok) throw new Error(r.status); return r.text(); })
                .then(html => {
                    loading.style.display = 'none';
                    content.innerHTML = html;
                    content.querySelectorAll('script').forEach(s => {
                        const ns = document.createElement('script');
                        ns.textContent = s.textContent;
                        s.replaceWith(ns);
                    });
                    // Inicializa componentes Alpine inyectados (ej. carrusel de presentacion)
                    if (window.Alpine) {
                        content.querySelectorAll('[x-data]').forEach(el => {
                            if (!el._x_dataStack) window.Alpine.initTree(el);
                        });
                    }
                    RHP.done();
                })
                .catch(() => {
                    loading.innerHTML = '<p style="color:var(--danger);text-align:center;">Error al cargar. Intenta de nuevo.</p>';
                    RHP.done();
                });
        }
        function rhModalClose() {
            const overlay = document.getElementById('rh-modal-overlay');
            overlay.classList.remove('rh-open');
            setTimeout(() => {
                document.getElementById('rh-modal-content').innerHTML = '';
                document.getElementById('rh-modal-loading').style.display = 'block';
                document.body.style.overflow = '';
            }, 250);
        }

        // ── SPA global: todos los links internos sin recarga ─────────
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href]');
            if (!link) return;

            // Ya tiene wire:navigate → Livewire lo maneja
            if (link.hasAttribute('wire:navigate')) return;

            // Links especiales: ignorar
            if (link.target === '_blank' || link.hasAttribute('download')) return;
            if (e.ctrlKey || e.metaKey || e.shiftKey) return;
            if (link.hasAttribute('data-no-spa')) return;

            // Solo links del mismo origen
            try {
                const url = new URL(link.href, window.location.origin);
                if (url.origin !== window.location.origin) return;
                // Hash puro en la misma página
                if (url.pathname === window.location.pathname && url.hash) return;
            } catch { return; }

            // Links dentro de forms, links con onclick que abren modal, links javascript:
            if (link.closest('form')) return;
            if (link.href.startsWith('javascript:')) return;
            if (link.getAttribute('onclick')?.includes('rhModal')) return;

            // Usar Livewire.navigate() → SPA silencioso
            e.preventDefault();
            Livewire.navigate(link.href);
        }, true);

        // ── Keyboard & sidebar ────────────────────────────────────────
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                rhModalClose();
                rhSidebarToggle(false);
            }
        });
        document.addEventListener('click', e => {
            if (!window.matchMedia('(max-width: 768px)').matches) {
                return;
            }

            if (e.target.closest('.sidebar a, .sidebar button[type=submit]')) {
                setTimeout(() => rhSidebarToggle(false), 40);
            }
        });
        window.addEventListener('resize', () => rhSidebarToggle(false));

        // ── Sonidos del chat ──────────────────────────────────────────
        window.rhSonido = (function() {
            let ctx = null;
            function ac() {
                if (!ctx || ctx.state === 'closed') ctx = new (window.AudioContext || window.webkitAudioContext)();
                if (ctx.state === 'suspended') ctx.resume();
                return ctx;
            }
            // El navegador bloquea el audio hasta la primera interacción.
            // Lo desbloqueamos con el primer clic o tecla del usuario.
            function desbloquear() {
                try { ac(); } catch (e) {}
                window.removeEventListener('pointerdown', desbloquear);
                window.removeEventListener('keydown', desbloquear);
            }
            window.addEventListener('pointerdown', desbloquear);
            window.addEventListener('keydown', desbloquear);
            function nodo(c, freq, start, dur, vol, type) {
                const o = c.createOscillator(), g = c.createGain();
                o.type = type || 'sine';
                o.frequency.value = freq;
                g.gain.setValueAtTime(0, start);
                g.gain.linearRampToValueAtTime(vol, start + 0.008);
                g.gain.exponentialRampToValueAtTime(0.001, start + dur);
                o.connect(g); g.connect(c.destination);
                o.start(start); o.stop(start + dur);
            }
            return {
                enviado: function() {
                    try {
                        const c = ac(), t = c.currentTime;
                        nodo(c, 520,  t,        0.06, 0.18, 'sine');
                        nodo(c, 780,  t + 0.04, 0.07, 0.14, 'sine');
                        nodo(c, 1180, t + 0.07, 0.09, 0.10, 'sine');
                    } catch(e){}
                },
                recibido: function() {
                    try {
                        const c = ac(), t = c.currentTime;
                        nodo(c, 830,  t,        0.12, 0.28, 'sine');
                        nodo(c, 1250, t + 0.09, 0.15, 0.22, 'sine');
                    } catch(e){}
                },
                notificacion: function() {
                    try {
                        const c = ac(), t = c.currentTime;
                        nodo(c, 660,  t,        0.10, 0.25, 'sine');
                        nodo(c, 880,  t + 0.08, 0.10, 0.22, 'sine');
                        nodo(c, 1100, t + 0.16, 0.14, 0.18, 'sine');
                    } catch(e){}
                },
                escribiendo: function() {
                    try {
                        const c = ac(), t = c.currentTime;
                        nodo(c, 440, t, 0.06, 0.10, 'sine');
                    } catch(e){}
                }
            };
        })();
        </script>

        {{-- Flash toasts (se ejecutan después de que rhToast esté definido) --}}
        @if(session('success'))
            <script>rhToast(@json(session('success')), 'success');</script>
        @endif
        @if(session('error'))
            <script>rhToast(@json(session('error')), 'error');</script>
        @endif
        @if(session('warning'))
            <script>rhToast(@json(session('warning')), 'warning');</script>
        @endif
        @stack('page-scripts')
    </body>
</html>
