@php
    $isPdf = $isPdf ?? false;
    $rowsPerPage = $rowsPerPage ?? 30;
    $totalPages = max(1, $chunks->count());
    $pageNumber = 0;

    // Identité de l'entreprise de l'espace (logo des paramètres, sinon logo IVOSPHERE pour l'espace principal)
    $companyName = \App\Models\Setting::get('company_name', 'IVOSPHERE');
    $logoPath = null;
    $customLogo = (string) \App\Models\Setting::get('company_logo', '');
    if ($customLogo !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($customLogo)) {
        $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($customLogo);
    } elseif (\App\Support\TenantContext::isMain() && is_file(public_path('images/logo-horizontal.png'))) {
        $logoPath = public_path('images/logo-horizontal.png');
    }
    $logoSrc = $logoPath ? 'data:'.(mime_content_type($logoPath) ?: 'image/png').';base64,'.base64_encode(file_get_contents($logoPath)) : null;

    $editedAt = now()->format('d/m/Y à H:i');
    $editor = auth()->user()->name ?? '';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue produits — {{ $companyName }}</title>
    <style>
        @page { size: A4 portrait; margin: 0; }

        :root {
            --ink: #0B0F14;
            --muted: #64748B;
            --line: #E2E8F0;
            --soft: #F5F7FA;
            --accent: #0066FF;
        }

        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        body {
            margin: 0;
            background: {{ $isPdf ? '#FFFFFF' : '#E9EDF2' }};
            color: var(--ink);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 8.6pt;
            line-height: 1.25;
        }

        /* ---------- Barre d'outils (écran uniquement) ---------- */
        .toolbar {
            position: sticky; top: 0; z-index: 10;
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 20px; background: #FFFFFF; border-bottom: 1px solid var(--line);
            box-shadow: 0 6px 20px -12px rgba(11, 15, 20, .25);
        }
        .toolbar .group { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 16px; border-radius: 999px;
            border: 1px solid var(--line); background: #FFFFFF; color: var(--ink);
            font-family: inherit; font-size: 12.5px; font-weight: 600; line-height: 1; text-decoration: none; cursor: pointer;
        }
        .btn:hover { border-color: #94A3B8; }
        .btn-dark { background: var(--ink); border-color: var(--ink); color: #FFFFFF; }
        .btn-dark:hover { background: #000000; }
        .pill { font-size: 12px; color: var(--muted); }
        .pill strong { color: var(--ink); }
        .notice { margin: 16px auto 0; max-width: 210mm; padding: 10px 14px; border-radius: 12px; background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; font-size: 12.5px; }

        /* ---------- Pages A4 portrait ---------- */
        .sheet {
            width: 210mm; height: 297mm; margin: {{ $isPdf ? '0' : '20px auto' }};
            padding: 11mm 11mm 9mm; background: #FFFFFF; overflow: hidden;
            display: flex; flex-direction: column;
            {{ $isPdf ? '' : 'box-shadow: 0 12px 40px -18px rgba(11, 15, 20, .35); border-radius: 4px;' }}
            break-after: page; page-break-after: always;
        }
        .sheet:last-child { break-after: auto; page-break-after: auto; }

        .head { display: flex; align-items: flex-end; justify-content: space-between; gap: 10mm; padding-bottom: 4mm; border-bottom: 2.2pt solid var(--ink); }
        .brand img { display: block; height: 11mm; width: auto; max-width: 60mm; object-fit: contain; }
        .brand .name { font-size: 15pt; font-weight: 800; letter-spacing: -.02em; }
        .title { text-align: right; }
        .title h1 { margin: 0; font-size: 17pt; font-weight: 800; letter-spacing: -.03em; }
        .title p { margin: 1.2mm 0 0; color: var(--muted); font-size: 8.4pt; }
        .title p strong { color: var(--accent); }

        .meta { display: flex; gap: 4mm; margin: 3.5mm 0 3mm; }
        .meta div { flex: 1; padding: 2mm 3mm; border-radius: 2.5mm; background: var(--soft); }
        .meta span { display: block; font-size: 6.6pt; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); }
        .meta strong { display: block; margin-top: .6mm; font-size: 8.8pt; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead th {
            height: 7mm; padding: 0 2mm; background: var(--ink); color: #FFFFFF; text-align: left;
            font-size: 6.8pt; font-weight: 700; text-transform: uppercase; letter-spacing: .07em;
        }
        thead th:first-child { border-radius: 1.6mm 0 0 1.6mm; }
        thead th:last-child { border-radius: 0 1.6mm 1.6mm 0; }
        tbody td { height: 7.15mm; padding: 0 2mm; border-bottom: .5pt solid var(--line); vertical-align: middle; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        tbody tr:nth-child(even) td { background: #FAFBFC; }
        .c-num { width: 9mm; text-align: center; color: var(--muted); font-variant-numeric: tabular-nums; }
        .c-ref { width: 27mm; font-family: ui-monospace, 'SFMono-Regular', Menlo, Consolas, monospace; font-size: 7.6pt; }
        .c-name .n { font-weight: 600; }
        .c-name .s { margin-left: 1.5mm; color: var(--muted); font-size: 7pt; }
        .c-qty { width: 21mm; text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; }
        .c-qty small { margin-left: .8mm; color: var(--muted); font-weight: 500; font-size: 6.6pt; }
        .c-price { width: 27mm; border-left: .5pt dashed #CBD5E1; }
        th.c-price, th.c-qty { text-align: right; }
        tr.blank td { color: #CBD5E1; }

        .foot { margin-top: auto; padding-top: 3mm; display: flex; align-items: center; justify-content: space-between; gap: 6mm; border-top: .5pt solid var(--line); color: var(--muted); font-size: 7pt; }
        .foot .page { color: var(--ink); font-weight: 700; }
        .foot .sign { display: flex; align-items: center; gap: 2mm; }
        .foot .sign i { display: inline-block; width: 38mm; border-bottom: .5pt solid #94A3B8; }

        .empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: var(--muted); }
        .empty strong { color: var(--ink); font-size: 13pt; }

        @media print {
            body { background: #FFFFFF; }
            .toolbar, .notice { display: none !important; }
            .sheet { margin: 0; box-shadow: none; border-radius: 0; }
        }
    </style>
</head>
<body>

@unless($isPdf)
    <div class="toolbar">
        <div class="group">
            <a href="{{ url()->previous() ?: route('stock.index', ['tab' => 'disponibilite']) }}" class="btn">← Retour</a>
            <a href="{{ route('stock.products.download-catalog', request()->except('autoprint')) }}" class="btn btn-dark">Télécharger le PDF</a>
            <button type="button" onclick="window.print()" class="btn">Imprimer</button>
        </div>
        <p class="pill">A4 portrait · {{ $rowsPerPage }} articles par page · <strong>{{ $productCount }}</strong> articles · <strong>{{ $totalPages }}</strong> {{ Str::plural('page', $totalPages) }}</p>
    </div>

    @if(session('pdf_unavailable'))
        <p class="notice">Le PDF n'a pas pu être généré automatiquement sur ce serveur. Utilisez « Imprimer » puis « Enregistrer au format PDF ».</p>
    @endif
@endunless

@if($chunks->isEmpty())
    <section class="sheet">
        <div class="empty">
            <strong>Aucun produit à afficher</strong>
            <p>Aucun article ne correspond à ce catalogue (filtres ou entrepôt sans stock).</p>
        </div>
    </section>
@endif

@foreach($sections as $section)
    @php
        $warehouse = $section['warehouse'];
        $index = 0;
    @endphp
    @foreach($section['chunks'] as $pageRows)
        @php $pageNumber++; @endphp
        <section class="sheet">
            <header class="head">
                <div class="brand">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="{{ $companyName }}">
                    @else
                        <span class="name">{{ $companyName }}</span>
                    @endif
                </div>
                <div class="title">
                    <h1>Catalogue produits</h1>
                    <p>
                        @if($warehouse)
                            Entrepôt <strong>{{ $warehouse->name }}</strong> · {{ $warehouse->code }}@if($warehouse->address) · {{ $warehouse->address }}@endif
                        @else
                            Stock global · tous entrepôts
                        @endif
                    </p>
                </div>
            </header>

            <div class="meta">
                <div><span>Édité le</span><strong>{{ $editedAt }}</strong></div>
                <div><span>Articles</span><strong>{{ number_format($section['rows']->count(), 0, ',', ' ') }}</strong></div>
                @if($warehouse?->manager)
                    <div><span>Responsable</span><strong>{{ $warehouse->manager }}</strong></div>
                @endif
                <div><span>Édité par</span><strong>{{ $editor ?: '—' }}</strong></div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th class="c-num">N°</th>
                        <th class="c-ref">Référence</th>
                        <th class="c-name">Désignation</th>
                        <th class="c-qty">{{ $warehouse ? 'Qté dépôt' : 'Quantité' }}</th>
                        <th class="c-price">Prix unitaire</th>
                        <th class="c-price">Prix revente</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pageRows as $row)
                        @php
                            $product = $row['product'];
                            $index++;
                            $details = collect([$product->brand?->name, $product->category?->name])->filter()->implode(' · ');
                        @endphp
                        <tr>
                            <td class="c-num">{{ $index }}</td>
                            <td class="c-ref">{{ $product->sku }}</td>
                            <td class="c-name"><span class="n">{{ $product->name }}</span>@if($details)<span class="s">{{ $details }}</span>@endif</td>
                            <td class="c-qty">{{ number_format($row['quantity'], 0, ',', ' ') }}<small>{{ $product->unit ?? 'pcs' }}</small></td>
                            <td class="c-price"></td>
                            <td class="c-price"></td>
                        </tr>
                    @endforeach
                    @for($i = $pageRows->count(); $i < $rowsPerPage; $i++)
                        <tr class="blank">
                            <td class="c-num">{{ $index + ($i - $pageRows->count() + 1) }}</td>
                            <td class="c-ref"></td>
                            <td class="c-name"></td>
                            <td class="c-qty"></td>
                            <td class="c-price"></td>
                            <td class="c-price"></td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            <footer class="foot">
                <span>{{ $companyName }} · Catalogue{{ $warehouse ? ' '.$warehouse->code : '' }} · {{ $editedAt }}</span>
                <span class="page">Page {{ $pageNumber }} / {{ $totalPages }}</span>
                <span class="sign">Visa <i></i></span>
            </footer>
        </section>
    @endforeach
@endforeach

@if(! $isPdf && request()->boolean('autoprint'))
    <script>window.addEventListener('load', () => window.print());</script>
@endif
</body>
</html>
