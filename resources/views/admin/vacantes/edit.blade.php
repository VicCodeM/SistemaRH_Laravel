<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administraci&oacute;n</a>
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
        $presentacionActivaInicial = (bool) old('presentacion_activa', $vacante->presentacion_activa ?? false);
    @endphp

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    <div
        class="catalogo-servicio-shell"
        x-data="{ presentacionActiva: @js($presentacionActivaInicial) }"
    >
        <form
            id="vacante-form"
            method="POST"
            action="{{ route('admin.vacantes.actualizar', $vacante) }}"
            class="card"
            style="padding:24px;"
        >
            @csrf
            @method('PUT')

            {{-- Resumen compacto (reemplaza al sidebar anterior) --}}
            <div style="padding:14px 16px; background:var(--surface-2); border-radius:12px; border:1px solid var(--border); margin-bottom:22px; display:flex; flex-wrap:wrap; gap:18px; font-size:0.84rem; align-items:center;">
                <div>
                    <span style="color:#64748b; font-size:0.72rem; display:block;">Empresa</span>
                    <strong style="color:var(--text); font-weight:600;">{{ $vacante->empresa?->nombre_empresa ?? '—' }}</strong>
                </div>
                <div>
                    <span style="color:#64748b; font-size:0.72rem; display:block;">Enviada</span>
                    <span style="color:var(--text);">{{ $vacante->fecha_publicacion?->format('d/m/Y') ?? '—' }}</span>
                </div>
                <div>
                    <span style="color:#64748b; font-size:0.72rem; display:block;">Estado</span>
                    <span class="badge {{ \App\Models\Vacante::estadoBadgeClass($vacante->estado) }}">{{ \App\Models\Vacante::estadoLabel($vacante->estado) }}</span>
                </div>
                <div>
                    <span style="color:#64748b; font-size:0.72rem; display:block;">Candidatos</span>
                    <span style="color:var(--text);">{{ $vacante->postulaciones_count ?? $vacante->postulaciones()->count() }}</span>
                </div>
                <div style="min-width:180px; flex:1;">
                    <span style="color:#64748b; font-size:0.72rem; display:block;">Requisitos</span>
                    <span style="color:var(--text);">{{ $vacante->requisitoResumen() }}</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="titulo">T&iacute;tulo / Puesto <span style="color:var(--danger)">*</span></label>
                <input type="text" id="titulo" name="titulo" class="form-input @error('titulo') is-invalid @enderror" value="{{ old('titulo', $vacante->titulo) }}" maxlength="200" spellcheck="true" autocapitalize="sentences">
                @error('titulo')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" style="margin-top:18px;">
                <label class="form-label" for="cupos">&iquest;Cu&aacute;ntas personas? *</label>
                <input type="number" id="cupos" name="cupos" value="{{ old('cupos', $vacante->cupos ?? 1) }}" required min="1" max="100" class="form-input" style="max-width:160px;">
                @if($vacante->cuposCubiertos() > 0)
                    <p style="font-size:11px; color:#f59e0b; margin-top:4px;">{{ $vacante->cuposCubiertos() }} cupo(s) ya cubiertos. No puede ser menor.</p>
                @endif
            </div>

            <div class="form-group" style="margin-top:18px;">
                <label class="form-label" for="nivel_jerarquico">Nivel jer&aacute;rquico <span style="color:var(--danger)">*</span></label>
                <select id="nivel_jerarquico" name="nivel_jerarquico" class="form-input @error('nivel_jerarquico') is-invalid @enderror">
                    <option value="">— Selecciona —</option>
                    @foreach($niveles as $key => $label)
                        <option value="{{ $key }}" {{ $nivelActual === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('nivel_jerarquico')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            {{-- Requisitos de compatibilidad --}}
            <div style="margin-top:22px;">
                <h2 style="margin:0 0 6px; font-size:0.98rem; font-weight:600;">Requisitos de compatibilidad</h2>
                <p style="margin:0 0 16px; font-size:0.84rem; color:#64748b;">Este bloque ayuda a ordenar candidatos de forma autom&aacute;tica y tambi&eacute;n a justificar excepciones.</p>

                <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:16px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label" for="nivel_estudios_minimo">Nivel m&iacute;nimo de estudios</label>
                        <select id="nivel_estudios_minimo" name="nivel_estudios_minimo" class="form-input @error('nivel_estudios_minimo') is-invalid @enderror">
                            <option value="">Sin requisito</option>
                            @foreach($estudios as $key => $label)
                                <option value="{{ $key }}" {{ $nivelEstudiosActual === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('nivel_estudios_minimo')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group" style="margin:0;">
                        <label class="form-label" for="experiencia_minima">Experiencia m&iacute;nima (a&ntilde;os)</label>
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

                @php
                    $areasSel = collect(explode(',', (string) $vacante->area_requerida))
                        ->map(fn ($s) => trim($s))->filter()->values()->all();
                    $areasSel = (array) old('area_requerida', $areasSel);
                @endphp
                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label">&Aacute;rea(s) o carrera(s) requerida(s)</label>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:6px;">
                        @foreach($areas as $key => $label)
                            <label style="display:inline-flex; align-items:center; gap:6px; padding:7px 12px; border:1px solid var(--border); border-radius:8px; cursor:pointer; font-size:.84rem; background:#fff;">
                                <input type="checkbox" name="area_requerida[]" value="{{ $label }}" @checked(in_array($label, $areasSel))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    <p style="margin:6px 0 0; font-size:11px; color:#94a3b8;">Puedes marcar varias. Si no marcas ninguna, queda abierta a cualquier ramo.</p>
                    @error('area_requerida')<div class="form-error">{{ $message }}</div>@enderror
                    @error('area_requerida.*')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Compensaci&oacute;n y prestaciones --}}
            <div style="margin-top:22px;">
                <h2 style="margin:0 0 6px; font-size:0.98rem; font-weight:600;">Compensaci&oacute;n y prestaciones</h2>
                <p style="margin:0 0 16px; font-size:0.84rem; color:#64748b;">Agrega el sueldo y beneficios que ofreces para que empresa y candidatos vean la oferta completa.</p>

                <div style="display:grid; grid-template-columns:1fr 1fr 2fr; gap:16px;">
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

                <div style="display:grid; gap:16px; margin-top:16px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label" for="ingresos_ofrecidos">Ingresos que se ofrecen</label>
                        <textarea id="ingresos_ofrecidos" name="ingresos_ofrecidos" class="form-input @error('ingresos_ofrecidos') is-invalid @enderror" rows="3" maxlength="1000" spellcheck="true" autocapitalize="sentences"
                                  placeholder="Ej. sueldo base, bonos, comisiones, pago semanal o quincenal, etc.">{{ old('ingresos_ofrecidos', $vacante->ingresos_ofrecidos) }}</textarea>
                        @error('ingresos_ofrecidos')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group" style="margin:0;">
                        <label class="form-label" for="prestaciones">Prestaciones</label>
                        <textarea id="prestaciones" name="prestaciones" class="form-input @error('prestaciones') is-invalid @enderror" rows="4" maxlength="2000" spellcheck="true" autocapitalize="sentences"
                                  placeholder="Ej. d&iacute;a de descanso, IMSS, Infonavit, vacaciones, fondo de ahorro, etc.">{{ old('prestaciones', $vacante->prestaciones) }}</textarea>
                        @error('prestaciones')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top:18px;">
                <label class="form-label" for="requerimientos">Requerimientos del cliente</label>
                <textarea id="requerimientos" name="requerimientos" class="form-input @error('requerimientos') is-invalid @enderror" rows="3" maxlength="2000" placeholder="Lo que el cliente describi&oacute; al solicitar el servicio..." spellcheck="true" autocapitalize="sentences">{{ old('requerimientos', $vacante->requerimientos) }}</textarea>
                @error('requerimientos')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" style="margin-top:18px;">
                <label class="form-label" for="notas_internas" style="display:flex; align-items:center; gap:6px;">
                    &#128274; Notas internas
                    <span style="font-size:11px; color:#94a3b8; font-weight:normal;">(solo t&uacute; las ves, no la empresa)</span>
                </label>
                <textarea id="notas_internas" name="notas_internas" class="form-input" rows="3" maxlength="2000" placeholder="Recordatorios, observaciones, contactos internos..." spellcheck="true" autocapitalize="sentences">{{ old('notas_internas', $vacante->notas_internas) }}</textarea>
            </div>

            <div class="form-group" style="margin-top:18px;">
                <div class="catalogo-servicio-toggle">
                    <label class="catalogo-servicio-toggle__label">
                        <input type="checkbox" name="presentacion_activa" value="1" x-model="presentacionActiva" @checked($presentacionActivaInicial)>
                        <span>Activar presentación visual de esta vacante</span>
                    </label>
                    <p class="catalogo-servicio-toggle__text">
                        Si la activas, podr&aacute;s subir diapositivas (im&aacute;genes) que la empresa y los candidatos ver&aacute;n. El editor aparece abajo despu&eacute;s de guardar.
                    </p>
                </div>
                @error('presentacion_activa')<div class="form-error" style="margin-top:8px;">{{ $message }}</div>@enderror
            </div>
        </form>

        <div id="presentacion-vacante" x-show="presentacionActiva" x-cloak>
            @include('partials.catalogo-servicio-recursos', [
                'owner' => $vacante,
                'puedeGestionar' => true,
                'tituloSeccion' => 'Presentación de la vacante',
                'tieneTablaRecursos' => \App\Models\Vacante::tieneTablaRecursos(),
                'rutaStore' => 'admin.vacantes.recursos.store',
                'rutaUpdate' => 'admin.vacantes.recursos.update',
                'rutaDestroy' => 'admin.vacantes.recursos.destroy',
            ])
        </div>

        <div class="catalogo-servicio-actions">
            <a href="{{ route('admin.vacantes') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" form="vacante-form" class="btn btn-primary">Guardar cambios</button>
        </div>
    </div>
</x-app-layout>
