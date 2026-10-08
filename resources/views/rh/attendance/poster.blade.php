<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Affiche de pointage — {{ $payload['month_name'] }} — IVOSPHERE RH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        @page { size: A4 portrait; margin: 10mm 12mm; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #0f172a;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .font-mono-ref { font-family: 'JetBrains Mono', ui-monospace, monospace; }

        /* Le QR est généré en haute résolution puis mis à l'échelle par le CSS */
        #monthly-qrcode canvas,
        #monthly-qrcode img { width: 100% !important; height: auto !important; display: block; }
        #monthly-qrcode img { image-rendering: pixelated; }

        @media print {
            html, body { background: #fff !important; }
            body { padding: 0 !important; display: block !important; }
            .no-print { display: none !important; }
            .poster {
                max-width: 100% !important;
                border: 1.5px solid #0f172a !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                padding: 8mm 10mm !important;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body class="min-h-full flex flex-col items-center p-3 sm:p-8 gap-4 sm:gap-6">

    {{-- ============ Barre d'outils (masquée à l'impression) ============ --}}
    <div class="no-print w-full max-w-[760px] bg-white rounded-xl border border-slate-200 p-3 sm:p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <nav class="flex items-center gap-2 overflow-x-auto [scrollbar-width:none]">
            <a href="{{ route('rh.attendance.index') }}"
               class="shrink-0 h-10 px-3.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-medium inline-flex items-center gap-2 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Registre des présences
            </a>
            <a href="{{ route('rh.attendance.terminal') }}"
               class="shrink-0 h-10 px-3.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-medium inline-flex items-center gap-2 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 4h4v4H5V4zm10 0h4v4h-4V4zM5 16h4v4H5v-4zm10 0h1m3 0h1m-5 4h5m-5-8v4m-3-4h.01M4 12h4m12 0h.01"/></svg>
                Mode écran
            </a>
        </nav>

        <form method="GET" action="{{ route('rh.attendance.poster') }}" class="flex flex-col sm:flex-row sm:items-center gap-2">
            <input type="hidden" name="year" value="{{ $year }}">
            <div class="flex items-center gap-2">
                <label for="month" class="text-sm text-slate-600 shrink-0">Période</label>
                <select id="month" name="month" onchange="this.form.submit()"
                        class="flex-1 sm:flex-none h-10 px-3 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-900 focus:outline-none focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/15">
                    @foreach($availableMonths as $m)
                        <option value="{{ $m['month'] }}" @selected($m['month'] == $month)>
                            {{ $m['label'] }}{{ $m['is_current'] ? ' (en cours)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="button" onclick="window.print()"
                    class="h-10 px-4 rounded-lg bg-[#0066FF] hover:bg-[#0052CC] text-white text-sm font-semibold inline-flex items-center justify-center gap-2 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimer (A4)
            </button>
        </form>
    </div>

    {{-- ============ Affiche A4 ============ --}}
    <main class="poster w-full max-w-[760px] bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-10 flex flex-col gap-6 sm:gap-7">

        {{-- En-tête --}}
        <header class="flex items-center justify-between gap-4 pb-4 border-b border-slate-200">
            <div class="flex items-center gap-3 min-w-0">
                <img src="{{ asset('images/logo.png') }}" alt="IVOSPHERE" class="w-11 h-11 sm:w-12 sm:h-12 object-contain shrink-0">
                <div class="min-w-0">
                    <p class="text-base sm:text-lg font-bold text-slate-900 leading-tight">IVOSPHERE ERP</p>
                    <p class="text-xs sm:text-sm text-slate-500">Ressources humaines</p>
                </div>
            </div>
            <p class="font-mono-ref text-[11px] text-slate-400 text-right shrink-0">Réf. {{ $payload['token'] }}</p>
        </header>

        {{-- Titre + période --}}
        <section class="text-center space-y-4">
            <div class="space-y-1.5">
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Pointage de présence</h1>
                <p class="text-sm sm:text-base text-slate-500 max-w-md mx-auto">
                    Scannez ce QR code à votre arrivée. Votre compte et votre position sont vérifiés.
                </p>
            </div>

            <div class="inline-flex flex-col items-center px-6 py-3 rounded-xl bg-slate-900 text-white">
                <span class="text-xl sm:text-2xl font-bold capitalize">{{ $payload['month_name'] }}</span>
                <span class="text-xs sm:text-sm text-slate-300 mt-0.5">
                    Valable du {{ $payload['valid_from'] }} au {{ $payload['valid_until'] }}
                </span>
            </div>
        </section>

        {{-- QR code --}}
        <section class="flex flex-col items-center gap-4">
            <div class="relative p-4 sm:p-5 rounded-2xl border-2 border-slate-900 bg-white w-full max-w-[320px]">
                <span class="absolute -top-px -left-px w-5 h-5 border-t-4 border-l-4 border-[#0066FF] rounded-tl-2xl"></span>
                <span class="absolute -top-px -right-px w-5 h-5 border-t-4 border-r-4 border-[#0066FF] rounded-tr-2xl"></span>
                <span class="absolute -bottom-px -left-px w-5 h-5 border-b-4 border-l-4 border-[#0066FF] rounded-bl-2xl"></span>
                <span class="absolute -bottom-px -right-px w-5 h-5 border-b-4 border-r-4 border-[#0066FF] rounded-br-2xl"></span>

                <div id="monthly-qrcode" class="w-full aspect-square" role="img" aria-label="QR code de pointage du mois"></div>
            </div>

            <p class="text-sm font-medium text-slate-700 text-center">Scannez ce code à votre arrivée dans les locaux</p>

            <p class="max-w-lg text-xs sm:text-[13px] text-slate-500 text-center leading-relaxed">
                Ce code est renouvelé le 1<sup>er</sup> de chaque mois. Un scan effectué hors de la zone autorisée, ou avec une ancienne affiche, est refusé.
            </p>
        </section>

        {{-- Étapes --}}
        <section>
            <h2 class="text-sm font-semibold text-slate-900 mb-3 text-center sm:text-left">Comment pointer</h2>
            <ol class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach([
                    ['Connexion', 'Ouvrez votre espace collaborateur IVOSPHERE.'],
                    ['Scanner', 'Lancez le scanner de pointage dans l’application.'],
                    ['Localisation', 'Autorisez le GPS : vous devez être à moins de ' . $payload['radius'] . ' m.'],
                    ['Scan', 'Visez ce QR code : votre arrivée et vos tâches s’activent.'],
                ] as $i => [$title, $text])
                    <li class="rounded-xl border border-slate-200 p-3.5">
                        <span class="w-6 h-6 rounded-full inline-flex items-center justify-center text-xs font-bold text-white {{ $i === 3 ? 'bg-emerald-600' : 'bg-[#0066FF]' }}">{{ $i + 1 }}</span>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $title }}</p>
                        <p class="mt-0.5 text-xs text-slate-500 leading-snug">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Pied de page --}}
        <footer class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 text-xs text-slate-500">
            <dl class="space-y-0.5">
                <div><dt class="inline font-medium text-slate-700">Lieu :</dt> <dd class="inline">{{ $payload['office_name'] }} · rayon {{ $payload['radius'] }} m</dd></div>
                <div><dt class="inline font-medium text-slate-700">Horaires :</dt> <dd class="inline">{{ $settings->work_start_time }} → {{ $settings->work_end_time }} · clôture auto à {{ $settings->auto_checkout_time }}</dd></div>
            </dl>
            <div class="sm:text-right">
                <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
                <p class="font-medium text-slate-700">Direction des ressources humaines</p>
            </div>
        </footer>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Généré en 480 px pour rester net à l'impression ; le CSS adapte la taille à l'écran.
            new QRCode(document.getElementById('monthly-qrcode'), {
                text: "{{ $payload['token'] }}",
                width: 480,
                height: 480,
                colorDark: "#0b0f14",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        });
    </script>
</body>
</html>