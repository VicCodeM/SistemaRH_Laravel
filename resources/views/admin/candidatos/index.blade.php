@php
    $nivelesEstudios = \App\Models\Vacante::nivelesEstudios();
@endphp

<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administracion</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Candidatos</span>
        </nav>
        <h1 class="page-title">Aprobaciones de perfil - Candidatos</h1>
        <p class="page-subtitle">{{ $candidatos->total() }} candidato(s) registrado(s). Aqui apruebas o rechazas el perfil/CV que envio cada candidato. Esto NO es asignacion de servicios.</p>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--success-light); color:var(--success); border-radius:8px; border-left:4px solid var(--success);">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--danger-light); color:var(--danger); border-radius:8px; border-left:4px solid var(--danger);">{{ session('error') }}</div>
    @endif

    <div class="card fade-in" style="margin-bottom:20px;">
        <form method="GET" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; align-items:end;">
            <div style="min-width:0;">
                <label style="font-size:12px; color:var(--text-muted); display:block; margin-bottom:4px;">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o CURP..." style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
            </div>
            <div style="min-width:0;">
                <label style="font-size:12px; color:var(--text-muted); display:block; margin-bottom:4px;">Estado</label>
                <select name="estado" style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                    <option value="">Todos</option>
                    @foreach(\App\Models\Candidato::solicitudEstados() as $key => $label)
                        <option value="{{ $key }}" {{ request('estado') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:0;">
                <label style="font-size:12px; color:var(--text-muted); display:block; margin-bottom:4px;">Estudios</label>
                <select name="estudios" style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                    <option value="">Todos</option>
                    @foreach($nivelesEstudios as $key => $label)
                        <option value="{{ $key }}" {{ request('estudios') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:0;">
                <label style="font-size:12px; color:var(--text-muted); display:block; margin-bottom:4px;">Experiencia minima</label>
                <input type="number" name="experiencia_min" min="0" value="{{ request('experiencia_min') }}" placeholder="Anios" style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
            </div>
            <div style="min-width:0;">
                <label style="font-size:12px; color:var(--text-muted); display:block; margin-bottom:4px;">Aspiracion</label>
                <input type="text" name="aspiracion" value="{{ request('aspiracion') }}" placeholder="Puesto deseado..." style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
            </div>
            <div class="toolbar-wrap">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                @if(request()->hasAny(['buscar', 'estado', 'estudios', 'experiencia_min', 'aspiracion']))
                    <a href="{{ route('admin.candidatos') }}" class="btn btn-secondary">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card fade-in">
        @if($candidatos->isEmpty())
            <p class="text-muted text-sm" style="text-align:center; padding:40px 0;">No hay candidatos que coincidan.</p>
        @else
            <div class="desktop-only table-scroll">
                <table style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border);">
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Candidato</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Contacto</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">CURP</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Solicitud</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Enviada</th>
                            <th style="text-align:right; padding:10px 12px; color:var(--text-muted); font-weight:500;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($candidatos as $candidato)
                            @php
                                $colors = ['borrador' => 'var(--text-muted)', 'enviada' => 'var(--warning)', 'aprobada' => 'var(--success)', 'rechazada' => 'var(--danger)', 'en_revision' => 'var(--accent)'];
                                $bgs = ['borrador' => 'var(--surface-2)', 'enviada' => 'var(--warning-light)', 'aprobada' => 'var(--success-light)', 'rechazada' => 'var(--danger-light)', 'en_revision' => 'var(--accent-light)'];
                                $estado = $candidato->solicitud_estado ?? 'borrador';
                                $progreso = $candidato->solicitudProgreso();
                            @endphp
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:12px;">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <x-avatar :src="$candidato->usuario?->avatar_url" :nombre="$candidato->nombre . ' ' . ($candidato->apellido_paterno ?? '')" :tamano="36" />
                                        <div>
                                            <p style="font-weight:500; margin:0;">{{ $candidato->nombre }} {{ $candidato->apellido_paterno }} {{ $candidato->apellido_materno }}</p>
                                            <p style="font-size:12px; color:var(--text-muted); margin:0;">Aspiracion: {{ $candidato->puesto_deseado ?? 'Sin puesto indicado' }}</p>
                                            <p style="font-size:12px; color:var(--text-muted); margin:0;">Estudios: {{ \App\Models\Vacante::nivelEstudiosLabel($candidato->escolaridad) }} · Experiencia: {{ (int) ($candidato->experiencia_anios ?? 0) }} anio(s)</p>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:12px;">
                                    <p style="margin:0; font-size:13px;">{{ $candidato->usuario?->email }}</p>
                                    <p style="margin:0; font-size:12px; color:var(--text-muted);">{{ $candidato->celular ?? $candidato->telefono ?? '—' }}</p>
                                </td>
                                <td style="padding:12px; color:var(--text-muted); font-size:13px;">{{ $candidato->curp ?? '—' }}</td>
                                <td style="padding:12px;">
                                    <span style="padding:3px 10px; border-radius:20px; font-size:12px; font-weight:500; background:{{ $bgs[$estado] ?? 'var(--surface-2)' }}; color:{{ $colors[$estado] ?? 'var(--text-muted)' }};">
                                        {{ \App\Models\Candidato::solicitudEstadoLabel($estado) }}
                                    </span>
                                    <div style="margin-top:6px; font-size:12px; color:var(--text-muted);">Avance: {{ $progreso }}%</div>
                                </td>
                                <td style="padding:12px; color:var(--text-muted); font-size:13px;">{{ $candidato->solicitud_enviada_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td style="padding:12px; text-align:right;">
                                    <div class="toolbar-wrap" style="justify-content:flex-end;">
                                        <button type="button" onclick="rhModal('{{ route('admin.candidatos.modal', $candidato) }}')" title="Ver solicitud" class="btn btn-ghost" style="width:30px; height:30px; padding:0;">
                                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </button>
                                        <a href="{{ route('admin.candidatos.solicitud.pdf', $candidato) }}" target="_blank" title="Descargar solicitud en PDF" class="btn btn-ghost" style="width:30px; height:30px; padding:0; display:inline-flex; align-items:center; justify-content:center;">PDF</a>
                                        @if($candidato->solicitud_estado === 'enviada')
                                            <form method="POST" action="{{ route('admin.candidatos.aprobar', $candidato) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-success btn-sm">Aprobar</button>
                                            </form>
                                            <button type="button" onclick="rhModal('{{ route('admin.candidatos.accion.modal', [$candidato, 'rechazar']) }}')" class="btn btn-danger btn-sm">Rechazar</button>
                                        @elseif($candidato->solicitud_estado === 'rechazada')
                                            <form method="POST" action="{{ route('admin.candidatos.aprobar', $candidato) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-success btn-sm">Reactivar</button>
                                            </form>
                                        @endif
                                        <button type="button" onclick="rhModal('{{ route('admin.candidatos.accion.modal', [$candidato, 'eliminar']) }}')" class="btn btn-danger" style="width:30px; height:30px; padding:0;" title="Eliminar">
                                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px;"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397M4.772 5.79c.342-.052.682-.107 1.022-.166m1.022.165l.346 9"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mobile-only">
                <div class="candidate-mobile-list">
                    @foreach($candidatos as $candidato)
                        @php
                            $colors = ['borrador' => 'var(--text-muted)', 'enviada' => 'var(--warning)', 'aprobada' => 'var(--success)', 'rechazada' => 'var(--danger)', 'en_revision' => 'var(--accent)'];
                            $bgs = ['borrador' => 'var(--surface-2)', 'enviada' => 'var(--warning-light)', 'aprobada' => 'var(--success-light)', 'rechazada' => 'var(--danger-light)', 'en_revision' => 'var(--accent-light)'];
                            $estado = $candidato->solicitud_estado ?? 'borrador';
                            $progreso = $candidato->solicitudProgreso();
                        @endphp
                        <article class="candidate-mobile-card">
                            <div class="candidate-inline-meta">
                                <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                                    <x-avatar :src="$candidato->usuario?->avatar_url" :nombre="$candidato->nombre . ' ' . ($candidato->apellido_paterno ?? '')" :tamano="40" />
                                    <div style="min-width:0;">
                                        <h3 class="candidate-mobile-card-title">{{ $candidato->nombre }} {{ $candidato->apellido_paterno }} {{ $candidato->apellido_materno }}</h3>
                                        <p class="candidate-mobile-card-subtitle">{{ $candidato->puesto_deseado ?: 'Sin puesto indicado' }}</p>
                                    </div>
                                </div>
                                <span style="padding:3px 10px; border-radius:20px; font-size:12px; font-weight:500; background:{{ $bgs[$estado] ?? 'var(--surface-2)' }}; color:{{ $colors[$estado] ?? 'var(--text-muted)' }};">
                                    {{ \App\Models\Candidato::solicitudEstadoLabel($estado) }}
                                </span>
                            </div>

                            <div class="candidate-mobile-meta">
                                <div>
                                    <p class="candidate-mobile-meta-label">Contacto</p>
                                    <p class="candidate-mobile-meta-value">{{ $candidato->usuario?->email ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">Telefono</p>
                                    <p class="candidate-mobile-meta-value">{{ $candidato->celular ?? $candidato->telefono ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">CURP</p>
                                    <p class="candidate-mobile-meta-value">{{ $candidato->curp ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">Avance</p>
                                    <p class="candidate-mobile-meta-value">{{ $progreso }}%</p>
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">Enviada</p>
                                    <p class="candidate-mobile-meta-value">{{ $candidato->solicitud_enviada_at?->format('d/m/Y H:i') ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="candidate-actions" style="margin-top:14px;">
                                <button type="button" onclick="rhModal('{{ route('admin.candidatos.modal', $candidato) }}')" class="btn btn-secondary btn-sm">Ver</button>
                                <a href="{{ route('admin.candidatos.solicitud.pdf', $candidato) }}" target="_blank" class="btn btn-secondary btn-sm">PDF</a>
                                @if($candidato->solicitud_estado === 'enviada')
                                    <form method="POST" action="{{ route('admin.candidatos.aprobar', $candidato) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm">Aprobar</button>
                                    </form>
                                    <button type="button" onclick="rhModal('{{ route('admin.candidatos.accion.modal', [$candidato, 'rechazar']) }}')" class="btn btn-danger btn-sm">Rechazar</button>
                                @elseif($candidato->solicitud_estado === 'rechazada')
                                    <form method="POST" action="{{ route('admin.candidatos.aprobar', $candidato) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm">Reactivar</button>
                                    </form>
                                @endif
                                <button type="button" onclick="rhModal('{{ route('admin.candidatos.accion.modal', [$candidato, 'eliminar']) }}')" class="btn btn-danger btn-sm">Eliminar</button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <div style="margin-top:16px;">
                {{ $candidatos->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
