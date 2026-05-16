{{-- Partial – se inyecta en #rh-modal-content (tema light) --}}
<div style="font-family: inherit;">

    {{-- Header --}}
    <div class="modal-header">
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="modal-header-icon" style="background:rgba(37,99,235,.1);color:#2563eb;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
            </div>
            <div>
                <h2 class="modal-title">{{ $empresa->nombre_empresa }}</h2>
                <span class="modal-subtitle">Registrada el {{ $empresa->created_at?->format('d/m/Y') ?? '—' }}</span>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span class="badge {{ \App\Models\Empresa::estadoBadgeClass($empresa->estado) }}" style="font-size:12px;">
                {{ \App\Models\Empresa::estadoLabel($empresa->estado) }}
            </span>
            <button onclick="rhModalClose()" class="modal-close">&times;</button>
        </div>
    </div>

    {{-- Cuerpo --}}
    <div class="modal-body">
        <p class="modal-section-label">Datos de la empresa</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;">
            @foreach (['RFC' => $empresa->rfc, 'Teléfono' => $empresa->telefono, 'Ciudad' => $empresa->ciudad, 'Giro / Industria' => $empresa->descripcion] as $label => $valor)
                <div>
                    <p class="modal-field-label">{{ $label }}</p>
                    <p class="modal-field-value">{{ $valor ?: '—' }}</p>
                </div>
            @endforeach
        </div>

        <p class="modal-section-label">Contacto principal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;">
            <div>
                <p class="modal-field-label">Nombre</p>
                <p class="modal-field-value">{{ $empresa->usuario?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="modal-field-label">Correo</p>
                <p class="modal-field-value">{{ $empresa->usuario?->email ?? '—' }}</p>
            </div>
        </div>

        @if ($empresa->vacantes->isNotEmpty())
            <p class="modal-section-label">Solicitudes de servicio</p>
            @foreach ($empresa->vacantes as $v)
                <div class="modal-list-item">
                    <div>
                        <span class="modal-list-item-title">{{ $v->titulo }}</span>
                        <span class="modal-list-item-sub" style="margin-left:8px;">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($v->nivel_jerarquico) }}</span>
                    </div>
                    <span class="badge {{ \App\Models\Vacante::estadoBadgeClass($v->estado) }}" style="font-size:11px;">
                        {{ \App\Models\Vacante::estadoLabel($v->estado) }}
                    </span>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Acciones --}}
    <div class="modal-footer" style="border-top:1px solid var(--border);padding-top:20px;">
        @if ($empresa->estado === 'pendiente')
            <form method="POST" action="{{ route('admin.empresas.aprobar', $empresa) }}" style="margin:0;">@csrf @method('PATCH')
                <button type="submit" onclick="rhModalClose()" class="btn btn-success">✓ Aprobar empresa</button>
            </form>
            <form method="POST" action="{{ route('admin.empresas.rechazar', $empresa) }}" style="margin:0;">@csrf @method('PATCH')
                <button type="submit" onclick="rhModalClose()" class="btn btn-danger">✗ Rechazar</button>
            </form>
        @elseif ($empresa->estado === 'activa')
            <form method="POST" action="{{ route('admin.empresas.suspender', $empresa) }}" style="margin:0;">@csrf @method('PATCH')
                <button type="submit" onclick="rhModalClose()" class="btn btn-ghost">Suspender</button>
            </form>
        @elseif (in_array($empresa->estado, ['rechazada', 'suspendida']))
            <form method="POST" action="{{ route('admin.empresas.aprobar', $empresa) }}" style="margin:0;">@csrf @method('PATCH')
                <button type="submit" onclick="rhModalClose()" class="btn btn-success">Reactivar</button>
            </form>
        @endif
        <button onclick="rhModalClose()" class="btn btn-ghost">Cerrar</button>
    </div>
</div>
