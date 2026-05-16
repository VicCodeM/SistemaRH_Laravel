<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Candidato</span>
        </nav>
        <h1 class="page-title">{{ $titulo ?? 'Acceso pendiente' }}</h1>
        <p class="page-subtitle">Tu cuenta está registrada, pero todavía no puedes continuar con la solicitud.</p>
    </x-slot>

    <div class="card fade-in" style="max-width:820px; margin:0 auto; padding:28px;">
        <div style="display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap;">
            <div style="width:54px; height:54px; border-radius:16px; background:rgba(245,158,11,.12); color:#f59e0b; display:flex; align-items:center; justify-content:center; font-size:1.4rem; font-weight:800;">
                !
            </div>

            <div style="flex:1; min-width:240px;">
                <h2 style="margin:0 0 6px; font-size:1.2rem; font-weight:800;">{{ $mensaje ?? 'Tu acceso está pendiente de aprobación.' }}</h2>
                <p style="margin:0; color:#64748b; line-height:1.65;">
                    {{ $detalle ?? 'El admin debe validar tu cuenta antes de que puedas completar tu solicitud y continuar con el proceso.' }}
                </p>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-top:20px;">
            <div style="padding:14px 16px; border:1px solid var(--border); border-radius:12px; background:var(--surface-2);">
                <div style="font-weight:700; margin-bottom:4px;">1. Registro</div>
                <div style="font-size:0.88rem; color:#64748b;">Tu acceso ya quedó creado.</div>
            </div>
            <div style="padding:14px 16px; border:1px solid var(--border); border-radius:12px; background:var(--surface-2);">
                <div style="font-weight:700; margin-bottom:4px;">2. Aprobación</div>
                <div style="font-size:0.88rem; color:#64748b;">El admin debe activarte si la configuración lo exige.</div>
            </div>
            <div style="padding:14px 16px; border:1px solid var(--border); border-radius:12px; background:var(--surface-2);">
                <div style="font-weight:700; margin-bottom:4px;">3. Solicitud</div>
                <div style="font-size:0.88rem; color:#64748b;">Cuando te activen podrás completar tu expediente.</div>
            </div>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:22px;">
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form-pendiente').submit();"
               class="btn btn-primary">
                Cerrar sesión
            </a>
            <form id="logout-form-pendiente" method="POST" action="{{ route('logout') }}" style="display:none;">
                @csrf
            </form>
            <a href="{{ route('login') }}" class="btn btn-secondary">Volver al inicio de sesión</a>
        </div>
    </div>
</x-app-layout>
