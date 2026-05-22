@php
    $empresaActual = Auth::user()?->empresa;
    $empresaActiva = $empresaActual?->estado === 'activa';
    $candidatoActual = Auth::user()?->candidato;
    $candidatoAprobado = $candidatoActual?->solicitud_estado === 'aprobada';
    $rol = Auth::user()?->rol;

    // Contadores para badges del sidebar
    $internoPendientes = 0;
    if ($rol === 'interno') {
        $internoPendientes = Auth::user()
            ->serviciosAsignados()
            ->where('estado', 'activo')
            ->whereNull('fecha_inicio')
            ->count();
    }

    $candidatoNovedades = 0;
    if ($candidatoActual) {
        $candidatoNovedades = $candidatoActual
            ->postulaciones()
            ->whereIn('estado', ['seleccionado', 'rechazado'])
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();
    }

    $empresaNovedades = 0;
    if ($empresaActual) {
        $empresaNovedades = \App\Models\Postulacion::whereHas(
            'vacante',
            fn ($q) => $q->where('empresa_id', $empresaActual->id)
        )
        ->where('estado', 'postulado')
        ->where('created_at', '>=', now()->subDays(7))
        ->count();
    }
@endphp

<nav class="sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}">
            <h2>{{ $sitio['sitio_nombre'] ?? 'SistemaRH' }}</h2>
            <span class="sidebar-subtitle">Panel {{ ucfirst($rol ?? '') }}</span>
        </a>
    </div>

    @if($rol === 'admin')
        <div style="padding:12px 16px; border-bottom:1px solid rgba(255,255,255,0.06);">
            <button type="button" onclick="window.rhBuscador && window.rhBuscador.abrir()"
                style="display:flex; gap:6px; align-items:center; background:rgba(255,255,255,0.08); border:none; border-radius:8px; padding:8px 10px; width:100%; cursor:pointer;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;color:#94a3b8;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <span style="color:#94a3b8; font-size:13px; flex:1; text-align:left;">Buscar...</span>
                <kbd style="background:rgba(255,255,255,0.12); color:#cbd5e1; font-size:10px; padding:2px 6px; border-radius:4px; font-family:inherit;">Ctrl K</kbd>
            </button>
        </div>
    @endif
    <div class="sidebar-nav">
        @if($rol === 'admin')
            <a wire:navigate href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z"/></svg>
                Panel
            </a>

            <div class="sidebar-label">Acceso a la plataforma</div>
            <p class="sidebar-hint" style="font-size:10px; color:#94a3b8; margin:0 0 6px 14px; line-height:1.3;">Aprobar cuentas y perfiles</p>
            <a wire:navigate href="{{ route('admin.empresas') }}" class="sidebar-link {{ request()->routeIs('admin.empresas*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                Empresas
            </a>
            <a wire:navigate href="{{ route('admin.candidatos') }}" class="sidebar-link {{ request()->routeIs('admin.candidatos*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                Candidatos
            </a>

            <div class="sidebar-label">Gestión operativa</div>
            <p class="sidebar-hint" style="font-size:10px; color:#94a3b8; margin:0 0 6px 14px; line-height:1.3;">Asignar y dar seguimiento</p>
            <a wire:navigate href="{{ route('admin.vacantes') }}" class="sidebar-link {{ request()->routeIs('admin.vacantes*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                Vacantes (reclutamiento)
            </a>
            <a wire:navigate href="{{ route('admin.tareas.index') }}" class="sidebar-link {{ request()->routeIs('admin.tareas*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                Pedidos de servicio
            </a>
            <a wire:navigate href="{{ route('admin.personal-interno.index') }}" class="sidebar-link {{ request()->routeIs('admin.personal-interno*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                Personal interno
            </a>

            <div class="sidebar-label">Análisis</div>
            <a wire:navigate href="{{ route('admin.reportes') }}" class="sidebar-link {{ request()->routeIs('admin.reportes*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                Reportes
            </a>

            <div class="sidebar-label">Sistema</div>
            <a wire:navigate href="{{ route('admin.catalogos.index') }}" class="sidebar-link {{ request()->routeIs('admin.catalogos*', 'admin.catalogo*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>
                Catálogos
            </a>
            <a wire:navigate href="{{ route('admin.configuracion') }}" class="sidebar-link {{ request()->routeIs('admin.configuracion*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                Configuración
            </a>
        @endif

        @if($rol === 'empresa')
            <div class="sidebar-label">Servicios RH</div>
            <a wire:navigate href="{{ route('empresa.dashboard') }}" class="sidebar-link {{ request()->routeIs('empresa.dashboard') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                {{ $empresaActiva ? 'Panel de empresa' : 'Estado de aprobación' }}
            </a>
            @if($empresaActiva)
                <a wire:navigate href="{{ route('empresa.solicitudes') }}" class="sidebar-link {{ request()->routeIs('empresa.solicitudes*', 'empresa.vacantes*') ? 'active' : '' }}" style="position:relative;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    Vacantes
                    @if($empresaNovedades > 0)
                        <span style="background:#3b82f6; color:#fff; font-size:10px; font-weight:700; padding:1px 7px; border-radius:10px; margin-left:auto;" title="Postulaciones nuevas">{{ $empresaNovedades }}</span>
                    @endif
                </a>
                <a wire:navigate href="{{ route('empresa.servicios.index') }}" class="sidebar-link {{ request()->routeIs('empresa.servicios*') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                    Servicios solicitados
                </a>
            @endif
        @endif

        @if($rol === 'candidato')
            <div class="sidebar-label">Mi búsqueda</div>
            <a wire:navigate href="{{ route('candidato.dashboard') }}" class="sidebar-link {{ request()->routeIs('candidato.dashboard') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.126 1.126 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                Inicio
            </a>
            <a wire:navigate href="{{ route('candidato.solicitud') }}" class="sidebar-link {{ request()->routeIs('candidato.solicitud*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                Mi solicitud
            </a>
            @if($candidatoAprobado)
                <a wire:navigate href="{{ route('candidato.vacantes') }}" class="sidebar-link {{ request()->routeIs('candidato.vacantes*') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    Vacantes
                </a>
                <a wire:navigate href="{{ route('candidato.postulaciones') }}" class="sidebar-link {{ request()->routeIs('candidato.postulaciones*') ? 'active' : '' }}" style="position:relative;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></svg>
                    Seguimiento
                    @if($candidatoNovedades > 0)
                        <span style="background:#22c55e; color:#fff; font-size:10px; font-weight:700; padding:1px 7px; border-radius:10px; margin-left:auto;" title="Tienes novedades">{{ $candidatoNovedades }}</span>
                    @endif
                </a>
                <a wire:navigate href="{{ route('candidato.servicios.index') }}" class="sidebar-link {{ request()->routeIs('candidato.servicios*') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                    Mis servicios
                </a>
            @endif
        @endif

        @if($rol === 'interno')
            <div class="sidebar-label">Operaciones</div>
            <a wire:navigate href="{{ route('interno.dashboard') }}" class="sidebar-link {{ request()->routeIs('interno.dashboard') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.126 1.126 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                Inicio
            </a>
            <a wire:navigate href="{{ route('interno.tareas.index') }}" class="sidebar-link {{ request()->routeIs('interno.tareas*') ? 'active' : '' }}" style="position:relative;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                Mis tareas
                @if($internoPendientes > 0)
                    <span style="background:#ef4444; color:#fff; font-size:10px; font-weight:700; padding:1px 7px; border-radius:10px; margin-left:auto;">{{ $internoPendientes }}</span>
                @endif
            </a>
        @endif

        <div class="sidebar-label">Comunicación</div>
        <a wire:navigate href="{{ route('chat.index') }}" class="sidebar-link {{ request()->routeIs('chat.*') ? 'active' : '' }}" style="position:relative;">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
            Chat
            <livewire:chat.chat-notificaciones />
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            @php $u = Auth::user(); @endphp
            @if($u?->avatar_url)
                <img src="{{ asset('storage/' . $u->avatar_url) }}" alt="{{ $u->name }}"
                     style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:1px solid rgba(255,255,255,.15);">
            @else
                <div class="sidebar-avatar">{{ strtoupper(substr($u?->name ?? 'U', 0, 1)) }}</div>
            @endif
            <div class="sidebar-user-info">
                <p class="sidebar-user-name">{{ Auth::user()?->name ?? 'Usuario' }}</p>
                <p class="sidebar-user-role">{{ $rol ?? 'invitado' }}</p>
                @if($rol === 'empresa' && $empresaActual)
                    <p class="sidebar-user-role" style="margin-top:2px; color:{{ $empresaActiva ? '#22c55e' : '#f59e0b' }};">
                        {{ $empresaActiva ? 'Empresa aprobada' : 'En revisión' }}
                    </p>
                @endif
                @if($rol === 'candidato' && $candidatoActual)
                    <p class="sidebar-user-role" style="margin-top:2px; color:{{ $candidatoAprobado ? '#22c55e' : '#f59e0b' }};">
                        {{ \App\Models\Candidato::solicitudEstadoLabel($candidatoActual->solicitud_estado) }}
                    </p>
                @endif
            </div>
        </div>

        <a href="{{ route('profile.edit') }}" class="sidebar-link" style="margin-top:6px;" title="Edita tu nombre, correo, contraseña y foto">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
            Mi cuenta
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-logout">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                Cerrar sesión
            </button>
        </form>
    </div>
</nav>

@if($rol === 'admin')
    @include('partials.buscador-rapido')
@endif
