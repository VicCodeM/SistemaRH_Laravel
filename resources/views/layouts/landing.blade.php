<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name', 'SistemaRH'))</title>
        <meta name="description" content="Plataforma de gestión de talento - Reclutamiento, seguimiento de candidatos y automatización de procesos.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="landing-page">
        @yield('content')
    </body>
</html>
