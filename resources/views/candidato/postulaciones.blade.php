<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">›</span>
            <span>Postulaciones</span>
        </nav>
        <h1 class="page-title">Mis Postulaciones</h1>
        <p class="page-subtitle">Seguimiento de tus aplicaciones a vacantes.</p>
    </x-slot>

    @if($postulaciones->count())
        <div class="card fade-in" style="padding: 0; overflow: hidden;">
            <div class="table-wrap" style="border: none; box-shadow: none;">
                <table>
                    <thead>
                        <tr>
                            <th>Vacante</th>
                            <th>Empresa</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($postulaciones as $p)
                            <tr>
                                <td>
                                    <span style="font-weight: 500;">{{ $p->vacante?->titulo ?? '—' }}</span>
                                </td>
                                <td class="text-muted">{{ $p->vacante?->empresa?->nombre ?? '—' }}</td>
                                <td class="text-muted text-sm">{{ $p->fecha_postulacion?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    @php
                                        $statusClasses = [
                                            'pendiente' => 'badge-yellow',
                                            'revisado' => 'badge-blue',
                                            'seleccionado' => 'badge-green',
                                            'rechazado' => 'badge-red',
                                        ];
                                        $statusLabels = [
                                            'pendiente' => 'Pendiente',
                                            'revisado' => 'En Revisión',
                                            'seleccionado' => 'Seleccionado',
                                            'rechazado' => 'Rechazado',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusClasses[$p->estado] ?? 'badge-gray' }}">
                                        {{ $statusLabels[$p->estado] ?? $p->estado }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card fade-in" style="text-align: center; padding: 60px 40px;">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
            </svg>
            <h3 style="font-weight: 600; margin-bottom: 8px;">Sin postulaciones</h3>
            <p class="text-muted text-sm">Aún no te has postulado a ninguna vacante.</p>
            <a wire:navigate href="{{ route('candidato.vacantes') }}" class="btn btn-primary" style="margin-top: 16px;">Ver Vacantes</a>
        </div>
    @endif
</x-app-layout>
