<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('tickets.index') }}">Tickets</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Nuevo</span>
        </nav>
        <h1 class="page-title">Abrir ticket de soporte</h1>
        <p class="page-subtitle">Envía un aviso claro para que el equipo lo atienda sin pasos extra.</p>
    </x-slot>

    <div class="card fade-in" style="max-width:660px;">
        <form method="POST" action="{{ route('tickets.guardar') }}">
            @csrf

            <div style="display:grid; gap:18px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:500; margin-bottom:6px;">Asunto <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="asunto" value="{{ old('asunto') }}" required maxlength="200"
                        placeholder="Resume tu solicitud en una línea..."
                        style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                    @error('asunto') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label style="display:block; font-size:13px; font-weight:500; margin-bottom:6px;">Tipo de solicitud <span style="color:var(--danger);">*</span></label>
                        <select name="categoria" required style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                            <option value="">Seleccionar...</option>
                            @foreach(\App\Models\Ticket::categorias() as $key => $label)
                                <option value="{{ $key }}" {{ old('categoria') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('categoria') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:13px; font-weight:500; margin-bottom:6px;">Prioridad <span style="color:var(--danger);">*</span></label>
                        <select name="prioridad" required style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                            <option value="baja" {{ old('prioridad') === 'baja' ? 'selected' : '' }}>Baja - cuando sea posible</option>
                            <option value="media" {{ old('prioridad', 'media') === 'media' ? 'selected' : '' }}>Media - esta semana</option>
                            <option value="alta" {{ old('prioridad') === 'alta' ? 'selected' : '' }}>Alta - hoy</option>
                            <option value="urgente" {{ old('prioridad') === 'urgente' ? 'selected' : '' }}>Urgente - ahora</option>
                        </select>
                        @error('prioridad') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:500; margin-bottom:6px;">Descripción detallada <span style="color:var(--danger);">*</span></label>
                    <textarea name="descripcion" rows="5" required maxlength="3000"
                        placeholder="Explica qué necesitas, qué pasó y qué resultado esperas..."
                        style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface); resize:vertical;">{{ old('descripcion') }}</textarea>
                    @error('descripcion') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <div style="display:flex; gap:12px; justify-content:flex-end; padding-top:8px; border-top:1px solid var(--border);">
                    <a href="{{ route('tickets.index') }}" style="padding:10px 18px; border:1px solid var(--border); border-radius:8px; text-decoration:none; font-size:14px; color:var(--text-muted);">Cancelar</a>
                    <button type="submit" style="padding:10px 20px; background:var(--accent); color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:14px; font-weight:500;">Enviar ticket</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
