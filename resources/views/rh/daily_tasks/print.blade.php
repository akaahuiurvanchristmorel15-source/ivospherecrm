<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Tâches — {{ $sheet->reference }} — {{ $sheet->employee?->full_name }}</title>
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
            font-size: 13px;
            line-height: 1.5;
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
            padding: 40px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .watermark {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 70px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 6px;
            color: rgba(0, 102, 255, 0.03);
            pointer-events: none;
            user-select: none;
            white-space: nowrap;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0066FF;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .brand-title {
            font-size: 24px;
            font-weight: 900;
            color: #0066FF;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .brand-sub {
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 1px;
            margin-top: 3px;
            font-weight: 700;
        }
        .doc-title-block {
            text-align: right;
        }
        .doc-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .doc-meta {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            font-family: monospace;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-assigned {
            background: #ebf3ff;
            color: #0066FF;
            border: 1px solid #bfdbfe;
        }
        .badge-done {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
        }
        .info-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 12px;
        }
        .info-label {
            color: #64748b;
            font-weight: 600;
        }
        .info-val {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
        }
        .table-tasks {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .table-tasks th {
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            text-align: left;
        }
        .table-tasks td {
            border: 1px solid #e2e8f0;
            padding: 12px;
            font-size: 12px;
            vertical-align: middle;
        }
        .task-num {
            font-weight: 800;
            color: #0066FF;
            text-align: center;
            width: 35px;
        }
        .task-check {
            width: 90px;
            text-align: center;
        }
        .checkbox-box {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 1.5px solid #94a3b8;
            border-radius: 4px;
            vertical-align: middle;
        }
        .notes-card {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 0 8px 8px 0;
            padding: 12px 16px;
            font-size: 12px;
            color: #92400e;
            margin-bottom: 24px;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 35px;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
        }
        .sig-block {
            text-align: center;
        }
        .sig-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 50px;
        }
        .sig-line {
            border-top: 1px solid #94a3b8;
            margin: 0 30px;
            padding-top: 6px;
            font-size: 11px;
            color: #64748b;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 12px;
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
            transition: all 0.2s;
        }
        .btn-primary {
            background: #0066FF;
            color: #ffffff;
            border: none;
        }
        .btn-primary:hover {
            background: #0052cc;
        }
        .btn-whatsapp {
            background: #25D366;
            color: #ffffff;
            border: none;
        }
        .btn-whatsapp:hover {
            background: #1eb956;
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

    <!-- Barre d'actions (Invisible à l'impression) -->
    <div class="action-bar no-print">
        <div style="display: flex; align-items: center; gap: 8px;">
            <a href="{{ route('rh.daily-tasks.index') }}" class="btn btn-secondary">
                ← Retour à la liste
            </a>
            <span style="font-size: 12px; color: #64748b;">Fiche <strong>{{ $sheet->reference }}</strong></span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            @if($sheet->whats_app_url)
                <a href="{{ $sheet->whats_app_url }}" target="_blank" class="btn btn-whatsapp" title="Envoyer directement sur WhatsApp">
                    💬 Ouvrir WhatsApp
                </a>
            @endif
            <button onclick="window.print()" class="btn btn-primary" title="Imprimer ou enregistrer au format PDF">
                🖨️ Imprimer en PDF
            </button>
        </div>
    </div>

    <!-- Fiche de Tâches Officielle (A4) -->
    <div class="sheet-box">
        <div class="watermark">IVOSPHERE RH</div>

        <!-- En-tête officiel -->
        <div class="header">
            <div style="display: flex; gap: 14px; align-items: center;">
                <img src="{{ asset('images/logo.png') }}" alt="IVOSPHERE" style="height: 56px; width: 56px; object-fit: contain;">
                <div>
                    <h1 class="brand-title">IVOSPHERE</h1>
                    <div class="brand-sub">ERP &bull; Direction des Ressources Humaines</div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                        Plateforme intégrée de gestion et suivi opérationnel
                    </div>
                </div>
            </div>
            <div class="doc-title-block">
                <div class="doc-title">Fiche de Tâches Journalières</div>
                <div class="doc-meta">RÉF : <strong>{{ $sheet->reference }}</strong></div>
                <div style="margin-top: 6px;">
                    <span class="badge {{ $sheet->status === 'termine' ? 'badge-done' : 'badge-assigned' }}">
                        {{ strtoupper(str_replace('_', ' ', $sheet->status)) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Informations Collaborateur et Mission -->
        <div class="info-grid">
            <div class="info-card">
                <div class="info-title">👤 Collaborateur Assigné</div>
                <div class="info-row">
                    <span class="info-label">Nom complet :</span>
                    <span class="info-val">{{ $sheet->employee?->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Poste / Fonction :</span>
                    <span class="info-val" style="color: #0066FF;">{{ $sheet->position }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Matricule :</span>
                    <span class="info-val">{{ $sheet->employee?->employee_code ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Département :</span>
                    <span class="info-val">{{ $sheet->employee?->department ?? 'Général' }}</span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-title">📅 Détails de la Mission</div>
                <div class="info-row">
                    <span class="info-label">Date d'exécution :</span>
                    <span class="info-val">{{ $sheet->date->translatedFormat('d F Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Superviseur / Assigné par :</span>
                    <span class="info-val">{{ $sheet->assignedBy?->name ?? 'Direction RH' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Téléphone WhatsApp :</span>
                    <span class="info-val">{{ $sheet->employee?->phone ?? 'Non renseigné' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email :</span>
                    <span class="info-val">{{ $sheet->employee?->email ?? 'Non renseigné' }}</span>
                </div>
            </div>
        </div>

        <!-- Tableau des Tâches du Jour -->
        <table class="table-tasks">
            <thead>
                <tr>
                    <th class="task-num">N°</th>
                    <th>Intitulé & Objectif de la Tâche</th>
                    <th class="task-check">Statut</th>
                    <th style="width: 140px; text-align: center;">Émargement</th>
                </tr>
            </thead>
            <tbody>
                @if(is_array($sheet->tasks))
                    @foreach($sheet->tasks as $idx => $task)
                        @php
                            $title = is_array($task) ? ($task['title'] ?? '') : (string) $task;
                            $isDone = is_array($task) && (($task['status'] ?? '') === 'termine');
                        @endphp
                        <tr>
                            <td class="task-num">{{ $idx + 1 }}</td>
                            <td>
                                <strong style="color: #0f172a;">{{ $title }}</strong>
                            </td>
                            <td class="task-check">
                                <span class="badge {{ $isDone ? 'badge-done' : 'badge-assigned' }}" style="font-size: 9px;">
                                    {{ $isDone ? 'TERMINÉ' : 'À FAIRE' }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="checkbox-box" style="{{ $isDone ? 'background: #059669; border-color: #059669;' : '' }}"></span>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <!-- Consignes particulières / Notes -->
        @if($sheet->notes)
            <div class="notes-card">
                <strong>💡 Instructions & Consignes particulières :</strong><br>
                {{ $sheet->notes }}
            </div>
        @endif

        <!-- Bloc de Signatures -->
        <div class="signatures">
            <div class="sig-block">
                <div class="sig-title">Le Superviseur / RH</div>
                <div class="sig-line">Nom, Visa et Signature</div>
            </div>
            <div class="sig-block">
                <div class="sig-title">L'Employé(e) Réceptionnaire</div>
                <div class="sig-line">Mention « Lu et approuvé » & Signature</div>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            IVOSPHERE ERP &bull; Document officiel RH édité le {{ now()->format('d/m/Y à H:i') }} &bull; Fiche Réf : {{ $sheet->reference }}
        </div>
    </div>

</body>
</html>
