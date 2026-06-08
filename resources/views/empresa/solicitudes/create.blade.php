<x-app-layout>
    @php
        $servicioCatalogo = $servicioCatalogo ?? null;
    @endphp

    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <a href="{{ route('empresa.solicitudes') }}">Vacantes</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Nueva vacante</span>
        </nav>
        <h1 class="page-title">Solicitar una vacante</h1>
        <p class="page-subtitle">Cuentanos que perfil necesitas. Solo el primer bloque es obligatorio.</p>
    </x-slot>

    @if($errors->any())
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--danger-light); color:var(--danger); border-radius:8px; border-left:4px solid var(--danger); max-width:860px;">
            <ul style="margin:0; padding-left:16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($servicioCatalogo)
        <div class="card" style="max-width:860px; margin-bottom:18px; padding:20px 22px; border:1px solid rgba(59,130,246,.18); background:linear-gradient(180deg, rgba(59,130,246,.06), rgba(255,255,255,.98));">
            <div style="display:flex; justify-content:space-between; gap:14px; flex-wrap:wrap; align-items:flex-start;">
                <div style="flex:1; min-width:260px;">
                    <p style="margin:0 0 8px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#64748b;">Servicio seleccionado</p>
                    <h2 style="margin:0 0 8px; font-size:1.05rem; font-weight:800;">{{ $servicioCatalogo->nombre }}</h2>
                    <p style="margin:0; color:#64748b; line-height:1.6;">
                        Entraste a esta vacante desde el catalogo de servicios. El flujo interno de reclutamiento sigue igual; aqui solo definimos el perfil y las condiciones del puesto.
                    </p>
                </div>

                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="{{ route('empresa.servicios.crear', ['servicio_id' => $servicioCatalogo->id]) }}" class="btn btn-secondary">Volver al detalle</a>
                    <span class="badge badge-gray">Flujo de vacante</span>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('empresa.solicitudes.guardar') }}" style="max-width:860px; display:flex; flex-direction:column; gap:18px;">
        @csrf

        @if($servicioCatalogo)
            <input type="hidden" name="catalogo_servicio_id" value="{{ old('catalogo_servicio_id', $servicioCatalogo->id) }}">
        @endif

        <div class="card" style="padding:24px; border-left:4px solid var(--accent);">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                <span style="background:var(--accent); color:#fff; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700;">1</span>
                <div>
                    <h2 style="margin:0; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;">Lo basico</h2>
                    <p style="margin:2px 0 0; font-size:12px; color:#64748b;">Este es el unico bloque obligatorio.</p>
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="form-label">Nombre del puesto *</label>
                <input
                    type="text"
                    name="titulo"
                    value="{{ old('titulo') }}"
                    required
                    autofocus
                    spellcheck="true"
                    autocorrect="on"
                    autocapitalize="sentences"
                    lang="es"
                    placeholder="Ej. Gerente de ventas, contador, analista de sistemas"
                    class="form-input"
                    style="font-size:15px; padding:12px 14px;"
                >
            </div>

            <div style="margin-bottom:16px;">
                <label class="form-label">Cuantas personas necesitas? *</label>
                <input
                    type="number"
                    name="cupos"
                    value="{{ old('cupos', 1) }}"
                    required
                    min="1"
                    max="100"
                    class="form-input"
                    style="font-size:15px; padding:12px 14px; max-width:160px;"
                >
                <p style="margin:4px 0 0; font-size:11px; color:#94a3b8;">Cuantos candidatos se contrataran para este puesto.</p>
            </div>

            <div>
                <label class="form-label">Nivel jerarquico del puesto *</label>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(170px, 1fr)); gap:8px;">
                    @foreach($niveles as $key => $label)
                        <label style="display:flex; align-items:center; gap:8px; padding:12px; border:2px solid {{ old('nivel_jerarquico') === $key ? 'var(--accent)' : 'var(--border)' }}; border-radius:8px; cursor:pointer; background:{{ old('nivel_jerarquico') === $key ? 'rgba(59,130,246,.08)' : 'var(--surface)' }};">
                            <input type="radio" name="nivel_jerarquico" value="{{ $key }}" @checked(old('nivel_jerarquico') === $key) required style="accent-color:var(--accent);">
                            <span style="font-size:13px; font-weight:500;">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card" style="padding:24px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                <span style="background:#94a3b8; color:#fff; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700;">2</span>
                <div>
                    <h2 style="margin:0; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;">Requisitos del candidato</h2>
                    <p style="margin:2px 0 0; font-size:12px; color:#64748b;">Opcional. Nos ayuda a encontrar mejores coincidencias.</p>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div>
                    <label class="form-label">Estudios minimos</label>
                    <select name="nivel_estudios_minimo" class="form-input">
                        <option value="">Sin requisito</option>
                        @foreach($estudios as $key => $label)
                            <option value="{{ $key }}" @selected(old('nivel_estudios_minimo') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Area(s) o carrera(s)</label>
                    @php $areasSel = (array) old('area_requerida', []); @endphp
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:6px;">
                        @foreach($areas as $key => $label)
                            <label style="display:inline-flex; align-items:center; gap:6px; padding:7px 12px; border:1px solid var(--border); border-radius:8px; cursor:pointer; font-size:.84rem; background:#fff;">
                                <input type="checkbox" name="area_requerida[]" value="{{ $label }}" @checked(in_array($label, $areasSel))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    <p style="margin:6px 0 0; font-size:11px; color:#94a3b8;">Puedes marcar varias. Si no marcas ninguna, queda abierta a cualquier ramo.</p>
                </div>

                <div>
                    <label class="form-label">Anios de experiencia</label>
                    <input type="number" name="experiencia_minima" value="{{ old('experiencia_minima') }}" min="0" max="60" placeholder="0" class="form-input">
                </div>

                <div>
                    <label class="form-label">Tipo de contrato</label>
                    <select name="tipo_contrato" class="form-input">
                        <option value="">Por definir</option>
                        @foreach($contratos as $key => $label)
                            <option value="{{ $label }}" @selected(old('tipo_contrato') === $label)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card" style="padding:24px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                <span style="background:#94a3b8; color:#fff; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700;">3</span>
                <div>
                    <h2 style="margin:0; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;">Compensacion y detalles</h2>
                    <p style="margin:2px 0 0; font-size:12px; color:#64748b;">Opcional. Entre mas claro quede, mejor podremos filtrar candidatos.</p>
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label class="form-label">Detalles del puesto</label>
                <textarea
                    name="requerimientos"
                    rows="4"
                    maxlength="2000"
                    class="form-input"
                    spellcheck="true"
                    autocorrect="on"
                    autocapitalize="sentences"
                    lang="es"
                    placeholder="Funciones, habilidades necesarias, fecha objetivo, ubicacion, tipo de jornada, etc."
                >{{ old('requerimientos') }}</textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 2fr; gap:14px;">
                <div>
                    <label class="form-label">Sueldo desde</label>
                    <input type="number" name="salario_min" value="{{ old('salario_min') }}" min="0" step="0.01" placeholder="0.00" class="form-input">
                </div>
                <div>
                    <label class="form-label">Sueldo hasta</label>
                    <input type="number" name="salario_max" value="{{ old('salario_max') }}" min="0" step="0.01" placeholder="0.00" class="form-input">
                </div>
                <div>
                    <label class="form-label">Lugar de trabajo</label>
                    <input
                        type="text"
                        name="ubicacion"
                        value="{{ old('ubicacion') }}"
                        maxlength="200"
                        placeholder="Ciudad, sucursal o remoto"
                        class="form-input"
                        spellcheck="true"
                        autocorrect="on"
                        autocapitalize="sentences"
                        lang="es"
                    >
                </div>
            </div>

            <div style="display:grid; gap:14px; margin-top:14px;">
                <div>
                    <label class="form-label">Ingresos que se ofrecen</label>
                    <textarea
                        name="ingresos_ofrecidos"
                        rows="3"
                        maxlength="1000"
                        class="form-input"
                        spellcheck="true"
                        autocorrect="on"
                        autocapitalize="sentences"
                        lang="es"
                        placeholder="Ej. Sueldo base, bonos, comisiones, pago semanal o quincenal."
                    >{{ old('ingresos_ofrecidos') }}</textarea>
                </div>

                <div>
                    <label class="form-label">Prestaciones</label>
                    <textarea
                        name="prestaciones"
                        rows="4"
                        maxlength="2000"
                        class="form-input"
                        spellcheck="true"
                        autocorrect="on"
                        autocapitalize="sentences"
                        lang="es"
                        placeholder="Ej. Dia de descanso, IMSS, Infonavit, vacaciones, fondo de ahorro."
                    >{{ old('prestaciones') }}</textarea>
                </div>
            </div>
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end; align-items:center;">
            <a href="{{ route('empresa.solicitudes') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="padding:12px 28px; font-size:15px;">Enviar solicitud</button>
        </div>

        <div style="margin-top:-8px; padding:12px 16px; background:rgba(59,130,246,.06); border-radius:8px; font-size:12px; color:#64748b;">
            Necesitas algo distinto a reclutamiento? Ve al catalogo de <a href="{{ route('empresa.servicios.index') }}" style="color:var(--accent); font-weight:600;">servicios disponibles</a>.
        </div>
    </form>
</x-app-layout>
