<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Affiche de pointage — {{ $payload['month_name'] }} — IVOSPHERE RH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <script src="{{ asset('vendor/qrcodejs/qrcode.min.js') }}"></script>

    <style>
        @page { size: A4 portrait; margin: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #0b0f14;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Feuille au ratio A4 à l'écran, pleine page à l'impression */
        .poster { aspect-ratio: 210 / 297; }

        /* Le QR est généré en haute résolution puis mis à l'échelle par le CSS */
        #monthly-qrcode canvas,
        #monthly-qrcode img { width: 100% !important; height: auto !important; display: block; }
        #monthly-qrcode img { image-rendering: pixelated; }

        @media print {
            html, body { background: #fff !important; }
            body { padding: 0 !important; display: block !important; }
            .no-print { display: none !important; }
            .poster {
                width: 210mm !important;
                height: 297mm !important;
                max-width: none !important;
                aspect-ratio: auto;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                padding: 16mm 18mm !important;
            }
            .poster-qr { width: 125mm !important; max-width: none !important; }
        }
    </style>
</head>
<body class="min-h-full flex flex-col items-center gap-4 p-3 sm:gap-6 sm:p-8">

    {{-- ============ Barre d'outils (masquée à l'impression) ============ --}}
    <div class="no-print flex w-full max-w-[640px] flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('rh.attendance.index') }}"
           class="inline-flex h-10 items-center gap-2 rounded-xl px-3 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Présences
        </a>

        <form method="GET" action="{{ route('rh.attendance.poster') }}" class="flex items-center gap-2">
            <label for="month" class="sr-only">Mois de l'affiche</label>
            <select id="month" name="month" onchange="this.form.submit()"
                    class="h-10 flex-1 rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-900 focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15 sm:flex-none">
                @foreach($availableMonths as $m)
                    <option value="{{ $m['month'] }}" @selected($m['month'] == $month)>
                        {{ $m['label'] }}{{ $m['is_current'] ? ' (en cours)' : '' }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="year" value="{{ $year }}">
            <button type="button" onclick="window.print()"
                    class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052CC]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimer
            </button>
        </form>
    </div>

    {{-- ============ Affiche A4 ============ --}}
    <main class="poster flex w-full max-w-[640px] flex-col items-center justify-between rounded-2xl bg-white px-6 py-8 text-center shadow-sm sm:px-12 sm:py-12">

        {{-- Titre + mois --}}
        <header class="flex flex-col items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="IVOSPHERE" class="h-10 w-10 object-contain sm:h-12 sm:w-12">
            <h1 class="text-4xl font-black uppercase tracking-tight sm:text-6xl">Pointage</h1>
            <span class="rounded-full bg-[#0B0F14] px-5 py-1.5 text-base font-semibold capitalize text-white sm:text-xl">{{ $payload['month_name'] }}</span>
        </header>

        {{-- QR code --}}
        <div class="poster-qr relative w-full max-w-[340px] rounded-3xl p-4 sm:p-5">
            <span class="absolute left-0 top-0 h-10 w-10 rounded-tl-3xl border-l-[6px] border-t-[6px] border-[#0066FF]"></span>
            <span class="absolute right-0 top-0 h-10 w-10 rounded-tr-3xl border-r-[6px] border-t-[6px] border-[#0066FF]"></span>
            <span class="absolute bottom-0 left-0 h-10 w-10 rounded-bl-3xl border-b-[6px] border-l-[6px] border-[#0066FF]"></span>
            <span class="absolute bottom-0 right-0 h-10 w-10 rounded-br-3xl border-b-[6px] border-r-[6px] border-[#0066FF]"></span>
            <div id="monthly-qrcode" class="aspect-square w-full" role="img" aria-label="QR code de pointage du mois"></div>
        </div>

        {{-- Consignes --}}
        <ol class="grid w-full grid-cols-3 gap-3 sm:gap-5">
            @foreach([
                ['Ouvrez', 'l’application IVOSPHERE', 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'],
                ['Scannez', 'ce QR code', 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z'],
                ['C’est validé', 'votre arrivée est enregistrée', 'M5 13l4 4L19 7'],
            ] as $i => [$title, $text, $icon])
                <li class="flex flex-col items-center gap-2">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl sm:h-14 sm:w-14 {{ $loop->last ? 'bg-emerald-500 text-white' : 'bg-[#0066FF] text-white' }}">
                        <svg class="h-5 w-5 sm:h-7 sm:w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    </span>
                    <span class="text-sm font-bold sm:text-lg">{{ $i + 1 }}. {{ $title }}</span>
                    <span class="-mt-1.5 text-xs leading-snug text-slate-500 sm:text-sm">{{ $text }}</span>
                </li>
            @endforeach
        </ol>

        <p class="text-[11px] text-slate-400 sm:text-xs">Valable du {{ $payload['valid_from'] }} au {{ $payload['valid_until'] }}</p>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Généré en 600 px pour rester net à l'impression ; le CSS adapte la taille.
            new QRCode(document.getElementById('monthly-qrcode'), {
                text: @js($payload['token']),
                width: 600,
                height: 600,
                colorDark: '#0b0f14',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        });
    </script>
</body>
</html>
