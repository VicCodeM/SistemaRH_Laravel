<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', ($sitio['sitio_nombre'] ?? config('app.name', 'SistemaRH')) . (!empty($sitio['sitio_subtitulo']) ? ' — ' . $sitio['sitio_subtitulo'] : ''))</title>
        @if(!empty($sitio['sitio_favicon']))
            <link rel="icon" href="{{ asset('storage/' . $sitio['sitio_favicon']) }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="guest-page">
        <div class="guest-wrapper">
            @php $marca = \App\Services\SitioService::partirMarca($sitio['sitio_nombre'] ?? 'SistemaRH'); @endphp
            <div class="guest-brand fade-in">
                <a href="/" class="guest-logo">
                    <h1>{{ $marca['base'] }}<span>{{ $marca['acento'] }}</span></h1>
                    @if(!empty($sitio['sitio_subtitulo']))
                        <p class="guest-tagline">{{ $sitio['sitio_subtitulo'] }}</p>
                    @endif
                </a>
            </div>

            <div class="card fade-in guest-card">
                {{ $slot }}
            </div>

            <p class="guest-footer fade-in">&copy; {{ date('Y') }} {{ $sitio['landing_footer'] ?? ($sitio['sitio_nombre'] ?? 'SistemaRH') . '. Todos los derechos reservados.' }} · v{{ config('app.version') }}</p>
        </div>
    </body>
</html>
