<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Empresa</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>En revisión</span>
        </nav>
        <h1 class="page-title">{{ $empresa->nombre_empresa }}</h1>
        <p class="page-subtitle">Tu cuenta está esperando aprobación.</p>
    </x-slot>

    @if(session('warning'))
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--warning-light); color:var(--warning); border-radius:8px; border-left:4px solid var(--warning);">
            {{ session('warning') }}
        </div>
    @endif

    <div class="card fade-in" style="max-width:760px; margin:0 auto; padding:28px;">
        <div style="display:flex; align-items:flex-start; gap:18px; flex-wrap:wrap;">
            <div style="width:56px; height:56px; border-radius:16px; background:var(--warning-light); color:var(--warning); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:28px; height:28px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4.5m0 3.75h.007v.008H12v-.008zm-.375-15.75h.75a.75.75 0 01.75.75l.3 3.75a.75.75 0 01-.75.75h-1.35a.75.75 0 01-.75-.75l.3-3.75a.75.75 0 01.75-.75z" />
                </svg>
            </div>

            <div style="flex:1; min-width:240px;">
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:8px;">
                    <h2 style="margin:0; font-size:1.35rem; font-weight:800;">Empresa en revisión</h2>
                    <span class="badge {{ \App\Models\Empresa::estadoBadgeClass($empresa->estado) }}">{{ \App\Models\Empresa::estadoLabel($empresa->estado) }}</span>
                </div>

                <p style="margin:0 0 14px; color:#64748b; line-height:1.6;">
                    {{ $empresa->nombre_empresa }} todavía no ha sido aprobada por el administrador. Mientras tanto, solo verás esta pantalla para evitar confusiones y mantener el flujo simple.
                </p>

                <div style="display:grid; gap:10px; margin-bottom:18px; color:#475569; font-size:0.9rem;">
                    <div><strong style="color:#0f172a;">Responsable:</strong> {{ $empresa->usuario?->name ?? 'Sin definir' }}</div>
                    <div><strong style="color:#0f172a;">Correo:</strong> {{ $empresa->usuario?->email ?? 'Sin definir' }}</div>
                    <div><strong style="color:#0f172a;">RFC:</strong> {{ $empresa->rfc ?: 'No capturado' }}</div>
                    <div><strong style="color:#0f172a;">Ciudad:</strong> {{ $empresa->ciudad ?: 'No capturada' }}</div>
                </div>

                <div style="padding:14px 16px; border-radius:12px; background:rgba(37,99,235,.06); border:1px solid rgba(37,99,235,.14); color:#1e3a8a; font-size:0.88rem; line-height:1.6;">
                    En cuanto el administrador apruebe tu empresa, aquí aparecerá el panel completo con solicitudes, seguimiento y acceso a los módulos operativos.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
