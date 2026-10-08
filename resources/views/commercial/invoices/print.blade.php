<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Facture {{ $invoice->reference }} — IVOSPHERE ERP</title>
    <style>
        @page { size: A4; margin: 14mm; }

        :root {
            --ink: #0f172a;
            --text: #334155;
            --muted: #64748b;
            --line: #e2e8f0;
            --soft: #f8fafc;
            --brand: #0066ff;
            --ok: #047857;
            --warn: #b45309;
            --bad: #b91c1c;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px 16px 40px;
            background: #f1f5f9;
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ---------- Barre d'actions ---------- */
        .action-bar {
            max-width: 820px;
            margin: 0 auto 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: space-between;
            align-items: center;
        }
        .action-group { display: flex; flex-wrap: wrap; gap: 8px; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            height: 40px;
            padding: 0 16px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            font: inherit;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background .15s, border-color .15s;
        }
        .btn:hover { background: var(--soft); border-color: #cbd5e1; }
        .btn:focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; }
        .btn-primary { background: var(--brand); border-color: var(--brand); color: #fff; }
        .btn-primary:hover { background: #0052cc; border-color: #0052cc; }

        /* ---------- Feuille ---------- */
        .invoice-box {
            position: relative;
            max-width: 820px;
            margin: 0 auto;
            padding: 40px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
        }
        .watermark {
            position: absolute;
            top: 42%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-28deg);
            font-size: 96px;
            font-weight: 800;
            letter-spacing: 6px;
            opacity: .05;
            pointer-events: none;
            user-select: none;
            white-space: nowrap;
        }
        .invoice-box > *:not(.watermark) { position: relative; }

        /* ---------- En-tête ---------- */
        .header {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--line);
        }
        .company { display: flex; gap: 14px; min-width: 0; }
        .company img { width: 56px; height: 56px; object-fit: contain; flex-shrink: 0; }
        .company h1 { margin: 0; font-size: 18px; font-weight: 700; color: var(--ink); letter-spacing: -.2px; }
        .company p { margin: 2px 0 0; font-size: 12px; color: var(--muted); }
        .company .pole { color: var(--text); }

        .doc-title { text-align: right; flex-shrink: 0; }
        .doc-title h2 { margin: 0; font-size: 22px; font-weight: 700; color: var(--ink); letter-spacing: -.3px; }
        .doc-title .ref { margin-top: 2px; font-family: ui-monospace, 'SF Mono', Menlo, Consolas, monospace; font-size: 13px; color: var(--muted); }
        .status-badge {
            display: inline-block;
            margin-top: 10px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        /* ---------- Infos ---------- */
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; padding: 24px 0; }
        .meta-col h3 { margin: 0 0 8px; font-size: 12px; font-weight: 600; color: var(--muted); }
        .meta-col p { margin: 3px 0; font-size: 13px; color: var(--ink); }
        .meta-col .label { color: var(--muted); }
        .meta-col .name { font-size: 14px; font-weight: 600; }

        /* ---------- Tableaux ---------- */
        table { width: 100%; border-collapse: collapse; }
        th {
            padding: 10px 12px;
            border-bottom: 1.5px solid var(--ink);
            color: var(--ink);
            font-size: 12px;
            font-weight: 600;
            text-align: left;
        }
        td { padding: 12px; border-bottom: 1px solid var(--line); vertical-align: top; color: var(--ink); }
        th:first-child, td:first-child { padding-left: 0; }
        th:last-child, td:last-child { padding-right: 0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .num { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .sub { margin-top: 2px; font-size: 12px; color: var(--muted); }
        .idx { color: var(--muted); }

        /* ---------- Totaux ---------- */
        .totals-section {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 32px;
            align-items: start;
            margin-top: 24px;
        }
        .payment-info { font-size: 12px; color: var(--text); }
        .payment-info h4 { margin: 0 0 8px; font-size: 12px; font-weight: 600; color: var(--muted); }
        .payment-info p { margin: 3px 0; }
        .payment-info .hint { margin-top: 10px; color: var(--muted); }

        .totals-table td { padding: 7px 0; border-bottom: none; color: var(--text); }
        .totals-table td:last-child { text-align: right; color: var(--ink); font-variant-numeric: tabular-nums; white-space: nowrap; }
        .totals-table .discount td { color: var(--ok); }
        .totals-table .grand-total td {
            padding-top: 12px;
            border-top: 1.5px solid var(--ink);
            color: var(--ink);
            font-size: 16px;
            font-weight: 700;
        }
        .totals-table .paid-row td { color: var(--ok); }
        .totals-table .due-row td {
            padding: 10px 12px;
            background: var(--soft);
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
        }
        .totals-table .due-row td:first-child { border-radius: 8px 0 0 8px; }
        .totals-table .due-row td:last-child { border-radius: 0 8px 8px 0; }

        /* ---------- Historique ---------- */
        .payments { margin-top: 32px; }
        .payments h4 { margin: 0 0 6px; font-size: 12px; font-weight: 600; color: var(--muted); }
        .mono { font-family: ui-monospace, 'SF Mono', Menlo, Consolas, monospace; font-size: 12px; }

        /* ---------- Signatures ---------- */
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 36px; break-inside: avoid; }
        .signature-box { height: 96px; padding: 12px 14px; border: 1px dashed #cbd5e1; border-radius: 8px; }
        .signature-box .sign-title { font-weight: 600; color: var(--ink); font-size: 12px; }
        .signature-box .sign-hint { margin-top: 6px; font-size: 11px; color: var(--muted); }

        /* ---------- Mobile ---------- */
        @media (max-width: 640px) {
            body { padding: 12px 10px 32px; }
            .action-bar, .action-group { width: 100%; }
            .action-group .btn, .action-bar > .btn { flex: 1; }
            .invoice-box { padding: 20px 16px; border-radius: 10px; }
            .watermark { font-size: 56px; }

            .header { flex-direction: column; gap: 16px; }
            .doc-title { text-align: left; }

            .meta-grid { grid-template-columns: 1fr; gap: 20px; }

            /* Lignes d'articles : une carte par ligne */
            table.stack thead { display: none; }
            table.stack, table.stack tbody, table.stack tr, table.stack td { display: block; width: 100% !important; }
            table.stack tr { padding: 12px 0; border-bottom: 1px solid var(--line); }
            table.stack td { padding: 2px 0 !important; border: 0; display: flex; justify-content: space-between; gap: 12px; text-align: right; }
            table.stack td::before { content: attr(data-label); color: var(--muted); font-size: 12px; text-align: left; flex-shrink: 0; }
            table.stack td.cell-title { display: block; text-align: left; font-size: 14px; margin-bottom: 4px; }
            table.stack td.cell-title::before, table.stack td.cell-idx { display: none; }

            .totals-section { grid-template-columns: 1fr; gap: 24px; }
            .totals-section .totals-table { order: -1; }

            .signatures { grid-template-columns: 1fr; }
        }

        /* ---------- Impression ---------- */
        @media print {
            body { background: #fff; padding: 0; font-size: 12px; }
            .action-bar { display: none !important; }
            .invoice-box { max-width: none; padding: 0; border: 0; border-radius: 0; }
            .watermark { opacity: .06; }
            tr, .totals-section, .signatures { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="action-bar">
        <div class="action-group">
            <a href="{{ route('commercial.invoices.show', $invoice) }}" class="btn">← Retour à la facture</a>
            @if(str_starts_with($invoice->reference, 'FAC-POS-') || ($invoice->order && str_starts_with($invoice->order->reference, 'POS-')))
                <a href="{{ route('commercial.pos.index') }}" class="btn">Nouvelle vente</a>
            @endif
        </div>
        <button onclick="window.print()" class="btn btn-primary">Imprimer / Enregistrer en PDF</button>
    </div>

    <div class="invoice-box">
        {{-- Filigrane de statut --}}
        @if($invoice->status === 'payee')
            <div class="watermark" style="color: #047857;">PAYÉE</div>
        @elseif($invoice->status === 'partielle')
            <div class="watermark" style="color: #b45309;">PARTIELLE</div>
        @else
            <div class="watermark" style="color: #b91c1c;">À PAYER</div>
        @endif

        {{-- En-tête --}}
        <div class="header">
            <div class="company">
                <img src="{{ asset('images/logo.png') }}" alt="IVOSPHERE">
                <div>
                    <h1>{{ \App\Models\Setting::get('company_name', 'IVOSPHERE GROUP') }}</h1>
                    <p class="pole">{{ $invoice->domain?->name ?? 'Commercial & Multiservices' }}</p>
                    <p>{{ \App\Models\Setting::get('company_address', "Abidjan, Côte d'Ivoire • Cocody Angré 8e Tranche") }}</p>
                    <p>{{ \App\Models\Setting::get('company_phone', '+225 27 22 00 00 00') }} • {{ \App\Models\Setting::get('company_email', 'contact@ivosphere.com') }}</p>
                    <p>RCCM {{ \App\Models\Setting::get('company_rccm', 'CI-ABJ-2026-B-0012') }} • CC {{ \App\Models\Setting::get('company_cc', '2026-IVOSPHERE') }}</p>
                </div>
            </div>
            <div class="doc-title">
                <h2>FACTURE CLIENT</h2>
                <div class="ref">{{ $invoice->reference }}</div>
                @php
                    $badgeStyle = match($invoice->status) {
                        'payee' => 'background: #d1fae5; color: #065f46;',
                        'partielle' => 'background: #fef3c7; color: #92400e;',
                        default => 'background: #fee2e2; color: #991b1b;',
                    };
                @endphp
                <span class="status-badge" style="{{ $badgeStyle }}">
                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                </span>
            </div>
        </div>

        {{-- Infos facture / client --}}
        <div class="meta-grid">
            <div class="meta-col">
                <h3>Facture</h3>
                <p><span class="label">Émise le</span> {{ $invoice->date->format('d/m/Y') }}</p>
                @if($invoice->due_date)
                    <p><span class="label">Échéance</span> {{ $invoice->due_date->format('d/m/Y') }}</p>
                @endif
                @if($invoice->order)
                    <p><span class="label">Commande</span> {{ $invoice->order->reference }}</p>
                @endif
                <p><span class="label">Gestionnaire</span> {{ $invoice->user?->name ?? 'Direction financière' }}</p>
            </div>
            <div class="meta-col">
                <h3>Facturé à</h3>
                <p class="name">{{ $invoice->customer->name }} <span class="label" style="font-weight: 400;">({{ $invoice->customer->code }})</span></p>
                @if($invoice->customer->company)<p>{{ $invoice->customer->company }}</p>@endif
                @if($invoice->customer->address)<p>{{ $invoice->customer->address }}</p>@endif
                @if($invoice->customer->phone)<p><span class="label">Tél</span> {{ $invoice->customer->phone }}</p>@endif
                @if($invoice->customer->email)<p><span class="label">Email</span> {{ $invoice->customer->email }}</p>@endif
                @if($invoice->customer->nif)<p><span class="label">NIF</span> {{ $invoice->customer->nif }}</p>@endif
            </div>
        </div>

        {{-- Lignes --}}
        <table class="stack">
            <thead>
                <tr>
                    <th style="width: 32px;">#</th>
                    <th>Désignation</th>
                    <th class="text-center" style="width: 60px;">Qté</th>
                    <th class="text-right" style="width: 110px;">Prix unit. HT</th>
                    <th class="text-right" style="width: 80px;">Remise</th>
                    <th class="text-right" style="width: 120px;">Total HT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td class="idx cell-idx">{{ $index + 1 }}</td>
                    <td class="cell-title">
                        <strong>{{ $item->product?->name ?? $item->description }}</strong>
                        @if($item->product && $item->description !== $item->product->name)
                            <div class="sub">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="text-center num" data-label="Quantité">{{ $item->quantity }}</td>
                    <td class="text-right num" data-label="Prix unitaire">{{ number_format($item->unit_price, 0, ',', ' ') }} F</td>
                    <td class="text-right num" data-label="Remise">{{ $item->discount > 0 ? number_format($item->discount, 0, ',', ' ') . ' F' : '—' }}</td>
                    <td class="text-right num" data-label="Total HT" style="font-weight: 600;">{{ number_format(($item->quantity * $item->unit_price) - $item->discount, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Règlement & totaux --}}
        <div class="totals-section">
            <div class="payment-info">
                <h4>Règlement</h4>
                <p><strong>Mobile Money :</strong> +225 07 00 00 00 00 (Wave, Orange, MTN)</p>
                <p><strong>Banque :</strong> {{ \App\Models\Setting::get('company_bank_name', 'Société Générale Côte d\'Ivoire (SGCI)') }}</p>
                <p><strong>RIB / IBAN :</strong> <span class="mono">{{ \App\Models\Setting::get('company_bank_rib', 'CI034 01001 012345678901 45') }}</span></p>
                <p><strong>Titulaire :</strong> {{ \App\Models\Setting::get('company_name', 'GROUPE IVOSPHERE') }} {{ \App\Models\Setting::get('company_legal_form', 'SARL') }}</p>
                <p class="hint">Indiquez la référence <strong>{{ $invoice->reference }}</strong> dans le libellé de votre paiement.</p>
            </div>

            <table class="totals-table">
                <tr>
                    <td>Sous-total HT</td>
                    <td>{{ number_format($invoice->subtotal, 0, ',', ' ') }} FCFA</td>
                </tr>
                @if($invoice->discount > 0)
                <tr class="discount">
                    <td>Remise commerciale</td>
                    <td>− {{ number_format($invoice->discount, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endif
                <tr>
                    <td>TVA ({{ $invoice->tax_amount > 0 ? '18 %' : '0 %' }})</td>
                    <td>{{ number_format($invoice->tax_amount, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr class="grand-total">
                    <td>Total TTC</td>
                    <td>{{ number_format($invoice->total, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr class="paid-row">
                    <td>Déjà réglé</td>
                    <td>− {{ number_format($invoice->paid_amount, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr class="due-row">
                    <td>Reste à payer</td>
                    <td style="color: {{ $invoice->remaining > 0 ? 'var(--bad)' : 'var(--ok)' }};">{{ number_format($invoice->remaining, 0, ',', ' ') }} FCFA</td>
                </tr>
            </table>
        </div>

        {{-- Historique des paiements --}}
        @if($invoice->payments->count() > 0)
        <div class="payments">
            <h4>Paiements reçus</h4>
            <table class="stack">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Date</th>
                        <th>Mode</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $pay)
                    <tr>
                        <td class="mono cell-title">{{ $pay->reference }}</td>
                        <td data-label="Date">{{ $pay->date->format('d/m/Y') }}</td>
                        <td data-label="Mode" style="text-transform: capitalize;">{{ str_replace('_', ' ', $pay->method) }}</td>
                        <td class="text-right num" data-label="Montant" style="font-weight: 600; color: var(--ok);">{{ number_format($pay->amount, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Signatures --}}
        <div class="signatures">
            <div class="signature-box">
                <div class="sign-title">Pour {{ \App\Models\Setting::get('company_name', 'IVOSPHERE GROUP') }}</div>
                <div class="sign-hint">Direction financière et comptable (cachet)</div>
            </div>
            <div class="signature-box">
                <div class="sign-title">Réception client</div>
                <div class="sign-hint">Date, nom et signature</div>
            </div>
        </div>
    </div>
</body>
</html>