<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <a href="{{ route('admin.catalogos.index', ['tab' => 'servicios']) }}">Catalogos</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>{{ $servicio->exists ? 'Editar servicio' : 'Nuevo servicio' }}</span>
        </nav>
        <h1 class="page-title">{{ $servicio->exists ? 'Editar servicio' : 'Nuevo servicio' }}</h1>
        <p class="page-subtitle">Configura el catalogo, define el flujo y agrega aqui mismo la presentacion visual del servicio.</p>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    @php
        $flujoInicial = old('flujo', $servicio->flujo ?? 'servicio');
    @endphp

    <div style="max-width:980px; display:flex; flex-direction:column; gap:20px;">
        <form
            method="POST"
            action="{{ $servicio->exists ? route('admin.catalogo.update', $servicio) : route('admin.catalogo.store') }}"
            class="card"
            style="padding:24px;"
            x-data="{ flujo: '{{ $flujoInicial }}' }"
        >
            @csrf
            @if($servicio->exists)
                @method('PUT')
            @endif

            <div class="form-grid form-grid-2">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-input" value="{{ old('nombre', $servicio->nombre) }}" required maxlength="200">
                    @error('nombre') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Categoria *</label>
                    <select name="tipo" class="form-input" required>
                        @foreach(\App\Models\CatalogoServicio::tipos() as $key => $label)
                            <option value="{{ $key }}" @selected(old('tipo', $servicio->tipo) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('tipo') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Flujo *</label>
                    <select name="flujo" class="form-input" x-model="flujo">
                        @foreach(\App\Models\CatalogoServicio::flujos() as $key => $label)
                            <option value="{{ $key }}" @selected($flujoInicial === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p style="margin:6px 0 0; font-size:12px; color:#94a3b8;">
                        <span x-show="flujo === 'servicio'">Queda disponible para solicitarse desde el catalogo del rol correspondiente.</span>
                        <span x-show="flujo === 'vacante'">Abre el formulario actual de vacantes sin cambiar su logica interna.</span>
                    </p>
                    @error('flujo') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group" x-show="flujo !== 'vacante'">
                    <label class="form-label">Para quien *</label>
                    <select name="para_quien" class="form-input">
                        @foreach([
                            'empresa' => 'Empresa',
                            'candidato' => 'Candidato',
                            'ambos' => 'Ambos',
                        ] as $key => $label)
                            <option value="{{ $key }}" @selected(old('para_quien', $servicio->para_quien ?: 'empresa') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p style="margin:6px 0 0; font-size:12px; color:#94a3b8;">Empresa ve servicios marcados como Empresa o Ambos. Candidato solo ve Candidato o Ambos.</p>
                    @error('para_quien') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group" x-show="flujo === 'vacante'">
                    <label class="form-label">Para quien</label>
                    <input type="hidden" name="para_quien" value="empresa">
                    <div class="form-input" style="display:flex; align-items:center; color:#475569; background:var(--surface-2);">Empresa</div>
                    <p style="margin:6px 0 0; font-size:12px; color:#94a3b8;">Las solicitudes de vacante solo se publican para empresas.</p>
                </div>

                <div class="form-group" x-show="flujo !== 'vacante'">
                    <label class="form-label">Nivel jerarquico *</label>
                    <select name="nivel_jerarquico" class="form-input">
                        @foreach(\App\Models\CatalogoServicio::nivelesJerarquicosCompatibles() as $key => $label)
                            <option value="{{ $key }}" @selected(old('nivel_jerarquico', $servicio->nivel_jerarquico ?: 'todos') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p style="margin:6px 0 0; font-size:12px; color:#94a3b8;">Usalo solo para servicios de empresa. En candidato normalmente aplica "Todos".</p>
                    @error('nivel_jerarquico') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group" x-show="flujo === 'vacante'">
                    <label class="form-label">Nivel del catalogo</label>
                    <input type="hidden" name="nivel_jerarquico" value="todos">
                    <div class="form-input" style="display:flex; align-items:center; color:#475569; background:var(--surface-2);">Todos</div>
                    <p style="margin:6px 0 0; font-size:12px; color:#94a3b8;">El nivel real se define despues en el formulario de vacante.</p>
                </div>

                <div class="form-group form-grid-span-2">
                    <label class="form-label">Descripcion</label>
                    <textarea name="descripcion" class="form-input" rows="4" maxlength="4000" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es">{{ old('descripcion', $servicio->descripcion) }}</textarea>
                    <p style="margin:6px 0 0; font-size:12px; color:#94a3b8;">Este texto alimenta el tooltip de la lista y el detalle completo del servicio.</p>
                    @error('descripcion') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Orden</label>
                    <input type="number" name="orden" class="form-input" value="{{ old('orden', $servicio->orden ?? 0) }}" min="0">
                    @error('orden') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group" style="display:flex; align-items:end; gap:12px;">
                    <label style="display:flex; align-items:center; gap:8px; font-weight:600; margin:0;">
                        <input type="checkbox" name="activo" value="1" @checked(old('activo', $servicio->exists ? $servicio->activo : true))>
                        Servicio activo
                    </label>
                </div>
            </div>

            <div x-show="flujo === 'vacante'" style="margin-top:18px; padding:14px 16px; border:1px solid #bfdbfe; background:#eff6ff; border-radius:12px; color:#1d4ed8; font-size:13px;">
                Este servicio aparecera en Servicios disponibles, pero el boton del detalle abrira el flujo actual de vacantes.
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <a href="{{ route('admin.catalogos.index', ['tab' => 'servicios']) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">{{ $servicio->exists ? 'Guardar cambios' : 'Crear servicio' }}</button>
            </div>
        </form>

        @if($servicio->exists)
            @include('partials.catalogo-servicio-recursos', [
                'catalogo' => $servicio,
                'puedeGestionar' => true,
            ])
        @else
            <div class="card" style="padding:20px;">
                <p style="margin:0; color:#64748b;">Guarda primero el servicio para poder cargar las imagenes de su presentacion.</p>
            </div>
        @endif
    </div>
</x-app-layout>
