<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('empresa.servicios.index') }}">Servicios</a>
            <span class="breadcrumb-sep">›</span>
            <span>Nueva solicitud</span>
        </nav>
        <h1 class="page-title">Solicitar servicio</h1>
        <p class="page-subtitle">Selecciona el servicio que necesitas y cuéntanos los detalles.</p>
    </x-slot>

    @if($errors->any())
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--danger-light); color:var(--danger); border-radius:8px; border-left:4px solid var(--danger); max-width:760px;">
            <ul style="margin:0; padding-left:16px;">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('empresa.servicios.guardar') }}" style="max-width:760px; display:flex; flex-direction:column; gap:18px;">
        @csrf

        {{-- Servicio del catálogo --}}
        <div class="card" style="padding:24px;">
            <h2 style="margin:0 0 6px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">1. ¿Qué servicio necesitas?</h2>
            <p style="margin:0 0 14px; font-size:12px; color:#64748b;">Elige uno del catálogo. Si no encuentras el que buscas, contacta al administrador.</p>

            @if($catalogo->isEmpty())
                <div style="padding:16px; background:var(--surface-2); border:1px dashed var(--border); border-radius:8px; color:#64748b; font-size:13px; text-align:center;">
                    No hay servicios disponibles en el catálogo.
                </div>
            @else
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:8px;">
                    @foreach($catalogo as $s)
                        <label style="display:flex; align-items:start; gap:10px; padding:12px; border:2px solid {{ old('servicio_id') == $s->id ? 'var(--accent)' : 'var(--border)' }}; border-radius:8px; cursor:pointer; background:{{ old('servicio_id') == $s->id ? 'rgba(59,130,246,.08)' : 'var(--surface)' }}; transition:all .15s;"
                               onmouseover="this.style.background='var(--surface-2)'"
                               onmouseout="this.style.background='{{ old('servicio_id') == $s->id ? 'rgba(59,130,246,.08)' : 'var(--surface)' }}'">
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

        {{-- Nivel jerárquico --}}
        <div class="card" style="padding:24px;">
            <h2 style="margin:0 0 6px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">2. ¿Para qué nivel del personal?</h2>
            <p style="margin:0 0 14px; font-size:12px; color:#64748b;">Indica el nivel jerárquico de las personas a quienes va dirigido este servicio.</p>

            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(170px, 1fr)); gap:8px;">
                @foreach($niveles as $key => $label)
                    <label style="display:flex; align-items:center; gap:8px; padding:10px 12px; border:2px solid {{ old('nivel_jerarquico') === $key ? 'var(--accent)' : 'var(--border)' }}; border-radius:8px; cursor:pointer; background:{{ old('nivel_jerarquico') === $key ? 'rgba(59,130,246,.08)' : 'var(--surface)' }};">
                        <input type="radio" name="nivel_jerarquico" value="{{ $key }}" @checked(old('nivel_jerarquico') === $key) style="accent-color:var(--accent);">
                        <span style="font-size:0.85rem; font-weight:500;">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Detalles --}}
        <div class="card" style="padding:24px;">
            <h2 style="margin:0 0 14px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">3. Detalles de la solicitud</h2>

            <div style="margin-bottom:14px;">
                <label class="form-label">¿Cuántas horas durará aproximadamente?</label>
                <input type="number" name="horas_estimadas" value="{{ old('horas_estimadas') }}" min="0" max="500" placeholder="Ej. 8"
                       class="form-input" style="max-width:160px;">
                <p style="margin:4px 0 0; font-size:11px; color:#94a3b8;">Opcional. Nos ayuda a calcular la carga del responsable.</p>
            </div>

            <label class="form-label">¿Qué necesitas exactamente? *</label>
            <textarea name="notas" rows="6" class="form-input" required maxlength="2000"
                      placeholder="Describe el objetivo, fechas tentativas, número de personas involucradas, ubicación, etc.">{{ old('notas') }}</textarea>
            <p style="margin:6px 0 0; font-size:11px; color:#94a3b8;">Mientras más detallado, más rápido podremos asignar al responsable adecuado.</p>
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <a href="{{ route('empresa.servicios.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Enviar solicitud</button>
        </div>
    </form>
</x-app-layout>
