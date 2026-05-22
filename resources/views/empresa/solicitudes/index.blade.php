<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Mi panel</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Vacantes</span>
        </nav>
        <div class="candidate-inline-meta">
            <div>
                <h1 class="page-title">Vacantes</h1>
                <p class="page-subtitle">Puestos que solicitaste cubrir. &iquest;Necesitas capacitacion u otro servicio? Ve a <a href="{{ route('empresa.servicios.index') }}" style="color:var(--accent);">Servicios solicitados</a>.</p>
            </div>
            <a href="{{ route('empresa.solicitudes.crear') }}" class="btn btn-primary">+ Nueva vacante</a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    @if($solicitudes->isEmpty())
        <div class="table-wrapper">
            <x-estado-vacio
                icono="💼"
                titulo="Aun no has solicitado vacantes"
                mensaje="Cuando publiques un puesto que necesitas cubrir aparecera aqui. Te ayudaremos a encontrar al candidato adecuado."
                accion="+ Solicitar primera vacante"
                :href="route('empresa.solicitudes.crear')" />
        </div>
    @else
        <div class="table-wrapper desktop-only table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>Puesto</th>
                        <th>Nivel</th>
                        <th>Requisitos</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($solicitudes as $sol)
                        <tr>
                            <td>
                                <div style="font-weight:600; color:var(--text);">{{ $sol->titulo }}</div>
                                @if($sol->requerimientos)
                                    <div style="font-size:0.78rem; color:#64748b; margin-top:2px;">{{ \Illuminate\Support\Str::limit($sol->requerimientos, 80) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-blue" style="font-size:0.75rem;">
                                    {{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($sol->nivel_jerarquico) }}
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
                                <div class="toolbar-wrap">
                                    <a href="{{ route('empresa.solicitudes.ver', $sol) }}" class="btn btn-secondary btn-sm">Ver detalle</a>
                                    @if($sol->estado === 'pendiente')
                                        <a href="{{ route('empresa.solicitudes.editar', $sol) }}" class="btn btn-secondary btn-sm">Editar</a>
                                        <form method="POST" action="{{ route('empresa.solicitudes.eliminar', $sol) }}" style="display:inline;" onsubmit="return confirm('&iquest;Eliminar esta solicitud? Se borrará permanentemente.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm" style="color:#dc2626;">Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mobile-only">
            <div class="candidate-mobile-list">
                @foreach($solicitudes as $sol)
                    <article class="candidate-mobile-card">
                        <div class="candidate-inline-meta">
                            <div>
                                <h3 class="candidate-mobile-card-title">{{ $sol->titulo }}</h3>
                                <p class="candidate-mobile-card-subtitle">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($sol->nivel_jerarquico) }}</p>
                            </div>
                            <span class="badge {{ \App\Models\Vacante::estadoBadgeClass($sol->estado) }}">
                                {{ \App\Models\Vacante::estadoLabel($sol->estado) }}
                            </span>
                        </div>

                        <div class="candidate-mobile-meta">
                            <div>
                                <p class="candidate-mobile-meta-label">Requisitos</p>
                                <p class="candidate-mobile-meta-value">{{ $sol->requisitoResumen() }}</p>
                            </div>
                            <div>
                                <p class="candidate-mobile-meta-label">Fecha</p>
                                <p class="candidate-mobile-meta-value">{{ $sol->fecha_publicacion?->format('d/m/Y') ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="candidate-actions" style="margin-top:14px;">
                            <a href="{{ route('empresa.solicitudes.ver', $sol) }}" class="btn btn-secondary btn-sm">Ver detalle</a>
                            @if($sol->estado === 'pendiente')
                                <a href="{{ route('empresa.solicitudes.editar', $sol) }}" class="btn btn-secondary btn-sm">Editar</a>
                                <form method="POST" action="{{ route('empresa.solicitudes.eliminar', $sol) }}" onsubmit="return confirm('&iquest;Eliminar esta solicitud? Se borrará permanentemente.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm" style="color:#dc2626;">Eliminar</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif

    <div style="margin-top:20px;">
        {{ $solicitudes->links() }}
    </div>
</x-app-layout>
