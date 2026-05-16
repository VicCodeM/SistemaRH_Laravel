<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Solicitudes disponibles</span>
        </nav>
        <h1 class="page-title">Solicitudes disponibles</h1>
        <p class="page-subtitle">Abre el detalle en un modal y revisa requisitos, empresa y alcance antes de solicitar.</p>
    </x-slot>

    <div class="card fade-in" style="margin-bottom:20px;">
        <form method="GET" style="display:grid; grid-template-columns:1fr auto; gap:12px; align-items:end;">
            <div>
                <label style="font-size:12px; color:var(--text-muted); display:block; margin-bottom:4px;">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Título o empresa..." style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
            </div>
            <button type="submit" class="btn btn-primary" style="height:38px;">Filtrar</button>
        </form>
    </div>

    @if($vacantes->count())
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:16px;">
            @foreach($vacantes as $vacante)
                <div class="card fade-in" style="display:flex; flex-direction:column; min-height:240px;">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:12px;">
                        <div>
                            <h3 style="font-size:1.05rem; font-weight:600; margin-bottom:4px;">{{ $vacante->titulo }}</h3>
                            <span class="badge badge-blue">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($vacante->nivel_jerarquico) }}</span>
                        </div>
                        @if($vacante->empresa)
                            <div class="metric-icon" style="background:var(--accent-light); color:var(--accent); width:36px; height:36px;">
                                <span style="font-weight:700; font-size:0.85rem;">{{ strtoupper(substr($vacante->empresa->nombre_empresa ?? 'E', 0, 1)) }}</span>
                            </div>
                        @endif
                    </div>

                    <p class="text-muted text-sm" style="margin-bottom:12px; flex:1;">
                        {{ \Illuminate\Support\Str::limit($vacante->descripcion, 120) }}
                    </p>

                    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px;">
                        @if($vacante->nivel_estudios_minimo)
                            <span class="badge" style="background:rgba(59,130,246,.12); color:#3b82f6;">{{ \App\Models\Vacante::nivelEstudiosLabel($vacante->nivel_estudios_minimo) }}</span>
                        @endif
                        @if($vacante->experiencia_minima !== null)
                            <span class="badge" style="background:rgba(16,185,129,.12); color:#10b981;">{{ $vacante->experiencia_minima }} año(s)</span>
                        @endif
                        @if($vacante->ubicacion)
                            <span class="badge" style="background:rgba(245,158,11,.12); color:#d97706;">{{ $vacante->ubicacion }}</span>
                        @endif
                    </div>

                    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:auto;">
                        <button type="button" class="btn btn-secondary" onclick="rhModal('{{ route('candidato.vacantes.modal', $vacante) }}')">
                            Ver detalle
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top:24px;">
            {{ $vacantes->links() }}
        </div>
    @else
        <div class="card fade-in" style="text-align:center; padding:60px 40px;">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:48px; height:48px; color:var(--text-muted); margin-bottom:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/>
            </svg>
            <h3 style="font-weight:600; margin-bottom:8px;">No hay solicitudes disponibles</h3>
            <p class="text-muted text-sm">Vuelve más tarde para encontrar nuevas oportunidades.</p>
        </div>
    @endif
</x-app-layout>
