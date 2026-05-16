<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Mi panel</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('empresa.vacantes') }}">Mis Solicitudes</a>
            <span class="breadcrumb-sep">›</span>
            <span>Solicitar vacante</span>
        </nav>
        <h1 class="page-title">Solicitar una nueva vacante</h1>
        <p class="page-subtitle">Indica el puesto y nivel jerárquico. El administrador completará los detalles y te notificará.</p>
    </x-slot>

    <div style="max-width: 520px;">
        <div style="background: rgba(37,99,235,0.07); border: 1px solid rgba(37,99,235,0.2); border-radius: 10px; padding: 14px 16px; margin-bottom: 24px; font-size: 0.85rem; color: #94a3b8;">
            <strong style="color: #60a5fa;">¿Cómo funciona?</strong>
            Tu solicitud llegará al administrador, quien asignará los candidatos más adecuados según tu nivel y puesto.
        </div>

        <form method="POST" action="{{ route('empresa.vacantes.guardar') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="titulo">Nombre del puesto <span style="color: #ef4444;">*</span></label>
                <input id="titulo" class="form-input" type="text" name="titulo" value="{{ old('titulo') }}" required
                       autofocus placeholder="Ej: Gerente de Contabilidad, Analista de Datos, Supervisor de Producción">
                @error('titulo') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="nivel_jerarquico">Nivel jerárquico <span style="color: #ef4444;">*</span></label>
                <select id="nivel_jerarquico" class="form-input" name="nivel_jerarquico" required>
                    <option value="">Seleccionar nivel...</option>
                    @foreach ($niveles as $key => $label)
                        <option value="{{ $key }}" {{ old('nivel_jerarquico') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p style="font-size: 0.78rem; color: #64748b; margin-top: 4px;">Selecciona el nivel que mejor describe la posición dentro de tu organización.</p>
                @error('nivel_jerarquico') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">Enviar solicitud</button>
                <a href="{{ route('empresa.vacantes') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
