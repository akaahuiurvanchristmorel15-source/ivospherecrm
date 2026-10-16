<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Abonnement {{ $subscription->reference }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Helvetica Neue', Arial, sans-serif; color: #0B0F14; font-size: 12px; line-height: 1.5; background: #fff; }
        .page { max-width: 780px; margin: 0 auto; padding: {{ $isPdf ? '0' : '32px 24px' }}; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #0066FF; padding-bottom: 16px; }
        .brand { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
        .brand small { display: block; font-size: 10px; font-weight: 500; color: #64748B; letter-spacing: 0; }
        .doc { text-align: right; }
        .doc h1 { margin: 0; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; }
        .doc p { margin: 2px 0; color: #64748B; font-size: 11px; }
        .status { display: inline-block; margin-top: 6px; padding: 3px 10px; border-radius: 999px; background: #ECFDF5; color: #047857; font-weight: 700; font-size: 10px; text-transform: uppercase; }
        h2 { margin: 24px 0 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #0066FF; }
        .grid { display: flex; gap: 16px; }
        .grid > div { flex: 1; }
        .box { border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 14px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 0; vertical-align: top; }
        td.label { color: #64748B; width: 42%; }
        td.value { font-weight: 600; }
        .modules td { border-bottom: 1px solid #F1F5F9; padding: 8px 0; }
        .modules tr:last-child td { border-bottom: none; }
        .module-name { font-weight: 700; }
        .module-desc { color: #64748B; font-size: 11px; }
        .amount { font-size: 22px; font-weight: 800; }
        .credentials { background: #0B0F14; color: #fff; border-radius: 10px; padding: 14px 16px; }
        .credentials td.label { color: rgba(255,255,255,.6); }
        .credentials td.value { color: #fff; }
        .note { margin-top: 8px; font-size: 10.5px; color: #64748B; }
        .footer { margin-top: 28px; padding-top: 12px; border-top: 1px solid #E2E8F0; font-size: 10px; color: #64748B; text-align: center; }
        .print-bar { margin-bottom: 16px; text-align: right; }
        .print-bar button { padding: 8px 14px; border: none; border-radius: 8px; background: #0066FF; color: #fff; font-weight: 600; cursor: pointer; }
        @media print { .print-bar { display: none; } .page { padding: 0; } }
    </style>
</head>
<body>
<div class="page">
    @unless($isPdf)
        <div class="print-bar"><button type="button" onclick="window.print()">Imprimer / Enregistrer en PDF</button></div>
    @endunless

    <div class="header">
        <div class="brand">
            IVOSPHERE
            <small>{{ $ivosphere['name'] }}</small>
        </div>
        <div class="doc">
            <h1>Récapitulatif d'abonnement</h1>
            <p>Réf. {{ $subscription->reference }}</p>
            <p>Émis le {{ ($subscription->paid_at ?? now())->format('d/m/Y à H:i') }}</p>
            <span class="status">{{ $subscription->isPaid() ? 'Payé — actif' : 'En attente de paiement' }}</span>
        </div>
    </div>

    <div class="grid">
        <div>
            <h2>Abonné</h2>
            <div class="box">
                <table>
                    <tr><td class="label">Entreprise</td><td class="value">{{ $tenant->name }}</td></tr>
                    <tr><td class="label">E-mail</td><td class="value">{{ $tenant->email }}</td></tr>
                    <tr><td class="label">Téléphone</td><td class="value">{{ $tenant->phone }}</td></tr>
                    @if($tenant->city || $tenant->address)
                        <tr><td class="label">Adresse</td><td class="value">{{ collect([$tenant->address, $tenant->city])->filter()->implode(', ') }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
        <div>
            <h2>Abonnement</h2>
            <div class="box">
                <table>
                    <tr><td class="label">Offre</td><td class="value">{{ $plan->name }}</td></tr>
                    <tr><td class="label">Formule</td><td class="value">{{ $subscription->billingCycleLabel() }}</td></tr>
                    <tr><td class="label">Période</td><td class="value">{{ $subscription->starts_at?->format('d/m/Y') ?? '—' }} au {{ $subscription->ends_at?->format('d/m/Y') ?? '—' }}</td></tr>
                    <tr><td class="label">Utilisateurs</td><td class="value">{{ $plan->max_users ? 'Jusqu\'à '.$plan->max_users : 'Illimités' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <h2>Modules inclus</h2>
    <div class="box">
        <table class="modules">
            @foreach($plan->moduleDetails() as $module)
                <tr>
                    <td>
                        <div class="module-name">{{ $module['label'] }}</div>
                        <div class="module-desc">{{ $module['description'] }}</div>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="grid">
        <div>
            <h2>Paiement</h2>
            <div class="box">
                <div class="amount">{{ number_format($subscription->amount, 0, ',', ' ') }} FCFA</div>
                <table>
                    <tr><td class="label">Moyen de paiement</td><td class="value">{{ $subscription->payment_method ?? '—' }}</td></tr>
                    <tr><td class="label">Transaction</td><td class="value">{{ $subscription->payment_transaction_id ?? '—' }}</td></tr>
                    <tr><td class="label">Payé le</td><td class="value">{{ $subscription->paid_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                </table>
            </div>
        </div>
        <div>
            <h2>Compte administrateur de votre espace</h2>
            <div class="credentials">
                <table>
                    <tr><td class="label">Adresse de connexion</td><td class="value">{{ route('login') }}</td></tr>
                    <tr><td class="label">Administrateur</td><td class="value">{{ $admin?->name ?? '—' }}</td></tr>
                    <tr><td class="label">Identifiant</td><td class="value">{{ $admin?->email ?? '—' }}</td></tr>
                    <tr><td class="label">Mot de passe</td><td class="value">Celui choisi à l'inscription</td></tr>
                </table>
            </div>
            <p class="note">Pour votre sécurité, le mot de passe n'est jamais inscrit sur ce document. Vous pouvez le modifier à tout moment depuis « Mon compte ».</p>
        </div>
    </div>

    <div class="footer">
        {{ $ivosphere['name'] }}
        @if($ivosphere['address']) · {{ $ivosphere['address'] }} @endif
        @if($ivosphere['phone']) · {{ $ivosphere['phone'] }} @endif
        @if($ivosphere['email']) · {{ $ivosphere['email'] }} @endif
        <br>Renouvelez votre abonnement avant le {{ $subscription->ends_at?->format('d/m/Y') ?? '—' }} depuis « Mon abonnement » pour conserver l'accès à votre espace.
    </div>
</div>
</body>
</html>
