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
    // dompdf a besoin de GD pour les PNG : sans GD, on affiche le nom de l'entreprise
    if ($logoPath && $isPdf && ! extension_loaded('gd') && str_ends_with(strtolower($logoPath), '.png')) {
        $logoPath = null;
    }
    $logoSrc = $logoPath ? 'data:'.(mime_content_type($logoPath) ?: 'image/png').';base64,'.base64_encode(file_get_contents($logoPath)) : null;

    $editedAt = now()->format('d/m/Y à H:i');
    $editor = auth()->user()->name ?? '';
    $nameLimit = 44;
    $lineLimit = 64;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue produits — {{ $companyName }}</title>
    <style>
        @page { size: A4 portrait; margin: {{ $isPdf ? '11mm 11mm 8mm' : '0' }}; }

        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        body {
            margin: 0;
            background: {{ $isPdf ? '#FFFFFF' : '#E9EDF2' }};
            color: #0B0F14;
            font-family: {!! $isPdf ? "'DejaVu Sans', sans-serif" : "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif" !!};
            font-size: {{ $isPdf ? '7.8pt' : '8.6pt' }};
            line-height: 1.25;
        }

        /* ---------- Barre d'outils (écran uniquement) ---------- */
        .toolbar {
            position: sticky; top: 0; z-index: 10;
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 20px; background: #FFFFFF; border-bottom: 1px solid #E2E8F0;
            box-shadow: 0 6px 20px -12px rgba(11, 15, 20, .25);
        }
        .toolbar .group { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 16px; border-radius: 999px;
            border: 1px solid #E2E8F0; background: #FFFFFF; color: #0B0F14;
            font-family: inherit; font-size: 12.5px; font-weight: 600; line-height: 1; text-decoration: none; cursor: pointer;
        }
        .btn:hover { border-color: #94A3B8; }
        .btn-dark { background: #0B0F14; border-color: #0B0F14; color: #FFFFFF; }
        .btn-dark:hover { background: #000000; }
        .pill { font-size: 12px; color: #64748B; }
        .pill strong { color: #0B0F14; }
        .notice { margin: 16px auto 0; max-width: 210mm; padding: 10px 14px; border-radius: 12px; background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; font-size: 12.5px; }

        /* ---------- Pages A4 portrait ---------- */
        .sheet { page-break-after: always; }
        .sheet.is-last { page-break-after: auto; }
@unless($isPdf)
        .sheet {
            width: 210mm; height: 297mm; margin: 20px auto; padding: 11mm 11mm 8mm;
            background: #FFFFFF; overflow: hidden; display: flex; flex-direction: column;
            box-shadow: 0 12px 40px -18px rgba(11, 15, 20, .35); border-radius: 4px;
        }
@endunless

        table { width: 100%; border-collapse: collapse; }

        .head td { padding: 0 0 4mm; border-bottom: 2.2pt solid #0B0F14; vertical-align: bottom; }
        .brand img { display: block; height: 11mm; width: auto; max-width: 60mm; }
        .brand .name { font-size: 15pt; font-weight: bold; }
        .title { text-align: right; }
        .title h1 { margin: 0; font-size: 17pt; font-weight: bold; }
        .title p { margin: 1.2mm 0 0; color: #64748B; font-size: 8pt; }
        .title p strong { color: #0066FF; }

        .meta { margin: 3.5mm 0 3mm; border-collapse: separate; border-spacing: 2mm 0; }
        .meta-wrap { margin: 0 -2mm; }
        .meta td { padding: 2mm 3mm; border-radius: 2.5mm; background: #F5F7FA; vertical-align: top; }
        .meta span { display: block; font-size: 6.4pt; text-transform: uppercase; letter-spacing: .08em; color: #64748B; }
        .meta strong { display: block; margin-top: .6mm; font-size: 8.6pt; }

        .grid { table-layout: fixed; }
        .grid th {
            height: 7mm; padding: 0 2mm; background: #0B0F14; color: #FFFFFF; text-align: left;
            font-size: 6.6pt; font-weight: bold; text-transform: uppercase; letter-spacing: .06em;
        }
        .grid td { height: 7.1mm; padding: 0 2mm; border-bottom: .5pt solid #E2E8F0; vertical-align: middle; white-space: nowrap; overflow: hidden; }
        .grid tbody tr:nth-child(even) td { background: #FAFBFC; }
        .c-num { width: 9mm; text-align: center; color: #64748B; }
        .grid td.c-num { color: #64748B; }
        .c-ref { width: 27mm; }
        .grid td.c-ref { font-family: {!! $isPdf ? "'DejaVu Sans Mono', monospace" : "ui-monospace, 'SFMono-Regular', Menlo, Consolas, monospace" !!}; font-size: 7.2pt; }
        .c-name .n { font-weight: bold; }
        .c-name .s { color: #64748B; font-size: 6.8pt; }
        .c-qty { width: 22mm; text-align: right; }
        .grid td.c-qty { font-weight: bold; }
        .c-qty small { color: #64748B; font-weight: normal; font-size: 6.4pt; }
        .c-price { width: 26mm; }
        .grid td.c-price { border-left: .5pt dashed #CBD5E1; }
        .grid th.c-price, .grid th.c-qty { text-align: right; }
        .grid th.c-num { text-align: center; }

        .foot { margin-top: {{ $isPdf ? '4mm' : 'auto' }}; }
        .foot td { padding-top: 3mm; border-top: .5pt solid #E2E8F0; color: #64748B; font-size: 6.8pt; vertical-align: middle; }
        .foot .page { text-align: center; color: #0B0F14; font-weight: bold; }
        .foot .sign { text-align: right; }
        .foot .sign i { display: inline-block; width: 38mm; border-bottom: .5pt solid #94A3B8; }

        .empty { padding: 90mm 0; text-align: center; color: #64748B; }
        .empty strong { color: #0B0F14; font-size: 13pt; }

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
    <section class="sheet is-last">
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
        @php
            $pageNumber++;
        @endphp
        <section class="sheet{{ $pageNumber === $totalPages ? ' is-last' : '' }}">
            <table class="head">
                <tr>
                    <td class="brand">
                        @if($logoSrc)
                            <img src="{{ $logoSrc }}" alt="{{ $companyName }}">
                        @else
                            <span class="name">{{ $companyName }}</span>
                        @endif
                    </td>
                    <td class="title">
                        <h1>Catalogue produits</h1>
                        <p>
                            @if($warehouse)
                                Entrepôt <strong>{{ $warehouse->name }}</strong> · {{ $warehouse->code }}@if($warehouse->address) · {{ Str::limit($warehouse->address, 40) }}@endif
                            @else
                                Stock global · tous entrepôts
                            @endif
                        </p>
                    </td>
                </tr>
            </table>

            <div class="meta-wrap">
                <table class="meta">
                    <tr>
                        <td><span>Édité le</span><strong>{{ $editedAt }}</strong></td>
                        <td><span>Articles</span><strong>{{ number_format($section['rows']->count(), 0, ',', ' ') }}</strong></td>
                        @if($warehouse?->manager)
                            <td><span>Responsable</span><strong>{{ Str::limit($warehouse->manager, 24) }}</strong></td>
                        @endif
                        <td><span>Édité par</span><strong>{{ $editor ? Str::limit($editor, 24) : '—' }}</strong></td>
                    </tr>
                </table>
            </div>

            <table class="grid">
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
                            $productName = Str::limit($product->name, $nameLimit);
                            $detailsRoom = $lineLimit - mb_strlen($productName);
                            $details = collect([$product->brand?->name, $product->category?->name])->filter()->implode(' · ');
                            $details = $detailsRoom >= 10 ? Str::limit($details, $detailsRoom) : '';
                        @endphp
                        <tr>
                            <td class="c-num">{{ $index }}</td>
                            <td class="c-ref">{{ Str::limit((string) $product->sku, 16) }}</td>
                            <td class="c-name"><span class="n">{{ $productName }}</span>@if($details) <span class="s">{{ $details }}</span>@endif</td>
                            <td class="c-qty">{{ number_format($row['quantity'], 0, ',', ' ') }} <small>{{ $product->unit ?? 'pcs' }}</small></td>
                            <td class="c-price"></td>
                            <td class="c-price"></td>
                        </tr>
                    @endforeach
                    @for($i = $pageRows->count(); $i < $rowsPerPage; $i++)
                        <tr>
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

            <table class="foot">
                <tr>
                    <td>{{ Str::limit($companyName, 30) }} · Catalogue{{ $warehouse ? ' '.$warehouse->code : '' }} · {{ $editedAt }}</td>
                    <td class="page">Page {{ $pageNumber }} / {{ $totalPages }}</td>
                    <td class="sign">Visa <i></i></td>
                </tr>
            </table>
        </section>
    @endforeach
@endforeach

@if(! $isPdf && request()->boolean('autoprint'))
    <script>window.addEventListener('load', () => window.print());</script>
@endif
</body>
</html>
