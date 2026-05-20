@props([
    'src'   => null,   // URL o ruta relativa de storage
    'nombre' => '',    // nombre completo (para iniciales)
    'tamano' => 40,    // tamaño en px
])

@php
    $iniciales = collect(explode(' ', trim($nombre)))
        ->filter()
        ->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('');

    if (! $iniciales) {
        $iniciales = '?';
    }

    // Si src es ruta relativa, convertirla a URL completa
    $url = null;
    if ($src) {
        $url = str_starts_with($src, 'http') || str_starts_with($src, '/')
            ? $src
            : asset('storage/' . $src);
    }

    $fontSize = max(10, (int) ($tamano * 0.4));
@endphp

@if($url)
    <img src="{{ $url }}" alt="{{ $nombre }}"
         style="width:{{ $tamano }}px; height:{{ $tamano }}px; border-radius:50%; object-fit:cover; flex-shrink:0; border:1px solid var(--border);"
         {{ $attributes }}>
@else
    <div style="width:{{ $tamano }}px; height:{{ $tamano }}px; border-radius:50%; background:var(--accent-light); color:var(--accent); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:{{ $fontSize }}px; flex-shrink:0;"
         title="{{ $nombre }}"
         {{ $attributes }}>
        {{ $iniciales }}
    </div>
@endif
