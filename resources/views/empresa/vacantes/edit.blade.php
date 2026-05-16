<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Mi panel</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('empresa.vacantes') }}">Mis Solicitudes</a>
            <span class="breadcrumb-sep">›</span>
            <span>Editar solicitud</span>
        </nav>
        <h1 class="page-title">Editar solicitud de vacante</h1>
        <p class="page-subtitle">Solo puedes editar solicitudes que aún no han sido aprobadas.</p>
    </x-slot>

    @php
        $nivelActual = \App\Models\CatalogoServicio::normalizarNivelJerarquico(old('nivel_jerarquico', $vacante->nivel_jerarquico));
    @endphp

    <div style="max-width: 520px;">
        <form method="POST" action="{{ route('empresa.vacantes.actualizar', $vacante) }}">
            @csrf @method('PUT')

            <div class="form-group">
                <label class="form-label" for="titulo">Nombre del puesto <span style="color: #ef4444;">*</span></label>
                <input id="titulo" class="form-input" type="text" name="titulo" value="{{ old('titulo', $vacante->titulo) }}" required>
                @error('titulo') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="nivel_jerarquico">Nivel jerárquico <span style="color: #ef4444;">*</span></label>
                <select id="nivel_jerarquico" class="form-input" name="nivel_jerarquico" required>
                    @foreach ($niveles as $key => $label)
                        <option value="{{ $key }}" {{ $nivelActual === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('nivel_jerarquico') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="{{ route('empresa.vacantes') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
