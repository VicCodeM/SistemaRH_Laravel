@php
    // Helper para mostrar valor o "—" si está vacío
    $val = fn ($v) => $v !== null && $v !== '' && $v !== [] ? $v : '—';
    $arrVal = fn ($arr, $key, $def = '—') => isset($arr[$key]) && $arr[$key] !== '' ? $arr[$key] : $def;
@endphp

@component('partials.layout-imprimible', [
    'titulo'    => 'Solicitud de candidato',
    'subtitulo' => $candidato->nombreCompleto() . ' · ' . ($candidato->usuario?->email ?? '—'),
])

    @php
        $estado = \App\Models\Candidato::solicitudEstadoLabel($candidato->solicitud_estado);
        $estadoColor = match($candidato->solicitud_estado) {
            'aprobada' => 'b-green',
            'enviada'  => 'b-yellow',
            'rechazada' => 'b-red',
            default    => 'b-gray',
        };
    @endphp

    <div style="margin-bottom:16px; padding:12px 14px; background:#f8fafc; border-radius:8px; display:flex; justify-content:space-between; align-items:center; font-size:11px;">
        <div>
            <strong>Estado:</strong> <span class="badge {{ $estadoColor }}">{{ $estado }}</span>
            @if($candidato->solicitud_enviada_at)
                · Enviada {{ $candidato->solicitud_enviada_at->format('d/m/Y') }}
            @endif
            @if($candidato->solicitud_revisada_at)
                · Revisada {{ $candidato->solicitud_revisada_at->format('d/m/Y') }}
            @endif
        </div>
    </div>

    {{-- ════════ 1. DATOS PERSONALES ════════ --}}
    <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">1. Datos personales</h2>
    <table>
        <tbody>
            <tr>
                <td style="width:33%;"><strong>Nombre completo:</strong> {{ $candidato->nombreCompleto() }}</td>
                <td style="width:33%;"><strong>Fecha de nacimiento:</strong> {{ $candidato->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</td>
                <td style="width:34%;"><strong>Edad:</strong> {{ $val($candidato->edad) }} años</td>
            </tr>
            <tr>
                <td><strong>Sexo:</strong> {{ $val($candidato->sexo) }}</td>
                <td><strong>Estado civil:</strong> {{ $val($candidato->estado_civil) }}</td>
                <td><strong>Nacionalidad:</strong> {{ $val($candidato->nacionalidad) }}</td>
            </tr>
            <tr>
                <td><strong>Lugar de nacimiento:</strong> {{ $val($candidato->lugar_nacimiento) }}</td>
                <td><strong>Peso:</strong> {{ $val($candidato->peso) }} kg</td>
                <td><strong>Estatura:</strong> {{ $val($candidato->estatura) }} m</td>
            </tr>
            <tr>
                <td><strong>Vive con:</strong> {{ $val($candidato->vive_con) }}</td>
                <td><strong>Dependientes:</strong> {{ $val($candidato->dependientes) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    {{-- ════════ 2. CONTACTO ════════ --}}
    <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">2. Contacto y dirección</h2>
    <table>
        <tbody>
            <tr>
                <td style="width:33%;"><strong>Correo:</strong> {{ $candidato->usuario?->email ?? '—' }}</td>
                <td style="width:33%;"><strong>Teléfono:</strong> {{ $val($candidato->telefono) }}</td>
                <td style="width:34%;"><strong>Celular:</strong> {{ $val($candidato->celular) }}</td>
            </tr>
            <tr>
                <td colspan="3"><strong>Domicilio:</strong> {{ $val($candidato->domicilio) }}</td>
            </tr>
            <tr>
                <td><strong>Colonia:</strong> {{ $val($candidato->colonia) }}</td>
                <td><strong>CP:</strong> {{ $val($candidato->codigo_postal) }}</td>
                <td><strong>Municipio:</strong> {{ $val($candidato->municipio) }}</td>
            </tr>
            <tr>
                <td colspan="3"><strong>Ciudad / Estado:</strong> {{ $val($candidato->ciudad) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ════════ 3. IDENTIFICACIÓN ════════ --}}
    <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">3. Documentos de identificación</h2>
    <table>
        <tbody>
            <tr>
                <td style="width:50%;"><strong>CURP:</strong> {{ $val($candidato->curp) }}</td>
                <td style="width:50%;"><strong>RFC:</strong> {{ $val($candidato->rfc) }}</td>
            </tr>
            <tr>
                <td><strong>NSS:</strong> {{ $val($candidato->nore_seguro_social) }}</td>
                <td><strong>AFORE:</strong> {{ $val($candidato->afore) }}</td>
            </tr>
            <tr>
                <td><strong>Cartilla militar:</strong> {{ $val($candidato->cartilla_militar) }}</td>
                <td><strong>Pasaporte:</strong> {{ $val($candidato->pasaporte) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Licencia de conducir --}}
    @if(! empty($candidato->licencia_conducir) && ($candidato->licencia_conducir['tiene'] ?? '') === 'si')
        <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">Licencia de conducir</h2>
        <table>
            <tbody>
                <tr>
                    <td><strong>Clase:</strong> {{ $arrVal($candidato->licencia_conducir, 'clase') }}</td>
                    <td><strong>Número:</strong> {{ $arrVal($candidato->licencia_conducir, 'numero') }}</td>
                    <td><strong>Vigencia:</strong> {{ $arrVal($candidato->licencia_conducir, 'vigencia') }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- ════════ 4. INFORMACIÓN LABORAL ════════ --}}
    <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">4. Aspiración laboral</h2>
    <table>
        <tbody>
            <tr>
                <td style="width:50%;"><strong>Puesto deseado:</strong> {{ $val($candidato->puesto_deseado) }}</td>
                <td style="width:50%;"><strong>Sueldo deseado:</strong> {{ $candidato->sueldo_deseado ? '$ ' . number_format((float) $candidato->sueldo_deseado, 2) : '—' }}</td>
            </tr>
            <tr>
                <td><strong>Años de experiencia:</strong> {{ $val($candidato->experiencia_anios) }}</td>
                <td><strong>Escolaridad máxima:</strong> {{ $val($candidato->escolaridad) }}</td>
            </tr>
        </tbody>
    </table>

    @if($candidato->habilidades)
        <table style="margin-top:6px;">
            <tbody>
                <tr><td><strong>Habilidades:</strong><br>{{ $candidato->habilidades }}</td></tr>
            </tbody>
        </table>
    @endif

    {{-- ════════ 5. ESCOLARIDAD DETALLADA ════════ --}}
    @if(! empty($candidato->escolaridad_detallada))
        <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">5. Historial académico</h2>
        <table>
            <thead>
                <tr>
                    <th>Nivel</th>
                    <th>Institución</th>
                    <th>Carrera / Área</th>
                    <th>Periodo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($candidato->escolaridad_detallada as $est)
                    <tr>
                        <td>{{ $arrVal($est, 'nivel') }}</td>
                        <td>{{ $arrVal($est, 'institucion') }}</td>
                        <td>{{ $arrVal($est, 'carrera') }}</td>
                        <td>{{ $arrVal($est, 'periodo') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ════════ 6. EXPERIENCIA LABORAL ════════ --}}
    @if(! empty($candidato->historial_laboral))
        <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">6. Experiencia laboral</h2>
        <table>
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th>Puesto</th>
                    <th>Periodo</th>
                    <th>Motivo de salida</th>
                </tr>
            </thead>
            <tbody>
                @foreach($candidato->historial_laboral as $emp)
                    <tr>
                        <td>{{ $arrVal($emp, 'empresa') }}</td>
                        <td>{{ $arrVal($emp, 'puesto') }}</td>
                        <td>{{ $arrVal($emp, 'periodo') }}</td>
                        <td>{{ $arrVal($emp, 'motivo_salida') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ════════ 7. REFERENCIAS PERSONALES ════════ --}}
    @if(! empty($candidato->referencias_personales))
        <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">7. Referencias personales</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Relación</th>
                    <th>Teléfono</th>
                    <th>Tiempo de conocerle</th>
                </tr>
            </thead>
            <tbody>
                @foreach($candidato->referencias_personales as $ref)
                    <tr>
                        <td>{{ $arrVal($ref, 'nombre') }}</td>
                        <td>{{ $arrVal($ref, 'relacion') }}</td>
                        <td>{{ $arrVal($ref, 'telefono') }}</td>
                        <td>{{ $arrVal($ref, 'tiempo') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ════════ 8. REDES SOCIALES ════════ --}}
    @if(! empty($candidato->redes_sociales))
        @php
            $hayRedes = collect($candidato->redes_sociales)->filter()->isNotEmpty();
        @endphp
        @if($hayRedes)
            <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">8. Redes sociales</h2>
            <table>
                <tbody>
                    @foreach(['facebook' => 'Facebook', 'twitter' => 'Twitter / X', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn'] as $key => $label)
                        @if(! empty($candidato->redes_sociales[$key]))
                            <tr>
                                <td style="width:25%;"><strong>{{ $label }}:</strong></td>
                                <td>{{ $candidato->redes_sociales[$key] }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif

    {{-- ════════ 9. POSTULACIONES ════════ --}}
    @if($candidato->postulaciones->isNotEmpty())
        <h2 style="font-size:14px; color:#1e40af; margin:18px 0 8px; padding-bottom:4px; border-bottom:1px solid #cbd5e1;">9. Postulaciones a vacantes</h2>
        <table>
            <thead>
                <tr>
                    <th>Vacante</th>
                    <th>Empresa</th>
                    <th>Estado</th>
                    <th>Postulado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($candidato->postulaciones as $p)
                    <tr>
                        <td>{{ $p->vacante?->titulo ?? '—' }}</td>
                        <td>{{ $p->vacante?->empresa?->nombre_empresa ?? '—' }}</td>
                        <td>
                            <span class="badge b-blue">{{ \App\Models\Postulacion::estadoLabel($p->estado) }}</span>
                        </td>
                        <td>{{ $p->fecha_postulacion?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Firma --}}
    <div style="margin-top:40px; padding-top:20px; border-top:1px solid #cbd5e1; display:flex; justify-content:space-between; gap:40px;">
        <div style="flex:1; text-align:center;">
            <div style="border-bottom:1px solid #475569; margin-top:30px;"></div>
            <p style="margin-top:6px; font-size:10px; color:#64748b;">Firma del candidato</p>
        </div>
        <div style="flex:1; text-align:center;">
            <div style="border-bottom:1px solid #475569; margin-top:30px;"></div>
            <p style="margin-top:6px; font-size:10px; color:#64748b;">Firma del revisor</p>
        </div>
        <div style="flex:1; text-align:center;">
            <div style="border-bottom:1px solid #475569; margin-top:30px;"></div>
            <p style="margin-top:6px; font-size:10px; color:#64748b;">Fecha</p>
        </div>
    </div>

@endcomponent
