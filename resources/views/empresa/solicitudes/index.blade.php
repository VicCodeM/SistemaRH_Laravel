<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Mi panel</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Solicitudes</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
            <div>
                <h1 class="page-title">Solicitudes</h1>
                <p class="page-subtitle">Solicitudes enviadas al equipo RH.</p>
            </div>
            <a href="{{ route('empresa.solicitudes.crear') }}" class="btn btn-primary">+ Nueva solicitud</a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Solicitud</th>
                    <th>Tipo</th>
                    <th>Requisitos</th>
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
                                <div style="font-size:0.78rem; color:#64748b; margin-top:2px;">{{ \Illuminate\Support\Str::limit($sol->requerimientos, 80) }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-blue" style="font-size:0.75rem;">
                                {{ $tipos[$sol->tipo_servicio] ?? $sol->tipo_servicio }}
                            </span>
                        </td>
                        <td style="font-size:0.8rem; color:#64748b; line-height:1.5;">
                            {{ $sol->requisitoResumen() }}
                        </td>
                        <td style="font-size:0.82rem; color:#64748b;">{{ $sol->fecha_publicacion?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            <span class="badge {{ \App\Models\Vacante::estadoBadgeClass($sol->estado) }}">
                                {{ \App\Models\Vacante::estadoLabel($sol->estado) }}
                            </span>
                        </td>
                        <td style="white-space:nowrap;">
                            <div style="display:flex; gap:6px; align-items:center;">
                                <a href="{{ route('empresa.solicitudes.ver', $sol) }}" class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Ver detalle</a>
                                @if($sol->estado === 'pendiente')
                                    <a href="{{ route('empresa.solicitudes.editar', $sol) }}" class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Editar</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:48px; color:#475569;">
                            Aún no has enviado ninguna solicitud.
                            <a href="{{ route('empresa.solicitudes.crear') }}" style="color:var(--accent);">Haz tu primera solicitud</a>
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
