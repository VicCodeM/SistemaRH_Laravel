@props([
    'icono'   => '📭',
    'titulo'  => 'Aún no hay nada aquí',
    'mensaje' => 'Cuando haya datos los verás en esta sección.',
    'accion'  => null,   // texto del botón
    'href'    => null,   // url del botón
])

<div style="text-align:center; padding:48px 24px; background:var(--surface-2); border:1px dashed var(--border); border-radius:12px;">
    <div style="font-size:48px; margin-bottom:10px; line-height:1;">{{ $icono }}</div>
    <h3 style="margin:0 0 6px; font-size:1rem; font-weight:600; color:var(--text);">{{ $titulo }}</h3>
    <p style="margin:0 0 18px; font-size:0.88rem; color:#64748b; max-width:380px; margin-left:auto; margin-right:auto;">{{ $mensaje }}</p>

    @if($accion && $href)
        <a href="{{ $href }}" class="btn btn-primary">{{ $accion }}</a>
    @endif

    @if($slot->isNotEmpty())
        <div style="margin-top:14px;">{{ $slot }}</div>
    @endif
</div>
