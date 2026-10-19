@php
    $isPdf = $isPdf ?? false;
    $totalPages = $pages->count();
    $companyName = \App\Models\Setting::get('company_name', 'IVOSPHERE');
    $editedAt = now()->format('d/m/Y à H:i');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR codes — {{ $warehouse->name }}</title>
    <style>
        @page { size: A4 portrait; margin: {{ $isPdf ? '10mm' : '0' }}; }

        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        body {
            margin: 0;
            background: {{ $isPdf ? '#FFFFFF' : '#E9EDF2' }};
            color: #0B0F14;
            font-family: {!! $isPdf ? "'DejaVu Sans', sans-serif" : "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif" !!};
            font-size: 8pt;
            line-height: 1.2;
        }

        /* ---------- Barre d'outils (écran uniquement) ---------- */
        .toolbar {
            position: sticky; top: 0; z-index: 10;
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 20px; background: #FFFFFF; border-bottom: 1px solid #E2E8F0;
        }
        .btn {
            display: inline-flex; align-items: center; height: 38px; padding: 0 16px; border-radius: 999px;
            border: 1px solid #E2E8F0; background: #FFFFFF; color: #0B0F14;
            font-family: inherit; font-size: 12.5px; font-weight: 600; text-decoration: none; cursor: pointer;
        }
        .btn-dark { background: #0B0F14; border-color: #0B0F14; color: #FFFFFF; }
        .notice { margin: 16px auto 0; max-width: 210mm; padding: 10px 14px; border-radius: 12px; background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; font-size: 12.5px; }

        /* ---------- Pages A4 : une grille de {{ $perRow }} × {{ $rowsPerPage }} QR codes ---------- */
        .sheet { page-break-after: always; }
        .sheet.is-last { page-break-after: auto; }
@unless($isPdf)
        .sheet { width: 210mm; height: 297mm; margin: 20px auto; padding: 10mm; background: #FFFFFF; overflow: hidden; box-shadow: 0 12px 40px -18px rgba(11, 15, 20, .35); }
@endunless

        table { width: 100%; border-collapse: collapse; }

        .head td { height: 10mm; padding: 0 0 2mm; border-bottom: 1.6pt solid #0B0F14; vertical-align: bottom; }
        .head .name { font-size: 12pt; font-weight: bold; }
        .head .title { text-align: right; font-size: 8pt; color: #64748B; }
        .head .title strong { color: #0B0F14; }

        .grid { margin-top: 3mm; table-layout: fixed; }
        .grid tr { page-break-inside: avoid; }
        .grid td {
            width: {{ round(100 / $perRow, 4) }}%; height: 56mm; padding: 3mm 3mm 2mm;
            border: .6pt dashed #94A3B8; text-align: center; vertical-align: top; overflow: hidden;
        }
        .grid td.blank { border-color: #E2E8F0; }
        .label-name { height: 9mm; overflow: hidden; font-size: 8.4pt; font-weight: bold; line-height: 1.15; }
        .label-qr { display: block; width: 40mm; height: 40mm; margin: 1.5mm auto; }
        .label-sku { font-family: {!! $isPdf ? "'DejaVu Sans Mono', monospace" : "ui-monospace, 'SFMono-Regular', Menlo, Consolas, monospace" !!}; font-size: 6.8pt; color: #64748B; }

        .foot td { padding-top: 2mm; color: #64748B; font-size: 6.6pt; }
        .foot .page { text-align: right; color: #0B0F14; font-weight: bold; }

        @media print {
            body { background: #FFFFFF; }
            .toolbar, .notice { display: none !important; }
            .sheet { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>

@unless($isPdf)
    <div class="toolbar">
        <div>
            <a href="{{ route('stock.warehouses.show', $warehouse) }}" class="btn">← Retour</a>
            <button type="button" onclick="window.print()" class="btn btn-dark">Imprimer</button>
        </div>
        <span>{{ $productCount }} QR codes · {{ $totalPages }} {{ Str::plural('page', $totalPages) }}</span>
    </div>
    <p class="notice">Le PDF n'a pas pu être généré automatiquement sur ce serveur. Utilisez « Imprimer » puis « Enregistrer au format PDF ».</p>
@endunless

@foreach($pages as $pageIndex => $rows)
    <section class="sheet{{ $loop->last ? ' is-last' : '' }}">
        <table class="head">
            <tr>
                <td class="name">{{ Str::limit($companyName, 40) }}</td>
                <td class="title">QR codes produits · Entrepôt <strong>{{ $warehouse->name }}</strong> · {{ $warehouse->code }}</td>
            </tr>
        </table>

        <table class="grid">
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $label)
                        <td>
                            <div class="label-name">{{ Str::limit($label['product']->name, 60) }}</div>
                            <img class="label-qr" src="{{ $label['qrSrc'] }}" alt="QR {{ $label['product']->sku }}">
                            <div class="label-sku">{{ $label['product']->sku }}</div>
                        </td>
                    @endforeach
                    @for($i = $row->count(); $i < $perRow; $i++)
                        <td class="blank"></td>
                    @endfor
                </tr>
            @endforeach
        </table>

        <table class="foot">
            <tr>
                <td>{{ $productCount }} {{ Str::plural('produit', $productCount) }} · Édité le {{ $editedAt }}</td>
                <td class="page">Page {{ $loop->iteration }} / {{ $totalPages }}</td>
            </tr>
        </table>
    </section>
@endforeach

</body>
</html>
