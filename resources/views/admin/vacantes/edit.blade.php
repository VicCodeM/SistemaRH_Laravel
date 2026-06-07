<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administración</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <a href="{{ route('admin.vacantes') }}">Solicitudes</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Editar</span>
        </nav>
        <h1 class="page-title">Editar solicitud</h1>
        <p class="page-subtitle" style="color:#94a3b8;">{{ $vacante->empresa?->nombre_empresa ?? '' }}</p>
    </x-slot>

    @php
        $nivelActual = \App\Models\CatalogoServicio::normalizarNivelJerarquico(old('nivel_jerarquico', $vacante->nivel_jerarquico));
        $nivelEstudiosActual = old('nivel_estudios_minimo', $vacante->nivel_estudios_minimo);
    @endphp

    <div style="display:grid; grid-template-columns:1fr 280px; gap:20px; align-items:start; max-width:980px;">
        <div class="card">
            <form method="POST" action="{{ route('admin.vacantes.actualizar', $vacante) }}">
                @csrf
                @method('PUT')

                <div style="padding:10px 14px; background:var(--surface-2); border-radius:8px; border:1px solid var(--border); margin-bottom:22px; font-size:0.83rem; color:#94a3b8;">
                    Empresa: <span style="color:var(--text); font-weight:600;">{{ $vacante->empresa?->nombre_empresa ?? '—' }}</span>
                    &nbsp;&middot;&nbsp; Enviada: <span style="color:var(--text);">{{ $vacante->fecha_publicacion?->format('d/m/Y') ?? '—' }}</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="titulo">Título / Puesto <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="titulo" name="titulo" class="form-input @error('titulo') is-invalid @enderror" value="{{ old('titulo', $vacante->titulo) }}" maxlength="200" spellcheck="true" autocapitalize="sentences">
                    @error('titulo')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label" for="cupos">¿Cuántas personas? *</label>
                    <input type="number" id="cupos" name="cupos" value="{{ old('cupos', $vacante->cupos ?? 1) }}" required min="1" max="100" class="form-input" style="max-width:160px;">
                    @if($vacante->cuposCubiertos() > 0)
                        <p style="font-size:11px; color:#f59e0b; margin-top:4px;">{{ $vacante->cuposCubiertos() }} cupo(s) ya cubiertos. No puede ser menor.</p>
                    @endif
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label" for="nivel_jerarquico">Nivel jerárquico <span style="color:var(--danger)">*</span></label>
                    <select id="nivel_jerarquico" name="nivel_jerarquico" class="form-input @error('nivel_jerarquico') is-invalid @enderror">
                        <option value="">— Selecciona —</option>
                        @foreach($niveles as $key => $label)
                            <option value="{{ $key }}" {{ $nivelActual === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('nivel_jerarquico')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="card" style="margin-top:18px; padding:18px; background:var(--surface-2);">
                    <h2 style="margin:0 0 6px; font-size:0.98rem;">Requisitos de compatibilidad</h2>
                    <p style="margin:0 0 14px; font-size:0.84rem; color:#64748b;">Este bloque ayuda a ordenar candidatos de forma automática y también a justificar excepciones.</p>

                    <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:14px;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="nivel_estudios_minimo">Nivel mínimo de estudios</label>
                            <select id="nivel_estudios_minimo" name="nivel_estudios_minimo" class="form-input @error('nivel_estudios_minimo') is-invalid @enderror">
                                <option value="">Sin requisito</option>
                                @foreach($estudios as $key => $label)
                                    <option value="{{ $key }}" {{ $nivelEstudiosActual === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('nivel_estudios_minimo')<div class="form-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="area_requerida">Área o carrera requerida</label>
                            <select id="area_requerida" name="area_requerida" class="form-input @error('area_requerida') is-invalid @enderror">
                                <option value="">Sin requisito</option>
                                @foreach($areas as $key => $label)
                                    <option value="{{ $label }}" {{ old('area_requerida', $vacante->area_requerida) === $label ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('area_requerida')<div class="form-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="experiencia_minima">Experiencia mínima (años)</label>
                            <input type="number" id="experiencia_minima" name="experiencia_minima" class="form-input @error('experiencia_minima') is-invalid @enderror"
                                   value="{{ old('experiencia_minima', $vacante->experiencia_minima) }}" min="0" step="1" placeholder="0">
                            @error('experiencia_minima')<div class="form-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="tipo_contrato">Tipo de contrato</label>
                            <select id="tipo_contrato" name="tipo_contrato" class="form-input">
                                <option value="">Por definir</option>
                                @foreach($contratos as $key => $label)
                                    <option value="{{ $label }}" {{ old('tipo_contrato', $vacante->tipo_contrato) === $label ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-top:18px; padding:18px; background:var(--surface-2);">
                    <h2 style="margin:0 0 6px; font-size:0.98rem;">Compensación y prestaciones</h2>
                    <p style="margin:0 0 14px; font-size:0.84rem; color:#64748b;">Agrega el sueldo y beneficios que ofreces para que empresa y candidatos vean la oferta completa.</p>

                    <div style="display:grid; grid-template-columns:1fr 1fr 2fr; gap:14px;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="salario_min">Sueldo desde</label>
                            <input type="number" id="salario_min" name="salario_min" class="form-input @error('salario_min') is-invalid @enderror"
                                   value="{{ old('salario_min', $vacante->salario_min) }}" min="0" step="0.01" placeholder="0.00">
                            @error('salario_min')<div class="form-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="salario_max">Sueldo hasta</label>
                            <input type="number" id="salario_max" name="salario_max" class="form-input @error('salario_max') is-invalid @enderror"
                                   value="{{ old('salario_max', $vacante->salario_max) }}" min="0" step="0.01" placeholder="0.00">
                            @error('salario_max')<div class="form-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="ubicacion">Lugar de trabajo</label>
                            <input type="text" id="ubicacion" name="ubicacion" class="form-input @error('ubicacion') is-invalid @enderror"
                                   value="{{ old('ubicacion', $vacante->ubicacion) }}" maxlength="200" placeholder="Ciudad, sucursal o remoto" spellcheck="true" autocapitalize="sentences">
                            @error('ubicacion')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div style="display:grid; gap:14px; margin-top:14px;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="ingresos_ofrecidos">Ingresos que se ofrecen</label>
                            <textarea id="ingresos_ofrecidos" name="ingresos_ofrecidos" class="form-input @error('ingresos_ofrecidos') is-invalid @enderror" rows="3" maxlength="1000" spellcheck="true" autocapitalize="sentences"
                                      placeholder="Ej. sueldo base, bonos, comisiones, pago semanal o quincenal, etc.">{{ old('ingresos_ofrecidos', $vacante->ingresos_ofrecidos) }}</textarea>
                            @error('ingresos_ofrecidos')<div class="form-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="prestaciones">Prestaciones</label>
                            <textarea id="prestaciones" name="prestaciones" class="form-input @error('prestaciones') is-invalid @enderror" rows="4" maxlength="2000" spellcheck="true" autocapitalize="sentences"
                                      placeholder="Ej. día de descanso, IMSS, Infonavit, vacaciones, fondo de ahorro, etc.">{{ old('prestaciones', $vacante->prestaciones) }}</textarea>
                            @error('prestaciones')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label" for="requerimientos">Requerimientos del cliente</label>
                    <textarea id="requerimientos" name="requerimientos" class="form-input @error('requerimientos') is-invalid @enderror" rows="3" maxlength="2000" placeholder="Lo que el cliente describió al solicitar el servicio..." spellcheck="true" autocapitalize="sentences">{{ old('requerimientos', $vacante->requerimientos) }}</textarea>
                    @error('requerimientos')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label" for="notas_internas" style="display:flex; align-items:center; gap:6px;">
                        🔒 Notas internas
                        <span style="font-size:11px; color:#94a3b8; font-weight:normal;">(solo tú las ves, no la empresa)</span>
                    </label>
                    <textarea id="notas_internas" name="notas_internas" class="form-input" rows="3" maxlength="2000" placeholder="Recordatorios, observaciones, contactos internos..." spellcheck="true" autocapitalize="sentences">{{ old('notas_internas', $vacante->notas_internas) }}</textarea>
                </div>

                <div style="display:flex; gap:12px; margin-top:24px; padding-top:18px; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ route('admin.vacantes') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>

        <div class="card" style="position:sticky; top:20px;">
            <h3 style="font-size:0.9rem; font-weight:600; margin:0 0 14px;">Resumen</h3>
            <div style="display:grid; gap:10px; font-size:0.82rem;">
                <div><span style="color:#64748b; display:block; font-size:0.72rem;">Empresa</span>{{ $vacante->empresa?->nombre_empresa ?? '—' }}</div>
                <div><span style="color:#64748b; display:block; font-size:0.72rem;">Estado</span><span class="badge {{ \App\Models\Vacante::estadoBadgeClass($vacante->estado) }}">{{ \App\Models\Vacante::estadoLabel($vacante->estado) }}</span></div>
                <div><span style="color:#64748b; display:block; font-size:0.72rem;">Candidatos</span>{{ $vacante->postulaciones_count ?? $vacante->postulaciones()->count() }}</div>
                <div><span style="color:#64748b; display:block; font-size:0.72rem;">Requisitos</span>{{ $vacante->requisitoResumen() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
