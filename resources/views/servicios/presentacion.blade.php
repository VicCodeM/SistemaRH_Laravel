<x-app-layout>
    @php
        $rolServicio = $rolServicio ?? 'candidato';
    @endphp

    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ $rutaInicio }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <a href="{{ $rutaListado }}">Servicios disponibles</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Detalle del servicio</span>
        </nav>
        <h1 class="page-title">
            {{ $servicioSeleccionado ? $servicioSeleccionado->nombre : 'Detalle del servicio' }}
        </h1>
        <p class="page-subtitle">
            {{ $servicioSeleccionado
                ? ($servicioSeleccionado->tienePresentacionActiva()
                    ? 'Revisa la presentacion interactiva del servicio y luego continua con tu solicitud.'
                    : 'Consulta el detalle del servicio y luego continua con tu solicitud.')
                : 'Selecciona un servicio de la lista para ver su presentacion y pedirlo.' }}
        </p>
    </x-slot>

    @if($errors->any())
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--danger-light); color:var(--danger); border-radius:8px; border-left:4px solid var(--danger); max-width:900px;">
            <ul style="margin:0; padding-left:16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($servicioSeleccionado)
        <div style="max-width:920px; display:flex; flex-direction:column; gap:18px;">
            <div class="card" style="padding:24px;">
                <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                    <div style="min-width:260px;">
                        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:10px;">
                            <span class="badge badge-green">Activo</span>
                            <span class="badge badge-blue">
                                {{ \App\Models\CatalogoServicio::tipos()[$servicioSeleccionado->tipo] ?? $servicioSeleccionado->tipo }}
                            </span>

                            @if($servicioSeleccionado->esFlujoVacante())
                                <span class="badge badge-gray">Solicitud de vacante</span>
                            @elseif($rolServicio === 'empresa' && $servicioSeleccionado->usaNivelJerarquicoPara('empresa') && $servicioSeleccionado->nivel_jerarquico !== 'todos')
                                <span class="badge badge-blue">
                                    {{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($servicioSeleccionado->nivel_jerarquico) }}
                                </span>
                            @endif
                        </div>

                        <h2 style="margin:0 0 6px; font-size:1.25rem; font-weight:800;">{{ $servicioSeleccionado->nombre }}</h2>
                        <p style="margin:0; color:#64748b; font-size:0.95rem; white-space:pre-wrap;">
                            {{ $servicioSeleccionado->descripcion ?: 'Sin descripcion detallada aun.' }}
                        </p>
                    </div>

                    <div style="flex:1; min-width:280px;">
                        <div style="background:var(--surface-2); border:1px solid var(--border); border-radius:14px; padding:14px 16px;">
                            <p style="margin:0 0 6px; font-size:0.72rem; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8;">Que veras aqui</p>
                            <p style="margin:0; font-size:0.92rem; color:var(--text);">
                                @if($servicioSeleccionado->tienePresentacionActiva())
                                    Aqui veras las diapositivas en imagen que preparo el administrador para este servicio. Se muestran en vivo con navegacion interactiva.
                                @else
                                    Este servicio no tiene presentacion visual activa en este momento. Aun asi puedes revisar su descripcion y solicitarlo desde aqui.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @include('partials.catalogo-servicio-recursos', [
                'catalogo' => $servicioSeleccionado,
                'puedeGestionar' => false,
            ])

            @if($rolServicio === 'empresa' && $servicioSeleccionado->esFlujoVacante())
                <div class="card" style="padding:24px;">
                    <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                        <div style="flex:1; min-width:260px;">
                            <h2 style="margin:0 0 10px; font-size:16px; font-weight:800; color:var(--text-primary);">Siguiente paso</h2>
                            <p style="margin:0; color:#64748b; line-height:1.6;">
                                Este servicio usa el flujo actual de vacantes. Desde aqui revisas la presentacion y despues entras al formulario donde defines puesto, nivel jerarquico, ingresos y prestaciones.
                            </p>
                        </div>

                        <div style="display:flex; gap:10px; flex-wrap:wrap; justify-content:flex-end;">
                            <a href="{{ $rutaListado }}" class="btn btn-secondary">Volver a la lista</a>
                            <a href="{{ route('empresa.solicitudes.crear', ['catalogo_servicio' => $servicioSeleccionado->id]) }}" class="btn btn-primary">
                                Solicitar vacante
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ $rutaGuardar }}" style="display:flex; flex-direction:column; gap:18px;">
                    @csrf
                    <input type="hidden" name="servicio_id" value="{{ $servicioSeleccionado->id }}">

                    @if($rolServicio === 'empresa')
                        <div class="card" style="padding:24px;">
                            <h2 style="margin:0 0 14px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">1. Alcance del servicio</h2>

                            @if($servicioSeleccionado->nivel_jerarquico !== 'todos')
                                <input type="hidden" name="nivel_jerarquico" value="{{ $servicioSeleccionado->nivel_jerarquico }}">

                                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:16px; border:1px solid var(--border); border-radius:12px; background:var(--surface-2);">
                                    <div>
                                        <p style="margin:0 0 6px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#64748b;">Nivel configurado</p>
                                        <h3 style="margin:0; font-size:16px; font-weight:700;">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($servicioSeleccionado->nivel_jerarquico) }}</h3>
                                    </div>
                                    <span class="badge badge-blue">Definido por el servicio</span>
                                </div>
                            @else
                                <p style="margin:0 0 12px; color:#64748b; font-size:0.92rem;">
                                    Este servicio aplica para varios niveles. Elige a quien va dirigido dentro de tu empresa.
                                </p>

                                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(170px, 1fr)); gap:8px;">
                                    @foreach($niveles as $key => $label)
                                        <label style="display:flex; align-items:center; gap:8px; padding:10px 12px; border:2px solid {{ old('nivel_jerarquico', 'todos') === $key ? 'var(--accent)' : 'var(--border)' }}; border-radius:8px; cursor:pointer; background:{{ old('nivel_jerarquico', 'todos') === $key ? 'rgba(59,130,246,.08)' : 'var(--surface)' }};">
                                            <input type="radio" name="nivel_jerarquico" value="{{ $key }}" @checked(old('nivel_jerarquico', 'todos') === $key) style="accent-color:var(--accent);">
                                            <span style="font-size:0.85rem; font-weight:500;">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="card" style="padding:24px;">
                        <h2 style="margin:0 0 14px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">
                            {{ $rolServicio === 'empresa' ? '2. Detalles de la solicitud' : '1. Cuentanos por que' }}
                        </h2>

                        <div style="margin-bottom:14px;">
                            <label class="form-label">{{ $rolServicio === 'empresa' ? 'Cuantas horas durara aproximadamente?' : 'Cuantas horas crees que durara?' }}</label>
                            <input type="number" name="horas_estimadas" value="{{ old('horas_estimadas') }}" min="0" max="500" placeholder="{{ $rolServicio === 'empresa' ? 'Ej. 8' : 'Ej. 4' }}"
                                   class="form-input" style="max-width:160px;">
                            <p style="margin:4px 0 0; font-size:11px; color:#94a3b8;">
                                {{ $rolServicio === 'empresa' ? 'Opcional. Nos ayuda a calcular la carga del responsable.' : 'Opcional. Nos ayuda a planear.' }}
                            </p>
                        </div>

                        <label class="form-label">{{ $rolServicio === 'empresa' ? 'Que necesitas exactamente? *' : 'Tu motivo u objetivo *' }}</label>
                        <textarea name="notas" rows="6" class="form-input" required maxlength="2000"
                                  placeholder="{{ $rolServicio === 'empresa'
                                      ? 'Describe el objetivo, fechas tentativas, numero de personas involucradas, ubicacion, etc.'
                                      : 'Ej. Quiero mejorar mi nivel de Excel para aspirar a vacantes administrativas.' }}"
                                  spellcheck="true"
                                  autocorrect="on"
                                  autocapitalize="sentences"
                                  lang="es">{{ old('notas') }}</textarea>
                        <p style="margin:6px 0 0; font-size:11px; color:#94a3b8;">
                            {{ $rolServicio === 'empresa'
                                ? 'Mientras mas detallado, mas rapido podremos asignar al responsable adecuado.'
                                : 'El administrador revisara tu solicitud y te asignara un responsable.' }}
                        </p>
                    </div>

                    <div style="display:flex; gap:10px; justify-content:flex-end;">
                        <a href="{{ $rutaListado }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Solicitar servicio</button>
                    </div>
                </form>
            @endif
        </div>
    @endif
</x-app-layout>
