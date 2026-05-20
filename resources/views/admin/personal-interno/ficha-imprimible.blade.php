@php
    $val = fn ($v) => $v !== null && $v !== '' ? $v : '—';
    $ocup = (int) round($interno->ocupacionPorcentaje());
    $estadoColor = $interno->estado === 'activo' ? 'b-green' : 'b-gray';
    $ocupColor   = $ocup < 50 ? 'b-green' : ($ocup < 80 ? 'b-yellow' : 'b-red');
    $dispColor   = $interno->disponibilidad === 'disponible' ? 'b-green' : 'b-gray';
@endphp

@component('partials.layout-imprimible', [
    'titulo'    => $interno->name,
    'subtitulo' => 'Ficha de personal interno · ' . $interno->email,
])

    <div style="margin-bottom:16px; padding:12px 14px; background:#f8fafc; border-radius:8px; font-size:11px;">
        <strong>Estado:</strong> <span class="badge {{ $estadoColor }}">{{ $interno->estado === 'activo' ? 'Activo' : 'Bloqueado' }}</span>
        · <strong>Disponibilidad:</strong> <span class="badge {{ $dispColor }}">{{ ucfirst(str_replace('_', ' ', $interno->disponibilidad ?? 'sin definir')) }}</span>
        · Registrado el {{ $interno->created_at?->format('d/m/Y') ?? '—' }}
    </div>

    {{-- 1. Datos generales --}}
    <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">1. Datos generales</h2>
    <table>
        <tbody>
            <tr>
                <td style="width:50%;"><strong>Nombre:</strong> {{ $interno->name }}</td>
                <td style="width:50%;"><strong>Correo:</strong> {{ $interno->email }}</td>
            </tr>
            <tr>
                <td><strong>Departamento:</strong> {{ $val($interno->departamento) }}</td>
                <td><strong>Disponible desde:</strong> {{ $interno->disponible_desde?->format('d/m/Y') ?? '—' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- 2. Capacidad de trabajo --}}
    <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">2. Capacidad de trabajo</h2>
    <table>
        <tbody>
            <tr>
                <td style="width:34%;"><strong>Carga actual:</strong> {{ $interno->carga_trabajo_horas }} horas</td>
                <td style="width:33%;"><strong>Capacidad máxima:</strong> {{ $interno->capacidad_maxima_horas }} horas / semana</td>
                <td style="width:33%;">
                    <strong>Ocupación:</strong>
                    <span class="badge {{ $ocupColor }}">{{ $ocup }}%</span>
                </td>
            </tr>
            <tr>
                <td colspan="3"><strong>Horas libres:</strong> {{ max(0, $interno->capacidad_maxima_horas - $interno->carga_trabajo_horas) }} horas disponibles</td>
            </tr>
        </tbody>
    </table>

    {{-- 3. Especialidades --}}
    <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">3. Especialidades / Servicios que sabe brindar</h2>
    @if($interno->serviciosCapacitados->isEmpty())
        <p style="color:#94a3b8; font-size:11px; padding:8px 0;">Sin especialidades registradas.</p>
    @else
        <table>
            <tbody>
                <tr>
                    <td>
                        @foreach($interno->serviciosCapacitados as $s)
                            <span class="badge b-green" style="margin:2px 4px 2px 0;">✓ {{ $s->nombre }}</span>
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

@endcomponent
