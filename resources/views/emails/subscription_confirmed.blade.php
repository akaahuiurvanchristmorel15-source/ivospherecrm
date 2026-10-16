<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><title>Abonnement activé</title></head>
<body style="margin:0;padding:24px;background:#F5F7FA;font-family:Arial,Helvetica,sans-serif;color:#0B0F14;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #E2E8F0;border-radius:14px;">
        <tr><td style="padding:24px 28px;border-bottom:3px solid #0066FF;font-size:20px;font-weight:800;">IVOSPHERE</td></tr>
        <tr>
            <td style="padding:24px 28px;font-size:14px;line-height:1.6;">
                <p style="margin:0 0 12px;">Bonjour {{ $subscription->tenant->name }},</p>
                <p style="margin:0 0 12px;">Votre abonnement <strong>{{ $subscription->plan->name }}</strong> ({{ strtolower($subscription->billingCycleLabel()) }}) est actif jusqu'au <strong>{{ $subscription->ends_at?->format('d/m/Y') }}</strong>.</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;background:#F5F7FA;border-radius:10px;font-size:13px;">
                    <tr><td style="padding:10px 14px;color:#64748B;">Montant payé</td><td style="padding:10px 14px;font-weight:700;text-align:right;">{{ number_format($subscription->amount, 0, ',', ' ') }} FCFA</td></tr>
                    <tr><td style="padding:10px 14px;color:#64748B;">Référence</td><td style="padding:10px 14px;font-weight:700;text-align:right;">{{ $subscription->reference }}</td></tr>
                    @if($adminEmail)
                        <tr><td style="padding:10px 14px;color:#64748B;">Identifiant administrateur</td><td style="padding:10px 14px;font-weight:700;text-align:right;">{{ $adminEmail }}</td></tr>
                    @endif
                </table>
                <p style="margin:0 0 20px;">Le récapitulatif détaillé de votre abonnement est joint à ce message (PDF).</p>
                <p style="margin:0 0 20px;"><a href="{{ route('login') }}" style="display:inline-block;padding:12px 20px;background:#0066FF;color:#ffffff;border-radius:10px;text-decoration:none;font-weight:700;">Accéder à mon espace</a></p>
                <p style="margin:0;color:#64748B;font-size:12px;">Connectez-vous avec l'e-mail et le mot de passe choisis lors de l'inscription.</p>
            </td>
        </tr>
    </table>
</body>
</html>
