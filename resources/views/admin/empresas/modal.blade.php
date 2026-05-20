{{-- Partial – se inyecta en #rh-modal-content (tema light) --}}
<div style="font-family: inherit;">

    {{-- Header --}}
    <div class="modal-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <x-avatar :src="$empresa->usuario?->avatar_url" :nombre="$empresa->nombre_empresa" :tamano="48" />
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
        <div class="modal-grid-2" style="margin-bottom:24px;">
            @foreach (['RFC' => $empresa->rfc, 'Teléfono' => $empresa->telefono, 'Ciudad' => $empresa->ciudad, 'Giro / Industria' => $empresa->descripcion] as $label => $valor)
                <div>
                    <p class="modal-field-label">{{ $label }}</p>
                    <p class="modal-field-value">{{ $valor ?: '—' }}</p>
                </div>
            @endforeach
        </div>

        <p class="modal-section-label">Contacto principal</p>
        <div class="modal-grid-2" style="margin-bottom:24px;">
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
            <p class="modal-section-label">Vacantes</p>
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
    <div class="modal-footer modal-actions-wrap" style="border-top:1px solid var(--border);padding-top:20px;">
        <a href="{{ route('admin.empresas.pdf', $empresa) }}" target="_blank" class="btn btn-secondary" title="Descargar ficha completa en PDF">📄 PDF</a>
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
