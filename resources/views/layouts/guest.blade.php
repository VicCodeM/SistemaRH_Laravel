<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name', 'SistemaRH'))</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body style="background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
        <div style="width: 100%;">
            <div class="fade-in" style="text-align: center; margin-bottom: 28px;">
                <a href="/" style="text-decoration: none;">
                    <h1 style="font-size: 2rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -1px;">Sistema<span style="color: #2563eb;">RH</span></h1>
                    <p style="color: #94a3b8; font-size: 0.75rem; letter-spacing: 2px; text-transform: uppercase; margin-top: 2px;">Gestión de Talento</p>
                </a>
            </div>

            <div class="card fade-in" style="max-width: 420px; margin: 0 auto; animation-delay: 0.1s;">
                {{ $slot }}
            </div>

            <p style="text-align: center; color: #94a3b8; font-size: 0.75rem; margin-top: 28px;">&copy; {{ date('Y') }} SistemaRH. Todos los derechos reservados.</p>
        </div>
    </body>
</html>
