<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Admin</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('admin.vacantes') }}">Solicitudes</a>
            <span class="breadcrumb-sep">›</span>
            <span>Candidatos</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h1 class="page-title">{{ $vacante->titulo }}</h1>
                <p class="page-subtitle">
                    {{ $vacante->empresa?->nombre_empresa }}
                    · {{ ucfirst(str_replace('_',' ', $vacante->nivel_jerarquico)) }}
                    @if($vacante->tipo_servicio) · <span style="color:#60a5fa;">{{ \App\Models\Vacante::tiposServicio()[$vacante->tipo_servicio] ?? $vacante->tipo_servicio }}</span> @endif
                </p>
            </div>
            <div style="display:flex; gap:8px;">
                <a href="{{ route('admin.vacantes.editar', $vacante) }}" class="btn btn-secondary">Editar solicitud</a>
                <a href="{{ route('admin.vacantes') }}" class="btn btn-secondary">← Volver</a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    @php
        $etapaColors = ['postulado'=>'#3b82f6','entrevista'=>'#f59e0b','seleccionado'=>'#22c55e','rechazado'=>'#ef4444','retirado'=>'#64748b'];
        $etapaLabels = ['postulado'=>'En revisión','entrevista'=>'En entrevista','seleccionado'=>'Seleccionado','rechazado'=>'Rechazado','retirado'=>'Retirado'];
        $activos = $asignados->whereIn('estado', ['postulado','entrevista','seleccionado']);
        $inactivos = $asignados->whereIn('estado', ['rechazado','retirado']);
    @endphp

    {{-- SECCIÓN PRINCIPAL: candidatos en proceso --}}
    <div class="card" style="margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <h2 style="font-weight:700; font-size:1rem; margin:0;">
                Candidatos en proceso
                <span style="font-size:0.82rem; font-weight:400; color:#64748b; margin-left:6px;">{{ $activos->count() }} activos</span>
            </h2>
        </div>

        @if($activos->isEmpty())
            <div style="text-align:center; padding:32px; color:#64748b; background:var(--surface-2); border-radius:8px; border:1px dashed var(--border);">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:36px;height:36px;margin:0 auto 10px;display:block;color:#334155;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                Aún no hay candidatos en proceso para esta solicitud.<br>
                <span style="font-size:0.82rem; color:#475569;">Asigna candidatos desde la sección de abajo.</span>
            </div>
        @else
            {{-- Pipeline por etapa --}}
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
                @foreach(['postulado'=>'En revisión','entrevista'=>'En entrevista','seleccionado'=>'Seleccionado'] as $etapa => $etapaLabel)
                    <div style="background:var(--surface-2); border-radius:8px; padding:12px; min-height:80px;">
                        <div style="font-size:0.75rem; font-weight:600; color:{{ $etapaColors[$etapa] }}; text-transform:uppercase; letter-spacing:.05em; margin-bottom:10px;">
                            {{ $etapaLabel }}
                            <span style="background:{{ $etapaColors[$etapa] }}22; border-radius:20px; padding:1px 7px; font-size:10px; margin-left:4px;">
                                {{ $activos->where('estado',$etapa)->count() }}
                            </span>
                        </div>
                        @foreach($activos->where('estado',$etapa) as $postulacion)
                            @php $c = $postulacion->candidato; @endphp
                            <div style="background:var(--surface); border:1px solid var(--border); border-radius:7px; padding:11px; margin-bottom:7px;">
                                <div style="font-weight:600; font-size:0.85rem;">{{ $c?->nombre }} {{ $c?->apellido_paterno }}</div>
                                <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">{{ $c?->usuario?->email }}</div>
                                @if($c?->puesto_deseado)
                                    <div style="font-size:0.73rem; color:#94a3b8; margin-top:1px;">{{ $c->puesto_deseado }}</div>
                                @endif

                                {{-- Controles según etapa --}}
                                <div style="display:flex; gap:5px; margin-top:10px; flex-wrap:wrap;">
                                    @if($etapa === 'postulado')
                                        <form method="POST" action="{{ route('admin.postulaciones.mover', $postulacion) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="estado" value="entrevista">
                                            <button type="submit" style="padding:3px 9px; background:#f59e0b22; color:#f59e0b; border:1px solid #f59e0b55; border-radius:5px; cursor:pointer; font-size:0.72rem; font-weight:600;">
                                                → Entrevista
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.postulaciones.mover', $postulacion) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="estado" value="rechazado">
                                            <button type="submit" style="padding:3px 8px; background:transparent; color:#ef4444; border:1px solid #ef444433; border-radius:5px; cursor:pointer; font-size:0.72rem;">
                                                No apto
                                            </button>
                                        </form>
                                    @elseif($etapa === 'entrevista')
                                        <form method="POST" action="{{ route('admin.postulaciones.mover', $postulacion) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="estado" value="seleccionado">
                                            <button type="submit" style="padding:3px 9px; background:#22c55e22; color:#22c55e; border:1px solid #22c55e55; border-radius:5px; cursor:pointer; font-size:0.72rem; font-weight:600;">
                                                ✓ Seleccionar
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.postulaciones.mover', $postulacion) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="estado" value="rechazado">
                                            <button type="submit" style="padding:3px 8px; background:transparent; color:#ef4444; border:1px solid #ef444433; border-radius:5px; cursor:pointer; font-size:0.72rem;">
                                                No pasó
                                            </button>
                                        </form>
                                    @elseif($etapa === 'seleccionado')
                                        <span style="font-size:0.72rem; color:#22c55e;">✓ Colocado</span>
                                    @endif
                                    <form method="POST" action="{{ route('admin.postulaciones.mover', $postulacion) }}"
                                          onsubmit="return confirm('¿Retirar a este candidato? Volverá al banco disponible.')"
                                          style="margin-left:auto;">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="estado" value="retirado">
                                        <button type="submit" style="padding:3px 8px; background:transparent; color:#94a3b8; border:1px solid var(--border); border-radius:5px; cursor:pointer; font-size:0.7rem;">
                                            Retirar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Retirados/rechazados colapsados --}}
        @if($inactivos->isNotEmpty())
            <details style="margin-top:14px;">
                <summary style="cursor:pointer; font-size:0.82rem; color:#64748b; user-select:none;">
                    Ver {{ $inactivos->count() }} candidato(s) que no continuaron
                </summary>
                <div style="margin-top:10px; display:flex; flex-wrap:wrap; gap:8px;">
                    @foreach($inactivos as $postulacion)
                        @php $c = $postulacion->candidato; @endphp
                        <div style="background:var(--surface-2); border:1px solid var(--border); border-radius:7px; padding:9px 12px; opacity:0.7; font-size:0.82rem;">
                            <span style="font-weight:500;">{{ $c?->nombre }} {{ $c?->apellido_paterno }}</span>
                            <span style="margin-left:8px; color:{{ $etapaColors[$postulacion->estado] ?? '#64748b' }}; font-size:0.75rem;">
                                {{ $etapaLabels[$postulacion->estado] ?? $postulacion->estado }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </details>
        @endif
    </div>

    {{-- SECCIÓN SECUNDARIA: añadir candidatos --}}
    <div class="card">
        <h2 style="font-weight:700; font-size:1rem; margin:0 0 6px;">Agregar candidatos a esta solicitud</h2>
        <p style="font-size:0.82rem; color:#64748b; margin:0 0 16px;">
            Candidatos aprobados disponibles · ordenados por compatibilidad con el puesto.
        </p>

        @if($candidatos->isEmpty())
            <div style="text-align:center; padding:24px; color:#64748b; font-size:0.85rem;">
                No hay más candidatos aprobados disponibles para agregar.
            </div>
        @else
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:10px;">
                @foreach($candidatos as $candidato)
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; padding:12px 14px; background:var(--surface-2); border:1px solid var(--border); border-radius:8px;">
                        <div>
                            <div style="font-weight:600; font-size:0.88rem;">{{ $candidato->nombre }} {{ $candidato->apellido_paterno }}</div>
                            @if($candidato->puesto_deseado)
                                <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">{{ $candidato->puesto_deseado }}</div>
                            @endif
                            @if($candidato->experiencia_anios)
                                <div style="font-size:0.73rem; color:#475569;">{{ $candidato->experiencia_anios }} años exp.</div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.vacantes.asignar', $vacante) }}" style="flex-shrink:0;">
                            @csrf
                            <input type="hidden" name="candidato_id" value="{{ $candidato->id }}">
                            <button type="submit" class="btn btn-primary" style="padding:5px 12px; font-size:0.78rem;">+ Agregar</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
