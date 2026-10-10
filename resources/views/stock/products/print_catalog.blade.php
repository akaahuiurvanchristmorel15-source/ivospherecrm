<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue Produits & Bordereau de Prix (A4 Paysage) — IVOSPHERE ERP</title>
    <style>
        /* ============================================================
           CONFIGURATION PAGE A4 PAYSAGE (LANDSCAPE)
           Dimensions A4 Landscape : 297mm x 210mm
           ============================================================ */
        @page {
            size: A4 landscape;
            margin: 8mm 10mm 8mm 10mm;
        }

        :root {
            --ink: #0b0f14;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
            --border-light: #e2e8f0;
            --bg-table-head: #f1f5f9;
            --bg-zebra: #f8fafc;
            --brand-blue: #0066ff;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f1f5f9;
            color: var(--text-dark);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.35;
        }

        /* ---------- Barre d'actions à l'écran (non imprimée) ---------- */
        .screen-toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #ffffff;
            border-bottom: 1px solid var(--border-light);
            padding: 12px 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .screen-toolbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }

        .btn-print {
            background: var(--brand-blue);
            color: #ffffff;
        }
        .btn-print:hover {
            background: #0052cc;
        }

        .btn-back {
            background: #ffffff;
            color: #475569;
            border-color: var(--border-light);
        }
        .btn-back:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        .info-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            background: #eff6ff;
            color: #1e40af;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        /* ---------- Conteneur principal & Pages ---------- */
        .print-container {
            max-width: 297mm;
            margin: 20px auto;
            padding: 0;
        }

        .catalog-page {
            width: 297mm;
            min-height: 204mm;
            max-height: 208mm;
            background: #ffffff;
            margin: 0 auto 24px auto;
            padding: 8mm 10mm;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-after: always;
            break-after: page;
            position: relative;
        }

        .catalog-page:last-child {
            margin-bottom: 40px;
            page-break-after: auto;
            break-after: auto;
        }

        /* ---------- En-tête de page ---------- */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 2px solid var(--ink);
            padding-bottom: 7px;
            margin-bottom: 6px;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo-badge {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: #0b0f14;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 18px;
            letter-spacing: -0.5px;
        }

        .brand-text h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 900;
            letter-spacing: -0.3px;
            color: var(--ink);
            text-transform: uppercase;
        }

        .brand-text p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .header-title-box {
            text-align: center;
            flex: 1;
            padding: 0 16px;
        }

        .header-title-box h2 {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            color: var(--brand-blue);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-title-box p {
            margin: 2px 0 0 0;
            font-size: 9.5px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .header-meta {
            text-align: right;
            font-size: 9.5px;
            color: var(--text-dark);
            line-height: 1.4;
        }

        .header-meta strong {
            color: var(--ink);
        }

        .badge-landscape {
            display: inline-block;
            margin-top: 2px;
            padding: 2px 6px;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 8.5px;
            font-weight: 700;
            border-radius: 4px;
            text-transform: uppercase;
        }

        /* ---------- Tableau des 20 Produits ---------- */
        .table-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        table.catalog-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            table-layout: fixed;
        }

        table.catalog-table thead th {
            background-color: var(--bg-table-head);
            color: var(--ink);
            font-weight: 800;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.4px;
            padding: 6px 7px;
            border: 1px solid var(--border-color);
            vertical-align: middle;
        }

        table.catalog-table tbody tr {
            height: 25px; /* Calibré pour 20 lignes exactes sur une page A4 paysage */
        }

        table.catalog-table tbody tr:nth-child(even) {
            background-color: var(--bg-zebra);
        }

        table.catalog-table tbody td {
            padding: 4px 7px;
            border: 1px solid var(--border-light);
            vertical-align: middle;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Alignements et colonnes */
        .col-num {
            width: 32px;
            text-align: center;
            font-weight: 700;
            color: var(--text-muted);
        }

        .col-ref {
            width: 105px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 700;
            color: #0369a1;
        }

        .col-designation {
            width: auto; /* Prend tout l'espace disponible */
            font-weight: 600;
            color: var(--ink);
        }

        .col-designation .product-name {
            font-weight: 700;
            color: var(--ink);
        }

        .col-designation .product-sub {
            color: var(--text-muted);
            font-size: 8.5px;
            margin-left: 4px;
            font-weight: 400;
        }

        .col-qty {
            width: 80px;
            text-align: right;
            font-weight: 800;
            color: var(--ink);
        }

        .col-qty .unit-label {
            font-size: 8.5px;
            color: var(--text-muted);
            font-weight: 500;
            margin-left: 2px;
        }

        /* Colonnes PRIX UNITAIRE et PRIX DE REVENTE (STRICTEMENT VIDES) */
        .col-price-empty {
            width: 120px;
            text-align: center;
            background-color: #ffffff !important;
            border: 1px dashed var(--border-color) !important;
            position: relative;
        }

        .col-price-empty:after {
            content: "";
            display: inline-block;
        }

        /* ---------- Pied de page ---------- */
        .page-footer {
            border-top: 1px solid var(--border-color);
            padding-top: 5px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 9px;
            color: var(--text-muted);
        }

        .footer-left {
            font-weight: 500;
        }

        .footer-center {
            font-weight: 700;
            color: var(--ink);
            background: #f1f5f9;
            padding: 2px 10px;
            border-radius: 12px;
        }

        .footer-right {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .signature-line {
            display: inline-block;
            width: 110px;
            border-bottom: 1px dotted var(--text-muted);
            height: 12px;
        }

        /* ============================================================
           MEDIA PRINT : OPTIMISATIONS POUR IMPRESSION & PDF
           ============================================================ */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .screen-toolbar {
                display: none !important;
            }

            .print-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .catalog-page {
                width: 100% !important;
                min-height: 100% !important;
                max-height: none !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                page-break-after: always !important;
                break-after: page !important;
            }

            .catalog-page:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }

            table.catalog-table tbody tr {
                height: 25.5px !important;
            }
        }
    </style>
</head>
<body>

    {{-- BARRE D'ACTIONS ÉCRAN (Cachée à l'impression / PDF) --}}
    <div class="screen-toolbar">
        <div class="screen-toolbar-left">
            <a href="{{ url()->previous() ?: route('stock.index', ['tab' => 'disponibilite']) }}" class="btn-action btn-back">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Retour au Catalogue</span>
            </a>

            <button type="button" onclick="window.print()" class="btn-action btn-print">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimer / Exporter en PDF (A4 Paysage)</span>
            </button>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <span class="info-pill">
                📄 Format : A4 Paysage · 20 articles / page · Total : {{ $products->count() }} produits ({{ $chunks->count() }} {{ Str::plural('page', $chunks->count()) }})
            </span>
            <span style="font-size: 11px; color: #64748b;">
                Colonnes Prix Unitaire & Prix de Revente : <strong>Vides</strong> (pour saisie manuscrite / inventaire)
            </span>
        </div>
    </div>

    {{-- CONTENEUR DES PAGES CHUNKÉES PAR 20 PRODUITS --}}
    <div class="print-container">
        @php
            $globalIndex = 0;
            $totalPages = $chunks->count() ?: 1;
        @endphp

        @forelse($chunks as $pageIndex => $pageProducts)
            @php
                $currentPageNumber = $pageIndex + 1;
            @endphp
            <div class="catalog-page">
                {{-- 1. EN-TÊTE DE LA PAGE --}}
                <div class="page-header">
                    <div class="header-brand">
                        <div class="brand-logo-badge">IV</div>
                        <div class="brand-text">
                            <h1>IVOSPHERE ERP</h1>
                            <p>Système Intégré de Gestion & Logistique</p>
                        </div>
                    </div>

                    <div class="header-title-box">
                        <h2>Catalogue & Bordereau de Prix des Produits</h2>
                        <p>Grille officielle de tarification & contrôle des stocks · Format A4 Paysage</p>
                    </div>

                    <div class="header-meta">
                        <div>Date d'édition : <strong>{{ now()->format('d/m/Y à H:i') }}</strong></div>
                        <div>Édité par : <strong>{{ auth()->user()->name ?? 'Administrateur' }}</strong></div>
                        <div>Total articles : <strong>{{ $products->count() }}</strong></div>
                        <span class="badge-landscape">A4 Paysage · 20 / page</span>
                    </div>
                </div>

                {{-- 2. TABLEAU DES 20 PRODUITS --}}
                <div class="table-wrapper">
                    <table class="catalog-table">
                        <thead>
                            <tr>
                                <th class="col-num">N°</th>
                                <th class="col-ref">RÉFÉRENCE</th>
                                <th class="col-designation">DÉSIGNATION DU PRODUIT</th>
                                <th class="col-qty">QUANTITÉ</th>
                                <th class="col-price-empty">PRIX UNITAIRE</th>
                                <th class="col-price-empty">PRIX DE REVENTE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pageProducts as $product)
                                @php
                                    $globalIndex++;
                                @endphp
                                <tr>
                                    {{-- N° index incrémental --}}
                                    <td class="col-num">{{ $globalIndex }}</td>

                                    {{-- Référence SKU & EAN --}}
                                    <td class="col-ref">
                                        <span>{{ $product->sku }}</span>
                                    </td>

                                    {{-- Désignation du produit --}}
                                    <td class="col-designation">
                                        <span class="product-name">{{ $product->name }}</span>
                                        @if($product->brand || $product->domain)
                                            <span class="product-sub">
                                                ({{ $product->brand?->name ?? 'IVOSPHERE' }} · {{ strtoupper($product->domain?->name ?? 'Général') }})
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Quantité en stock --}}
                                    <td class="col-qty">
                                        <span>{{ number_format((float) $product->current_stock, 0, ',', ' ') }}</span>
                                        <span class="unit-label">{{ $product->unit ?? 'pcs' }}</span>
                                    </td>

                                    {{-- Colonne PRIX UNITAIRE : Strictement VIDE pour saisie manuelle / inventaire --}}
                                    <td class="col-price-empty"></td>

                                    {{-- Colonne PRIX DE REVENTE : Strictement VIDE pour saisie manuelle / inventaire --}}
                                    <td class="col-price-empty"></td>
                                </tr>
                            @endforeach

                            {{-- Si la dernière page contient moins de 20 produits, compléter avec des lignes vierges pour conserver la grille parfaite --}}
                            @for($i = $pageProducts->count(); $i < 20; $i++)
                                <tr>
                                    <td class="col-num" style="color: #cbd5e1;">{{ $globalIndex + ($i - $pageProducts->count() + 1) }}</td>
                                    <td class="col-ref" style="color: #cbd5e1;">-</td>
                                    <td class="col-designation" style="color: #cbd5e1;">-</td>
                                    <td class="col-qty" style="color: #cbd5e1;">-</td>
                                    <td class="col-price-empty"></td>
                                    <td class="col-price-empty"></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                {{-- 3. PIED DE PAGE --}}
                <div class="page-footer">
                    <div class="footer-left">
                        Document officiel IVOSPHERE CRM & WMS · Édité le {{ now()->format('d/m/Y à H:i:s') }}
                    </div>
                    <div class="footer-center">
                        Page {{ $currentPageNumber }} sur {{ $totalPages }}
                    </div>
                    <div class="footer-right">
                        <span>Visa / Signature :</span>
                        <span class="signature-line"></span>
                    </div>
                </div>
            </div>
        @empty
            <div class="catalog-page" style="justify-content: center; align-items: center;">
                <div style="text-align: center; color: #64748b;">
                    <p style="font-size: 16px; font-weight: 700; color: #0b0f14;">Aucun produit trouvé dans le catalogue.</p>
                    <p>Ajoutez des produits au catalogue pour générer le bordereau PDF.</p>
                </div>
            </div>
        @endforelse
    </div>

</body>
</html>
