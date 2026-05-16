@php
    $editando = $servicio->exists;
    $action = $editando
        ? route('admin.catalogo.update', $servicio)
        : route('admin.catalogo.store');
    $nivelActual = old('nivel_jerarquico', \App\Models\CatalogoServicio::normalizarNivelJerarquico($servicio->nivel_jerarquico));
@endphp

<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administración</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <a href="{{ route('admin.catalogos.index', ['tab' => 'servicios']) }}">Catálogo de servicios</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>{{ $editando ? 'Editar servicio' : 'Nuevo servicio' }}</span>
        </nav>
        <h1 class="page-title">{{ $editando ? 'Editar servicio' : 'Agregar servicio al catálogo' }}</h1>
    </x-slot>

    <div style="max-width:640px;">
        <div class="card">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if($editando)
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre del servicio <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="nombre" name="nombre" class="form-input @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $servicio->nombre) }}" maxlength="200"
                           placeholder="Ej: Reclutamiento ejecutivo, Coaching de liderazgo">
                    @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-top:18px;">
                    <label class="form-label" for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" class="form-input @error('descripcion') is-invalid @enderror"
                              rows="3" placeholder="Breve descripción del servicio">{{ old('descripcion', $servicio->descripcion) }}</textarea>
                    @error('descripcion')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:18px;">
                    <div class="form-group">
                        <label class="form-label" for="tipo">Tipo de servicio <span style="color:var(--danger)">*</span></label>
                        <select id="tipo" name="tipo" class="form-input @error('tipo') is-invalid @enderror">
                            <option value="">— Selecciona —</option>
                            @foreach(\App\Models\CatalogoServicio::tipos() as $key => $label)
                                <option value="{{ $key }}" {{ old('tipo', $servicio->tipo) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('tipo')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nivel_jerarquico">Jerarquía de servicio <span style="color:var(--danger)">*</span></label>
                        <select id="nivel_jerarquico" name="nivel_jerarquico" class="form-input @error('nivel_jerarquico') is-invalid @enderror">
                            <option value="">— Selecciona —</option>
                            @foreach(\App\Models\CatalogoServicio::nivelesJerarquicosFormulario() as $key => $label)
                                <option value="{{ $key }}" {{ $nivelActual === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('nivel_jerarquico')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:18px;">
                    <div class="form-group">
                        <label class="form-label" for="para_quien">Disponible para <span style="color:var(--danger)">*</span></label>
                        <select id="para_quien" name="para_quien" class="form-input @error('para_quien') is-invalid @enderror">
                            <option value="empresa" {{ old('para_quien', $servicio->para_quien) === 'empresa' ? 'selected' : '' }}>Empresas</option>
                            <option value="candidato" {{ old('para_quien', $servicio->para_quien) === 'candidato' ? 'selected' : '' }}>Candidatos</option>
                            <option value="ambos" {{ old('para_quien', $servicio->para_quien) === 'ambos' ? 'selected' : '' }}>Ambos</option>
                        </select>
                        @error('para_quien')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="orden">Orden de aparición</label>
                        <input type="number" id="orden" name="orden" class="form-input @error('orden') is-invalid @enderror"
                               value="{{ old('orden', $servicio->orden ?? 0) }}" min="0">
                        <div style="font-size:0.75rem; color:#64748b; margin-top:3px;">Número menor = aparece primero</div>
                        @error('orden')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group" style="margin-top:18px;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1"
                               {{ old('activo', $servicio->activo ?? true) ? 'checked' : '' }}
                               style="width:16px; height:16px; accent-color:var(--accent);">
                        <span class="form-label" style="margin:0;">Servicio activo (visible para clientes)</span>
                    </label>
                </div>

                <div style="display:flex; gap:12px; margin-top:28px; padding-top:20px; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary">
                        {{ $editando ? 'Guardar cambios' : 'Agregar al catálogo' }}
                    </button>
                    <a href="{{ route('admin.catalogos.index', ['tab' => 'servicios']) }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
