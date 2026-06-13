@php
    $colorEstado = [
        'recibida' => 'b-blue',
        'en_revision' => 'b-yellow',
        'referencias' => 'b-yellow',
        'entrevista' => 'b-yellow',
        'pendiente_proxima_vacante' => 'b-gray',
        'firma_contrato' => 'b-green',
        'capacitacion' => 'b-green',
        'postulado' => 'b-blue',
        'seleccionado' => 'b-green',
        'rechazado' => 'b-red',
        'retirado' => 'b-gray',
    ];
@endphp

@component('partials.layout-imprimible', [
    'titulo'    => 'Candidatos: ' . $vacante->titulo,
    'subtitulo' => 'Empresa: ' . ($vacante->empresa?->nombre_empresa ?? '—') . ' · ' . $vacante->postulaciones->count() . ' candidato(s)',
    'tipo'      => 'Reporte',
])
    @if($vacante->postulaciones->isEmpty())
        <p style="text-align:center; padding:40px; color:#64748b;">Esta vacante aún no tiene candidatos.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Aspiración</th>
                    <th>Exp.</th>
                    <th>Estado</th>
                    <th>Postulado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vacante->postulaciones as $p)
                    <tr>
                        <td><strong>{{ $p->candidato?->nombre }} {{ $p->candidato?->apellido_paterno }}</strong></td>
                        <td>{{ $p->candidato?->usuario?->email ?? '—' }}</td>
                        <td>{{ $p->candidato?->puesto_deseado ?? '—' }}</td>
                        <td>{{ $p->candidato?->experiencia_anios ?? 0 }} años</td>
                        <td>
                            <span class="badge {{ $colorEstado[$p->estado] ?? 'b-gray' }}">
                                {{ \App\Models\Postulacion::estadoLabel($p->estado) }}
                            </span>
                        </td>
                        <td>{{ $p->fecha_postulacion?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endcomponent
