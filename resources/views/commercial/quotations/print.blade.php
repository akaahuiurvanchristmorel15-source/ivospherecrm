<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis {{ $quotation->reference }} — IVOSPHERE ERP</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
            font-size: 13px;
            line-height: 1.5;
        }
        .invoice-box {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 35px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .logo-box h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #4338ca;
        }
        .logo-box p {
            margin: 3px 0 0 0;
            font-size: 11px;
            color: #64748b;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }
        .doc-title .ref {
            font-size: 14px;
            font-weight: 700;
            color: #4338ca;
            font-family: monospace;
        }
        .meta-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            gap: 20px;
        }
        .meta-col {
            flex: 1;
            background: #f1f5f9;
            padding: 15px;
            border-radius: 6px;
        }
        .meta-col h3 {
            margin: 0 0 8px 0;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            letter-spacing: 0.5px;
        }
        .meta-col p {
            margin: 2px 0;
            font-size: 12px;
            color: #1e293b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 600;
            text-align: left;
            padding: 10px 12px;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
        }
        tr:nth-child(even) td {
            background: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        .totals-table {
            width: 320px;
            margin-bottom: 0;
        }
        .totals-table td {
            padding: 6px 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .totals-table .grand-total td {
            background: #eef2ff;
            color: #312e81;
            font-size: 15px;
            font-weight: 800;
            border-top: 2px solid #4338ca;
            border-bottom: none;
        }
        .terms {
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            margin-bottom: 30px;
            font-size: 11px;
            color: #475569;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 45%;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 15px;
            height: 100px;
            font-size: 11px;
        }
        .signature-box .sign-title {
            font-weight: 700;
            color: #334155;
            margin-bottom: 5px;
        }
        .action-bar {
            max-width: 800px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        .btn-primary {
            background: #4338ca;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #3730a3;
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #334155;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .invoice-box {
                box-shadow: none;
                padding: 0;
            }
            .action-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="action-bar">
        <a href="{{ route('commercial.quotations.show', $quotation) }}" class="btn btn-secondary">
            ← Retour au Devis
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Imprimer / Sauvegarder en PDF
        </button>
    </div>

    <div class="invoice-box">
        <!-- Header -->
        <div class="header">
            <div class="logo-box" style="display: flex; gap: 15px; align-items: center;">
                <img src="{{ asset('images/logo.png') }}" alt="IVOSPHERE" style="height: 64px; width: 64px; object-fit: contain;">
                <div>
                    <h1>{{ \App\Models\Setting::get('company_name', 'IVOSPHERE GROUP') }}</h1>
                    <p><strong>Pôle :</strong> {{ $quotation->domain?->name ?? 'Commercial & Multiservices' }}</p>
                    <p>{{ \App\Models\Setting::get('company_address', "Abidjan, Côte d'Ivoire • Cocody Angré 8e Tranche") }}</p>
                    <p>Tél: {{ \App\Models\Setting::get('company_phone', '+225 27 22 00 00 00') }} • Email: {{ \App\Models\Setting::get('company_email', 'contact@ivosphere.com') }}</p>
                    <p>RCCM: {{ \App\Models\Setting::get('company_rccm', 'CI-ABJ-2026-B-0012') }} • CC: {{ \App\Models\Setting::get('company_cc', '2026-IVOSPHERE') }}</p>
                </div>
            </div>
            <div class="doc-title">
                <h2>DEVIS PROFORMA</h2>
                <div class="ref">{{ $quotation->reference }}</div>
                <div style="margin-top: 6px;">
                    <span class="status-badge" style="background: #e0e7ff; color: #3730a3;">
                        {{ strtoupper($quotation->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Meta Grid -->
        <div class="meta-grid">
            <div class="meta-col">
                <h3>Informations Devis</h3>
                <p><strong>Date d'émission :</strong> {{ $quotation->date->format('d/m/Y') }}</p>
                <p><strong>Date de validité :</strong> {{ $quotation->valid_until ? $quotation->valid_until->format('d/m/Y') : '30 jours' }}</p>
                <p><strong>Établi par :</strong> {{ $quotation->user?->name ?? 'Direction Commerciale' }}</p>
            </div>
            <div class="meta-col">
                <h3>Destinataire / Client</h3>
                <p><strong>Client :</strong> {{ $quotation->customer->name }} ({{ $quotation->customer->code }})</p>
                @if($quotation->customer->company)<p><strong>Entreprise :</strong> {{ $quotation->customer->company }}</p>@endif
                @if($quotation->customer->nif)<p><strong>NIF :</strong> {{ $quotation->customer->nif }}</p>@endif
                @if($quotation->customer->phone)<p><strong>Tél :</strong> {{ $quotation->customer->phone }}</p>@endif
                @if($quotation->customer->email)<p><strong>Email :</strong> {{ $quotation->customer->email }}</p>@endif
                @if($quotation->customer->address)<p><strong>Adresse :</strong> {{ $quotation->customer->address }}</p>@endif
            </div>
        </div>

        <!-- Line items table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 35px;" class="text-center">#</th>
                    <th>Désignation des Articles / Prestations</th>
                    <th class="text-center" style="width: 70px;">Qté</th>
                    <th class="text-right" style="width: 110px;">Prix Unit. HT</th>
                    <th class="text-right" style="width: 70px;">Remise</th>
                    <th class="text-right" style="width: 120px;">Total HT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $index => $item)
                <tr>
                    <td class="text-center" style="color: #64748b;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->name ?? $item->description }}</strong>
                        @if($item->product && $item->description !== $item->product->name)
                            <div style="font-size: 11px; color: #64748b;">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ $item->discount > 0 ? number_format($item->discount, 0, ',', ' ') . ' F' : '-' }}</td>
                    <td class="text-right" style="font-weight: 600;">{{ number_format(($item->quantity * $item->unit_price) - $item->discount, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals summary -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td style="color: #64748b;">Sous-total Brut HT :</td>
                    <td class="text-right" style="font-weight: 600;">{{ number_format($quotation->subtotal, 0, ',', ' ') }} FCFA</td>
                </tr>
                @if($quotation->discount > 0)
                <tr>
                    <td style="color: #10b981;">Remise accordée :</td>
                    <td class="text-right" style="font-weight: 600; color: #10b981;">- {{ number_format($quotation->discount, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endif
                <tr>
                    <td style="color: #64748b;">TVA ({{ $quotation->tax_amount > 0 ? '18%' : '0%' }}) :</td>
                    <td class="text-right" style="font-weight: 600;">{{ number_format($quotation->tax_amount, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr class="grand-total">
                    <td>NET À PAYER TTC :</td>
                    <td class="text-right">{{ number_format($quotation->total, 0, ',', ' ') }} FCFA</td>
                </tr>
            </table>
        </div>

        <!-- Terms and Conditions -->
        <div class="terms">
            <p><strong>Conditions de règlement & Validité :</strong></p>
            <p>{{ $quotation->conditions ?: 'Validité de l\'offre : 30 jours à compter de la date d\'émission. Règlement à la commande ou selon conditions convenues. Les marchandises restent la propriété d\'IVOSPHERE jusqu\'au paiement intégral.' }}</p>
            @if($quotation->notes)
                <p style="margin-top: 5px;"><strong>Note complémentaire :</strong> {{ $quotation->notes }}</p>
            @endif
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="sign-title">Pour IVOSPHERE GROUP</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 30px;">Cachet & Signature autorisée</div>
            </div>
            <div class="signature-box">
                <div class="sign-title">Bon pour Accord (Le Client)</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 30px;">Date, mention manuscrite et signature</div>
            </div>
        </div>
    </div>
</body>
</html>
