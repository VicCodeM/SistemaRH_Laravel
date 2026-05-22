<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', $sitio['sitio_nombre'] ?? config('app.name', 'SistemaRH'))</title>
        @if(!empty($sitio['sitio_favicon']))
            <link rel="icon" href="{{ asset('storage/' . $sitio['sitio_favicon']) }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="guest-page">
        <div class="guest-wrapper">
            <div class="guest-brand fade-in">
                <a href="/" class="guest-logo">
                    <h1>Sistema<span>RH</span></h1>
                    <p class="guest-tagline">Gestión de talento</p>
                </a>
            </div>

            <div class="card fade-in guest-card">
                {{ $slot }}
            </div>

            <p class="guest-footer fade-in">&copy; {{ date('Y') }} SistemaRH. Todos los derechos reservados.</p>
        </div>
    </body>
</html>
