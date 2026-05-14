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
        <div class="app-layout">
            @include('layouts.navigation')
            <main class="main-content">
                @isset($header)
                    <div class="page-header fade-in">
                        {{ $header }}
                    </div>
                @endisset
                {{ $slot }}
            </main>
        </div>
        @livewireScripts

        {{-- Sistema global de modales --}}
        <div id="rh-modal-overlay"
             onclick="rhModalClose()"
             style="display:none; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,0.65); backdrop-filter:blur(3px); padding:24px;">
            <div id="rh-modal-box"
                 onclick="event.stopPropagation()"
                 style="position:relative; background:#0f172a; border:1px solid #1e293b; border-radius:16px; width:700px; max-width:96vw; max-height:88vh; overflow-y:auto; margin:0 auto; box-shadow:0 24px 60px rgba(0,0,0,0.5);">
                <div id="rh-modal-loading" style="padding:60px; text-align:center; color:#475569; font-size:14px;">
                    <svg style="width:28px;height:28px;margin:0 auto 12px;display:block;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                    Cargando...
                </div>
                <div id="rh-modal-content"></div>
            </div>
        </div>
        <style>@keyframes spin{to{transform:rotate(360deg)}}</style>
        <script>
        function rhModal(url) {
            const overlay = document.getElementById('rh-modal-overlay');
            const loading = document.getElementById('rh-modal-loading');
            const content = document.getElementById('rh-modal-content');
            overlay.style.display = 'block';
            loading.style.display = 'block';
            content.innerHTML = '';
            document.body.style.overflow = 'hidden';
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
                .then(r => r.text())
                .then(html => { loading.style.display = 'none'; content.innerHTML = html; })
                .catch(() => { loading.innerHTML = 'Error al cargar. Intenta de nuevo.'; });
        }
        function rhModalClose() {
            document.getElementById('rh-modal-overlay').style.display = 'none';
            document.getElementById('rh-modal-content').innerHTML = '';
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') rhModalClose(); });
        </script>
    </body>
</html>
