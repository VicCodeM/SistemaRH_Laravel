<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Búsqueda</span>
        </nav>
        <h1 class="page-title">Resultados de búsqueda</h1>
        @if($q)
            <p class="page-subtitle">{{ $resultados->count() }} resultado(s) para "{{ $q }}"</p>
        @else
            <p class="page-subtitle">Escribe un término en el buscador del sidebar para buscar en todo el sistema.</p>
        @endif
    </x-slot>

    @if($q)
        @if($resultados->isEmpty())
            <div class="card fade-in" style="text-align:center; padding:48px;">
                <p style="color:var(--text-muted); margin:0;">No se encontraron resultados para "{{ $q }}".</p>
            </div>
        @else
            <div style="display:grid; gap:12px;">
                @foreach($resultados as $r)
                    @php
                        $tipoColors = [
                            'empresa' => ['bg' => 'rgba(245,158,11,.12)', 'color' => '#f59e0b', 'label' => 'Empresa'],
                            'candidato' => ['bg' => 'rgba(96,165,250,.12)', 'color' => '#60a5fa', 'label' => 'Candidato'],
                            'vacante' => ['bg' => 'rgba(167,139,250,.12)', 'color' => '#a78bfa', 'label' => 'Solicitud'],
                        ];
                        $tc = $tipoColors[$r['tipo']] ?? $tipoColors['empresa'];
                    @endphp
                    <a href="{{ $r['url'] }}" class="card fade-in" style="text-decoration:none; display:flex; align-items:center; gap:16px; padding:14px 18px;">
                        <div style="padding:8px 12px; border-radius:8px; background:{{ $tc['bg'] }}; color:{{ $tc['color'] }}; font-size:12px; font-weight:600; white-space:nowrap;">
                            {{ $tc['label'] }}
                        </div>
                        <div style="flex:1; min-width:0;">
                            <p style="margin:0; font-weight:600; color:var(--text-primary); font-size:14px;">{{ $r['titulo'] }}</p>
                            <p style="margin:4px 0 0; color:var(--text-muted); font-size:12px;">{{ $r['sub'] }}</p>
                        </div>
                        <span class="badge {{ match($r['tipo']) {
                            'empresa' => \App\Models\Empresa::estadoBadgeClass($r['estado']),
                            'candidato' => \App\Models\Candidato::solicitudEstadoBadgeClass($r['estado']),
                            'vacante' => \App\Models\Vacante::estadoBadgeClass($r['estado']),
                            default => 'badge-gray'
                        } }}">
                            {{ match($r['tipo']) {
                                'empresa' => \App\Models\Empresa::estadoLabel($r['estado']),
                                'candidato' => \App\Models\Candidato::solicitudEstadoLabel($r['estado']),
                                'vacante' => \App\Models\Vacante::estadoLabel($r['estado']),
                                default => $r['estado']
                            } }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    @endif
</x-app-layout>
