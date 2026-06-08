@include('servicios.index', [
    'rolServicio' => 'empresa',
    'rutaInicio' => route('empresa.dashboard'),
    'rutaListado' => route('empresa.servicios.index'),
    'rutaDetalle' => 'empresa.servicios.crear',
    'subtituloPagina' => 'Explora los servicios que tu empresa puede solicitar y abre cada uno para ver su presentacion.',
    'metricasTarjetas' => [
        'disponibles' => ['label' => 'Disponibles', 'color' => '#3b82f6'],
        'categorias' => ['label' => 'Categorias', 'color' => '#64748b'],
        'vacantes' => ['label' => 'Vacantes', 'color' => '#8b5cf6'],
        'solicitados' => ['label' => 'Solicitudes', 'color' => '#10b981'],
    ],
    'accionSecundaria' => [
        'href' => route('empresa.solicitudes'),
        'label' => 'Ver mis vacantes',
    ],
    'usaFiltroNivel' => true,
    'mostrarNivel' => true,
    'estadoVacio' => [
        'titulo' => 'Aun no hay servicios para mostrar',
        'mensaje' => 'Cuando el administrador publique servicios para empresas, apareceran aqui con su detalle y presentacion.',
        'accion' => 'Ir al inicio',
        'href' => route('empresa.dashboard'),
    ],
    'textoLateral' => 'La lista solo muestra servicios activos para tu empresa. Pasa el mouse para ver una vista rapida y entra al detalle para solicitarlo o abrir el flujo de vacante.',
])
