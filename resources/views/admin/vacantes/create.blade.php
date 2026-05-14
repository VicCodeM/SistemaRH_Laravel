<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Admin</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('admin.vacantes') }}">Solicitudes</a>
            <span class="breadcrumb-sep">›</span>
            <span>Nueva solicitud</span>
        </nav>
        <h1 class="page-title">Nueva solicitud de servicio</h1>
        <p class="page-subtitle">Registra una solicitud en nombre de una empresa cliente.</p>
    </x-slot>

    <div style="max-width:680px;">
        <div class="card">
            <form method="POST" action="{{ route('admin.vacantes.guardar') }}">
                @csrf

                {{-- Empresa --}}
                <div class="form-group">
                    <label class="form-label" for="empresa_id">Empresa cliente <span style="color:var(--danger)">*</span></label>
                    <select id="empresa_id" name="empresa_id" class="form-input @error('empresa_id') is-invalid @enderror">
                        <option value="">— Selecciona la empresa —</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ old('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                {{ $empresa->nombre_empresa }}
                            </option>
                        @endforeach
                    </select>
                    @error('empresa_id')<div class="form-error">{{ $message }}</div>@enderror
                    @if($empresas->isEmpty())
                        <div style="font-size:0.8rem; color:#f59e0b; margin-top:4px;">
                            No hay empresas activas. <a href="{{ route('admin.empresas') }}" style="color:#60a5fa;">Aprobar una empresa primero</a>.
                        </div>
                    @endif
                </div>

                {{-- Tipo de servicio --}}
                <div class="form-group" style="margin-top:22px;">
                    <label class="form-label">Tipo de servicio <span style="color:var(--danger)">*</span></label>
                    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(190px, 1fr)); gap:8px; margin-top:8px;">
                        @foreach($tipos as $key => $label)
                            <label style="display:flex; align-items:center; gap:8px; padding:10px 12px; border:1px solid {{ old('tipo_servicio') === $key ? 'var(--accent)' : 'var(--border)' }}; border-radius:8px; cursor:pointer; background:{{ old('tipo_servicio') === $key ? 'rgba(59,130,246,0.07)' : 'var(--surface-2)' }};">
                                <input type="radio" name="tipo_servicio" value="{{ $key }}"
                                       {{ old('tipo_servicio') === $key ? 'checked' : '' }}
                                       style="accent-color:var(--accent);">
                                <span style="font-size:0.85rem; font-weight:500;">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('tipo_servicio')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Título --}}
                <div class="form-group" style="margin-top:22px;">
                    <label class="form-label" for="titulo">Título / Puesto <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="titulo" name="titulo" class="form-input @error('titulo') is-invalid @enderror"
                           value="{{ old('titulo') }}" maxlength="200"
                           placeholder="Ej: Gerente de Ventas, Capacitación en liderazgo…">
                    @error('titulo')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Nivel --}}
                <div class="form-group" style="margin-top:18px;">
                    <label class="form-label" for="nivel_jerarquico">Nivel jerárquico <span style="color:var(--danger)">*</span></label>
                    <select id="nivel_jerarquico" name="nivel_jerarquico" class="form-input @error('nivel_jerarquico') is-invalid @enderror">
                        <option value="">— Selecciona —</option>
                        @foreach($niveles as $key => $label)
                            <option value="{{ $key }}" {{ old('nivel_jerarquico') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('nivel_jerarquico')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Requerimientos --}}
                <div class="form-group" style="margin-top:18px;">
                    <label class="form-label" for="requerimientos">Descripción y requerimientos</label>
                    <textarea id="requerimientos" name="requerimientos"
                              class="form-input @error('requerimientos') is-invalid @enderror"
                              rows="5" maxlength="2000"
                              placeholder="Perfil requerido, habilidades, número de posiciones, fecha objetivo…">{{ old('requerimientos') }}</textarea>
                    @error('requerimientos')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="margin-top:10px; padding:10px 14px; background:rgba(59,130,246,0.06); border-radius:8px; font-size:0.82rem; color:#94a3b8; border-left:3px solid var(--accent);">
                    La solicitud se creará directamente como <strong style="color:var(--accent);">Activa</strong> — sin pasar por revisión.
                </div>

                <div style="display:flex; gap:12px; margin-top:24px; padding-top:20px; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary">Crear solicitud</button>
                    <a href="{{ route('admin.vacantes') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
