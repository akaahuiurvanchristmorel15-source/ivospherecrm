@php
    use App\Models\Setting;
    use Illuminate\Support\Facades\Storage;

    $companyName = Setting::get('company_name', 'IVOSPHERE GROUP');
    $companyLegalForm = Setting::get('company_legal_form', '');
    $companyAddress = Setting::get('company_address', '');
    $companyPostalBox = Setting::get('company_postal_box', '');
    $companyPhone = Setting::get('company_phone', '');
    $companyEmail = Setting::get('company_email', '');
    $companyRccm = Setting::get('company_rccm', '');
    $companyCc = Setting::get('company_cc', '');
    $companyLogo = Setting::get('company_logo', '');

    $logoUrl = match (true) {
        $companyLogo && Storage::disk('public')->exists($companyLogo) => asset('storage/'.$companyLogo),
        file_exists(public_path('images/logo.png')) => asset('images/logo.png'),
        default => null,
    };

    $tasks = collect(is_array($sheet->tasks) ? $sheet->tasks : [])->map(fn ($task) => [
        'title' => is_array($task) ? ($task['title'] ?? $task['label'] ?? '') : (string) $task,
        'done' => is_array($task) && ($task['status'] ?? '') === 'termine',
        'validation' => is_array($task) ? ($task['validation_status'] ?? null) : null,
    ]);
    $doneCount = $tasks->where('done', true)->count();
    $progress = $tasks->isNotEmpty() ? round($doneCount / $tasks->count() * 100) : 0;

    $statusLabels = ['assigne' => 'Assignée', 'en_cours' => 'En cours', 'termine' => 'Terminée'];
    $statusLabel = $statusLabels[$sheet->status] ?? ucfirst(str_replace('_', ' ', $sheet->status));

    $contactLine = collect([$companyAddress, $companyPostalBox])->filter()->implode(' · ');
    $reachLine = collect([$companyPhone ? 'Tél. '.$companyPhone : null, $companyEmail])->filter()->implode(' · ');
    $legalLine = collect([
        $companyLegalForm,
        $companyRccm ? 'RCCM '.$companyRccm : null,
        $companyCc ? 'CC '.$companyCc : null,
    ])->filter()->implode(' · ');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de tâches {{ $sheet->reference }} — {{ $sheet->employee?->full_name }}</title>
    <style>
        @page { size: A4; margin: 14mm 14mm 16mm; }

        * { box-sizing: border-box; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body {
            margin: 0;
            padding: 24px 16px;
            background: #eef1f5;
            color: #0b0f14;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 11.5px;
            line-height: 1.5;
        }

        /* ── Barre d'actions (écran uniquement) ── */
        .toolbar {
            max-width: 210mm;
            margin: 0 auto 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: space-between;
            align-items: center;
        }
        .toolbar-group { display: flex; gap: 8px; align-items: center; }
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            height: 36px; padding: 0 14px;
            border-radius: 10px; border: 1px solid #e2e8f0;
            background: #fff; color: #334155;
            font: 600 12.5px/1 inherit; text-decoration: none; cursor: pointer;
        }
        .btn:hover { background: #f8fafc; }
        .btn-primary { background: #0066ff; border-color: #0066ff; color: #fff; }
        .btn-primary:hover { background: #0052cc; }
        .btn-whatsapp { background: #25d366; border-color: #25d366; color: #fff; }
        .btn-whatsapp:hover { background: #1eb956; }

        /* ── Feuille A4 ── */
        .page {
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 16mm 15mm;
            background: #fff;
            box-shadow: 0 10px 30px -12px rgba(15, 23, 42, .18);
            display: flex;
            flex-direction: column;
        }

        /* En-tête */
        .header { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; }
        .company { display: flex; gap: 12px; align-items: flex-start; }
        .company img { width: 52px; height: 52px; object-fit: contain; }
        .company-initials {
            width: 52px; height: 52px; border-radius: 10px;
            background: #0b0f14; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 18px;
        }
        .company-name { margin: 0; font-size: 15px; font-weight: 800; letter-spacing: -.2px; }
        .company-line { color: #64748b; font-size: 10.5px; }

        .doc { text-align: right; }
        .doc-kicker { font-size: 9.5px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #0066ff; }
        .doc-title { margin: 2px 0 0; font-size: 20px; font-weight: 800; letter-spacing: -.4px; }
        .doc-ref { margin-top: 4px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 11px; color: #334155; }

        .rule { height: 3px; margin: 14px 0 18px; background: linear-gradient(90deg, #0066ff 0 64px, #e2e8f0 64px); border-radius: 2px; }

        /* Bandeau d'informations */
        .facts {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr 1fr;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .fact { padding: 10px 12px; border-right: 1px solid #e2e8f0; }
        .fact:last-child { border-right: 0; }
        .fact-label { font-size: 9px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: #94a3b8; }
        .fact-value { margin-top: 2px; font-size: 12px; font-weight: 700; }
        .fact-sub { font-size: 10.5px; color: #64748b; }

        .status {
            display: inline-block; padding: 1px 8px; border-radius: 999px;
            font-size: 10.5px; font-weight: 700;
            background: #eff6ff; color: #0066ff;
        }
        .status-termine { background: #ecfdf5; color: #047857; }

        /* Section */
        .section-head { display: flex; justify-content: space-between; align-items: baseline; margin: 22px 0 8px; }
        .section-title { margin: 0; font-size: 12px; font-weight: 800; letter-spacing: .2px; text-transform: uppercase; }
        .progress { display: flex; align-items: center; gap: 8px; font-size: 10.5px; color: #64748b; }
        .progress-bar { width: 90px; height: 5px; background: #e2e8f0; border-radius: 999px; overflow: hidden; }
        .progress-bar span { display: block; height: 100%; background: #10b981; }

        /* Tableau des tâches */
        table { width: 100%; border-collapse: collapse; }
        thead th {
            padding: 8px 10px;
            background: #0b0f14; color: #fff;
            font-size: 9.5px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase;
            text-align: left;
        }
        thead th:first-child { border-radius: 8px 0 0 0; }
        thead th:last-child { border-radius: 0 8px 0 0; }
        tbody td { padding: 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tbody tr:nth-child(even) td { background: #f8fafc; }
        tbody tr { page-break-inside: avoid; }
        .col-num { width: 34px; text-align: center; font-weight: 800; color: #0066ff; }
        .col-check { width: 72px; text-align: center; }
        .col-validation { width: 82px; text-align: center; }
        .col-notes { width: 150px; }
        .task-title { font-weight: 600; }
        .box {
            display: inline-flex; align-items: center; justify-content: center;
            width: 15px; height: 15px; border: 1.5px solid #94a3b8; border-radius: 4px;
            color: #fff; font-size: 11px; font-weight: 800; line-height: 1;
        }
        .box-done { background: #10b981; border-color: #10b981; }
        .tag { font-size: 10px; font-weight: 700; }
        .tag-ok { color: #047857; }
        .tag-ko { color: #b91c1c; }
        .tag-wait { color: #94a3b8; font-weight: 500; }
        .write-line { height: 16px; border-bottom: 1px dotted #cbd5e1; }
        .empty { padding: 24px; text-align: center; color: #94a3b8; border: 1px dashed #e2e8f0; border-radius: 10px; }

        /* Consignes */
        .notes {
            margin-top: 18px;
            padding: 10px 14px;
            border-left: 3px solid #0066ff;
            background: #f5f8ff;
            border-radius: 0 8px 8px 0;
        }
        .notes-label { font-size: 9.5px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: #0066ff; }
        .notes p { margin: 3px 0 0; white-space: pre-line; }

        /* Bilan & signatures */
        .bottom { margin-top: auto; padding-top: 26px; }
        .review { display: grid; grid-template-columns: 1fr; gap: 6px; margin-bottom: 22px; }
        .review-label { font-size: 9.5px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: #64748b; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .signature { border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; height: 92px; display: flex; flex-direction: column; }
        .signature-role { font-size: 9.5px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: #64748b; }
        .signature-name { font-size: 11.5px; font-weight: 600; }
        .signature-hint { margin-top: auto; font-size: 9.5px; color: #94a3b8; }

        .footer {
            margin-top: 18px; padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            display: flex; justify-content: space-between; gap: 12px;
            font-size: 9px; color: #94a3b8;
        }

        @media (max-width: 720px) {
            body { padding: 12px 8px; }
            .page { padding: 20px 16px; min-height: 0; }
            .header { flex-direction: column; }
            .doc { text-align: left; }
            .facts { grid-template-columns: 1fr 1fr; }
            .fact:nth-child(2) { border-right: 0; }
            .fact:nth-child(-n+2) { border-bottom: 1px solid #e2e8f0; }
            .col-notes { display: none; }
        }

        @media print {
            body { background: #fff; padding: 0; font-size: 11px; }
            .toolbar { display: none !important; }
            .page { max-width: none; min-height: calc(297mm - 30mm); padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <div class="toolbar-group">
            <a href="{{ route('rh.daily-tasks.show', $sheet) }}" class="btn">← Retour à la fiche</a>
        </div>
        <div class="toolbar-group">
            @if($sheet->whats_app_url)
                <a href="{{ $sheet->whats_app_url }}" target="_blank" rel="noopener" class="btn btn-whatsapp">Envoyer sur WhatsApp</a>
            @endif
            <button type="button" onclick="window.print()" class="btn btn-primary">Imprimer / PDF</button>
        </div>
    </div>

    <main class="page">
        {{-- En-tête --}}
        <header class="header">
            <div class="company">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $companyName }}">
                @else
                    <div class="company-initials">{{ strtoupper(substr($companyName, 0, 2)) }}</div>
                @endif
                <div>
                    <p class="company-name">{{ $companyName }}</p>
                    <div class="company-line">Direction des Ressources Humaines</div>
                    @if($contactLine)<div class="company-line">{{ $contactLine }}</div>@endif
                    @if($reachLine)<div class="company-line">{{ $reachLine }}</div>@endif
                </div>
            </div>
            <div class="doc">
                <div class="doc-kicker">Document interne</div>
                <h1 class="doc-title">Fiche de tâches du jour</h1>
                <div class="doc-ref">N° {{ $sheet->reference }}</div>
            </div>
        </header>

        <div class="rule"></div>

        {{-- Informations --}}
        <section class="facts">
            <div class="fact">
                <div class="fact-label">Collaborateur</div>
                <div class="fact-value">{{ $sheet->employee?->full_name ?? '—' }}</div>
                <div class="fact-sub">{{ $sheet->position }}@if($sheet->employee?->employee_code) · {{ $sheet->employee->employee_code }}@endif</div>
            </div>
            <div class="fact">
                <div class="fact-label">Date</div>
                <div class="fact-value">{{ $sheet->date->translatedFormat('d F Y') }}</div>
                <div class="fact-sub">{{ ucfirst($sheet->date->translatedFormat('l')) }}</div>
            </div>
            <div class="fact">
                <div class="fact-label">Assignée par</div>
                <div class="fact-value">{{ $sheet->assignedBy?->name ?? 'Direction RH' }}</div>
                <div class="fact-sub">{{ $sheet->employee?->department ?? 'Général' }}</div>
            </div>
            <div class="fact">
                <div class="fact-label">Statut</div>
                <div class="fact-value"><span class="status {{ $sheet->status === 'termine' ? 'status-termine' : '' }}">{{ $statusLabel }}</span></div>
                <div class="fact-sub">{{ $doneCount }} / {{ $tasks->count() }} réalisée(s)</div>
            </div>
        </section>

        {{-- Tâches --}}
        <div class="section-head">
            <h2 class="section-title">Tâches à réaliser</h2>
            <div class="progress">
                <div class="progress-bar"><span style="width: {{ $progress }}%"></span></div>
                {{ $progress }} %
            </div>
        </div>

        @if($tasks->isEmpty())
            <div class="empty">Aucune tâche sur cette fiche.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th class="col-num">N°</th>
                        <th>Tâche</th>
                        <th class="col-check">Réalisée</th>
                        <th class="col-validation">Validation</th>
                        <th class="col-notes">Observations</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $index => $task)
                        <tr>
                            <td class="col-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="task-title">{{ $task['title'] }}</td>
                            <td class="col-check"><span class="box {{ $task['done'] ? 'box-done' : '' }}">{{ $task['done'] ? '✓' : '' }}</span></td>
                            <td class="col-validation">
                                @if($task['validation'] === 'valide')
                                    <span class="tag tag-ok">Validée</span>
                                @elseif($task['validation'] === 'refuse')
                                    <span class="tag tag-ko">Refusée</span>
                                @else
                                    <span class="tag tag-wait">—</span>
                                @endif
                            </td>
                            <td class="col-notes"><div class="write-line"></div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($sheet->notes)
            <section class="notes">
                <div class="notes-label">Consignes</div>
                <p>{{ $sheet->notes }}</p>
            </section>
        @endif

        {{-- Bilan & signatures --}}
        <section class="bottom">
            <div class="review">
                <div class="review-label">Bilan de la journée</div>
                <div class="write-line"></div>
                <div class="write-line"></div>
            </div>

            <div class="signatures">
                <div class="signature">
                    <div class="signature-role">Le collaborateur</div>
                    <div class="signature-name">{{ $sheet->employee?->full_name }}</div>
                    <div class="signature-hint">Date et signature</div>
                </div>
                <div class="signature">
                    <div class="signature-role">Le superviseur</div>
                    <div class="signature-name">{{ $sheet->assignedBy?->name ?? 'Direction RH' }}</div>
                    <div class="signature-hint">Visa et signature</div>
                </div>
            </div>

            <footer class="footer">
                <span>{{ $legalLine ?: $companyName }}</span>
                <span>Fiche {{ $sheet->reference }} · éditée le {{ now()->format('d/m/Y à H:i') }}</span>
            </footer>
        </section>
    </main>

</body>
</html>
