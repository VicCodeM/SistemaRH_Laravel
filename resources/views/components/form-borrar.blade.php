@props([
    'action',                    // URL del DELETE
    'titulo' => 'Eliminar',      // texto del botón
    'pregunta' => '¿Estás seguro de eliminar este elemento?',
    'detalle' => null,           // contexto adicional opcional ("Esto borrará X postulaciones")
    'method'  => 'DELETE',
    'estilo'  => 'btn btn-danger',
])

@php
    $mensaje = $pregunta;
    if ($detalle) {
        $mensaje .= "\n\n" . $detalle;
    }
    $mensaje .= "\n\nEsta acción no se puede deshacer.";
@endphp

<form method="POST" action="{{ $action }}" style="display:inline; margin:0;"
      onsubmit="return confirm(@js($mensaje))">
    @csrf
    @if(strtoupper($method) !== 'POST')
        @method($method)
    @endif
    <button type="submit" {{ $attributes->merge(['class' => $estilo]) }}>
        {{ $titulo }}
    </button>
</form>
