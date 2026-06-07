@php
    $editando = $catalogo->exists;
    $action = $editando
        ? route('admin.catalogos.update', $catalogo)
        : route('admin.catalogos.store');
    $grupoSeleccionado = old('grupo', $catalogo->grupo ?: ($grupoInicial ?? null));
    $tabRegreso = \App\Models\CatalogoOpcion::moduloDelGrupo($grupoSeleccionado) ?? 'vacantes';
@endphp

<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administración</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <a href="{{ route('admin.catalogos.index', ['tab' => $tabRegreso]) }}">Catálogos del sistema</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>{{ $editando ? 'Editar opción' : 'Nueva opción' }}</span>
        </nav>
        <h1 class="page-title">{{ $editando ? 'Editar opción' : 'Agregar opción al catálogo' }}</h1>
    </x-slot>

    <div style="max-width:720px;">
        <div class="card">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if($editando)
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label class="form-label" for="grupo">Grupo <span style="color:var(--danger)">*</span></label>
                    <select id="grupo" name="grupo" class="form-input @error('grupo') is-invalid @enderror">
                        <option value="">Selecciona un grupo</option>
                        @foreach($grupos as $key => $label)
                            <option value="{{ $key }}" {{ $grupoSeleccionado === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('grupo')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-top:18px;">
                    <label class="form-label" for="clave">Clave <span style="color:var(--danger)">*</span></label>
                    @if($editando)
                        <input type="text" id="clave" class="form-input" value="{{ $catalogo->clave }}" readonly data-no-spellcheck="true" spellcheck="false" autocorrect="off">
                        <input type="hidden" name="clave" value="{{ $catalogo->clave }}">
                    @else
                        <input type="text" id="clave" name="clave" class="form-input @error('clave') is-invalid @enderror"
                               value="{{ old('clave', $catalogo->clave) }}" placeholder="ej: soporte_tecnico, gerencia"
                               spellcheck="true" autocorrect="on" autocapitalize="none" lang="es-MX">
                    @endif
                    <div style="font-size:0.75rem; color:#64748b; margin-top:3px;">La clave no cambia después de crear la opción.</div>
                    @error('clave')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-top:18px;">
                    <label class="form-label" for="valor">Valor visible <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="valor" name="valor" class="form-input @error('valor') is-invalid @enderror"
                           value="{{ old('valor', $catalogo->valor) }}" maxlength="150"
                           placeholder="Texto que verá el usuario" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                    @error('valor')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-top:18px;">
                    <label class="form-label" for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" class="form-input @error('descripcion') is-invalid @enderror"
                              rows="3" placeholder="Nota breve de uso" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">{{ old('descripcion', $catalogo->descripcion) }}</textarea>
                    @error('descripcion')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:18px;">
                    <div class="form-group">
                        <label class="form-label" for="orden">Orden</label>
                        <input type="number" id="orden" name="orden" class="form-input @error('orden') is-invalid @enderror"
                               value="{{ old('orden', $catalogo->orden ?? 0) }}" min="0">
                        @error('orden')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer; margin-top:28px;">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" name="activo" value="1" {{ old('activo', $catalogo->activo ?? true) ? 'checked' : '' }} {{ $editando && $catalogo->es_sistema ? 'disabled' : '' }}
                                   style="width:16px; height:16px; accent-color:var(--accent);">
                            <span class="form-label" style="margin:0;">Opción activa</span>
                        </label>
                    </div>
                </div>

                @if($editando && $catalogo->es_sistema)
                    <div class="alert alert-info" style="margin-top:18px;">
                        Esta es una opción del sistema. Se puede editar el texto, pero no eliminarla.
                    </div>
                @endif

                <div style="display:flex; gap:12px; margin-top:28px; padding-top:20px; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary">
                        {{ $editando ? 'Guardar cambios' : 'Agregar opción' }}
                    </button>
                    <a href="{{ route('admin.catalogos.index', ['tab' => $tabRegreso]) }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
