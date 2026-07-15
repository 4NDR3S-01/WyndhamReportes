<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recomendación de Producción — Wyndham Manta</title>
    <style>
        @page { size: A4 landscape; margin: 1.5cm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; color: #1e293b; line-height: 1.5; padding: 0; }
        .header { border-bottom: 3px solid #0E7490; padding-bottom: 16px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; color: #0E7490; margin-bottom: 4px; }
        .header .subtitle { font-size: 13px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead th { background: #0E7490; color: #fff; padding: 10px 12px; text-align: left; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        thead th:last-child { text-align: right; }
        thead th:nth-child(3) { text-align: right; }
        tbody td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td:last-child { text-align: right; font-weight: 600; color: #0E7490; }
        tbody td:nth-child(3) { text-align: right; }
        .footer { margin-top: 24px; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 12px; }
        .empty { text-align: center; padding: 48px 0; color: #94a3b8; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
        .no-print { margin-top: 16px; }
        .btn-print { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #0E7490; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 500; }
        .btn-print:hover { background: #155e75; }
    </style>
</head>
<body>

<div class="header">
    <h1>Recomendación de Producción — Wyndham Manta</h1>
    <p class="subtitle">
        Fecha referencia: {{ \Carbon\Carbon::parse($fechaReferencia)->format('d/m/Y') }}
        &nbsp;|&nbsp; Huéspedes: {{ $huespedesReferencia }}
        @if ($archivo)
            &nbsp;|&nbsp; Documento: {{ \Illuminate\Support\Str::limit($archivo, 50) }}
        @endif
    </p>
</div>

@if ($recomendacion->isEmpty())
    <p class="empty">Sin datos de recomendación para los parámetros seleccionados.</p>
@else
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th style="text-align:center;">Unidad</th>
                <th>Consumo Base</th>
                <th>Por Persona</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($recomendacion as $rec)
                <tr>
                    <td>{{ $rec->nombre }}</td>
                    <td style="text-align:center;">{{ $rec->unidad }}</td>
                    <td>{{ $rec->esEntero ? number_format($rec->consumoBase, 0, ',', '.') : rtrim(rtrim(number_format($rec->consumoBase, 3, ',', '.'), '0'), ',') }}</td>
                    <td>{{ $rec->esEntero ? number_format($rec->sugerido, 0, ',', '.') : rtrim(rtrim(number_format($rec->sugerido, 3, ',', '.'), '0'), ',') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div class="footer">
    Generado el {{ now()->format('d/m/Y H:i') }} &middot; Sistema Wyndham Manta
</div>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
        Imprimir / Guardar PDF
    </button>
</div>

</body>
</html>
