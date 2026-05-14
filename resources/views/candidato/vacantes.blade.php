<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">›</span>
            <span>Vacantes</span>
        </nav>
        <h1 class="page-title">Vacantes Disponibles</h1>
        <p class="page-subtitle">Encuentra la oportunidad que buscas.</p>
    </x-slot>

    @if($vacantes->count())
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
            @foreach($vacantes as $vacante)
                <div class="card fade-in" style="display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px;">
                        <div>
                            <h3 style="font-size: 1.05rem; font-weight: 600; margin-bottom: 4px;">{{ $vacante->titulo }}</h3>
                            <span class="badge badge-blue">{{ $vacante->nivel_jerarquico }}</span>
                        </div>
                        @if($vacante->empresa)
                            <div class="metric-icon" style="background: var(--accent-light); color: var(--accent); width: 36px; height: 36px;">
                                <span style="font-weight: 700; font-size: 0.85rem;">{{ strtoupper(substr($vacante->empresa->nombre ?? 'E', 0, 1)) }}</span>
                            </div>
                        @endif
                    </div>
                    <p class="text-muted text-sm" style="margin-bottom: 12px; flex: 1;">{{ Str::limit($vacante->descripcion, 120) }}</p>
                    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 14px;">
                        @if($vacante->salario_min)
                            <span class="text-xs font-medium" style="color: var(--success);">${{ number_format($vacante->salario_min) }} - ${{ number_format($vacante->salario_max) }}</span>
                        @endif
                        @if($vacante->ubicacion)
                            <span class="text-xs text-muted">📍 {{ $vacante->ubicacion }}</span>
                        @endif
                        @if($vacante->tipo_contrato)
                            <span class="text-xs text-muted">{{ $vacante->tipo_contrato }}</span>
                        @endif
                    </div>
                    <div style="display: flex; gap: 8px; margin-top: auto;">
                        <form method="POST" action="{{ route('candidato.postular', $vacante) }}" style="flex: 1;">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Postularme</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <div style="margin-top: 24px;">
            {{ $vacantes->links() }}
        </div>
    @else
        <div class="card fade-in" style="text-align: center; padding: 60px 40px;">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/>
            </svg>
            <h3 style="font-weight: 600; margin-bottom: 8px;">No hay vacantes disponibles</h3>
            <p class="text-muted text-sm">Vuelve más tarde para encontrar nuevas oportunidades.</p>
        </div>
    @endif
</x-app-layout>
