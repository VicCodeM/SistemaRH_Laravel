@php
    $marca = \App\Services\SitioService::partirMarca(config('app.name', 'SistemaRH'));
    $nombreMarca = trim($marca['base'] . $marca['acento']);
@endphp
{{ $nombreMarca }}
Notificaciones oficiales del sistema

@if (! empty($greeting))
{{ $greeting }}
@else
@if ($level === 'error')
¡Ups!
@else
¡Hola!
@endif
@endif

@foreach ($introLines as $line)
{{ $line }}

@endforeach

@isset($actionText)
[{{ $actionText }}]({{ $actionUrl }})

@endisset

@foreach ($outroLines as $line)
{{ $line }}

@endforeach

@if (! empty($salutation))
{{ $salutation }}
@else
Saludos,
{{ config('app.name') }}
@endif

@isset($actionText)
Si tienes problemas para pulsar el botón "{{ $actionText }}", copia y pega esta URL en tu navegador:
{{ $displayableActionUrl }}
@endisset

© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
