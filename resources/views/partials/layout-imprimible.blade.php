{{--
    Layout minimalista para vistas imprimibles (descarga como PDF desde el navegador).
    Variables:
      $titulo = string
      $subtitulo = string|null
      $slot = HTML body
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif; font-size: 12px; color: #1f2937; line-height: 1.5; padding: 24px 32px; background: #fff; }
        h1 { font-size: 22px; color: #0f172a; margin-bottom: 4px; }
        .subtitle { color: #64748b; font-size: 12px; margin-bottom: 18px; }
        .header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 18px; }
        .brand { font-size: 13px; font-weight: 700; color: #2563eb; }
        .meta { font-size: 11px; color: #94a3b8; text-align: right; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 11px; }
        th { background: #f1f5f9; color: #475569; text-align: left; padding: 7px 9px; border-bottom: 1px solid #cbd5e1; font-weight: 600; text-transform: uppercase; font-size: 10px; letter-spacing: .04em; }
        td { padding: 7px 9px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tr:nth-child(even) td { background: #fafafa; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
        .b-green  { background: #dcfce7; color: #166534; }
        .b-blue   { background: #dbeafe; color: #1e40af; }
        .b-yellow { background: #fef3c7; color: #92400e; }
        .b-red    { background: #fee2e2; color: #991b1b; }
        .b-gray   { background: #f1f5f9; color: #475569; }

        .acciones-print { position: fixed; top: 16px; right: 16px; display: flex; gap: 8px; }
        .acciones-print button, .acciones-print a { padding: 8px 14px; font-size: 13px; border-radius: 6px; cursor: pointer; text-decoration: none; border: none; }
        .acciones-print .btn-pdf { background: #2563eb; color: #fff; }
        .acciones-print .btn-volver { background: #f1f5f9; color: #1f2937; }

        .footer-print { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; text-align: center; }

        @media print {
            .acciones-print { display: none; }
            body { padding: 12mm; }
        }
    </style>
</head>
<body>
    <div class="acciones-print">
        <a href="javascript:history.back()" class="btn-volver">← Volver</a>
        <button onclick="window.print()" class="btn-pdf">🖨 Imprimir / Guardar PDF</button>
    </div>

    <div class="header">
        <div>
            <h1>{{ $titulo }}</h1>
            @isset($subtitulo)
                <p class="subtitle">{{ $subtitulo }}</p>
            @endisset
        </div>
        <div class="meta">
            <div class="brand">Sistema RH</div>
            <div>Generado: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    {{ $slot }}

    <div class="footer-print">
        Documento generado por Sistema RH · Página <span class="page-num"></span>
    </div>
</body>
</html>
