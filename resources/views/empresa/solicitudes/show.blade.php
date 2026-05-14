<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Mi Panel</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('empresa.solicitudes') }}">Mis Servicios</a>
            <span class="breadcrumb-sep">›</span>
            <span>{{ $vacante->titulo }}</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h1 class="page-title">{{ $vacante->titulo }}</h1>
                <p class="page-subtitle">Detalle de tu solicitud de servicio.</p>
            </div>
            @if($vacante->estado === 'pendiente')
                <a href="{{ route('empresa.solicitudes.editar', $vacante) }}" class="btn btn-secondary">Editar solicitud</a>
            @endif
        </div>
    </x-slot>

    @php
        $estadoColors  = ['pendiente' => '#f59e0b', 'activa' => '#22c55e', 'cerrada' => '#64748b', 'rechazada' => '#ef4444'];
        $estadoLabels  = ['pendiente' => 'En revisión', 'activa' => 'Activa', 'cerrada' => 'Cerrada', 'rechazada' => 'Rechazada'];
        $tipos = \App\Models\Vacante::tiposServicio();
        $niveles = \App\Models\CatalogoServicio::nivelesJerarquicos();
    @endphp

    <div style="display:grid; grid-template-columns: 1fr 320px; gap:20px; align-items:start;">

        {{-- Detalle principal --}}
        <div>
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3 style="font-weight:600; margin:0; font-size:1rem;">Información del servicio</h3>
                    <span style="padding:5px 14px; border-radius:20px; font-size:0.8rem; font-weight:600; background:{{ $estadoColors[$vacante->estado] ?? '#64748b' }}22; color:{{ $estadoColors[$vacante->estado] ?? '#64748b' }};">
                        {{ $estadoLabels[$vacante->estado] ?? ucfirst($vacante->estado) }}
                    </span>
                </div>

                <div style="display:grid; gap:14px;">
                    <div style="display:grid; grid-template-columns:160px 1fr; gap:8px; align-items:start;">
                        <span style="color:#64748b; font-size:0.85rem; padding-top:1px;">Tipo de servicio</span>
                        <span style="font-weight:500;">{{ $tipos[$vacante->tipo_servicio] ?? $vacante->tipo_servicio }}</span>
                    </div>
                    <div style="display:grid; grid-template-columns:160px 1fr; gap:8px; align-items:start;">
                        <span style="color:#64748b; font-size:0.85rem; padding-top:1px;">Nivel jerárquico</span>
                        <span style="font-weight:500;">{{ $niveles[$vacante->nivel_jerarquico] ?? ucfirst($vacante->nivel_jerarquico) }}</span>
                    </div>
                    <div style="display:grid; grid-template-columns:160px 1fr; gap:8px; align-items:start;">
                        <span style="color:#64748b; font-size:0.85rem; padding-top:1px;">Fecha de solicitud</span>
                        <span>{{ $vacante->fecha_publicacion?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    @if($vacante->requerimientos)
                        <div style="border-top:1px solid var(--border); padding-top:14px; margin-top:4px;">
                            <div style="color:#64748b; font-size:0.85rem; margin-bottom:8px;">Requerimientos y descripción</div>
                            <div style="font-size:0.9rem; line-height:1.6; color:var(--text); white-space:pre-line;">{{ $vacante->requerimientos }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Candidatos asignados --}}
            @if($vacante->postulaciones->isNotEmpty())
                <div class="card" style="margin-top:16px;">
                    <h3 style="font-weight:600; font-size:1rem; margin:0 0 16px;">Candidatos asignados</h3>

                    @php
                        $etapaColors = ['postulado'=>'#3b82f6','entrevista'=>'#f59e0b','seleccionado'=>'#22c55e','rechazado'=>'#ef4444'];
                        $etapaLabels = ['postulado'=>'En revisión','entrevista'=>'En entrevista','seleccionado'=>'Seleccionado','rechazado'=>'No continúa'];
                    @endphp

                    <div style="display:grid; gap:10px;">
                        @foreach($vacante->postulaciones as $postulacion)
                            @php $candidato = $postulacion->candidato; $usuario = $candidato?->usuario; @endphp
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:var(--surface-2); border-radius:8px; border:1px solid var(--border);">
                                <div>
                                    <div style="font-weight:600; font-size:0.9rem;">{{ $usuario?->name ?? 'Candidato' }}</div>
                                    <div style="font-size:0.78rem; color:#64748b; margin-top:2px;">{{ $usuario?->email }}</div>
                                    @if($candidato?->puesto_deseado)
                                        <div style="font-size:0.78rem; color:#94a3b8; margin-top:1px;">{{ $candidato->puesto_deseado }}</div>
                                    @endif
                                </div>
                                <span style="padding:4px 12px; border-radius:20px; font-size:0.75rem; font-weight:600; background:{{ $etapaColors[$postulacion->estado] ?? '#64748b' }}22; color:{{ $etapaColors[$postulacion->estado] ?? '#64748b' }};">
                                    {{ $etapaLabels[$postulacion->estado] ?? ucfirst($postulacion->estado) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar de acciones / info --}}
        <div>
            <div class="card">
                <h3 style="font-weight:600; font-size:0.95rem; margin:0 0 14px;">¿Qué sigue?</h3>

                @if($vacante->estado === 'pendiente')
                    <div style="font-size:0.85rem; color:#94a3b8; line-height:1.6;">
                        Tu solicitud está siendo revisada por nuestro equipo. En breve te contactaremos para coordinar los detalles.
                    </div>
                    <div style="margin-top:16px; display:flex; flex-direction:column; gap:8px;">
                        <a href="{{ route('empresa.solicitudes.editar', $vacante) }}" class="btn btn-secondary" style="text-align:center; font-size:0.85rem;">Editar solicitud</a>
                    </div>
                @elseif($vacante->estado === 'activa')
                    <div style="font-size:0.85rem; color:#94a3b8; line-height:1.6;">
                        Tu solicitud está activa. El equipo RH está gestionando candidatos para este servicio.
                    </div>
                    @if($vacante->postulaciones->isNotEmpty())
                        <div style="margin-top:12px; font-size:0.85rem; color:#22c55e; font-weight:600;">
                            {{ $vacante->postulaciones->count() }} candidato(s) asignado(s)
                        </div>
                    @endif
                @elseif($vacante->estado === 'rechazada')
                    <div style="font-size:0.85rem; color:#ef4444; line-height:1.6;">
                        Esta solicitud fue rechazada. Puedes crear una nueva solicitud con la información corregida.
                    </div>
                    <div style="margin-top:16px;">
                        <a href="{{ route('empresa.solicitudes.crear') }}" class="btn btn-primary" style="text-align:center; font-size:0.85rem; display:block;">+ Nueva solicitud</a>
                    </div>
                @else
                    <div style="font-size:0.85rem; color:#64748b; line-height:1.6;">
                        Esta solicitud está cerrada.
                    </div>
                @endif
            </div>

            <div class="card" style="margin-top:12px;">
                <h3 style="font-weight:600; font-size:0.95rem; margin:0 0 12px;">¿Necesitas ayuda?</h3>
                <div style="font-size:0.83rem; color:#94a3b8; line-height:1.6;">
                    Si tienes dudas sobre tu solicitud, puedes escribirnos por el chat o abrir un ticket de soporte.
                </div>
                <div style="margin-top:14px; display:flex; flex-direction:column; gap:8px;">
                    <a href="{{ route('chat.index') }}" class="btn btn-secondary" style="text-align:center; font-size:0.83rem;">Ir al chat</a>
                    <a href="{{ route('tickets.crear') }}" class="btn btn-secondary" style="text-align:center; font-size:0.83rem;">Abrir ticket</a>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:20px;">
        <a href="{{ route('empresa.solicitudes') }}" style="font-size:0.85rem; color:#64748b; text-decoration:none;">← Volver a mis servicios</a>
    </div>
</x-app-layout>
