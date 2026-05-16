{{-- Partial modal – personal externo (tema light) --}}
<div style="font-family:inherit;">

    <div class="modal-header">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="modal-header-icon" style="background:rgba(139,92,246,.1);color:#8b5cf6;font-size:18px;font-weight:700;">
                {{ strtoupper(substr($persona->nombre, 0, 1)) }}
            </div>
            <div>
                <h2 class="modal-title">{{ $persona->nombre }} {{ $persona->apellidos }}</h2>
                <span class="modal-subtitle">
                    {{ \App\Models\CatalogoServicio::tipos()[$persona->especialidad] ?? $persona->especialidad }}
                    @if ($persona->empresa_o_razon_social) · {{ $persona->empresa_o_razon_social }} @endif
                </span>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span class="badge {{ match($persona->disponibilidad) { 'disponible' => 'badge-green', 'ocupado' => 'badge-yellow', 'inactivo' => 'badge-red', default => 'badge-gray' } }}" style="font-size:12px;">
                {{ \App\Models\PersonalExterno::disponibilidadLabel($persona->disponibilidad) }}
            </span>
            <button onclick="rhModalClose()" class="modal-close">&times;</button>
        </div>
    </div>

    <div class="modal-body">
        <p class="modal-section-label" style="color:#8b5cf6;">Contacto</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;">
            <div><p class="modal-field-label">Correo</p><p class="modal-field-value">{{ $persona->email }}</p></div>
            <div><p class="modal-field-label">Teléfono</p><p class="modal-field-value">{{ $persona->telefono ?: '—' }}</p></div>
        </div>

        <p class="modal-section-label" style="color:#8b5cf6;">Niveles que cubre</p>
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:20px;">
            @foreach ($persona->niveles_jerarquicos ?? [] as $n)
                <span class="badge badge-gray">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($n) }}</span>
            @endforeach
        </div>

        @if ($persona->descripcion)
            <p class="modal-section-label" style="color:#8b5cf6;">Perfil / Descripción</p>
            <p style="font-size:13px;color:var(--text-secondary);line-height:1.65;margin:0 0 20px;">{{ $persona->descripcion }}</p>
        @endif

        @if ($persona->cv_path)
            <a href="{{ Storage::url($persona->cv_path) }}" target="_blank" class="btn btn-secondary" style="gap:6px;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Descargar CV
            </a>
        @endif
    </div>

    <div class="modal-footer" style="border-top:1px solid var(--border);padding-top:20px;">
        <a href="{{ route('admin.personal-externo.edit', $persona) }}" class="btn btn-primary" onclick="rhModalClose()">Editar datos</a>
        <button onclick="rhModalClose()" class="btn btn-ghost">Cerrar</button>
    </div>
</div>
