<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('candidato.dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('candidato.servicios.index') }}">Mis servicios</a>
            <span class="breadcrumb-sep">›</span>
            <span>Nueva solicitud</span>
        </nav>
        <h1 class="page-title">Solicitar un servicio</h1>
        <p class="page-subtitle">Pide un curso, coaching o evaluación para ti.</p>
    </x-slot>

    @if($errors->any())
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--danger-light); color:var(--danger); border-radius:8px; border-left:4px solid var(--danger); max-width:760px;">
            <ul style="margin:0; padding-left:16px;">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('candidato.servicios.guardar') }}" style="max-width:760px; display:flex; flex-direction:column; gap:18px;">
        @csrf

        {{-- Servicio --}}
        <div class="card" style="padding:24px;">
            <h2 style="margin:0 0 6px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">1. ¿Qué te interesa?</h2>
            <p style="margin:0 0 14px; font-size:12px; color:#64748b;">Elige el servicio del catálogo.</p>

            @if($catalogo->isEmpty())
                <div style="padding:16px; background:var(--surface-2); border:1px dashed var(--border); border-radius:8px; color:#64748b; font-size:13px; text-align:center;">
                    No hay servicios disponibles en el catálogo.
                </div>
            @else
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:8px;">
                    @foreach($catalogo as $s)
                        <label style="display:flex; align-items:start; gap:10px; padding:12px; border:2px solid {{ old('servicio_id') == $s->id ? 'var(--accent)' : 'var(--border)' }}; border-radius:8px; cursor:pointer; background:{{ old('servicio_id') == $s->id ? 'rgba(59,130,246,.08)' : 'var(--surface)' }};">
                            <input type="radio" name="servicio_id" value="{{ $s->id }}" @checked(old('servicio_id') == $s->id) style="margin-top:3px; accent-color:var(--accent);">
                            <div>
                                <div style="font-weight:600; font-size:0.88rem;">{{ $s->nombre }}</div>
                                @if($s->descripcion)
                                    <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">{{ $s->descripcion }}</div>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Mensaje --}}
        <div class="card" style="padding:24px;">
            <h2 style="margin:0 0 14px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">2. Cuéntanos por qué</h2>

            <div style="margin-bottom:14px;">
                <label class="form-label">¿Cuántas horas crees que durará?</label>
                <input type="number" name="horas_estimadas" value="{{ old('horas_estimadas') }}" min="0" max="500" placeholder="Ej. 4"
                       class="form-input" style="max-width:160px;">
                <p style="margin:4px 0 0; font-size:11px; color:#94a3b8;">Opcional. Nos ayuda a planear.</p>
            </div>

            <label class="form-label">Tu motivo / objetivo *</label>
            <textarea name="notas" rows="5" class="form-input" required maxlength="2000"
                      placeholder="Ej: Quiero mejorar mi nivel de inglés para postular a puestos internacionales. Disponibilidad fines de semana.">{{ old('notas') }}</textarea>
            <p style="margin:6px 0 0; font-size:11px; color:#94a3b8;">El administrador revisará tu solicitud y te asignará un responsable.</p>
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <a href="{{ route('candidato.servicios.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Enviar solicitud</button>
        </div>
    </form>
</x-app-layout>
