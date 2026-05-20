@component('partials.layout-imprimible', [
    'titulo'    => 'Personal interno',
    'subtitulo' => 'Total: ' . $internos->count() . ' colaborador(es)',
])
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Estado</th>
                <th>Ocupación</th>
                <th>Departamento</th>
                <th>Activas</th>
                <th>Completadas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($internos as $i)
                @php $ocup = (int) round($i->ocupacionPorcentaje()); @endphp
                <tr>
                    <td><strong>{{ $i->name }}</strong></td>
                    <td>{{ $i->email }}</td>
                    <td>
                        <span class="badge {{ $i->estado === 'activo' ? 'b-green' : 'b-gray' }}">
                            {{ $i->estado === 'activo' ? 'Activo' : 'Bloqueado' }}
                        </span>
                    </td>
                    <td>
                        {{ $i->carga_trabajo_horas }}/{{ $i->capacidad_maxima_horas }} h
                        <br>
                        <span class="badge {{ $ocup < 50 ? 'b-green' : ($ocup < 80 ? 'b-yellow' : 'b-red') }}">{{ $ocup }}%</span>
                    </td>
                    <td>{{ $i->departamento ?? '—' }}</td>
                    <td>{{ $i->tareas_activas }}</td>
                    <td>{{ $i->tareas_completadas }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endcomponent
