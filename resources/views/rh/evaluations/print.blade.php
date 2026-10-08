<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport d'Évaluation — {{ $employee->full_name }} — {{ $evaluation->month_name }} {{ $evaluation->year }}</title>
    <style>
        @page {
            size: A4;
            margin: 12mm 15mm;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .sheet-box {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
            background: #f1f5f9;
            margin: 0;
            padding: 24px;
            font-size: 12px;
            line-height: 1.45;
        }
        .action-bar {
            max-width: 820px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .sheet-box {
            position: relative;
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            padding: 35px 40px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0066FF;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .brand-title {
            font-size: 20px;
            font-weight: 900;
            color: #0b0f14;
            letter-spacing: -0.5px;
        }
        .brand-title span {
            color: #0066FF;
        }
        .brand-sub {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            margin-top: 2px;
        }
        .meta-pill {
            text-align: right;
            font-size: 11px;
            color: #64748b;
        }
        .meta-pill strong {
            color: #0b0f14;
        }
        .doc-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .doc-title h1 {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0f172a;
            margin: 0;
        }
        .doc-title p {
            font-size: 12px;
            color: #64748b;
            margin: 3px 0 0 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 20px;
        }
        .info-item {
            font-size: 11px;
        }
        .info-label {
            color: #64748b;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: block;
        }
        .info-val {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 1px;
        }
        .score-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #0f172a;
            color: #ffffff;
            border-radius: 12px;
            padding: 14px 22px;
            margin-bottom: 20px;
        }
        .score-big {
            font-size: 26px;
            font-weight: 900;
            color: #ffffff;
            font-family: monospace;
        }
        .score-big span {
            font-size: 14px;
            color: #94a3b8;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 20px;
        }
        .table th {
            background: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }
        .table td {
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
            color: #0f172a;
        }
        .section-title {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin: 16px 0 8px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .analysis-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 12px;
            font-size: 11px;
        }
        .analysis-box strong {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .sig-block {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 12px;
            background: #ffffff;
            min-height: 90px;
            display: flex;
            flex-col: column;
            justify-content: space-between;
        }
        .sig-header {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: #475569;
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .sig-body {
            font-size: 11px;
            color: #0066FF;
            font-family: monospace;
            font-weight: 700;
        }
        .sig-date {
            font-size: 9px;
            color: #64748b;
            margin-top: 4px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: #0066FF;
            color: #ffffff;
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #cbd5e1;
        }
    </style>
</head>
<body>

    <!-- Barre d'Action (Masquée à l'impression) -->
    <div class="action-bar no-print">
        <div>
            <a href="{{ route('rh.evaluations.show', $evaluation) }}" class="btn btn-secondary">
                ← Retour à la Fiche
            </a>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Imprimer le Rapport (A4)
            </button>
        </div>
    </div>

    <!-- Feuille A4 du Rapport Officiel -->
    <div class="sheet-box">
        <!-- En-tête officiel IVOSPHERE -->
        <div class="header">
            <div>
                <div class="brand-title">IVO<span>SPHERE</span></div>
                <div class="brand-sub">Direction des Ressources Humaines</div>
            </div>
            <div class="meta-pill">
                <div>Réf Dossier : <strong>EVAL-{{ $evaluation->year }}-{{ str_pad($evaluation->month, 2, '0', STR_PAD_LEFT) }}-{{ $employee->employee_code }}</strong></div>
                <div>Date d'édition : <strong>{{ now()->format('d/m/Y à H:i') }}</strong></div>
                <div>Statut : <strong>{{ $evaluation->is_locked ? 'Verrouillé & Certifié' : ucfirst(str_replace('_', ' ', $evaluation->status)) }}</strong></div>
            </div>
        </div>

        <!-- Titre du Document -->
        <div class="doc-title">
            <h1>Rapport Mensuel d'Évaluation de Performance</h1>
            <p>Période évaluée : <strong>{{ $evaluation->month_name }} {{ $evaluation->year }}</strong> • Système de notation sur 20 points (10 matières)</p>
        </div>

        <!-- Dossier Collaborateur -->
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Collaborateur Évalué</span>
                <div class="info-val">{{ $employee->full_name }}</div>
                <div style="font-size: 10px; color: #64748b; font-family: monospace;">Matricule : {{ $employee->employee_code }}</div>
            </div>
            <div class="info-item">
                <span class="info-label">Fonction & Service</span>
                <div class="info-val">{{ $employee->position ?? 'Collaborateur' }}</div>
                <div style="font-size: 10px; color: #64748b;">Département : {{ $employee->department ?? 'Général' }}</div>
            </div>
            <div class="info-item">
                <span class="info-label">Jours Ouvrés / Planifiés</span>
                <div class="info-val">{{ $evaluation->total_working_days }} jours</div>
            </div>
            <div class="info-item">
                <span class="info-label">Responsable Évaluateur</span>
                <div class="info-val">{{ $evaluation->evaluator?->name ?? 'Direction RH IVOSPHERE' }}</div>
            </div>
        </div>

        <!-- Synthèse du Score / 20 -->
        <div class="score-banner">
            <div>
                <span style="font-size: 9px; text-transform: uppercase; font-weight: 800; color: #0066FF; letter-spacing: 1px;">Moyenne Mensuelle Officielle</span>
                <div class="score-big">
                    {{ number_format($evaluation->final_score_20, 2, ',', ' ') }} <span>/ 20 pts</span>
                </div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">
                    Total : <strong>{{ number_format($evaluation->total_score_200, 1) }} / 200 pts</strong>
                </div>
            </div>
            <div style="text-align: right; font-size: 11px;">
                <div style="font-weight: 800; color: #38bdf8; font-size: 14px; text-transform: uppercase;">
                    {{ $evaluation->appreciation }}
                </div>
                <div style="color: #94a3b8; font-size: 10px; margin-top: 2px;">
                    Taux d'accomplissement : {{ round(((float)$evaluation->final_score_20 / 20) * 100) }} %
                    @if($evaluation->score_progress > 0)
                        • Progression : +{{ number_format($evaluation->score_progress, 2) }} pts
                    @elseif($evaluation->score_progress < 0)
                        • Évolution : {{ number_format($evaluation->score_progress, 2) }} pts
                    @endif
                </div>
            </div>
        </div>

        <!-- Tableau des Critères d'Évaluation -->
        <div class="section-title">1. Barème Détaillé des 10 Matières (Chaque matière sur 20)</div>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">#</th>
                    <th>Matière évaluée</th>
                    <th style="text-align: center; width: 85px;">Mode</th>
                    <th style="text-align: center; width: 75px;">Note /20</th>
                    <th>Détail & Justificatif factuel</th>
                    <th>Observations RH</th>
                </tr>
            </thead>
            <tbody>
                @forelse($evaluation->criteriaScores as $idx => $critScore)
                    @php $c = $critScore->criterion; @endphp
                    <tr>
                        <td style="text-align: center; font-family: monospace; font-weight: 700; color: #64748b;">
                            {{ $idx + 1 }}
                        </td>
                        <td>
                            <strong>{{ $c->name ?? 'Matière' }}</strong>
                        </td>
                        <td style="text-align: center; font-size: 10px;">
                            {{ $c?->isAutomatic() ? 'Automatique' : 'Évaluation RH' }}
                        </td>
                        <td style="text-align: center; font-family: monospace; font-weight: 800; font-size: 12px; color: #0066FF;">
                            {{ number_format($critScore->score, 2) }} / 20
                        </td>
                        <td style="font-size: 10px; color: #0f172a;">
                            {{ $critScore->justification ?? '—' }}
                        </td>
                        <td style="font-size: 10px; color: #475569;">
                            {{ $critScore->comments ?? 'Conforme aux attentes' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8;">Aucune matière enregistrée.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td colspan="3" style="text-align: right; text-transform: uppercase; font-size: 10px;">Total (Somme des 10 notes) :</td>
                    <td style="text-align: center; font-family: monospace; font-size: 12px; color: #0b0f14;">
                        {{ number_format($evaluation->total_score_200, 2) }} / 200
                    </td>
                    <td colspan="2" style="font-size: 10px; color: #0066FF;">
                        Moyenne mensuelle : <strong>{{ number_format($evaluation->final_score_20, 2) }} / 20</strong> — Mention : <strong>{{ $evaluation->appreciation }}</strong>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Synthèse Qualitative & Recommandations -->
        <div class="section-title">2. Appréciation Managériale & Développement</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="analysis-box">
                <strong style="color: #059669;">Points Forts Constatés</strong>
                {{ $evaluation->strengths ?: 'Non renseigné' }}
            </div>
            <div class="analysis-box">
                <strong style="color: #d97706;">Axes d'Amélioration & Vigilance</strong>
                {{ $evaluation->weaknesses ?: 'Non renseigné' }}
            </div>
            <div class="analysis-box">
                <strong style="color: #0066FF;">Recommandations & Actions RH</strong>
                {{ $evaluation->recommendations ?: 'Non renseigné' }}
            </div>
            <div class="analysis-box">
                <strong style="color: #7c3aed;">Objectifs Prioritaires pour le Mois Prochain</strong>
                {{ $evaluation->future_goals ?: 'Non renseigné' }}
            </div>
        </div>

        @if($evaluation->manager_comment)
            <div class="analysis-box" style="margin-top: 4px;">
                <strong style="color: #0b0f14;">Synthèse Globale de la Direction RH</strong>
                {{ $evaluation->manager_comment }}
            </div>
        @endif

        <!-- Historique Récent (6 derniers mois) -->
        @if($history->isNotEmpty())
            <div class="section-title">3. Trajectoire des 6 Derniers Mois</div>
            <table class="table" style="margin-bottom: 12px;">
                <thead>
                    <tr>
                        @foreach($history as $h)
                            <th style="text-align: center;">{{ $h->month_name }} {{ $h->year }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach($history as $h)
                            <td style="text-align: center; font-family: monospace; font-weight: 700;">
                                {{ number_format((float) ($h->final_score_20 ?: ($h->final_score_30 / 1.5)), 2) }} / 20
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        @endif

        <!-- Blocs de Signatures Formelles -->
        <div class="signatures">
            <div class="sig-block">
                <div class="sig-header">Pour la Direction des Ressources Humaines</div>
                <div class="sig-body">
                    @if($evaluation->manager_signature)
                        ✓ Signé électroniquement : {{ $evaluation->manager_signature }}
                        <div class="sig-date">Le {{ $evaluation->manager_signed_at?->format('d/m/Y à H:i') }}</div>
                    @else
                        <div style="color: #94a3b8; font-style: italic; font-weight: normal; margin-top: 25px;">
                            (Signature et cachet RH)
                        </div>
                    @endif
                </div>
            </div>

            <div class="sig-block">
                <div class="sig-header">Le Collaborateur (Mention « Lu et pris connaissance »)</div>
                <div class="sig-body">
                    @if($evaluation->employee_signature)
                        ✓ Émargé électroniquement : {{ $evaluation->employee_signature }}
                        <div class="sig-date">Le {{ $evaluation->employee_signed_at?->format('d/m/Y à H:i') }}</div>
                    @else
                        <div style="color: #94a3b8; font-style: italic; font-weight: normal; margin-top: 25px;">
                            (Date et signature du collaborateur)
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div style="margin-top: 25px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; text-align: center;">
            Document officiel généré automatiquement par la plateforme IVOSPHERE CRM — Système de Notation RH Normalisé /30.
        </div>
    </div>

</body>
</html>
