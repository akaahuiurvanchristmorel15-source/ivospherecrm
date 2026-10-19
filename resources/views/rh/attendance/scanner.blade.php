@php
    $checkedIn = (bool) $todayAttendance?->isCheckedIn();
    $checkedOut = (bool) $todayAttendance?->isCheckedOut();
    $checkInTime = $checkedIn ? ($todayAttendance->check_in_at?->format('H:i') ?? substr((string) $todayAttendance->check_in, 0, 5)) : null;
    $checkOutTime = $checkedOut ? ($todayAttendance->check_out_at?->format('H:i') ?? substr((string) $todayAttendance->check_out, 0, 5)) : null;

    [$statusLabel, $statusStyle] = match (true) {
        $checkedOut => ['Journée terminée', 'bg-white/10 text-slate-300'],
        $checkedIn => ['Présent', 'bg-emerald-500/15 text-emerald-300'],
        $expectedStart && ! $expectedStart['is_working_day'] => ['Repos', 'bg-white/10 text-slate-300'],
        default => ['À pointer', 'bg-amber-400/15 text-amber-300'],
    };

    $lateMinutes = (int) ($todayAttendance?->late_minutes ?? 0);
    $lateLabel = $lateMinutes >= 60 ? intdiv($lateMinutes, 60).' h '.str_pad((string) ($lateMinutes % 60), 2, '0', STR_PAD_LEFT) : $lateMinutes.' min';

    $displayName = $employee?->full_name ?? auth()->user()->name;
    $initials = collect(preg_split('/\s+/', trim($displayName)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $canOpenTerminal = auth()->user()->isAdmin() || auth()->user()->hasAnyRole(['responsable', 'responsable_rh']);
@endphp

<x-layouts.app>
    <x-slot:title>Pointage — IVOSPHERE</x-slot>

    <div class="mx-auto max-w-md space-y-4 [font-variant-numeric:tabular-nums]">
        <header class="flex items-end justify-between gap-3">
            <div class="space-y-0.5">
                <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">Pointage</h1>
                <p class="text-sm text-[#64748B]">Scannez le QR code des locaux à l'arrivée, pointez votre départ en fin de journée.</p>
            </div>
            @if($canOpenTerminal)
                <a href="{{ route('rh.attendance.terminal') }}" class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-xl border border-[#E2E8F0] bg-white px-3 text-xs font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF]">
                    <svg class="h-4 w-4 text-[#0066FF]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Borne
                </a>
            @endif
        </header>

        <div 
            x-data="{
                hasCamera: false,
                cameraActive: false,
                submitting: false,
                manualCode: '',
                manualModalOpen: false,
                resultMessage: '',
                resultType: '', // 'success' | 'error'
                videoStream: null,
                barcodeDetector: null,
                scanInterval: null,
                geofenceEnabled: @js($geofenceEnabled),
                maxAccuracy: @js((int) config('attendance.geofence.max_accuracy_meters', 100)),
                locating: false,
                lastFix: null,
                locationWatchId: null,

                init() {
                    if ('BarcodeDetector' in window) {
                        try {
                            this.barcodeDetector = new BarcodeDetector({ formats: ['qr_code'] });
                        } catch(e) {
                            console.warn('BarcodeDetector non supporté :', e);
                        }
                    }
                },

                // 0. Géolocalisation haute précision (la vérification de distance est faite par le serveur)
                warmUpLocation() {
                    if (!this.geofenceEnabled || !navigator.geolocation || this.locationWatchId !== null) return;
                    this.locationWatchId = navigator.geolocation.watchPosition(
                        (pos) => this.rememberFix(pos),
                        () => {},
                        { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 }
                    );
                },

                rememberFix(pos) {
                    const fix = { latitude: pos.coords.latitude, longitude: pos.coords.longitude, accuracy: pos.coords.accuracy, at: Date.now() };
                    if (!this.lastFix || Date.now() - this.lastFix.at > 15000 || fix.accuracy <= this.lastFix.accuracy) {
                        this.lastFix = fix;
                    }
                },

                stopLocation() {
                    if (this.locationWatchId !== null) {
                        navigator.geolocation.clearWatch(this.locationWatchId);
                        this.locationWatchId = null;
                    }
                },

                locate() {
                    return new Promise((resolve, reject) => {
                        if (!window.isSecureContext) {
                            return reject(new Error('La localisation nécessite une connexion sécurisée (HTTPS). Ouvrez IVOSPHERE via son adresse https:// puis réessayez.'));
                        }
                        if (!navigator.geolocation) {
                            return reject(new Error('Votre navigateur ne permet pas la localisation : impossible de pointer depuis cet appareil.'));
                        }
                        const isFresh = (fix) => fix && Date.now() - fix.at < 15000;
                        if (isFresh(this.lastFix) && this.lastFix.accuracy <= this.maxAccuracy) {
                            return resolve(this.lastFix);
                        }
                        const notFound = 'Position GPS introuvable. Activez la localisation (GPS) de votre téléphone puis réessayez.';
                        let settled = false;
                        let watchId = null;
                        let timer = null;
                        const finish = (fn, value) => {
                            if (settled) return;
                            settled = true;
                            navigator.geolocation.clearWatch(watchId);
                            clearTimeout(timer);
                            fn(value);
                        };
                        watchId = navigator.geolocation.watchPosition(
                            (pos) => {
                                this.rememberFix(pos);
                                if (this.lastFix.accuracy <= this.maxAccuracy) finish(resolve, this.lastFix);
                            },
                            (err) => {
                                if (err.code === err.PERMISSION_DENIED) {
                                    finish(reject, new Error('Localisation refusée : autorisez l\'accès à votre position pour IVOSPHERE dans les réglages du navigateur, puis réessayez.'));
                                } else if (isFresh(this.lastFix)) {
                                    finish(resolve, this.lastFix);
                                } else {
                                    finish(reject, new Error(notFound));
                                }
                            },
                            { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 }
                        );
                        // Au bout de 12 s, on transmet la meilleure position obtenue : le serveur juge sa précision.
                        timer = setTimeout(() => {
                            isFresh(this.lastFix) ? finish(resolve, this.lastFix) : finish(reject, new Error(notFound));
                        }, 12000);
                    });
                },

                async positionPayload() {
                    if (!this.geofenceEnabled) return {};
                    this.locating = true;
                    try {
                        const fix = await this.locate();
                        return { latitude: fix.latitude, longitude: fix.longitude, accuracy: fix.accuracy };
                    } finally {
                        this.locating = false;
                    }
                },

                // 1. Activation Caméra pour scan QR
                async startCamera() {
                    this.resultMessage = '';
                    this.warmUpLocation();
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: 'environment' }
                        });
                        this.videoStream = stream;
                        const video = document.getElementById('qrVideo');
                        video.srcObject = stream;
                        await video.play();
                        this.cameraActive = true;
                        this.startScanning();
                    } catch (err) {
                        this.cameraActive = false;
                        this.resultType = 'error';
                        this.resultMessage = 'Impossible d\'activer la caméra. Vous pouvez saisir le code manuellement.';
                        this.manualModalOpen = true;
                    }
                },

                stopCamera() {
                    if (this.scanInterval) {
                        clearInterval(this.scanInterval);
                        this.scanInterval = null;
                    }
                    if (this.videoStream) {
                        this.videoStream.getTracks().forEach(t => t.stop());
                        this.videoStream = null;
                    }
                    this.cameraActive = false;
                },

                startScanning() {
                    const video = document.getElementById('qrVideo');
                    this.scanInterval = setInterval(async () => {
                        if (!this.cameraActive || !video.videoWidth) return;
                        if (this.barcodeDetector) {
                            try {
                                const barcodes = await this.barcodeDetector.detect(video);
                                if (barcodes.length > 0) {
                                    const code = barcodes[0].rawValue;
                                    this.stopCamera();
                                    this.submitCheckIn(code);
                                }
                            } catch (e) {
                                console.error('Erreur scan frame :', e);
                            }
                        }
                    }, 400);
                },

                // 2. Soumission du pointage d'arrivée
                async submitCheckIn(qrPayload) {
                    if (!qrPayload) return;
                    this.submitting = true;
                    this.resultMessage = '';

                    let position;
                    try {
                        position = await this.positionPayload();
                    } catch (err) {
                        this.submitting = false;
                        this.resultType = 'error';
                        this.resultMessage = err.message;
                        if (navigator.vibrate) navigator.vibrate(300);
                        return;
                    }

                    try {
                        const res = await fetch('{{ route('rh.attendance.check-in') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                qr_payload: qrPayload,
                                ...position,
                                device_fingerprint: navigator.userAgent
                            })
                        });

                        const data = await res.json();
                        this.submitting = false;
                        this.stopLocation();

                        if (res.ok && data.success) {
                            this.resultType = 'success';
                            this.resultMessage = data.message + ' (Ponctualité : ' + data.punctuality_score + ' pt)';
                            if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.resultType = 'error';
                            this.resultMessage = data.message || 'Échec de la validation du pointage.';
                            if (navigator.vibrate) navigator.vibrate(300);
                        }
                    } catch (err) {
                        this.submitting = false;
                        this.resultType = 'error';
                        this.resultMessage = 'Erreur réseau lors de la transmission du pointage.';
                    }
                },

                // 3. Pointage de départ
                async submitCheckOut() {
                    if (!confirm('Confirmez-vous l\'enregistrement de votre départ maintenant ?')) return;
                    this.submitting = true;
                    this.resultMessage = '';

                    let position;
                    try {
                        position = await this.positionPayload();
                    } catch (err) {
                        this.submitting = false;
                        this.resultType = 'error';
                        this.resultMessage = err.message;
                        if (navigator.vibrate) navigator.vibrate(300);
                        return;
                    }

                    try {
                        const res = await fetch('{{ route('rh.attendance.check-out') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(position)
                        });

                        const data = await res.json();
                        this.submitting = false;

                        if (res.ok && data.success) {
                            this.resultType = 'success';
                            this.resultMessage = data.message;
                            if (navigator.vibrate) navigator.vibrate([150, 100]);
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.resultType = 'error';
                            this.resultMessage = data.message || 'Erreur lors du départ.';
                        }
                    } catch (err) {
                        this.submitting = false;
                        this.resultType = 'error';
                        this.resultMessage = 'Erreur réseau.';
                    }
                }
            }"
            class="space-y-4"
        >

            {{-- ───────── Carte du jour ───────── --}}
            <section class="overflow-hidden rounded-3xl bg-[#0B0F14] text-white">
                <div class="space-y-5 p-5">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-sm font-semibold">{{ $initials }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">{{ $employee?->full_name ?? auth()->user()->name }}</p>
                            <p class="truncate text-xs text-slate-400">{{ $employee?->position ?? 'Collaborateur' }} · {{ $employee?->employee_code ?? '—' }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-medium {{ $statusStyle }}">{{ $statusLabel }}</span>
                    </div>

                    <div class="text-center">
                        <p class="text-5xl font-semibold tracking-tight sm:text-6xl"
                           x-data="{ time: '', tick() { this.time = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) } }"
                           x-init="tick(); setInterval(() => tick(), 1000)"
                           x-text="time">{{ now()->format('H:i') }}</p>
                        <p class="mt-1 text-xs text-slate-400 first-letter:uppercase">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
                    </div>

                    @if($expectedStart)
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-white/[0.06] px-4 py-3">
                            @if($expectedStart['is_working_day'])
                                <div>
                                    <p class="text-[11px] text-slate-400">Prise de poste</p>
                                    <p class="text-base font-semibold">{{ $expectedStart['start_time'] }}</p>
                                </div>
                                <p class="text-right text-[11px] leading-snug text-slate-400">
                                    {{ $expectedStart['from_schedule'] ? 'Selon votre planning' : 'Horaire standard' }}<br>
                                    Tolérance {{ $settings->punctuality_grace_minutes }} min
                                </p>
                            @else
                                <p class="text-sm">Jour de repos sur votre planning</p>
                                <span class="text-lg" aria-hidden="true">☾</span>
                            @endif
                        </div>
                    @endif
                </div>
            </section>

            {{-- ───────── Arrivée / Départ ───────── --}}
            <section class="grid grid-cols-2 gap-3" aria-label="Pointages du jour">
                @foreach([
                    ['Arrivée', $checkInTime, $checkedIn ? ($todayAttendance->is_late ? 'Retard '.$lateLabel : 'À l\'heure') : 'À pointer', $checkedIn ? ($todayAttendance->is_late ? 'text-amber-600' : 'text-emerald-600') : 'text-[#64748B]', 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1'],
                    ['Départ', $checkOutTime, $checkedOut ? ($todayAttendance->isAutomaticCheckout() ? 'Clôture automatique' : 'Pointé') : ($checkedIn ? 'En cours de journée' : '—'), $checkedOut && $todayAttendance->isAutomaticCheckout() ? 'text-amber-600' : 'text-[#64748B]', 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1'],
                ] as [$label, $time, $hint, $hintColor, $icon])
                    <article class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-[#64748B]">{{ $label }}</p>
                            <svg class="h-4 w-4 {{ $time ? 'text-[#0066FF]' : 'text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        </div>
                        <p class="mt-2 text-2xl font-semibold {{ $time ? 'text-[#0B0F14]' : 'text-slate-300' }}">{{ $time ?? '--:--' }}</p>
                        <p class="mt-0.5 truncate text-[11px] {{ $hintColor }}">{{ $hint }}</p>
                    </article>
                @endforeach
            </section>

            @if($checkedIn)
                <div class="flex items-center justify-between rounded-2xl border border-[#E2E8F0] bg-white px-4 py-3 text-xs">
                    <span class="text-[#64748B]">Note de ponctualité du jour</span>
                    <span class="font-semibold text-[#0B0F14]">+{{ number_format((float) $todayAttendance->punctuality_score, 2, ',', ' ') }} pt</span>
                </div>
            @endif

            {{-- ───────── Action ───────── --}}
            <section class="space-y-3">
                @if(! $checkedIn)
                    <button type="button" @click="startCamera()" :disabled="submitting"
                            class="flex h-14 w-full items-center justify-center gap-2.5 rounded-2xl bg-[#0066FF] text-base font-semibold text-white shadow-lg shadow-[#0066FF]/25 transition hover:bg-[#0052cc] active:scale-[0.99] disabled:opacity-60">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7V5a1 1 0 011-1h2M17 4h2a1 1 0 011 1v2M20 17v2a1 1 0 01-1 1h-2M7 20H5a1 1 0 01-1-1v-2M8 12h8"/></svg>
                        <span x-text="locating ? 'Localisation en cours…' : (submitting ? 'Validation…' : 'Scanner pour pointer mon arrivée')">Scanner pour pointer mon arrivée</span>
                    </button>
                    <button type="button" @click="manualModalOpen = true"
                            class="w-full py-1 text-center text-xs font-medium text-[#64748B] underline-offset-4 hover:text-[#0066FF] hover:underline">
                        Le scan ne fonctionne pas ? Saisir le code
                    </button>
                @elseif(! $checkedOut)
                    <button type="button" @click="submitCheckOut()" :disabled="submitting"
                            class="flex h-14 w-full items-center justify-center gap-2.5 rounded-2xl bg-[#0B0F14] text-base font-semibold text-white transition hover:bg-slate-800 active:scale-[0.99] disabled:opacity-60">
                        <svg class="h-5 w-5 text-rose-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span x-text="locating ? 'Localisation en cours…' : (submitting ? 'Enregistrement…' : 'Pointer mon départ')">Pointer mon départ</span>
                    </button>
                @else
                    <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-emerald-900">Journée terminée</p>
                            <p class="text-xs text-emerald-700">Arrivée et départ enregistrés. Bonne fin de journée !</p>
                        </div>
                    </div>
                @endif

                @if($geofenceEnabled && ! $checkedOut)
                    <p class="flex items-center justify-center gap-1.5 text-center text-[11px] text-[#64748B]">
                        <svg class="h-3.5 w-3.5 shrink-0 text-[#0066FF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Votre position GPS est vérifiée : pointez depuis un lieu autorisé.
                    </p>
                @endif
            </section>

            {{-- ───────── Retours ───────── --}}
            <div x-show="locating" x-transition class="flex items-center justify-center gap-2 rounded-2xl border border-[#E2E8F0] bg-white p-3.5 text-xs font-medium text-[#64748B]" style="display: none;">
                <svg class="h-4 w-4 animate-spin text-[#0066FF]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                <span>Vérification de votre position GPS…</span>
            </div>

            <div x-show="resultMessage" x-transition role="status"
                 class="flex items-start gap-3 rounded-2xl border p-4 text-sm"
                 :class="resultType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-rose-200 bg-rose-50 text-rose-900'"
                 style="display: none;">
                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[11px] font-bold text-white"
                      :class="resultType === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
                      x-text="resultType === 'success' ? '✓' : '!'"></span>
                <p class="leading-snug" x-text="resultMessage"></p>
            </div>

            {{-- ───────── Viseur caméra (plein écran) ───────── --}}
            <div x-show="cameraActive" x-transition.opacity class="fixed inset-0 z-50 flex flex-col bg-black" style="display: none;">
                <video id="qrVideo" class="absolute inset-0 h-full w-full object-cover" playsinline muted></video>
                <div class="relative flex flex-1 flex-col items-center justify-center px-8">
                    <div class="relative aspect-square w-full max-w-64 rounded-3xl shadow-[0_0_0_9999px_rgba(0,0,0,0.55)]">
                        <span class="absolute -left-0.5 -top-0.5 h-10 w-10 rounded-tl-3xl border-l-4 border-t-4 border-white"></span>
                        <span class="absolute -right-0.5 -top-0.5 h-10 w-10 rounded-tr-3xl border-r-4 border-t-4 border-white"></span>
                        <span class="absolute -bottom-0.5 -left-0.5 h-10 w-10 rounded-bl-3xl border-b-4 border-l-4 border-white"></span>
                        <span class="absolute -bottom-0.5 -right-0.5 h-10 w-10 rounded-br-3xl border-b-4 border-r-4 border-white"></span>
                    </div>
                    <p class="relative mt-6 text-center text-sm font-medium text-white">Placez le QR code de pointage dans le cadre</p>
                </div>
                <div class="relative flex justify-center pb-[max(2rem,env(safe-area-inset-bottom))]">
                    <button type="button" @click="stopCamera()" class="h-12 rounded-full bg-white/15 px-8 text-sm font-semibold text-white backdrop-blur-md transition hover:bg-white/25">
                        Annuler
                    </button>
                </div>
            </div>

            {{-- ───────── Saisie manuelle ───────── --}}
            <div x-show="manualModalOpen" x-cloak @keydown.escape.window="manualModalOpen = false"
                 class="fixed inset-0 z-50 flex items-end justify-center bg-[#0B0F14]/50 backdrop-blur-sm sm:items-center sm:p-4"
                 role="dialog" aria-modal="true" aria-labelledby="manualTitle" style="display: none;">
                <div @click.outside="manualModalOpen = false" class="w-full rounded-t-3xl bg-white p-5 pb-[max(1.25rem,env(safe-area-inset-bottom))] shadow-xl sm:max-w-sm sm:rounded-2xl">
                    <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-slate-200 sm:hidden"></div>
                    <h3 id="manualTitle" class="text-base font-semibold text-[#0B0F14]">Saisir le code</h3>
                    <p class="mt-1 text-xs text-[#64748B]">Entrez le code affiché sous le QR de la borne ou de l'affiche, ou votre matricule.</p>
                    <input type="text" x-model="manualCode" autocomplete="off" autocapitalize="characters"
                           placeholder="Ex. IVO-LOC-… ou {{ $employee?->employee_code ?? 'EMP-0001' }}"
                           class="mt-4 w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-3 font-mono text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15">
                    <div class="mt-4 flex gap-2">
                        <button type="button" @click="manualModalOpen = false" class="h-11 flex-1 rounded-xl border border-[#E2E8F0] text-sm font-medium text-[#0B0F14] hover:bg-slate-50">Annuler</button>
                        <button type="button" @click="submitCheckIn(manualCode); manualModalOpen = false;" :disabled="!manualCode || submitting"
                                class="h-11 flex-1 rounded-xl bg-[#0066FF] text-sm font-semibold text-white hover:bg-[#0052cc] disabled:opacity-50">Valider</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
