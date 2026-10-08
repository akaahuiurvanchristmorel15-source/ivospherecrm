<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Borne de Pointage QR — IVOSPHERE ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <!-- Librairie QR Code légère et autonome (sans dépendance externe requise) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="h-full text-slate-100 flex flex-col justify-between p-6 sm:p-10 select-none overflow-hidden font-sans">

    <!-- En-tête : Logo + Nom Entreprise + Horloge Temps Réel -->
    <header class="flex items-center justify-between border-b border-slate-800/80 pb-6">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-white p-1 flex items-center justify-center shadow-lg shadow-black/20 shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="IVOSPHERE" class="w-full h-full object-contain">
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-extrabold tracking-tight text-white">IVOSPHERE ERP</h1>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Borne Active
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">Pointage officiel sécurisé des locaux</p>
            </div>
        </div>

        <!-- Horloge numérique temps réel -->
        <div class="text-right">
            <div id="liveClock" class="text-3xl sm:text-4xl font-mono font-extrabold text-white tracking-wider">
                --:--:--
            </div>
            <div id="liveDate" class="text-xs font-semibold text-slate-400 uppercase tracking-widest mt-0.5">
                Chargement...
            </div>
        </div>
    </header>

    <!-- Zone Centrale : QR Code Dynamique + Instruction -->
    <main class="flex-1 flex flex-col items-center justify-center my-8 text-center max-w-2xl mx-auto w-full">
        
        <!-- Badge Localisation & Géofence -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900 border border-slate-800 text-xs text-slate-300 mb-6 shadow-sm">
            <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Zone autorisée : <strong>{{ $settings->geofence_radius_meters }} mètres</strong> autour du siège</span>
        </div>

        <!-- Cadre QR Code Dynamique -->
        <div class="relative p-6 sm:p-8 bg-white rounded-3xl shadow-[0_0_50px_rgba(0,102,255,0.18)] border-4 border-slate-800/60 flex flex-col items-center">
            
            <div id="qrcode" class="flex items-center justify-center min-h-[260px] min-w-[260px]"></div>

            <!-- Token en clair discret pour assistance -->
            <div class="mt-4 pt-3 border-t border-slate-100 w-full flex items-center justify-between text-[11px] font-mono text-slate-400">
                <span id="tokenText" class="truncate max-w-[200px]">{{ $payload['token'] }}</span>
                <span id="countdown" class="font-bold text-[#0066FF]">--s</span>
            </div>
        </div>

        <!-- Instructions pour les employés -->
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-3 text-left w-full">
            <div class="p-3.5 rounded-2xl bg-slate-900/80 border border-slate-800/80">
                <div class="w-6 h-6 rounded-lg bg-[#0066FF]/20 text-[#0066FF] flex items-center justify-center font-bold text-xs mb-2">1</div>
                <h4 class="text-xs font-bold text-white">Connectez-vous</h4>
                <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Ouvrez l'application IVOSPHERE sur votre smartphone.</p>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-900/80 border border-slate-800/80">
                <div class="w-6 h-6 rounded-lg bg-[#0066FF]/20 text-[#0066FF] flex items-center justify-center font-bold text-xs mb-2">2</div>
                <h4 class="text-xs font-bold text-white">Scannez le code</h4>
                <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Visez l'écran en autorisant la géolocalisation GPS.</p>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-900/80 border border-slate-800/80">
                <div class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs mb-2">3</div>
                <h4 class="text-xs font-bold text-white">Arrivée validée</h4>
                <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Votre présence et vos tâches du jour s'affichent aussitôt.</p>
            </div>
        </div>
    </main>

    <!-- Pied de page : Statut & Retour RH -->
    <footer class="flex items-center justify-between border-t border-slate-800/80 pt-4 text-xs text-slate-500">
        <div class="flex items-center gap-2">
            <span>Horaire standard : <strong>{{ $settings->work_start_time }} → {{ $settings->work_end_time }}</strong></span>
            <span>•</span>
            <span>Départ automatique : <strong>{{ $settings->auto_checkout_time }}</strong></span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('rh.attendance.poster') }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-emerald-400 font-semibold transition border border-slate-800 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Affiche A4 Imprimable</span>
            </a>
            <a href="{{ route('rh.attendance.index') }}" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 font-semibold transition border border-slate-800">
                ← Quitter la borne
            </a>
        </div>
    </footer>

    <!-- Script de rafraîchissement temps réel & génération QR -->
    <script>
        let qrcodeInstance = null;
        let currentToken = "{{ $payload['token'] }}";
        let secondsLeft = {{ $payload['expires_in'] }};

        function renderQr(token) {
            const container = document.getElementById("qrcode");
            container.innerHTML = "";
            qrcodeInstance = new QRCode(container, {
                text: token,
                width: 256,
                height: 256,
                colorDark : "#0B0F14",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M
            });
            document.getElementById("tokenText").innerText = token;
        }

        async function fetchFreshToken() {
            try {
                const res = await fetch("{{ route('rh.attendance.dynamic-qr-token') }}");
                if (res.ok) {
                    const data = await res.json();
                    currentToken = data.token;
                    secondsLeft = data.expires_in;
                    renderQr(currentToken);
                }
            } catch (err) {
                console.error("Erreur rafraîchissement token :", err);
            }
        }

        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById("liveClock").innerText = `${hours}:${minutes}:${seconds}`;

            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById("liveDate").innerText = now.toLocaleDateString('fr-FR', options);

            // Décompte renouvellement
            secondsLeft--;
            if (secondsLeft <= 0) {
                fetchFreshToken();
            } else {
                document.getElementById("countdown").innerText = `Renouvellement dans ${secondsLeft}s`;
            }
        }

        // Initialisation
        renderQr(currentToken);
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>
