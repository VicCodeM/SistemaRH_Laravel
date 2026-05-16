<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Empresa</a>
            <span class="breadcrumb-sep">›</span>
            <span>Mis Solicitudes</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-end;">
            <div>
                <h1 class="page-title">Mis Solicitudes</h1>
                <p class="page-subtitle">{{ $vacantes->total() }} vacante(s) publicada(s).</p>
            </div>
            <a href="{{ route('empresa.vacantes.crear') }}" style="padding:10px 18px; background: var(--accent); color:#fff; border-radius:8px; text-decoration:none; font-size:14px; font-weight:500; white-space:nowrap;">+ Nueva solicitud</a>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px; padding:12px 16px; background: var(--success-light); color: var(--success); border-radius:8px; border-left:4px solid var(--success);">
            {{ session('success') }}
        </div>
    @endif

    @if($vacantes->isEmpty())
        <div class="card fade-in" style="text-align:center; padding:60px 40px;">
            <p class="text-muted" style="margin-bottom:16px;">Aún no tienes vacantes publicadas.</p>
            <a href="{{ route('empresa.vacantes.crear') }}" style="padding:10px 20px; background: var(--accent); color:#fff; border-radius:8px; text-decoration:none; font-size:14px;">Crear primera solicitud</a>
        </div>
    @else
        <div class="card fade-in">
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Vacante</th>
                        <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Salario</th>
                        <th style="text-align:center; padding:10px 12px; color:var(--text-muted); font-weight:500;">Postulaciones</th>
                        <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Estado</th>
                        <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Publicada</th>
                        <th style="text-align:right; padding:10px 12px; color:var(--text-muted); font-weight:500;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vacantes as $vacante)
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding:12px;">
                                <p style="font-weight:500; margin:0;">{{ $vacante->titulo }}</p>
                                <p style="font-size:12px; color:var(--text-muted); margin:0;">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($vacante->nivel_jerarquico) }} · {{ $vacante->ubicacion ?? 'Sin ubicación' }}</p>
                            </td>
                            <td style="padding:12px; font-size:13px; color:var(--text-muted);">
                                @if($vacante->salario_min || $vacante->salario_max)
                                    ${{ number_format($vacante->salario_min, 0) }} – ${{ number_format($vacante->salario_max, 0) }}
                                @else
                                    A convenir
                                @endif
                            </td>
                            <td style="padding:12px; text-align:center; font-weight:600;">{{ $vacante->postulaciones_count }}</td>
                            <td style="padding:12px;">
                                @php
                                    $colors = ['pendiente'=>'var(--warning)','activa'=>'var(--success)','cerrada'=>'var(--text-muted)'];
                                    $bgs    = ['pendiente'=>'var(--warning-light)','activa'=>'var(--success-light)','cerrada'=>'var(--surface-2)'];
                                @endphp
                                <span style="padding:3px 10px; border-radius:20px; font-size:12px; font-weight:500; background:{{ $bgs[$vacante->estado] ?? 'var(--surface-2)' }}; color:{{ $colors[$vacante->estado] ?? 'var(--text-muted)' }};">
                                    {{ ucfirst($vacante->estado) }}
                                </span>
                            </td>
                            <td style="padding:12px; color:var(--text-muted); font-size:13px;">{{ $vacante->fecha_publicacion?->format('d/m/Y') ?? '—' }}</td>
                            <td style="padding:12px; text-align:right;">
                                <div style="display:flex; gap:8px; justify-content:flex-end;">
                                    <a href="{{ route('empresa.vacantes.ver', $vacante) }}" style="padding:5px 10px; background: var(--accent); color:#fff; border-radius:6px; font-size:12px; text-decoration:none; font-weight:500;">Seguimiento</a>
                                    <a href="{{ route('empresa.vacantes.editar', $vacante) }}" style="padding:5px 10px; border:1px solid var(--border); color: var(--text-muted); border-radius:6px; font-size:12px; text-decoration:none;">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:16px;">{{ $vacantes->links() }}</div>
        </div>
    @endif
</x-app-layout>
