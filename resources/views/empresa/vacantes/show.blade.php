<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Mi Panel</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('empresa.vacantes') }}">Mis Vacantes</a>
            <span class="breadcrumb-sep">›</span>
            <span>Candidatos asignados</span>
        </nav>
        <h1 class="page-title">{{ $vacante->titulo }}</h1>
        <p class="page-subtitle">
            Nivel: <strong>{{ ucfirst($vacante->nivel_jerarquico) }}</strong>
            @if ($vacante->ubicacion) · {{ $vacante->ubicacion }} @endif
            · <span class="badge badge-{{ match($vacante->estado) { 'activa' => 'success', 'pendiente' => 'warning', default => 'secondary' } }}">{{ ucfirst($vacante->estado) }}</span>
        </p>
    </x-slot>

    @if ($vacante->estado === 'pendiente')
        <div style="background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; font-size: 0.85rem; color: #fbbf24;">
            Esta vacante está pendiente de aprobación. El administrador la revisará y asignará candidatos.
        </div>
    @endif

    @if ($vacante->postulaciones->isEmpty())
        <div style="text-align: center; padding: 60px 24px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px;">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width: 40px; height: 40px; margin: 0 auto 12px; display: block; color: #334155;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
            </svg>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">El administrador aún no ha asignado candidatos a esta vacante.</p>
        </div>
    @else
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Candidato</th>
                        <th>Puesto deseado</th>
                        <th>Experiencia</th>
                        <th>Fecha asignación</th>
                        <th>Estado del proceso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vacante->postulaciones as $postulacion)
                        <tr>
                            <td>
                                <div style="font-weight: 600;">
                                    {{ $postulacion->candidato->nombre ?? '—' }}
                                    {{ $postulacion->candidato->apellido_paterno ?? '' }}
                                </div>
                                <div style="font-size: 0.78rem; color: #64748b;">{{ $postulacion->candidato->usuario?->email }}</div>
                            </td>
                            <td style="font-size: 0.85rem; color: #94a3b8;">
                                {{ $postulacion->candidato->puesto_deseado ?? '—' }}
                            </td>
                            <td style="font-size: 0.85rem; color: #94a3b8;">
                                @if ($postulacion->candidato->experiencia_anios)
                                    {{ $postulacion->candidato->experiencia_anios }} año(s)
                                @else —
                                @endif
                            </td>
                            <td style="font-size: 0.82rem; color: #64748b;">
                                {{ $postulacion->fecha_postulacion?->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge badge-{{ match($postulacion->estado) {
                                    'postulado'    => 'secondary',
                                    'entrevista'   => 'warning',
                                    'seleccionado' => 'success',
                                    'rechazado'    => 'danger',
                                    default        => 'secondary',
                                } }}">
                                    {{ match($postulacion->estado) {
                                        'postulado'    => 'En revisión',
                                        'entrevista'   => 'En entrevista',
                                        'seleccionado' => 'Seleccionado',
                                        'rechazado'    => 'No seleccionado',
                                        default        => ucfirst($postulacion->estado),
                                    } }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div style="margin-top: 20px;">
        <a href="{{ route('empresa.vacantes') }}" class="btn btn-secondary">← Volver a mis vacantes</a>
    </div>
</x-app-layout>
