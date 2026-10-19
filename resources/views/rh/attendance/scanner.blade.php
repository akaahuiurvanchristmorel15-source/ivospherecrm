<x-layouts.app>
    <x-slot:title>Pointage Collaborateur — IVOSPHERE</x-slot>

    <!-- En-tête mobile épuré -->
    <div class="max-w-md mx-auto space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pointage Sécurisé</span>
                <h1 class="text-xl font-extrabold text-[#0B0F14] tracking-tight">Pointage de Présence</h1>
            </div>
            <a href="{{ route('rh.attendance.terminal') }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:text-[#0066FF] shadow-2xs">
                <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span>Borne QR</span>
            </a>
        </div>

        <!-- Composant Alpine de Pointage par QR code (caméra) -->
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

            @if($geofenceEnabled)
                <div class="flex items-start gap-2.5 rounded-2xl border border-blue-100 bg-blue-50/70 p-3 text-xs text-blue-900">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-[#0066FF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p>Le pointage (arrivée et départ) n'est accepté que sur un lieu autorisé de l'entreprise. Votre position GPS est vérifiée au moment du scan : autorisez la localisation lorsque le téléphone le demande.</p>
                </div>
            @endif

            <!-- 2. Statut du Jour de l'Employé -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs">
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-slate-100">
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase block">{{ $employee?->employee_code ?? 'EMP-0000' }}</span>
                        <h3 class="text-sm font-bold text-[#0B0F14]">{{ $employee?->full_name ?? auth()->user()->name }}</h3>
                        <span class="text-xs text-slate-500">{{ $employee?->position ?? 'Collaborateur' }}</span>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">{{ date('d/m/Y') }}</span>
                </div>

                @if($expectedStart)
                    <div class="mb-3 flex items-center justify-between gap-2 rounded-xl bg-slate-50 px-3 py-2 text-xs text-slate-600">
                        @if($expectedStart['is_working_day'])
                            <span>Prise de poste aujourd'hui : <strong class="text-[#0B0F14]">{{ $expectedStart['start_time'] }}</strong></span>
                            <span class="text-[10px] text-slate-400">{{ $expectedStart['from_schedule'] ? 'Votre planning' : 'Horaire standard' }} · tolérance {{ $settings->punctuality_grace_minutes }} min</span>
                        @else
                            <span>Aujourd'hui est un <strong class="text-[#0B0F14]">jour de repos</strong> sur votre planning.</span>
                        @endif
                    </div>
                @endif

                @if($todayAttendance && $todayAttendance->isCheckedIn())
                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-emerald-50/80 p-3 rounded-xl border border-emerald-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shadow-2xs">✓</div>
                                <div>
                                    <span class="text-xs font-bold text-emerald-950 block">Arrivée validée</span>
                                    <span class="text-[11px] text-emerald-700">
                                        Pointé à {{ $todayAttendance->check_in_at ? $todayAttendance->check_in_at->format('H:i') : substr($todayAttendance->check_in, 0, 5) }}
                                        ({{ $todayAttendance->notes }})
                                    </span>
                                </div>
                            </div>
                            <span class="text-xs font-extrabold text-emerald-800 bg-white px-2 py-1 rounded-lg border border-emerald-200/60 shadow-2xs">
                                +{{ $todayAttendance->punctuality_score }} pt
                            </span>
                        </div>

                        @if($todayAttendance->isCheckedOut())
                            <div class="flex items-center justify-between bg-slate-50 p-3 rounded-xl border border-slate-200/70 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                    <span>Départ validé à <strong>{{ $todayAttendance->check_out_at ? $todayAttendance->check_out_at->format('H:i') : substr($todayAttendance->check_out, 0, 5) }}</strong></span>
                                </div>
                                @if($todayAttendance->isAutomaticCheckout())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        ⚠ Automatique 20h
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        Manuel
                                    </span>
                                @endif
                            </div>
                        @else
                            <button 
                                type="button" 
                                @click="submitCheckOut()"
                                :disabled="submitting"
                                class="w-full py-3 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition flex items-center justify-center gap-2 disabled:opacity-50"
                            >
                                <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span x-text="locating ? 'Localisation en cours…' : (submitting ? 'Enregistrement...' : 'Pointer mon départ (Fin de journée)')"></span>
                            </button>
                        @endif
                    </div>
                @else
                    <!-- Si non pointé : Bouton d'activation du Scanner Caméra -->
                    <div class="space-y-3">
                        <button 
                            type="button" 
                            @click="startCamera()" 
                            x-show="!cameraActive"
                            :disabled="submitting"
                            class="w-full py-3.5 px-4 rounded-2xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-sm font-extrabold shadow-md shadow-[#0066FF]/25 transition flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            <span>Scanner le QR Code d'Arrivée</span>
                        </button>

                        <button 
                            type="button" 
                            @click="manualModalOpen = true" 
                            x-show="!cameraActive"
                            class="w-full py-2 px-3 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-semibold border border-slate-200/80 transition"
                        >
                            Saisir le code / badge manuellement
                        </button>
                    </div>
                @endif
            </div>

            <!-- 3. Viseur Caméra en Direct -->
            <div x-show="cameraActive" class="p-3 bg-black rounded-3xl overflow-hidden relative shadow-xl" style="display: none;">
                <video id="qrVideo" class="w-full h-64 object-cover rounded-2xl" playsinline></video>
                <div class="absolute inset-x-0 bottom-6 flex items-center justify-center gap-3">
                    <button 
                        type="button" 
                        @click="stopCamera()" 
                        class="px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-xs font-bold transition"
                    >
                        Annuler
                    </button>
                </div>
            </div>

            <div x-show="locating" class="flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white p-3.5 text-xs font-semibold text-slate-600" style="display: none;">
                <svg class="h-4 w-4 animate-spin text-[#0066FF]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                <span>Vérification de votre position GPS…</span>
            </div>

            <!-- Messages de retour -->
            <div x-show="resultMessage" x-transition class="p-3.5 rounded-2xl text-xs font-semibold text-center"
                 :class="resultType === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'"
                 style="display: none;">
                <span x-text="resultMessage"></span>
            </div>

            <!-- Modal de saisie manuelle si pas de caméra -->
            <div x-show="manualModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs" style="display: none;">
                <div class="bg-white rounded-3xl p-5 max-w-sm w-full space-y-4 shadow-2xl" @click.outside="manualModalOpen = false">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-bold text-[#0B0F14]">Saisie manuelle</h4>
                        <button type="button" @click="manualModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
                    </div>
                    <p class="text-xs text-slate-500">Entrez le jeton affiché sur la borne des locaux ou votre matricule employé :</p>
                    <input 
                        type="text" 
                        x-model="manualCode" 
                        placeholder="Ex: IVO-LOC-xxxx ou {{ $employee?->employee_code ?? 'EMP-0001' }}" 
                        class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-200 focus:border-[#0066FF] outline-none"
                    >
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="manualModalOpen = false" class="px-3 py-1.5 text-xs text-slate-500">Annuler</button>
                        <button 
                            type="button" 
                            @click="submitCheckIn(manualCode); manualModalOpen = false;" 
                            :disabled="!manualCode || submitting"
                            class="px-4 py-2 rounded-xl bg-[#0066FF] text-white text-xs font-bold disabled:opacity-50"
                        >
                            Valider le pointage
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
