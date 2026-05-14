<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Mi Panel</a>
            <span class="breadcrumb-sep">›</span>
            <span>Mis Servicios</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h1 class="page-title">Mis Servicios</h1>
                <p class="page-subtitle">Solicitudes de servicio enviadas al equipo RH.</p>
            </div>
            <a href="{{ route('empresa.solicitudes.crear') }}" class="btn btn-primary">+ Solicitar servicio</a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    @php
        $estadoColors  = ['pendiente' => 'warning', 'activa' => 'success', 'cerrada' => 'secondary', 'rechazada' => 'danger'];
        $estadoLabels  = ['pendiente' => 'En revisión', 'activa' => 'Activa', 'cerrada' => 'Cerrada', 'rechazada' => 'Rechazada'];
    @endphp

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Servicio solicitado</th>
                    <th>Tipo</th>
                    <th>Nivel jerárquico</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($solicitudes as $sol)
                    <tr>
                        <td>
                            <div style="font-weight:600; color:var(--text);">{{ $sol->titulo }}</div>
                            @if($sol->requerimientos)
                                <div style="font-size:0.78rem; color:#64748b; margin-top:2px;">{{ Str::limit($sol->requerimientos, 80) }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="font-size:0.75rem;">
                                {{ $tipos[$sol->tipo_servicio] ?? $sol->tipo_servicio }}
                            </span>
                        </td>
                        <td style="font-size:0.85rem; color:#94a3b8;">{{ ucfirst(str_replace('_', ' ', $sol->nivel_jerarquico)) }}</td>
                        <td style="font-size:0.82rem; color:#64748b;">{{ $sol->fecha_publicacion?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $estadoColors[$sol->estado] ?? 'secondary' }}">
                                {{ $estadoLabels[$sol->estado] ?? ucfirst($sol->estado) }}
                            </span>
                        </td>
                        <td style="white-space:nowrap;">
                            <div style="display:flex; gap:6px; align-items:center;">
                                <a href="{{ route('empresa.solicitudes.ver', $sol) }}"
                                   class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Ver detalle</a>
                                @if($sol->estado === 'pendiente')
                                    <a href="{{ route('empresa.solicitudes.editar', $sol) }}"
                                       class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Editar</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:48px; color:#475569;">
                            Aún no has solicitado ningún servicio.
                            <a href="{{ route('empresa.solicitudes.crear') }}" style="color:var(--accent);">Hacer tu primera solicitud</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;">
        {{ $solicitudes->links() }}
    </div>
</x-app-layout>
