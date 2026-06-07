<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('candidato.dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <a href="{{ route('candidato.servicios.index') }}">Servicios disponibles</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Detalle del servicio</span>
        </nav>
        <h1 class="page-title">
            {{ $servicioSeleccionado ? $servicioSeleccionado->nombre : 'Detalle del servicio' }}
        </h1>
        <p class="page-subtitle">
            {{ $servicioSeleccionado
                ? 'Revisa la presentacion visual del servicio y continua con tu solicitud.'
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
                        </div>
                        <h2 style="margin:0 0 6px; font-size:1.25rem; font-weight:800;">{{ $servicioSeleccionado->nombre }}</h2>
                        <p style="margin:0; color:#64748b; font-size:0.95rem; white-space:pre-wrap;">{{ $servicioSeleccionado->descripcion ?: 'Sin descripcion detallada aun.' }}</p>
                    </div>

                    <div style="flex:1; min-width:280px;">
                        <div style="background:var(--surface-2); border:1px solid var(--border); border-radius:14px; padding:14px 16px;">
                            <p style="margin:0 0 6px; font-size:0.72rem; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8;">Que veras aqui</p>
                            <p style="margin:0; font-size:0.92rem; color:var(--text);">
                                Aqui veras las diapositivas en imagen que preparo el administrador para este servicio.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @include('partials.catalogo-servicio-recursos', [
                'catalogo' => $servicioSeleccionado,
                'puedeGestionar' => false,
            ])

            <form method="POST" action="{{ route('candidato.servicios.guardar') }}" style="display:flex; flex-direction:column; gap:18px;">
                @csrf
                <input type="hidden" name="servicio_id" value="{{ $servicioSeleccionado->id }}">

                <div class="card" style="padding:24px;">
                    <h2 style="margin:0 0 14px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">1. Cuentanos por que</h2>

                    <div style="margin-bottom:14px;">
                        <label class="form-label">Cuantas horas crees que durara?</label>
                        <input type="number" name="horas_estimadas" value="{{ old('horas_estimadas') }}" min="0" max="500" placeholder="Ej. 4"
                               class="form-input" style="max-width:160px;">
                        <p style="margin:4px 0 0; font-size:11px; color:#94a3b8;">Opcional. Nos ayuda a planear.</p>
                    </div>

                    <label class="form-label">Tu motivo u objetivo *</label>
                    <textarea name="notas" rows="6" class="form-input" required maxlength="2000"
                              placeholder="Ej. Quiero mejorar mi nivel de Excel para aspirar a vacantes administrativas."
                              spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es">{{ old('notas') }}</textarea>
                    <p style="margin:6px 0 0; font-size:11px; color:#94a3b8;">El administrador revisara tu solicitud y te asignara un responsable.</p>
                </div>

                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <a href="{{ route('candidato.servicios.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Solicitar servicio</button>
                </div>
            </form>
        </div>
    @endif
</x-app-layout>
