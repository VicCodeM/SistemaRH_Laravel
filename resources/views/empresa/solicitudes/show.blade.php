<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Mi panel</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <a href="{{ route('empresa.solicitudes') }}">Solicitudes</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>{{ $vacante->titulo }}</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
            <div>
                <h1 class="page-title">{{ $vacante->titulo }}</h1>
                <p class="page-subtitle">Detalle de tu solicitud.</p>
            </div>
            @if($vacante->estado === 'pendiente')
                <div class="toolbar-wrap">
                    <a href="{{ route('empresa.solicitudes.editar', $vacante) }}" class="btn btn-secondary">Editar solicitud</a>
                    <form method="POST" action="{{ route('empresa.solicitudes.eliminar', $vacante) }}" onsubmit="return confirm('&iquest;Eliminar esta solicitud? Se borrará permanentemente.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-ghost" style="color:#dc2626;">Eliminar</button>
                    </form>
                </div>
            @endif
        </div>
    </x-slot>

    @php
        $tipos = \App\Models\Vacante::tiposServicio();
    @endphp

    <div style="display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start;">
        <div>
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3 style="font-weight:600; margin:0; font-size:1rem;">Información de la solicitud</h3>
                    <span class="badge {{ \App\Models\Vacante::estadoBadgeClass($vacante->estado) }}" style="font-size:0.8rem;">
                        {{ \App\Models\Vacante::estadoLabel($vacante->estado) }}
                    </span>
                </div>

                <div style="display:grid; gap:14px;">
                    <div style="display:grid; grid-template-columns:160px 1fr; gap:8px; align-items:start;">
                        <span style="color:#64748b; font-size:0.85rem; padding-top:1px;">Tipo de servicio</span>
                        <span style="font-weight:500;">{{ $tipos[$vacante->tipo_servicio] ?? $vacante->tipo_servicio }}</span>
                    </div>
                    <div style="display:grid; grid-template-columns:160px 1fr; gap:8px; align-items:start;">
                        <span style="color:#64748b; font-size:0.85rem; padding-top:1px;">Nivel jerárquico</span>
                        <span style="font-weight:500;">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($vacante->nivel_jerarquico) }}</span>
                    </div>
                    <div style="display:grid; grid-template-columns:160px 1fr; gap:8px; align-items:start;">
                        <span style="color:#64748b; font-size:0.85rem; padding-top:1px;">Fecha de solicitud</span>
                        <span>{{ $vacante->fecha_publicacion?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    <div style="display:grid; grid-template-columns:160px 1fr; gap:8px; align-items:start;">
                        <span style="color:#64748b; font-size:0.85rem; padding-top:1px;">Requisitos</span>
                        <span style="font-size:0.9rem; line-height:1.6; color:var(--text);">{{ $vacante->requisitoResumen() }}</span>
                    </div>
                    @if($vacante->requerimientos)
                        <div style="border-top:1px solid var(--border); padding-top:14px; margin-top:4px;">
                            <div style="color:#64748b; font-size:0.85rem; margin-bottom:8px;">Requerimientos y descripción</div>
                            <div style="font-size:0.9rem; line-height:1.6; color:var(--text); white-space:pre-line;">{{ $vacante->requerimientos }}</div>
                        </div>
                    @endif
                </div>
            </div>

            @if($vacante->postulaciones->isNotEmpty())
                <div class="card" style="margin-top:16px;">
                    <h3 style="font-weight:600; font-size:1rem; margin:0 0 16px;">Candidatos asignados</h3>

                    <div style="display:grid; gap:10px;">
                        @foreach($vacante->postulaciones as $postulacion)
                            @php $candidato = $postulacion->candidato; $usuario = $candidato?->usuario; @endphp
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:var(--surface-2); border-radius:8px; border:1px solid var(--border); gap:10px;">
                                <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                                    <x-avatar :src="$usuario?->avatar_url" :nombre="$usuario?->name ?? '?'" :tamano="36" />
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-weight:600; font-size:0.9rem;">{{ $usuario?->name ?? 'Candidato' }}</div>
                                        <div style="font-size:0.78rem; color:#64748b; margin-top:2px;">{{ $usuario?->email }}</div>
                                        @if($candidato?->puesto_deseado)
                                            <div style="font-size:0.78rem; color:#94a3b8; margin-top:1px;">{{ $candidato->puesto_deseado }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    @if($postulacion->asignacion_forzada)
                                        <span class="badge badge-red">Excepción</span>
                                    @endif
                                    <span class="badge {{ \App\Models\Postulacion::estadoBadgeClass($postulacion->estado) }}" style="font-size:0.75rem;">
                                        {{ \App\Models\Postulacion::estadoLabel($postulacion->estado) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

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
                    Si tienes dudas sobre tu solicitud, puedes escribirnos por el chat y el equipo te atenderá.
                </div>
                <div style="margin-top:14px; display:flex; flex-direction:column; gap:8px;">
                    <a href="{{ route('chat.index') }}" class="btn btn-secondary" style="text-align:center; font-size:0.83rem;">Ir al chat</a>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:20px;">
        <a href="{{ route('empresa.solicitudes') }}" style="font-size:0.85rem; color:#64748b; text-decoration:none;">&larr; Volver a mis solicitudes</a>
    </div>
</x-app-layout>
