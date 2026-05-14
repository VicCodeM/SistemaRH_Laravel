<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SistemaRH - Gestión de Talento</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
        <div class="fade-in" style="max-width: 600px; text-align: center;">
            <h1 style="font-size: 3rem; font-weight: 800; color: #fff; margin-bottom: 4px; letter-spacing: -1px;">
                Sistema<span style="color: #3b82f6;">RH</span>
            </h1>
            <p style="color: #3b82f6; font-size: 0.8rem; letter-spacing: 3px; text-transform: uppercase; font-weight: 600; margin-bottom: 32px;">Plataforma de Gestión de Talento</p>

            <p style="color: #94a3b8; font-size: 1.05rem; margin-bottom: 40px; line-height: 1.7;">
                Reclutamiento, seguimiento de candidatos y automatización de procesos<br>
                en una plataforma moderna e inteligente.
            </p>

            @auth
            <div style="display: flex; gap: 16px; justify-content: center;">
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Ir al Dashboard</a>
            </div>
            @else
            {{-- Two registration paths --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 560px; margin: 0 auto 32px;">
                <a href="{{ route('register.empresa') }}" style="display: block; background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px 20px; text-decoration: none; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='#334155'">
                    <div style="width: 40px; height: 40px; background: rgba(37,99,235,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#60a5fa" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                    <h4 style="color: #f1f5f9; font-size: 1rem; font-weight: 700; margin-bottom: 6px;">Soy Empresa</h4>
                    <p style="color: #64748b; font-size: 0.82rem; line-height: 1.5; margin-bottom: 16px;">Solicita servicios de RH, publica vacantes y gestiona tu talento.</p>
                    <span style="font-size: 0.8rem; color: #60a5fa; font-weight: 600;">Registrar empresa →</span>
                </a>

                <a href="{{ route('register.candidato') }}" style="display: block; background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px 20px; text-decoration: none; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#10b981'" onmouseout="this.style.borderColor='#334155'">
                    <div style="width: 40px; height: 40px; background: rgba(16,185,129,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#34d399" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <h4 style="color: #f1f5f9; font-size: 1rem; font-weight: 700; margin-bottom: 6px;">Soy Candidato</h4>
                    <p style="color: #64748b; font-size: 0.82rem; line-height: 1.5; margin-bottom: 16px;">Crea tu perfil profesional y accede a oportunidades acordes a tu nivel.</p>
                    <span style="font-size: 0.8rem; color: #34d399; font-weight: 600;">Crear mi perfil →</span>
                </a>
            </div>

            <a href="{{ route('login') }}" style="font-size: 0.85rem; color: #64748b; text-decoration: none;">¿Ya tienes cuenta? <span style="color: #60a5fa; font-weight: 600;">Inicia sesión</span></a>
            @endauth

            <div style="margin-top: 56px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; text-align: left;">
                <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(37,99,235,0.15); display: flex; align-items: center; justify-content: center; margin-bottom: 12px; color: #60a5fa;">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387"/></svg>
                    </div>
                    <h4 style="color: #f1f5f9; font-size: 0.95rem; font-weight: 600; margin-bottom: 6px;">Catálogo de Servicios</h4>
                    <p style="color: #64748b; font-size: 0.82rem; line-height: 1.5;">Reclutamiento, capacitación, coaching y más, por nivel jerárquico.</p>
                </div>
                <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(16,185,129,0.15); display: flex; align-items: center; justify-content: center; margin-bottom: 12px; color: #34d399;">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07"/></svg>
                    </div>
                    <h4 style="color: #f1f5f9; font-size: 0.95rem; font-weight: 600; margin-bottom: 6px;">Matching Inteligente</h4>
                    <p style="color: #64748b; font-size: 0.82rem; line-height: 1.5;">El sistema sugiere candidatos compatibles según jerarquía y perfil.</p>
                </div>
                <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(139,92,246,0.15); display: flex; align-items: center; justify-content: center; margin-bottom: 12px; color: #a78bfa;">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                    </div>
                    <h4 style="color: #f1f5f9; font-size: 0.95rem; font-weight: 600; margin-bottom: 6px;">SLA Inteligente</h4>
                    <p style="color: #64748b; font-size: 0.82rem; line-height: 1.5;">Tiempos de respuesta automáticos según prioridad e impacto.</p>
                </div>
            </div>

            <p style="color: #475569; font-size: 0.75rem; margin-top: 48px;">&copy; {{ date('Y') }} SistemaRH. Todos los derechos reservados.</p>
        </div>
    </body>
</html>
