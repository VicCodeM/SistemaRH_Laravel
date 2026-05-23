@php
    $val = fn ($v) => $v !== null && $v !== '' ? $v : '—';
    $estadoColor = match($empresa->estado) {
        'activa' => 'b-green',
        'pendiente' => 'b-yellow',
        'suspendida' => 'b-gray',
        'rechazada' => 'b-red',
        default => 'b-gray',
    };
@endphp

@component('partials.layout-imprimible', [
    'titulo'    => $empresa->nombre_empresa,
    'subtitulo' => 'Ficha de empresa cliente',
    'tipo'      => 'Ficha',
])

    <div style="margin-bottom:16px; padding:12px 14px; background:#f8fafc; border-radius:8px; font-size:11px;">
        <strong>Estado:</strong>
        <span class="badge {{ $estadoColor }}">{{ \App\Models\Empresa::estadoLabel($empresa->estado) }}</span>
        · Registrada el {{ $empresa->created_at?->format('d/m/Y') ?? '—' }}
    </div>

    {{-- 1. Datos generales --}}
    <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">1. Datos generales</h2>
    <table>
        <tbody>
            <tr>
                <td style="width:50%;"><strong>Nombre comercial:</strong> {{ $val($empresa->nombre_empresa) }}</td>
                <td style="width:50%;"><strong>Razón social:</strong> {{ $val($empresa->razon_social) }}</td>
            </tr>
            <tr>
                <td><strong>RFC:</strong> {{ $val($empresa->rfc) }}</td>
                <td><strong>Correo de la cuenta:</strong> {{ $empresa->usuario?->email ?? '—' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- 2. Contacto --}}
    <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">2. Contacto</h2>
    <table>
        <tbody>
            <tr>
                <td style="width:50%;"><strong>Responsable RH:</strong> {{ $val($empresa->nombre_rh) }}</td>
                <td style="width:50%;"><strong>Página web:</strong> {{ $val($empresa->pagina_web) }}</td>
            </tr>
            <tr>
                <td><strong>Teléfono general:</strong> {{ $val($empresa->telefono) }}</td>
                <td><strong>Teléfono directo:</strong> {{ $val($empresa->telefono_directo) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- 3. Ubicación --}}
    <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">3. Ubicación</h2>
    <table>
        <tbody>
            <tr>
                <td colspan="3"><strong>Dirección:</strong> {{ $val($empresa->direccion) }}</td>
            </tr>
            <tr>
                <td style="width:34%;"><strong>Ciudad:</strong> {{ $val($empresa->ciudad) }}</td>
                <td style="width:33%;"><strong>Municipio:</strong> {{ $val($empresa->municipio) }}</td>
                <td style="width:33%;"><strong>CP:</strong> {{ $val($empresa->codigo_postal) }}</td>
            </tr>
        </tbody>
    </table>

    @if($empresa->descripcion)
        <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">4. Descripción</h2>
        <table>
            <tbody>
                <tr><td>{{ $empresa->descripcion }}</td></tr>
            </tbody>
        </table>
    @endif

@endcomponent
