@include('servicios.index', [
    'rolServicio' => 'candidato',
    'rutaInicio' => route('candidato.dashboard'),
    'rutaListado' => route('candidato.servicios.index'),
    'rutaDetalle' => 'candidato.servicios.crear',
    'subtituloPagina' => 'Explora los servicios que puedes solicitar y abre cada uno para ver su presentacion.',
    'metricasTarjetas' => [
        'disponibles' => ['label' => 'Disponibles', 'color' => '#3b82f6'],
        'categorias' => ['label' => 'Categorias', 'color' => '#64748b'],
        'solicitados' => ['label' => 'Solicitudes', 'color' => '#10b981'],
    ],
    'accionSecundaria' => null,
    'usaFiltroNivel' => false,
    'mostrarNivel' => false,
    'nivelesDisponibles' => collect(),
    'nivel' => '',
    'estadoVacio' => [
        'titulo' => 'Aun no hay servicios para mostrar',
        'mensaje' => 'Cuando el administrador publique servicios para candidatos, apareceran aqui con su detalle y presentacion.',
        'accion' => 'Ir al inicio',
        'href' => route('candidato.dashboard'),
    ],
    'textoLateral' => 'Aqui solo veras servicios activos para candidatos. Pasa el mouse para una vista rapida y entra al detalle para ver la presentacion completa antes de solicitarlo.',
])
