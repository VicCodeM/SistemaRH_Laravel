@include('servicios.presentacion', [
    'rolServicio' => 'candidato',
    'rutaInicio' => route('candidato.dashboard'),
    'rutaListado' => route('candidato.servicios.index'),
    'rutaGuardar' => route('candidato.servicios.guardar'),
    'servicioSeleccionado' => $servicioSeleccionado,
    'niveles' => [],
])
