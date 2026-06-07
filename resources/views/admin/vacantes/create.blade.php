<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administraci&oacute;n</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <a href="{{ route('admin.vacantes') }}">Solicitudes</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Nueva solicitud</span>
        </nav>
        <h1 class="page-title">Nueva solicitud de servicio</h1>
        <p class="page-subtitle">Registra una solicitud en nombre de una empresa cliente.</p>
    </x-slot>

    <div style="max-width:860px;">
        <div class="card">
            <form method="POST" action="{{ route('admin.vacantes.guardar') }}">
                @csrf
                @php
                    $empresaSeleccionada = old('empresa_id') ? \App\Models\Empresa::find(old('empresa_id')) : null;
                @endphp

                <div class="form-group">
                    <label class="form-label" for="empresa_id">Empresa cliente <span style="color:var(--danger)">*</span></label>
                    <select id="empresa_id" name="empresa_id" class="form-input @error('empresa_id') is-invalid @enderror">
                        <option value="">- Selecciona la empresa -</option>
                        @if($empresaSeleccionada && ! $empresas->contains('id', $empresaSeleccionada->id))
                            <option value="{{ $empresaSeleccionada->id }}" selected>
                                {{ $empresaSeleccionada->nombre_empresa }}
                                @if($empresaSeleccionada->estado !== 'activa')
                                    ({{ \App\Models\Empresa::estadoLabel($empresaSeleccionada->estado) }})
                                @endif
                            </option>
                        @endif
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ old('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                {{ $empresa->nombre_empresa }}
                            </option>
                        @endforeach
                    </select>
                    @error('empresa_id')<div class="form-error">{{ $message }}</div>@enderror
                    @if($empresas->isEmpty())
                        <div style="font-size:0.8rem; color:#f59e0b; margin-top:4px;">
                            No hay empresas activas. <a href="{{ route('admin.empresas') }}" style="color:#60a5fa;">Aprueba una empresa primero</a>.
                        </div>
                    @endif
                </div>

                <div class="form-group" style="margin-top:22px;">
                    <label class="form-label" for="titulo">T&iacute;tulo / Puesto <span style="color:var(--danger)">*</span></label>
                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        class="form-input @error('titulo') is-invalid @enderror"
                        value="{{ old('titulo') }}"
                        maxlength="200"
                        placeholder="Ej: Gerente de ventas, capacitaci&oacute;n en liderazgo..."
                        spellcheck="true"
                        autocapitalize="sentences"
                    >
                    @error('titulo')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-top:18px;">
                    <label class="form-label" for="cupos">&iquest;Cu&aacute;ntas personas? *</label>
                    <input type="number" id="cupos" name="cupos" value="{{ old('cupos', 1) }}" required min="1" max="100" class="form-input" style="max-width:160px;">
                    <p style="font-size:11px; color:#94a3b8; margin-top:4px;">Cu&aacute;ntos candidatos se contratar&aacute;n.</p>
                </div>

                <div class="form-group" style="margin-top:18px;">
                    <label class="form-label" for="nivel_jerarquico">Nivel jer&aacute;rquico <span style="color:var(--danger)">*</span></label>
                    <select id="nivel_jerarquico" name="nivel_jerarquico" class="form-input @error('nivel_jerarquico') is-invalid @enderror">
                        <option value="">- Selecciona -</option>
                        @foreach($niveles as $key => $label)
                            <option value="{{ $key }}" {{ old('nivel_jerarquico') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('nivel_jerarquico')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="card" style="margin-top:18px; padding:18px; background:var(--surface-2);">
                    <h2 style="margin:0 0 6px; font-size:0.98rem;">Requisitos de compatibilidad</h2>
                    <p style="margin:0 0 14px; font-size:0.84rem; color:#64748b;">Estos campos permiten que el sistema ordene candidatos de forma autom&aacute;tica.</p>

                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:14px;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="nivel_estudios_minimo">Nivel m&iacute;nimo de estudios</label>
                            <select id="nivel_estudios_minimo" name="nivel_estudios_minimo" class="form-input @error('nivel_estudios_minimo') is-invalid @enderror">
                                <option value="">Sin requisito</option>
                                @foreach($estudios as $key => $label)
                                    <option value="{{ $key }}" {{ old('nivel_estudios_minimo') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('nivel_estudios_minimo')<div class="form-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="area_requerida">&Aacute;rea o carrera requerida</label>
                            <select id="area_requerida" name="area_requerida" class="form-input @error('area_requerida') is-invalid @enderror">
                                <option value="">Sin requisito</option>
                                @foreach($areas as $key => $label)
                                    <option value="{{ $label }}" {{ old('area_requerida') === $label ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('area_requerida')<div class="form-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="experiencia_minima">Experiencia m&iacute;nima (a&ntilde;os)</label>
                            <input
                                type="number"
                                id="experiencia_minima"
                                name="experiencia_minima"
                                class="form-input @error('experiencia_minima') is-invalid @enderror"
                                value="{{ old('experiencia_minima') }}"
                                min="0"
                                step="1"
                                placeholder="0"
                            >
                            @error('experiencia_minima')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:14px;">
                        <label class="form-label" for="tipo_contrato">Tipo de contrato</label>
                        <select id="tipo_contrato" name="tipo_contrato" class="form-input">
                            <option value="">Por definir</option>
                            @foreach($contratos as $key => $label)
                                <option value="{{ $label }}" {{ old('tipo_contrato') === $label ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="card" style="margin-top:18px; padding:18px; background:var(--surface-2);">
                    <h2 style="margin:0 0 6px; font-size:0.98rem;">Compensaci&oacute;n y prestaciones</h2>
                    <p style="margin:0 0 14px; font-size:0.84rem; color:#64748b;">Estos campos ayudan a describir mejor la oferta para la empresa y los candidatos.</p>

                    <div style="display:grid; grid-template-columns:1fr 1fr 2fr; gap:14px;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="salario_min">Sueldo desde</label>
                            <input
                                type="number"
                                id="salario_min"
                                name="salario_min"
                                class="form-input @error('salario_min') is-invalid @enderror"
                                value="{{ old('salario_min') }}"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                            >
                            @error('salario_min')<div class="form-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="salario_max">Sueldo hasta</label>
                            <input
                                type="number"
                                id="salario_max"
                                name="salario_max"
                                class="form-input @error('salario_max') is-invalid @enderror"
                                value="{{ old('salario_max') }}"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                            >
                            @error('salario_max')<div class="form-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="ubicacion">Lugar de trabajo</label>
                            <input
                                type="text"
                                id="ubicacion"
                                name="ubicacion"
                                class="form-input @error('ubicacion') is-invalid @enderror"
                                value="{{ old('ubicacion') }}"
                                maxlength="200"
                                placeholder="Ciudad, sucursal o remoto"
                                spellcheck="true"
                                autocapitalize="sentences"
                            >
                            @error('ubicacion')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div style="display:grid; gap:14px; margin-top:14px;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="ingresos_ofrecidos">Ingresos que se ofrecen</label>
                            <textarea
                                id="ingresos_ofrecidos"
                                name="ingresos_ofrecidos"
                                class="form-input @error('ingresos_ofrecidos') is-invalid @enderror"
                                rows="3"
                                maxlength="1000"
                                spellcheck="true"
                                autocapitalize="sentences"
                                placeholder="Ej. sueldo base, bonos, comisiones, pago semanal o quincenal, etc."
                            >{{ old('ingresos_ofrecidos') }}</textarea>
                            @error('ingresos_ofrecidos')<div class="form-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="prestaciones">Prestaciones</label>
                            <textarea
                                id="prestaciones"
                                name="prestaciones"
                                class="form-input @error('prestaciones') is-invalid @enderror"
                                rows="4"
                                maxlength="2000"
                                spellcheck="true"
                                autocapitalize="sentences"
                                placeholder="Ej. d&iacute;a de descanso, IMSS, Infonavit, vacaciones, fondo de ahorro, etc."
                            >{{ old('prestaciones') }}</textarea>
                            @error('prestaciones')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top:18px;">
                    <label class="form-label" for="requerimientos">Descripci&oacute;n y requerimientos</label>
                    <textarea
                        id="requerimientos"
                        name="requerimientos"
                        class="form-input @error('requerimientos') is-invalid @enderror"
                        rows="5"
                        maxlength="2000"
                        placeholder="Perfil requerido, habilidades, n&uacute;mero de posiciones, fecha objetivo..."
                        spellcheck="true"
                        autocapitalize="sentences"
                    >{{ old('requerimientos') }}</textarea>
                    @error('requerimientos')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-top:18px;">
                    <label class="form-label" for="estado">Estado inicial</label>
                    <select id="estado" name="estado" class="form-input">
                        <option value="activa" {{ old('estado', 'activa') === 'activa' ? 'selected' : '' }}>Activa</option>
                        <option value="pendiente" {{ old('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente / sin publicar</option>
                    </select>
                    <p style="font-size:0.78rem; color:#64748b; margin-top:4px;">Activa = se publica de inmediato. Pendiente = se guarda sin mostrarla como activa.</p>
                </div>

                <div class="form-group" style="margin-top:18px;">
                    <label class="form-label" for="notas_internas" style="display:flex; align-items:center; gap:6px;">
                        Notas internas
                        <span style="font-size:11px; color:#94a3b8; font-weight:normal;">(solo t&uacute; las ves, no la empresa)</span>
                    </label>
                    <textarea
                        id="notas_internas"
                        name="notas_internas"
                        class="form-input"
                        rows="3"
                        maxlength="2000"
                        placeholder="Recordatorios, observaciones, contactos internos..."
                        spellcheck="true"
                        autocapitalize="sentences"
                    >{{ old('notas_internas') }}</textarea>
                </div>

                <div style="margin-top:10px; padding:10px 14px; background:rgba(59,130,246,0.06); border-radius:8px; font-size:0.82rem; color:#94a3b8; border-left:3px solid var(--accent);">
                    La solicitud se crea con el estado que elijas y puede activarse o desactivarse despu&eacute;s desde la lista.
                </div>

                <div style="display:flex; gap:12px; margin-top:24px; padding-top:20px; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary">Crear solicitud</button>
                    <a href="{{ route('admin.vacantes') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
