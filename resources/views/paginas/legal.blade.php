@extends('layouts.landing')

@section('title', $titulo . ' — ' . ($sitio['sitio_nombre'] ?? 'SistemaRH'))
@section('meta_description', $titulo . ' de ' . ($sitio['sitio_nombre'] ?? 'SistemaRH') . '. Conoce cómo tratamos tu información y las condiciones de uso de la plataforma.')

@section('content')
<div class="lp-wrap">

    {{-- NAV --}}
    <header class="lp-nav">
        @php $marca = \App\Services\SitioService::partirMarca($sitio['sitio_nombre'] ?? 'SistemaRH'); @endphp
        <a href="/" class="lp-logo">{{ $marca['base'] }}<span>{{ $marca['acento'] }}</span></a>
        <div class="lp-nav-actions">
            <a href="/" class="btn btn-ghost lp-btn-ghost">← Volver al inicio</a>
        </div>
    </header>

    <section class="lp-legal">
        <h1 class="lp-legal-title">{{ $titulo }}</h1>

        @if(trim($contenido) === '')
            <p class="lp-legal-empty">
                El contenido de esta página aún no ha sido configurado.
            </p>
        @else
            <div class="lp-legal-body">{!! nl2br(e($contenido)) !!}</div>
        @endif
    </section>

    {{-- FOOTER --}}
    <footer class="lp-footer">
        <p>&copy; {{ date('Y') }} {{ $sitio['landing_footer'] ?? 'SistemaRH. Todos los derechos reservados.' }}</p>
        <p class="lp-footer-links">
            <a href="{{ route('paginas.privacidad') }}">Políticas de privacidad</a>
            <span>·</span>
            <a href="{{ route('paginas.terminos') }}">Términos del servicio</a>
        </p>
    </footer>

</div>

<style>
:root {
    --lp-bg: #0f172a; --lp-border: rgba(255,255,255,.08);
    --lp-text: #f1f5f9; --lp-muted: #94a3b8; --lp-accent: #3b82f6;
}
* { box-sizing: border-box; }
body.landing-page { background: var(--lp-bg); color: var(--lp-text); margin: 0; font-family: 'Inter', system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
.lp-wrap { min-height: 100vh; display: flex; flex-direction: column; max-width: 820px; margin: 0 auto; padding: 0 24px; }
.lp-nav { display: flex; align-items: center; justify-content: space-between; padding: 20px 0; border-bottom: 1px solid var(--lp-border); }
.lp-logo { font-size: 1.2rem; font-weight: 800; color: #fff; text-decoration: none; letter-spacing: -.5px; }
.lp-btn-ghost { background: transparent; border: 1px solid var(--lp-border); color: var(--lp-muted); padding: 7px 16px; border-radius: 8px; text-decoration: none; font-size: .82rem; font-weight: 500; transition: all .18s ease; }
.lp-btn-ghost:hover { border-color: rgba(255,255,255,.2); color: #fff; }
.lp-legal { padding: 48px 0; flex: 1; }
.lp-legal-title { font-size: clamp(1.6rem, 4vw, 2.2rem); font-weight: 800; letter-spacing: -1px; margin: 0 0 24px; color: #fff; }
.lp-legal-body { color: #cbd5e1; font-size: .95rem; line-height: 1.8; }
.lp-legal-empty { color: var(--lp-muted); font-size: .95rem; }
.lp-footer { border-top: 1px solid var(--lp-border); padding: 24px 0; text-align: center; }
.lp-footer p { color: #475569; font-size: .75rem; margin: 0; }
.lp-footer-links { margin-top: 8px !important; display: flex; gap: 10px; justify-content: center; align-items: center; }
.lp-footer-links a { color: var(--lp-muted); text-decoration: none; transition: color .18s; }
.lp-footer-links a:hover { color: #fff; }
</style>
@endsection
