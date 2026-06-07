<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'es' ? 'es' : str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', ($sitio['sitio_nombre'] ?? config('app.name', 'SistemaRH')) . (!empty($sitio['sitio_subtitulo']) ? ' — ' . $sitio['sitio_subtitulo'] : ''))</title>
        <meta name="description" content="@yield('meta_description', $sitio['sitio_descripcion'] ?? '')">
        @if(!empty($sitio['sitio_favicon']))
            @php $favExt = pathinfo($sitio['sitio_favicon'], PATHINFO_EXTENSION); @endphp
            <link rel="icon" type="{{ $favExt === 'svg' ? 'image/svg+xml' : ($favExt === 'ico' ? 'image/x-icon' : 'image/' . $favExt) }}" href="{{ asset('storage/' . $sitio['sitio_favicon']) }}">
        @else
            <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="landing-page">
        @yield('content')
    </body>
</html>
