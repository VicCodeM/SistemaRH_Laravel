<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administración</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <a href="{{ route('admin.empresas') }}">Empresas</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Editar</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
            <div>
                <h1 class="page-title">Editar empresa</h1>
                <p class="page-subtitle">{{ $empresa->nombre_empresa }} · {{ $empresa->usuario?->email }}</p>
            </div>
            <div style="display:flex; gap:10px;">
                <a href="{{ route('admin.empresas.pdf', $empresa) }}" target="_blank" class="btn btn-secondary" title="Descargar ficha completa en PDF">📄 Descargar PDF</a>
                <a href="{{ route('admin.empresas') }}" class="btn btn-secondary">&larr; Volver</a>
            </div>
    </x-slot>

    @if($errors->any())
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--danger-light); color:var(--danger); border-radius:8px; border-left:4px solid var(--danger); max-width:840px;">
            <ul style="margin:0; padding-left:16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.empresas.actualizar', $empresa) }}" style="max-width:840px; display:flex; flex-direction:column; gap:18px;">
        @csrf
        @method('PUT')

        {{-- Datos generales --}}
        <div class="card" style="padding:24px;">
            <h2 style="margin:0 0 16px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">Datos generales</h2>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div>
                    <label class="form-label">Nombre comercial *</label>
                    <input type="text" name="nombre_empresa" value="{{ old('nombre_empresa', $empresa->nombre_empresa) }}" required class="form-input" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                </div>
                <div>
                    <label class="form-label">Razón social</label>
                    <input type="text" name="razon_social" value="{{ old('razon_social', $empresa->razon_social) }}" class="form-input" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                </div>
                <div>
                    <label class="form-label">RFC</label>
                    <input type="text" name="rfc" value="{{ old('rfc', $empresa->rfc) }}" class="form-input" spellcheck="true" autocorrect="on" autocapitalize="none" lang="es-MX">
                </div>
                <div>
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-input" required>
                        @foreach(\App\Models\Empresa::estados() as $key => $label)
                            <option value="{{ $key }}" @selected(old('estado', $empresa->estado) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Contacto --}}
        <div class="card" style="padding:24px;">
            <h2 style="margin:0 0 16px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">Contacto</h2>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div>
                    <label class="form-label">Nombre del responsable RH</label>
                    <input type="text" name="nombre_rh" value="{{ old('nombre_rh', $empresa->nombre_rh) }}" class="form-input" spellcheck="true" autocorrect="on" autocapitalize="words" lang="es-MX">
                </div>
                <div>
                    <label class="form-label">Teléfono general</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}" class="form-input" spellcheck="true" autocorrect="on" autocapitalize="off" lang="es-MX">
                </div>
                <div>
                    <label class="form-label">Teléfono directo</label>
                    <input type="text" name="telefono_directo" value="{{ old('telefono_directo', $empresa->telefono_directo) }}" class="form-input" spellcheck="true" autocorrect="on" autocapitalize="off" lang="es-MX">
                </div>
                <div>
                    <label class="form-label">Página web</label>
                    <input type="text" name="pagina_web" value="{{ old('pagina_web', $empresa->pagina_web) }}" placeholder="https://..." class="form-input" spellcheck="true" autocorrect="on" autocapitalize="none" lang="es-MX">
                </div>
            </div>
        </div>

        {{-- Ubicación --}}
        <div class="card" style="padding:24px;">
            <h2 style="margin:0 0 16px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">Ubicación</h2>

            <div style="display:grid; grid-template-columns:2fr 1fr 1fr 100px; gap:14px;">
                <div>
                    <label class="form-label">Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $empresa->direccion) }}" class="form-input" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                </div>
                <div>
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="ciudad" value="{{ old('ciudad', $empresa->ciudad) }}" class="form-input" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                </div>
                <div>
                    <label class="form-label">Municipio</label>
                    <input type="text" name="municipio" value="{{ old('municipio', $empresa->municipio) }}" class="form-input" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                </div>
                <div>
                    <label class="form-label">CP</label>
                    <input type="text" name="codigo_postal" value="{{ old('codigo_postal', $empresa->codigo_postal) }}" class="form-input" spellcheck="true" autocorrect="on" autocapitalize="none" lang="es-MX">
                </div>
            </div>
        </div>

        {{-- Descripción --}}
        <div class="card" style="padding:24px;">
            <h2 style="margin:0 0 16px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">Descripción</h2>
            <textarea name="descripcion" rows="4" class="form-input" placeholder="Sector, tamaño, actividad principal..." spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">{{ old('descripcion', $empresa->descripcion) }}</textarea>
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <a href="{{ route('admin.empresas') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
    </form>
</x-app-layout>
