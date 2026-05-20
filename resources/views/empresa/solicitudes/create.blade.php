<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('empresa.solicitudes') }}">Vacantes</a>
            <span class="breadcrumb-sep">›</span>
            <span>Nueva vacante</span>
        </nav>
        <h1 class="page-title">Solicitar una vacante</h1>
        <p class="page-subtitle">Cuéntanos qué persona necesitas. Solo el primer paso es obligatorio.</p>
    </x-slot>

    @if($errors->any())
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--danger-light); color:var(--danger); border-radius:8px; border-left:4px solid var(--danger); max-width:860px;">
            <ul style="margin:0; padding-left:16px;">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('empresa.solicitudes.guardar') }}" style="max-width:860px; display:flex; flex-direction:column; gap:18px;">
        @csrf

        {{-- 1. Lo básico (OBLIGATORIO) --}}
        <div class="card" style="padding:24px; border-left:4px solid var(--accent);">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                <span style="background:var(--accent); color:#fff; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700;">1</span>
                <div>
                    <h2 style="margin:0; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;">Lo básico</h2>
                    <p style="margin:2px 0 0; font-size:12px; color:#64748b;">Esto es lo único obligatorio.</p>
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="form-label">Nombre del puesto *</label>
                <input type="text" name="titulo" value="{{ old('titulo') }}" required autofocus
                       placeholder="Ej. Gerente de Ventas, Contador, Analista de Sistemas"
                       class="form-input" style="font-size:15px; padding:12px 14px;">
            </div>

            <div style="margin-bottom:16px;">
                <label class="form-label">¿Cuántas personas necesitas? *</label>
                <input type="number" name="cupos" value="{{ old('cupos', 1) }}" required min="1" max="100"
                       class="form-input" style="font-size:15px; padding:12px 14px; max-width:160px;">
                <p style="margin:4px 0 0; font-size:11px; color:#94a3b8;">Cuántos candidatos se contratarán para este puesto.</p>
            </div>

            <div>
                <label class="form-label">Nivel jerárquico del puesto *</label>
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

        {{-- 2. Requisitos del candidato (OPCIONAL) --}}
        <div class="card" style="padding:24px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                <span style="background:#94a3b8; color:#fff; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700;">2</span>
                <div>
                    <h2 style="margin:0; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;">Requisitos del candidato</h2>
                    <p style="margin:2px 0 0; font-size:12px; color:#64748b;">Opcional. Nos ayuda a encontrar al mejor candidato.</p>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div>
                    <label class="form-label">Estudios mínimos</label>
                    <select name="nivel_estudios_minimo" class="form-input">
                        <option value="">— Sin requisito —</option>
                        @foreach($estudios as $key => $label)
                            <option value="{{ $key }}" @selected(old('nivel_estudios_minimo') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Área o carrera</label>
                    <select name="area_requerida" class="form-input">
                        <option value="">— Cualquier área —</option>
                        @foreach($areas as $key => $label)
                            <option value="{{ $label }}" @selected(old('area_requerida') === $label)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Años de experiencia</label>
                    <input type="number" name="experiencia_minima" value="{{ old('experiencia_minima') }}" min="0" max="60" placeholder="0" class="form-input">
                </div>

                <div>
                    <label class="form-label">Tipo de contrato</label>
                    <select name="tipo_contrato" class="form-input">
                        <option value="">— Por definir —</option>
                        @foreach($contratos as $key => $label)
                            <option value="{{ $label }}" @selected(old('tipo_contrato') === $label)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- 3. Información extra (OPCIONAL) --}}
        <div class="card" style="padding:24px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                <span style="background:#94a3b8; color:#fff; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700;">3</span>
                <div>
                    <h2 style="margin:0; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;">Información extra</h2>
                    <p style="margin:2px 0 0; font-size:12px; color:#64748b;">Opcional. Mientras más detalles, mejor.</p>
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label class="form-label">Detalles del puesto</label>
                <textarea name="requerimientos" rows="4" maxlength="2000" class="form-input"
                          placeholder="Funciones, habilidades necesarias, número de personas a contratar, fecha objetivo, etc.">{{ old('requerimientos') }}</textarea>
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
                    <input type="text" name="ubicacion" value="{{ old('ubicacion') }}" maxlength="200" placeholder="Ciudad, sucursal o remoto" class="form-input">
                </div>
            </div>
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end; align-items:center;">
            <a href="{{ route('empresa.solicitudes') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="padding:12px 28px; font-size:15px;">Enviar solicitud</button>
        </div>

        <div style="margin-top:-8px; padding:12px 16px; background:rgba(59,130,246,.06); border-radius:8px; font-size:12px; color:#64748b;">
            💡 ¿Necesitas algo distinto a contratar (capacitación, coaching, consultoría, etc.)? Ve a <a href="{{ route('empresa.servicios.index') }}" style="color:var(--accent); font-weight:600;">Servicios solicitados</a>.
        </div>
    </form>
</x-app-layout>
