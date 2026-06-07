<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Seguridad</span>
        </nav>
        <h1 class="page-title">Sesión detectada en otro dispositivo</h1>
        <p class="page-subtitle">
            Antes de continuar, revisa las sesiones activas y decide si quieres cerrarlas o mantenerlas abiertas.
        </p>
    </x-slot>

    <div class="card fade-in" style="max-width: 980px; margin: 0 auto; padding: 28px;">
        <div style="display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap;">
            <div style="width:54px; height:54px; border-radius:16px; background:rgba(59,130,246,.12); color:#3b82f6; display:flex; align-items:center; justify-content:center; font-size:1.4rem; font-weight:800;">
                !
            </div>

            <div style="flex:1; min-width:240px;">
                <h2 style="margin:0 0 6px; font-size:1.2rem; font-weight:800;">
                    Detectamos {{ $sesionesDetectadas }} {{ $sesionesDetectadas === 1 ? 'sesión activa' : 'sesiones activas' }} en otros dispositivos.
                </h2>
                <p style="margin:0; color:#64748b; line-height:1.65;">
                    Si reconoces estos accesos, puedes continuar sin tocar nada.
                    Si no los reconoces, te recomendamos cerrar las demás sesiones para proteger tu cuenta.
                </p>
            </div>
        </div>

        <div style="margin-top:22px; padding:16px; border:1px solid var(--border); border-radius:12px; background:var(--surface-2);">
            <div style="font-weight:700; margin-bottom:6px;">¿Qué quieres hacer?</div>
            <div style="font-size:0.92rem; color:#64748b; line-height:1.6;">
                Cerrar las otras sesiones invalidará los accesos anteriores en otros dispositivos.
                La sesión actual permanecerá abierta y podrás continuar a tu destino habitual.
            </div>
        </div>

        <div style="margin-top:24px; display:grid; gap:14px;">
            @forelse ($sesiones as $sesion)
                <article style="border:1px solid var(--border); border-radius:16px; padding:18px; background:var(--surface); box-shadow:0 10px 30px rgba(15,23,42,.04);">
                    <div style="display:flex; justify-content:space-between; gap:14px; flex-wrap:wrap; align-items:flex-start;">
                        <div>
                            <div style="font-size:1.02rem; font-weight:800; color:#0f172a;">{{ $sesion['dispositivo'] }}</div>
                            <div style="margin-top:4px; font-size:0.9rem; color:#64748b;">
                                {{ $sesion['navegador'] }} · {{ $sesion['sistema_operativo'] }}
                            </div>
                        </div>

                        <div style="padding:7px 12px; border-radius:999px; background:rgba(37,99,235,.1); color:#2563eb; font-weight:700; font-size:0.82rem;">
                            Última actividad: {{ $sesion['ultima_actividad_humana'] }}
                        </div>
                    </div>

                    <div style="margin-top:16px; display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px;">
                        <div>
                            <div style="font-size:0.78rem; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; font-weight:700;">IP</div>
                            <div style="margin-top:4px; font-weight:700; color:#0f172a; font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                                {{ $sesion['ip_address'] }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size:0.78rem; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; font-weight:700;">Navegador</div>
                            <div style="margin-top:4px; font-weight:700; color:#0f172a;">
                                {{ $sesion['navegador'] }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size:0.78rem; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; font-weight:700;">Sistema</div>
                            <div style="margin-top:4px; font-weight:700; color:#0f172a;">
                                {{ $sesion['sistema_operativo'] }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size:0.78rem; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; font-weight:700;">Fecha y hora</div>
                            <div style="margin-top:4px; font-weight:700; color:#0f172a;">
                                {{ $sesion['ultima_actividad_formateada'] }}
                            </div>
                        </div>
                    </div>

                    @if (! empty($sesion['user_agent']))
                        <div style="margin-top:14px; padding:12px 14px; border-radius:12px; background:#f8fafc; border:1px solid #e2e8f0; color:#475569; font-size:0.84rem; word-break:break-word;">
                            {{ $sesion['user_agent'] }}
                        </div>
                    @endif
                </article>
            @empty
                <div style="padding:16px; border:1px dashed var(--border); border-radius:12px; color:#64748b; background:var(--surface-2);">
                    No se pudo leer el detalle de las otras sesiones, pero sí detectamos accesos activos en tu cuenta.
                </div>
            @endforelse
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:24px;">
            <form method="POST" action="{{ route('sesiones.cerrar-otras') }}">
                @csrf
                <button type="submit" class="btn btn-primary">
                    Cerrar otras sesiones
                </button>
            </form>

            <form method="POST" action="{{ route('sesiones.continuar') }}">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    Continuar sin cerrar
                </button>
            </form>
        </div>

        <div style="margin-top:18px; font-size:0.85rem; color:#64748b;">
            Si no reconoces un acceso, también conviene cambiar tu contraseña desde
            <a href="{{ route('profile.edit') }}" style="color:var(--accent); font-weight:600;">Mi cuenta</a>.
        </div>
    </div>
</x-app-layout>
