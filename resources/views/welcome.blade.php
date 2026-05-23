@extends('layouts.landing')

@section('title', ($sitio['sitio_nombre'] ?? 'SistemaRH') . (!empty($sitio['sitio_subtitulo']) ? ' — ' . $sitio['sitio_subtitulo'] : ''))

@section('content')
<div class="lp-wrap">

    {{-- NAV --}}
    <header class="lp-nav">
        @php $marca = \App\Services\SitioService::partirMarca($sitio['sitio_nombre'] ?? 'SistemaRH'); @endphp
        <a href="/" class="lp-logo">{{ $marca['base'] }}<span>{{ $marca['acento'] }}</span></a>
        <div class="lp-nav-actions">
            <a href="{{ route('login') }}" class="btn btn-ghost lp-btn-ghost">Iniciar sesión</a>
        </div>
    </header>

    {{-- HERO --}}
    <section class="lp-hero">
        @if(!empty($sitio['landing_hero_badge']))
            <div class="lp-hero-badge">{{ $sitio['landing_hero_badge'] }}</div>
        @endif
        <h1 class="lp-hero-title">
            {!! nl2br(e($sitio['landing_hero_titulo'] ?? '')) !!}
            @if(!empty($sitio['landing_hero_acento']))
                <br><span class="lp-hero-accent">{{ $sitio['landing_hero_acento'] }}</span>
            @endif
        </h1>
        <p class="lp-hero-sub">
            {!! nl2br(e($sitio['landing_hero_subtitulo'] ?? '')) !!}
        </p>

        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg lp-cta-btn">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                Ir a mi panel
            </a>
        @else
            <div class="lp-hero-cta">
                <a href="{{ route('register.empresa') }}" class="btn btn-primary btn-lg lp-cta-btn">
                    Registrar empresa
                </a>
                <a href="{{ route('register.candidato') }}" class="lp-cta-outline">
                    Soy candidato →
                </a>
            </div>
            <p class="lp-hero-hint">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
        @endauth
    </section>

    {{-- ROLE CARDS --}}
    @guest
    <section class="lp-roles">
        <a href="{{ route('register.empresa') }}" class="lp-role-card lp-role-empresa">
            <div class="lp-role-icon">
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                </svg>
            </div>
            <div>
                <h3>Soy empresa</h3>
                <p>Solicita servicios de RH, publica vacantes y gestiona tu talento con aprobación de admin.</p>
            </div>
            <span class="lp-role-cta">Registrar empresa →</span>
        </a>

        <a href="{{ route('register.candidato') }}" class="lp-role-card lp-role-candidato">
            <div class="lp-role-icon">
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                </svg>
            </div>
            <div>
                <h3>Soy candidato</h3>
                <p>Crea tu perfil profesional, postúlate a vacantes y da seguimiento a tu proceso de selección.</p>
            </div>
            <span class="lp-role-cta">Crear mi perfil →</span>
        </a>
    </section>
    @endguest

    {{-- FEATURES --}}
    @php
        $lpFeatures = [
            ['icono' => 'lp-fi-blue',   'svg' => 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0'],
            ['icono' => 'lp-fi-green',  'svg' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0Zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
            ['icono' => 'lp-fi-purple', 'svg' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z'],
            ['icono' => 'lp-fi-teal',   'svg' => 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155'],
            ['icono' => 'lp-fi-rose',   'svg' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'],
        ];
    @endphp
    <section class="lp-features">
        @if(!empty($sitio['landing_feat_label']))
            <p class="lp-features-label">{{ $sitio['landing_feat_label'] }}</p>
        @endif
        <div class="lp-features-grid">
            @foreach($lpFeatures as $i => $feat)
                @php
                    $n = $i + 1;
                    $featTitulo = $sitio['landing_feat_' . $n . '_titulo'] ?? '';
                    $featTexto  = $sitio['landing_feat_' . $n . '_texto'] ?? '';
                @endphp
                @if($featTitulo !== '' || $featTexto !== '')
                    <div class="lp-feature">
                        <div class="lp-feature-icon {{ $feat['icono'] }}">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feat['svg'] }}"/>
                            </svg>
                        </div>
                        <h4>{{ $featTitulo }}</h4>
                        <p>{{ $featTexto }}</p>
                    </div>
                @endif
            @endforeach
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="lp-footer">
        <p>&copy; {{ date('Y') }} {{ $sitio['landing_footer'] ?? 'SistemaRH. Todos los derechos reservados.' }}</p>
        <p class="lp-footer-links">
            <a href="{{ route('paginas.privacidad') }}">Políticas de privacidad</a>
            <span>·</span>
            <a href="{{ route('paginas.terminos') }}">Términos del servicio</a>
            <span>·</span>
            <span>v{{ config('app.version') }}</span>
        </p>
    </footer>

</div>

<style>
/* Landing page — estilos autónomos para robustez */
:root {
    --lp-bg: #0f172a;
    --lp-bg2: #111827;
    --lp-border: rgba(255,255,255,.08);
    --lp-text: #f1f5f9;
    --lp-muted: #94a3b8;
    --lp-accent: #3b82f6;
    --lp-accent2: #6366f1;
}
* { box-sizing: border-box; }
body.landing-page { background: var(--lp-bg); color: var(--lp-text); margin: 0; font-family: 'Inter', system-ui, sans-serif; -webkit-font-smoothing: antialiased; }

.lp-wrap { min-height: 100vh; display: flex; flex-direction: column; max-width: 960px; margin: 0 auto; padding: 0 24px; }

/* Nav */
.lp-nav { display: flex; align-items: center; justify-content: space-between; padding: 20px 0; border-bottom: 1px solid var(--lp-border); }
.lp-logo { font-size: 1.2rem; font-weight: 800; color: #fff; text-decoration: none; letter-spacing: -.5px; }
.lp-logo span { color: var(--lp-accent); }
.lp-btn-ghost { background: transparent; border: 1px solid var(--lp-border); color: var(--lp-muted); padding: 7px 16px; border-radius: 8px; text-decoration: none; font-size: .82rem; font-weight: 500; transition: all .18s ease; }
.lp-btn-ghost:hover { border-color: rgba(255,255,255,.2); color: #fff; }

/* Hero */
.lp-hero { text-align: center; padding: 72px 0 56px; }
.lp-hero-badge { display: inline-block; padding: 5px 14px; border-radius: 20px; border: 1px solid rgba(99,102,241,.4); background: rgba(99,102,241,.1); color: #a5b4fc; font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 28px; }
.lp-hero-title { font-size: clamp(2rem, 5vw, 3rem); font-weight: 800; line-height: 1.15; letter-spacing: -1.5px; margin: 0 0 20px; color: #fff; }
.lp-hero-accent { background: linear-gradient(135deg, #3b82f6, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.lp-hero-sub { color: var(--lp-muted); font-size: 1.05rem; line-height: 1.7; margin: 0 0 36px; }
.lp-hero-cta { display: flex; align-items: center; justify-content: center; gap: 20px; margin-bottom: 16px; flex-wrap: wrap; }
.lp-cta-btn { padding: 12px 28px; font-size: .95rem; border-radius: 10px; background: linear-gradient(135deg, #2563eb, #6366f1); color: #fff; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all .2s ease; box-shadow: 0 4px 20px rgba(37,99,235,.35); }
.lp-cta-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(37,99,235,.45); }
.lp-cta-outline { color: var(--lp-muted); text-decoration: none; font-weight: 500; font-size: .9rem; transition: color .18s; }
.lp-cta-outline:hover { color: #fff; }
.lp-hero-hint { color: var(--lp-muted); font-size: .82rem; margin: 0; }
.lp-hero-hint a { color: var(--lp-accent); text-decoration: none; font-weight: 500; }

/* Role cards */
.lp-roles { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 60px; }
.lp-role-card { display: flex; flex-direction: column; gap: 12px; padding: 28px 24px; border-radius: 16px; border: 1px solid var(--lp-border); text-decoration: none; transition: all .2s ease; }
.lp-role-card:hover { transform: translateY(-3px); }
.lp-role-empresa { background: rgba(37,99,235,.07); border-color: rgba(37,99,235,.25); }
.lp-role-empresa:hover { border-color: rgba(37,99,235,.5); box-shadow: 0 12px 32px rgba(37,99,235,.15); }
.lp-role-candidato { background: rgba(16,185,129,.07); border-color: rgba(16,185,129,.25); }
.lp-role-candidato:hover { border-color: rgba(16,185,129,.5); box-shadow: 0 12px 32px rgba(16,185,129,.12); }
.lp-role-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.lp-role-empresa .lp-role-icon { background: rgba(37,99,235,.15); color: #60a5fa; }
.lp-role-candidato .lp-role-icon { background: rgba(16,185,129,.15); color: #34d399; }
.lp-role-card h3 { margin: 0; font-size: 1.05rem; font-weight: 700; color: #fff; }
.lp-role-card p { margin: 0; color: var(--lp-muted); font-size: .85rem; line-height: 1.55; }
.lp-role-cta { font-size: .8rem; font-weight: 600; margin-top: auto; }
.lp-role-empresa .lp-role-cta { color: #60a5fa; }
.lp-role-candidato .lp-role-cta { color: #34d399; }

/* Features */
.lp-features { margin-bottom: 64px; }
.lp-features-label { text-align: center; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: var(--lp-muted); margin: 0 0 28px; }
.lp-features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.lp-feature { background: rgba(255,255,255,.03); border: 1px solid var(--lp-border); border-radius: 14px; padding: 22px 20px; transition: all .2s ease; }
.lp-feature:hover { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.12); }
.lp-feature-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; }
.lp-fi-blue   { background: rgba(59,130,246,.15);  color: #60a5fa; }
.lp-fi-green  { background: rgba(16,185,129,.15);  color: #34d399; }
.lp-fi-purple { background: rgba(139,92,246,.15);  color: #a78bfa; }
.lp-fi-orange { background: rgba(245,158,11,.15);  color: #fbbf24; }
.lp-fi-teal   { background: rgba(20,184,166,.15);  color: #2dd4bf; }
.lp-fi-rose   { background: rgba(244,63,94,.15);   color: #fb7185; }
.lp-feature h4 { color: #e2e8f0; font-size: .88rem; font-weight: 600; margin: 0 0 6px; }
.lp-feature p  { color: var(--lp-muted); font-size: .78rem; line-height: 1.55; margin: 0; }

/* Footer */
.lp-footer { border-top: 1px solid var(--lp-border); padding: 24px 0; text-align: center; margin-top: auto; }
.lp-footer p { color: #475569; font-size: .75rem; margin: 0; }
.lp-footer-links { margin-top: 8px !important; display: flex; gap: 10px; justify-content: center; align-items: center; }
.lp-footer-links a { color: var(--lp-muted); text-decoration: none; transition: color .18s; }
.lp-footer-links a:hover { color: #fff; }

/* Responsive */
@media (max-width: 640px) {
    .lp-hero { padding: 48px 0 40px; }
    .lp-roles { grid-template-columns: 1fr; }
    .lp-features-grid { grid-template-columns: 1fr 1fr; }
    .lp-hero-title { letter-spacing: -1px; }
}
@media (max-width: 400px) {
    .lp-features-grid { grid-template-columns: 1fr; }
}
</style>
@endsection
