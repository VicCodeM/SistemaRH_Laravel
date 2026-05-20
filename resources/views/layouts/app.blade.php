<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name', 'SistemaRH'))</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
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

        // Disparar en submit de formularios
        document.addEventListener('submit', e => {
            RHP.start();
            const btn = e.target.querySelector('[type=submit]');
            if (btn && !btn.hasAttribute('data-no-load')) btn.classList.add('btn-loading');
        }, true);

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

        // Hook Livewire navigate
        document.addEventListener('livewire:navigate', () => {
            RHP.start();
            rhSidebarToggle(false);
        });
        document.addEventListener('livewire:navigated', () => { setTimeout(() => RHP.done(), 80); });

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
    </body>
</html>
