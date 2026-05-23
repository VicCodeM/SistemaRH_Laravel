@php
    $colorEstado = [
        'pendiente'  => 'b-yellow',
        'activo'     => 'b-blue',
        'en_proceso' => 'b-yellow',
        'completado' => 'b-green',
        'cancelado'  => 'b-gray',
    ];
@endphp

@component('partials.layout-imprimible', [
    'titulo'    => 'Pedidos de servicio',
    'subtitulo' => 'Total: ' . $tareas->count() . ' pedido(s)',
    'tipo'      => 'Reporte',
])
    @if($tareas->isEmpty())
        <p style="text-align:center; padding:40px; color:#64748b;">No hay pedidos que mostrar con estos filtros.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Solicitante</th>
                    <th>Responsable</th>
                    <th>Estado</th>
                    <th>Horas</th>
                    <th>Creado</th>
                    <th>Finalizado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tareas as $t)
                    <tr>
                        <td>
                            <strong>{{ $t->servicio?->nombre ?? 'Servicio' }}</strong>
                            @if($t->nivel_jerarquico)
                                <br><span style="color:#94a3b8; font-size:10px;">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($t->nivel_jerarquico) }}</span>
                            @endif
                        </td>
                        <td>
                            {{ $t->asignableNombre() }}
                            <br><span style="color:#94a3b8; font-size:10px;">{{ \App\Models\ServicioAsignado::asignableTipoLabel($t->asignable_type) }}</span>
                        </td>
                        <td>{{ $t->asignadoA?->name ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $colorEstado[$t->estado] ?? 'b-gray' }}">
                                {{ \App\Models\ServicioAsignado::estadoLabel($t->estado) }}
                            </span>
                        </td>
                        <td>{{ $t->horas_estimadas ?: '—' }}</td>
                        <td>{{ $t->created_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $t->fecha_fin?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endcomponent
