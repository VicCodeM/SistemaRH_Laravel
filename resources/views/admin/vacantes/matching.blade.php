@php
    $nivelesEstudios = \App\Models\Vacante::nivelesEstudios();
@endphp

<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administración</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <a href="{{ route('admin.vacantes') }}">Solicitudes</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Candidatos</span>
        </nav>
        @php
            $cuposCubiertos = $vacante->cuposCubiertos();
            $cuposTotales   = $vacante->cupos ?? 1;
            $vacanteLlena   = $vacante->estaLlena();
        @endphp
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
            <div>
                <h1 class="page-title">{{ $vacante->titulo }}</h1>
                <p class="page-subtitle">
                    {{ $vacante->empresa?->nombre_empresa }}
                    &middot; {{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($vacante->nivel_jerarquico) }}
                    &middot;
                    <span class="badge {{ $vacanteLlena ? 'badge-green' : 'badge-blue' }}" style="font-size:0.78rem;">
                        {{ $cuposCubiertos }} de {{ $cuposTotales }} cupo(s)
                    </span>
                </p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                @if($vacante->estado === 'cerrada')
                    <form method="POST" action="{{ route('admin.vacantes.reabrir', $vacante) }}" style="display:inline;" onsubmit="return confirm('¿Reabrir esta vacante?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-success">↻ Reabrir</button>
                    </form>
                @elseif(in_array($vacante->estado, ['pendiente', 'activa']))
                    <form method="POST" action="{{ route('admin.vacantes.cerrar-manual', $vacante) }}" style="display:inline;" onsubmit="return confirm('¿Cerrar esta vacante ahora? Ya no recibirá más postulaciones.')">
                        @csrf
                        <input type="hidden" name="motivo" value="">
                        <button type="submit" class="btn btn-danger" title="Desactivar la vacante manualmente (aunque no esté llena)">Desactivar vacante</button>
                    </form>
                @endif
                <a href="{{ route('admin.vacantes.candidatos.csv', $vacante) }}" class="btn btn-secondary" style="font-size:13px;" title="Descargar lista de candidatos en Excel">⬇ Excel</a>
                <a href="{{ route('admin.vacantes.candidatos.pdf', $vacante) }}" target="_blank" class="btn btn-secondary" style="font-size:13px;" title="Imprimir o guardar como PDF">📄 PDF</a>
                <a href="{{ route('admin.vacantes.editar', $vacante) }}" class="btn btn-secondary">Editar solicitud</a>
                <a href="{{ route('admin.vacantes') }}" class="btn btn-secondary">&larr; Volver</a>
            </div>
        </div>
    </x-slot>

    @if($vacanteLlena)
        <div style="margin-bottom:18px; padding:14px 18px; background:#f0fdf4; border-left:4px solid #16a34a; border-radius:8px;">
            <strong style="color:#16a34a;">✓ Vacante cubierta.</strong>
            <span style="color:#475569; font-size:0.9rem;">
                Se cubrieron los {{ $cuposTotales }} cupo(s). Si necesitas más, edita la vacante y aumenta el número de personas, o retira a alguien para liberar un cupo.
            </span>
        </div>
    @endif

    @php
        $activos = $asignados->whereIn('estado', \App\Models\Postulacion::estadosActivos());
        $inactivos = $asignados->whereIn('estado', \App\Models\Postulacion::estadosInactivos());
        $secciones = [
            'aptos' => ['titulo' => 'Aptos', 'texto' => 'Cumplen los requisitos principales.', 'clase' => 'badge-green'],
            'dudosos' => ['titulo' => 'Dudosos', 'texto' => 'Tienen compatibilidad parcial y conviene revisar.', 'clase' => 'badge-yellow'],
            'no_aptos' => ['titulo' => 'No aptos', 'texto' => 'No cumplen mínimos. Solo con excepción.', 'clase' => 'badge-red'],
        ];
    @endphp

    @if($vacante->notas_internas)
        <div class="card" style="margin-bottom:14px; padding:14px 18px; background:#fffbeb; border-left:4px solid #f59e0b;">
            <div style="display:flex; align-items:center; gap:6px; font-size:11px; font-weight:700; color:#92400e; text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px;">
                🔒 Tus notas internas (la empresa no las ve)
            </div>
            <p style="margin:0; font-size:0.88rem; color:#78350f; white-space:pre-wrap;">{{ $vacante->notas_internas }}</p>
        </div>
    @endif

    <div class="card" style="margin-bottom:20px;">
        <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:16px; align-items:start;">
            <div>
                <div style="display:inline-flex; align-items:center; gap:8px; padding:4px 10px; border-radius:999px; background:rgba(59,130,246,.08); color:#60a5fa; font-size:12px; font-weight:600;">
                    Requisitos de la solicitud
                </div>
                <h2 style="margin:10px 0 8px; font-size:1rem; font-weight:700;">Compatibilidad automática</h2>
                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    <span class="badge badge-blue">Jerarquía: {{ $requisitos['nivel_jerarquico'] ?? 'Sin definir' }}</span>
                    <span class="badge badge-blue">Estudios: {{ $requisitos['nivel_estudios_minimo'] ?? 'Sin requisito' }}</span>
                    <span class="badge badge-blue">Área: {{ $requisitos['area_requerida'] ?: 'Sin definir' }}</span>
                    <span class="badge badge-blue">Experiencia: {{ $requisitos['experiencia_minima'] !== null && $requisitos['experiencia_minima'] !== '' ? $requisitos['experiencia_minima'].' año(s)' : 'Sin requisito' }}</span>
                </div>
            </div>
            <div style="padding:14px 16px; background:var(--surface-2); border:1px solid var(--border); border-radius:10px; font-size:0.84rem; color:#64748b; line-height:1.6;">
                El sistema clasifica candidatos en <strong>Aptos</strong>, <strong>Dudosos</strong> y <strong>No aptos</strong>.
                Si el perfil no cumple, el administrador puede forzar la asignación dejando un motivo.
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <form method="GET" action="{{ route('admin.vacantes.matching', $vacante) }}" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; align-items:end;">
            <div style="min-width:0;">
                <label class="form-label">Estudios</label>
                <select name="estudios" class="form-input">
                    <option value="">Todos</option>
                    @foreach($nivelesEstudios as $key => $label)
                        <option value="{{ $key }}" {{ request('estudios') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:0;">
                <label class="form-label">Experiencia mínima</label>
                <input type="number" name="experiencia_min" min="0" value="{{ request('experiencia_min') }}" placeholder="Años" class="form-input">
            </div>
            <div style="min-width:0;">
                <label class="form-label">Aspiración</label>
                <input type="text" name="aspiracion" value="{{ request('aspiracion') }}" placeholder="Puesto deseado..." class="form-input" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                @if(request()->hasAny(['estudios', 'experiencia_min', 'aspiracion']))
                    <a href="{{ route('admin.vacantes.matching', $vacante) }}" class="btn btn-secondary">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; gap:10px;">
            <h2 style="font-weight:700; font-size:1rem; margin:0;">
                Candidatos en proceso
                <span style="font-size:0.82rem; font-weight:400; color:#64748b; margin-left:6px;">{{ $activos->count() }} activos</span>
            </h2>
        </div>

        @if($activos->isEmpty())
            <div style="text-align:center; padding:28px; color:#64748b; background:var(--surface-2); border-radius:10px; border:1px dashed var(--border);">
                Aún no hay candidatos en proceso para esta solicitud.
            </div>
        @else
            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px;">
                @foreach(['postulado', 'entrevista', 'seleccionado'] as $estado)
                    @php
                        $estadoCandidatos = $activos->where('estado', $estado);
                        $estadoBadge = \App\Models\Postulacion::estadoBadgeClass($estado);
                    @endphp
                    <div style="background:var(--surface-2); border-radius:10px; padding:14px; border:1px solid var(--border); min-height:120px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:12px;">
                            <span class="badge {{ $estadoBadge }}" style="font-size:0.72rem;">{{ \App\Models\Postulacion::estadoLabel($estado) }}</span>
                            <span class="badge {{ $estadoBadge }}" style="font-size:10px;">{{ $estadoCandidatos->count() }}</span>
                        </div>

                        @forelse($estadoCandidatos as $postulacion)
                            @php $c = $postulacion->candidato; @endphp
                            <div style="background:var(--surface); border:1px solid var(--border); border-radius:9px; padding:11px; margin-bottom:8px;">
                                <div style="display:flex; gap:8px; align-items:start;">
                                    <x-avatar :src="$c?->usuario?->avatar_url" :nombre="($c?->nombre ?? '') . ' ' . ($c?->apellido_paterno ?? '')" :tamano="32" />
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-weight:600; font-size:0.88rem;">{{ $c?->nombre }} {{ $c?->apellido_paterno }}</div>
                                        <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">{{ $c?->usuario?->email }}</div>
                                        <div style="font-size:0.74rem; color:#94a3b8; margin-top:2px;">Aspiración: {{ $c?->puesto_deseado ?: 'Sin puesto indicado' }}</div>
                                    </div>
                                </div>

                                <div style="display:flex; gap:6px; margin-top:10px; flex-wrap:wrap;">
                                    @if($estado === 'postulado')
                                        <form method="POST" action="{{ route('admin.postulaciones.mover', $postulacion) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="entrevista">
                                            <button type="submit" class="btn btn-primary" style="padding:4px 10px; font-size:0.75rem;">Ya entrevistado</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.postulaciones.mover', $postulacion) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="rechazado">
                                            <button type="submit" class="btn btn-secondary" style="padding:4px 10px; font-size:0.75rem;">No continúa</button>
                                        </form>
                                    @elseif($estado === 'entrevista')
                                        <form method="POST" action="{{ route('admin.postulaciones.mover', $postulacion) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="seleccionado">
                                            <button type="submit" class="btn btn-primary" style="padding:4px 10px; font-size:0.75rem;">Seleccionar</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.postulaciones.mover', $postulacion) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="rechazado">
                                            <button type="submit" class="btn btn-secondary" style="padding:4px 10px; font-size:0.75rem;">No continúa</button>
                                        </form>
                                    @elseif($estado === 'seleccionado')
                                        <span class="badge badge-green">Colocado</span>
                                    @endif

                                    <form method="POST" action="{{ route('admin.postulaciones.mover', $postulacion) }}" onsubmit="return confirm('¿Retirar a este candidato?')">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="estado" value="retirado">
                                        <button type="submit" class="btn btn-secondary" style="padding:4px 10px; font-size:0.72rem;">Retirar</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div style="color:#64748b; font-size:0.82rem;">Sin candidatos en este estado.</div>
                        @endforelse
                    </div>
                @endforeach
            </div>
        @endif

        @if($inactivos->isNotEmpty())
            <details style="margin-top:14px;">
                <summary style="cursor:pointer; font-size:0.82rem; color:#64748b; user-select:none;">
                    Ver {{ $inactivos->count() }} candidato(s) que no continuaron
                </summary>
                <div style="margin-top:10px; display:flex; flex-wrap:wrap; gap:8px;">
                    @foreach($inactivos as $postulacion)
                        @php $c = $postulacion->candidato; @endphp
                        <div style="background:var(--surface-2); border:1px solid var(--border); border-radius:7px; padding:9px 12px; opacity:0.8; font-size:0.82rem;">
                            <span style="font-weight:500;">{{ $c?->nombre }} {{ $c?->apellido_paterno }}</span>
                            <span class="badge {{ \App\Models\Postulacion::estadoBadgeClass($postulacion->estado) }}" style="margin-left:8px; font-size:0.72rem;">
                                {{ \App\Models\Postulacion::estadoLabel($postulacion->estado) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </details>
        @endif
    </div>

    @foreach($secciones as $clave => $config)
        <div class="card" style="margin-top:20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:14px;">
                <div>
                    <div class="badge {{ $config['clase'] }}" style="margin-bottom:8px;">{{ $config['titulo'] }}</div>
                    <h2 style="margin:0; font-size:1rem; font-weight:700;">{{ $config['texto'] }}</h2>
                </div>
                <div style="font-size:0.82rem; color:#64748b;">
                    {{ $grupos[$clave]->count() }} candidato(s)
                </div>
            </div>

            @if($grupos[$clave]->isEmpty())
                <div style="padding:18px; background:var(--surface-2); border:1px dashed var(--border); border-radius:10px; color:#64748b; font-size:0.85rem;">
                    No hay candidatos en esta categoría.
                </div>
            @else
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:12px;">
                    @foreach($grupos[$clave] as $item)
                        @php
                            $candidato = $item['candidato'];
                            $compatibilidad = $item['compatibilidad'];
                        @endphp
                        <div style="border:1px solid var(--border); border-radius:12px; padding:14px; background:var(--surface);">
                            <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start;">
                                <div style="display:flex; gap:10px; align-items:start; flex:1; min-width:0;">
                                    <x-avatar :src="$candidato->usuario?->avatar_url" :nombre="$candidato->nombre . ' ' . ($candidato->apellido_paterno ?? '')" :tamano="40" />
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-weight:700; font-size:0.92rem;">{{ $candidato->nombre }} {{ $candidato->apellido_paterno }}</div>
                                        <div style="font-size:0.8rem; color:#64748b; margin-top:2px;">{{ $candidato->usuario?->email }}</div>
                                        <div style="font-size:0.78rem; color:#94a3b8; margin-top:2px;">Aspiración: {{ $candidato->puesto_deseado ?: 'Sin puesto indicado' }}</div>
                                    </div>
                                </div>
                                <span class="badge {{ $config['clase'] }}">{{ $compatibilidad['puntaje'] }}/100</span>
                            </div>

                            <div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:12px;">
                                <span class="badge badge-blue">Estudios: {{ $compatibilidad['nivel_candidato_label'] }}</span>
                                <span class="badge badge-blue">Experiencia: {{ $compatibilidad['experiencia_candidato'] }} año(s)</span>
                                @if($compatibilidad['areas_requeridas'])
                                    <span class="badge badge-blue">Área: {{ implode(', ', $compatibilidad['areas_requeridas']) }}</span>
                                @endif
                            </div>

                            <div style="margin-top:12px; font-size:0.8rem; color:#64748b; line-height:1.5;">
                                @foreach($compatibilidad['detalles'] as $detalle)
                                    <div>• {{ $detalle }}</div>
                                @endforeach
                                <div style="margin-top:8px; color:#475569;">{{ $compatibilidad['resumen'] }}</div>
                            </div>

                            @if($vacanteLlena)
                                <div style="margin-top:14px; padding:8px 12px; background:var(--surface-2); border:1px dashed var(--border); border-radius:8px; font-size:0.8rem; color:#94a3b8; text-align:center;">
                                    🔒 Cupos cubiertos. Retira a alguien antes de asignar otro.
                                </div>
                            @else
                                <form method="POST" action="{{ route('admin.vacantes.asignar', $vacante) }}" style="margin-top:14px; display:flex; gap:8px; flex-wrap:wrap; align-items:end;">
                                    @csrf
                                    <input type="hidden" name="candidato_id" value="{{ $candidato->id }}">
                                    @if($clave === 'no_aptos')
                                        <input type="hidden" name="forzar" value="1">
                                        <div style="flex:1; min-width:200px;">
                                            <label class="form-label" style="font-size:0.75rem; margin-bottom:4px;">Motivo de excepción</label>
                                            <input type="text" name="motivo_asignacion" class="form-input" maxlength="1000" placeholder="Ej. el cliente lo pidió" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Asignar con excepción</button>
                                    @else
                                        <button type="submit" class="btn btn-primary">+ Agregar</button>
                                    @endif
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</x-app-layout>
