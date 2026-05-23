{{--
    Layout profesional para vistas imprimibles (guardar como PDF desde el navegador).
    Marca tomada de Configuración del sitio ($sitio, inyectado por el View Composer).

    Variables:
      $titulo    = string         (título del documento)
      $subtitulo = string|null    (subtítulo del documento)
      $tipo      = string|null    (etiqueta: "Reporte", "Ficha", etc.)
      $slot      = HTML body
--}}
@php
    $marca = \App\Services\SitioService::partirMarca($sitio['sitio_nombre'] ?? 'SistemaRH');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }} — {{ $sitio['sitio_nombre'] ?? 'SistemaRH' }}</title>
    <style>
        @page { size: A4; margin: 16mm 14mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif; font-size: 12px; color: #1f2937; line-height: 1.5; background: #fff; padding: 28px 32px; }

        /* Encabezado con marca */
        .doc-header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 14px; border-bottom: 3px solid #2563eb; }
        .doc-brand { display: flex; align-items: center; gap: 12px; }
        .doc-logo { width: 44px; height: 44px; border-radius: 10px; object-fit: contain; background: #f8fafc; border: 1px solid #e2e8f0; }
        .doc-logo-fb { width: 44px; height: 44px; border-radius: 10px; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; }
        .doc-brand-name { font-size: 18px; font-weight: 800; color: #0f172a; letter-spacing: -0.3px; }
        .doc-brand-name span { color: #2563eb; }
        .doc-brand-sub { font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: .12em; margin-top: 2px; }
        .doc-meta { text-align: right; }
        .doc-type { display: inline-block; background: #eff6ff; color: #1e40af; font-weight: 700; font-size: 9px; padding: 3px 10px; border-radius: 999px; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
        .doc-meta .gen { font-size: 10px; color: #94a3b8; }

        /* Título del documento */
        .doc-title { margin: 18px 0; }
        .doc-title h1 { font-size: 20px; color: #0f172a; margin-bottom: 2px; letter-spacing: -0.3px; }
        .doc-title .subtitle { color: #64748b; font-size: 12px; }

        /* Tablas */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 11px; }
        th { background: #f1f5f9; color: #475569; text-align: left; padding: 8px 10px; border-bottom: 2px solid #cbd5e1; font-weight: 700; text-transform: uppercase; font-size: 9.5px; letter-spacing: .04em; }
        td { padding: 7px 10px; border-bottom: 1px solid #e8edf3; vertical-align: top; }
        tr:nth-child(even) td { background: #fafbfc; }

        /* Tarjetas de detalle (fichas) */
        .doc-section { margin-bottom: 18px; }
        .doc-section-title { font-size: 11px; font-weight: 700; color: #2563eb; text-transform: uppercase; letter-spacing: .06em; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px; }
        .doc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 24px; }
        .doc-field { display: flex; gap: 8px; padding: 3px 0; border-bottom: 1px dotted #eef2f7; }
        .doc-field .k { color: #94a3b8; min-width: 130px; font-weight: 500; }
        .doc-field .v { color: #1f2937; font-weight: 500; }

        /* Badges */
        .badge { display: inline-block; padding: 2px 9px; border-radius: 10px; font-size: 10px; font-weight: 700; }
        .b-green  { background: #dcfce7; color: #166534; }
        .b-blue   { background: #dbeafe; color: #1e40af; }
        .b-yellow { background: #fef3c7; color: #92400e; }
        .b-red    { background: #fee2e2; color: #991b1b; }
        .b-gray   { background: #f1f5f9; color: #475569; }

        /* Pie */
        .footer-print { margin-top: 28px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; display: flex; justify-content: space-between; }

        /* Barra de acciones (solo pantalla) */
        .acciones-print { position: fixed; top: 16px; right: 16px; display: flex; gap: 8px; z-index: 50; }
        .acciones-print button, .acciones-print a { padding: 8px 14px; font-size: 13px; border-radius: 8px; cursor: pointer; text-decoration: none; border: none; font-family: inherit; box-shadow: 0 2px 6px rgba(0,0,0,.12); }
        .acciones-print .btn-pdf { background: #2563eb; color: #fff; font-weight: 600; }
        .acciones-print .btn-volver { background: #fff; color: #1f2937; border: 1px solid #e2e8f0; }

        @media print {
            .acciones-print { display: none; }
            body { padding: 0; }
            tr { page-break-inside: avoid; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>
    <div class="acciones-print">
        <a href="javascript:history.back()" class="btn-volver">← Volver</a>
        <button onclick="window.print()" class="btn-pdf">🖨 Imprimir / Guardar PDF</button>
    </div>

    <div class="doc-header">
        <div class="doc-brand">
            @if(!empty($sitio['sitio_favicon']))
                <img class="doc-logo" src="{{ asset('storage/' . $sitio['sitio_favicon']) }}" alt="logo">
            @else
                <div class="doc-logo-fb">{{ mb_strtoupper(mb_substr($sitio['sitio_nombre'] ?? 'S', 0, 1)) }}</div>
            @endif
            <div>
                <div class="doc-brand-name">{{ $marca['base'] }}<span>{{ $marca['acento'] }}</span></div>
                @if(!empty($sitio['sitio_subtitulo']))
                    <div class="doc-brand-sub">{{ $sitio['sitio_subtitulo'] }}</div>
                @endif
            </div>
        </div>
        <div class="doc-meta">
            <div class="doc-type">{{ $tipo ?? 'Documento' }}</div>
            <div class="gen">Generado: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="doc-title">
        <h1>{{ $titulo }}</h1>
        @isset($subtitulo)
            <p class="subtitle">{{ $subtitulo }}</p>
        @endisset
    </div>

    {{ $slot }}

    <div class="footer-print">
        <span>{{ $sitio['sitio_nombre'] ?? 'SistemaRH' }} · Documento generado automáticamente · v{{ config('app.version') }}</span>
        <span>{{ now()->format('d/m/Y H:i') }}</span>
    </div>
</body>
</html>
