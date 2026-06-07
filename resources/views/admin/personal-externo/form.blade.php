<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administración</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('admin.personal-externo.index') }}">Personal Externo</a>
            <span class="breadcrumb-sep">›</span>
            <span>{{ $persona->exists ? 'Editar' : 'Agregar persona' }}</span>
        </nav>
        <h1 class="page-title">{{ $persona->exists ? 'Editar consultor/capacitador' : 'Agregar consultor/capacitador' }}</h1>
    </x-slot>

    @php
        $nivelesSeleccionados = collect(old('niveles_jerarquicos', $persona->niveles_jerarquicos ?? []))
            ->map(fn ($nivel) => \App\Models\CatalogoServicio::normalizarNivelJerarquico($nivel))
            ->filter()
            ->values()
            ->all();
    @endphp

    <div style="max-width: 720px;">
        <form method="POST"
              action="{{ $persona->exists ? route('admin.personal-externo.update', $persona) : route('admin.personal-externo.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if ($persona->exists) @method('PUT') @endif

            <p style="font-size: 0.75rem; font-weight: 700; color: #60a5fa; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 12px;">Datos personales</p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre <span style="color: #ef4444;">*</span></label>
                    <input id="nombre" class="form-input" type="text" name="nombre" value="{{ old('nombre', $persona->nombre) }}" required spellcheck="true" autocorrect="on" autocapitalize="words" lang="es-MX">
                    @error('nombre') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="apellidos">Apellidos <span style="color: #ef4444;">*</span></label>
                    <input id="apellidos" class="form-input" type="text" name="apellidos" value="{{ old('apellidos', $persona->apellidos) }}" required spellcheck="true" autocorrect="on" autocapitalize="words" lang="es-MX">
                    @error('apellidos') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="email">Correo electrónico <span style="color: #ef4444;">*</span></label>
                    <input id="email" class="form-input" type="email" name="email" value="{{ old('email', $persona->email) }}" required>
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="telefono">Teléfono</label>
                    <input id="telefono" class="form-input" type="tel" name="telefono" value="{{ old('telefono', $persona->telefono) }}" placeholder="55 0000 0000" spellcheck="true" autocorrect="on" autocapitalize="off" lang="es-MX">
                    @error('telefono') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="empresa_o_razon_social">Empresa / Razón social</label>
                <input id="empresa_o_razon_social" class="form-input" type="text" name="empresa_o_razon_social" value="{{ old('empresa_o_razon_social', $persona->empresa_o_razon_social) }}" placeholder="Si trabaja de forma independiente, puede quedar vacío" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                @error('empresa_o_razon_social') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <p style="font-size: 0.75rem; font-weight: 700; color: #60a5fa; letter-spacing: 1px; text-transform: uppercase; margin: 20px 0 12px;">Perfil profesional</p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="especialidad">Especialidad <span style="color: #ef4444;">*</span></label>
                    <select id="especialidad" class="form-input" name="especialidad" required>
                        <option value="">Seleccionar...</option>
                        @foreach ($especialidades as $key => $label)
                            <option value="{{ $key }}" {{ old('especialidad', $persona->especialidad) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('especialidad') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="disponibilidad">Disponibilidad <span style="color: #ef4444;">*</span></label>
                    <select id="disponibilidad" class="form-input" name="disponibilidad" required>
                        @foreach ($disponibilidades as $key => $label)
                            <option value="{{ $key }}" {{ old('disponibilidad', $persona->disponibilidad ?? 'disponible') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('disponibilidad') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Niveles jerárquicos que puede cubrir <span style="color: #ef4444;">*</span></label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px;">
                    @foreach ($niveles as $key => $label)
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.85rem;">
                            <input type="checkbox"
                                   name="niveles_jerarquicos[]"
                                   value="{{ $key }}"
                                    {{ in_array($key, $nivelesSeleccionados) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                @error('niveles_jerarquicos') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="descripcion">Descripción / Perfil</label>
                <textarea id="descripcion" class="form-input" name="descripcion" rows="3" placeholder="Experiencia, certificaciones, áreas de enfoque..." spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">{{ old('descripcion', $persona->descripcion) }}</textarea>
                @error('descripcion') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="cv">CV / Currículum {{ $persona->exists ? '(dejar vacío para mantener el actual)' : '' }}</label>
                <input id="cv" class="form-input" type="file" name="cv" accept=".pdf,.doc,.docx">
                @if ($persona->exists && $persona->cv_path)
                    <p style="font-size: 0.78rem; color: #60a5fa; margin-top: 4px;">
                        <a href="{{ Storage::url($persona->cv_path) }}" target="_blank">Ver CV actual</a>
                    </p>
                @endif
                @error('cv') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">
                    {{ $persona->exists ? 'Guardar cambios' : 'Registrar persona' }}
                </button>
                <a href="{{ route('admin.personal-externo.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
