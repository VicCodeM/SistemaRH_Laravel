<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Mi Panel</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('empresa.solicitudes') }}">Mis Servicios</a>
            <span class="breadcrumb-sep">›</span>
            <span>Editar solicitud</span>
        </nav>
        <h1 class="page-title">Editar solicitud</h1>
        <p class="page-subtitle">Solo puedes editar solicitudes que aún están en revisión.</p>
    </x-slot>

    <div style="max-width:680px;">
        <div class="card">
            <form method="POST" action="{{ route('empresa.solicitudes.actualizar', $vacante) }}">
                @csrf
                @method('PUT')

                {{-- Tipo de servicio --}}
                <div class="form-group">
                    <label class="form-label">¿Qué tipo de servicio necesitas? <span style="color:var(--danger)">*</span></label>
                    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:10px; margin-top:8px;">
                        @foreach($tipos as $key => $label)
                            <label style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:1px solid {{ (old('tipo_servicio', $vacante->tipo_servicio)) === $key ? 'var(--accent)' : 'var(--border)' }}; border-radius:8px; cursor:pointer; background:{{ (old('tipo_servicio', $vacante->tipo_servicio)) === $key ? 'rgba(59,130,246,0.07)' : 'var(--surface-2)' }}; transition:border-color 0.15s;">
                                <input type="radio" name="tipo_servicio" value="{{ $key }}"
                                       {{ old('tipo_servicio', $vacante->tipo_servicio) === $key ? 'checked' : '' }}
                                       style="accent-color:var(--accent);">
                                <span style="font-size:0.88rem; font-weight:500;">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('tipo_servicio')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Título --}}
                <div class="form-group" style="margin-top:24px;">
                    <label class="form-label" for="titulo">Título o puesto <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="titulo" name="titulo" class="form-input @error('titulo') is-invalid @enderror"
                           value="{{ old('titulo', $vacante->titulo) }}" maxlength="200">
                    @error('titulo')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Nivel --}}
                <div class="form-group" style="margin-top:20px;">
                    <label class="form-label" for="nivel_jerarquico">Nivel jerárquico <span style="color:var(--danger)">*</span></label>
                    <select id="nivel_jerarquico" name="nivel_jerarquico" class="form-input @error('nivel_jerarquico') is-invalid @enderror">
                        <option value="">— Selecciona el nivel —</option>
                        @foreach($niveles as $key => $label)
                            <option value="{{ $key }}" {{ old('nivel_jerarquico', $vacante->nivel_jerarquico) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('nivel_jerarquico')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Requerimientos --}}
                <div class="form-group" style="margin-top:20px;">
                    <label class="form-label" for="requerimientos">Descripción y requerimientos</label>
                    <textarea id="requerimientos" name="requerimientos"
                              class="form-input @error('requerimientos') is-invalid @enderror"
                              rows="5" maxlength="2000">{{ old('requerimientos', $vacante->requerimientos) }}</textarea>
                    <div style="font-size:0.75rem; color:#64748b; margin-top:4px;">Opcional · máx. 2000 caracteres</div>
                    @error('requerimientos')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex; gap:12px; margin-top:28px;">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ route('empresa.solicitudes.ver', $vacante) }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
