<x-layouts.app>
    <script src="{{ asset('vendor/zxing/zxing-browser.min.js') }}"></script>
    <script>
        /**
         * Scanner universel de codes-barres & QR Codes pour le comptoir POS IVOSPHERE.
         * - Lecture directe caméra (arriére / webcam) avec moteur ZXing local & BarcodeDetector
         * - Prise de photo instantanée (capture="environment") fonctionnant 100% même en HTTP/LAN sur smartphone
         * - Douchette USB/Bluetooth (émulation clavier) : saisie + Entrée
         * - Reconnaissance universelle : EAN-13, EAN-8, QR Code, Code 128, SKU
         */
        function posBarcodeScanner() {
            const DUPLICATE_SCAN_DELAY_MS = 1400;

            // Handles natifs gardés hors de l'état Alpine pour ne pas casser MediaStream
            let activeStream = null;
            let activeZxingControls = null;
            let scanLoopTimer = null;

            return {
                scannerOpen: false,
                scannerStatus: '',
                scannerError: '',
                scanFeedback: null,
                scanFeedbackTimer: null,
                continuousScan: true,
                manualCode: '',
                lastScannedCode: '',
                lastScannedAt: 0,
                torchOn: false,
                hasTorch: false,
                selectedCameraId: '',
                camerasList: [],

                normalizeCode(code) {
                    return String(code ?? '').replace(/[\s\-_]/g, '').trim().toLowerCase();
                },

                findProductByCode(code) {
                    let needle = this.normalizeCode(code);
                    if (!needle) return null;

                    // Si le QR code contient un format JSON
                    if (typeof code === 'string' && code.includes('{') && code.includes('}')) {
                        try {
                            const parsed = JSON.parse(code);
                            const cand = parsed.barcode || parsed.ean || parsed.sku || parsed.code;
                            if (cand) needle = this.normalizeCode(cand);
                        } catch(e) {}
                    }

                    return this.products.find(p => this.normalizeCode(p.barcode) === needle)
                        || this.products.find(p => this.normalizeCode(p.ean) === needle)
                        || this.products.find(p => this.normalizeCode(p.sku) === needle)
                        || this.products.find(p => String(p.id) === needle)
                        || null;
                },

                handleScannedCode(rawCode) {
                    const code = String(rawCode ?? '').trim();
                    if (!code) return false;

                    this.lastScannedCode = code;
                    const product = this.findProductByCode(code);

                    if (product) {
                        const added = this.addToCart(product);
                        if (added) {
                            this.search = '';
                            this.notifyScan('success', product.name + ' ajouté au panier');
                            return true;
                        }
                        return false;
                    }

                    this.search = code;
                    this.notifyScan('error', 'Code inconnu ou non répertorié : ' + code);
                    return false;
                },

                onSearchEnter() {
                    const query = this.search.trim();
                    if (!query) return;

                    if (this.findProductByCode(query)) {
                        this.handleScannedCode(query);
                        return;
                    }

                    if (this.filteredProducts.length === 1) {
                        const product = this.filteredProducts[0];
                        const added = this.addToCart(product);
                        if (added) {
                            this.search = '';
                            this.notifyScan('success', product.name + ' ajouté au panier');
                        }
                        return;
                    }

                    this.notifyScan('error', 'Aucun article trouvé pour : ' + query);
                },

                onCameraDecode(rawCode) {
                    const code = String(rawCode ?? '').trim();
                    const now = Date.now();
                    if (!code || (code === this.lastScannedCode && now - this.lastScannedAt < DUPLICATE_SCAN_DELAY_MS)) {
                        return;
                    }

                    this.lastScannedAt = now;
                    const found = this.handleScannedCode(code);

                    if (found && !this.continuousScan) {
                        this.closeScanner();
                    }
                },

                notifyScan(type, message) {
                    this.scanFeedback = { type, message };
                    clearTimeout(this.scanFeedbackTimer);
                    this.scanFeedbackTimer = setTimeout(() => { this.scanFeedback = null; }, 2400);

                    try {
                        navigator.vibrate?.(type === 'success' ? 70 : [80, 60, 80]);
                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        if (AudioCtx) {
                            const ctx = new AudioCtx();
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.frequency.value = type === 'success' ? 1200 : 320;
                            gain.gain.value = 0.08;
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.start();
                            osc.stop(ctx.currentTime + (type === 'success' ? 0.09 : 0.22));
                            osc.onended = () => ctx.close();
                        }
                    } catch (e) {}
                },

                async openScanner() {
                    this.scannerError = '';
                    this.scannerStatus = 'Démarrage de la caméra…';
                    this.scannerOpen = true;
                    this.hasTorch = false;
                    this.torchOn = false;

                    await this.$nextTick();
                    const video = this.$refs.scannerVideo || document.getElementById('posScannerVideo');
                    if (!video) {
                        this.scannerError = "Composant vidéo non trouvé.";
                        return;
                    }

                    this.stopCamera();

                    // 1. Énumération des caméras
                    try {
                        if (navigator.mediaDevices?.enumerateDevices) {
                            const devices = await navigator.mediaDevices.enumerateDevices();
                            this.camerasList = devices.filter(d => d.kind === 'videoinput');
                        }
                    } catch(e) {}

                    // 2. Tenter d'ouvrir la caméra avec fallbacks automatiques
                    let stream = null;
                    if (navigator.mediaDevices?.getUserMedia) {
                        const attempts = [
                            { video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false },
                            { video: { facingMode: { ideal: 'environment' } }, audio: false },
                            { video: true, audio: false }
                        ];

                        if (this.selectedCameraId) {
                            attempts.unshift({ video: { deviceId: { exact: this.selectedCameraId } }, audio: false });
                        }

                        for (const constraint of attempts) {
                            try {
                                stream = await navigator.mediaDevices.getUserMedia(constraint);
                                if (stream) break;
                            } catch (err) {}
                        }
                    }

                    if (!this.scannerOpen) {
                        if (stream) stream.getTracks().forEach(t => t.stop());
                        return;
                    }

                    if (!stream) {
                        this.scannerError = "L'accès direct au flux vidéo n'est pas autorisé ou bloqué sur cette connexion HTTP. Vous pouvez utiliser le bouton 'Prendre une photo' ou la saisie manuelle ci-dessous.";
                        return;
                    }

                    activeStream = stream;
                    video.srcObject = stream;
                    try {
                        await video.play();
                    } catch(e) {}

                    const track = stream.getVideoTracks()[0];
                    if (track?.getCapabilities?.()?.torch) {
                        this.hasTorch = true;
                    }

                    this.scannerStatus = 'Placez le code-barres ou QR code dans le viseur';
                    this.startDecodingLoop(video);
                },

                async toggleTorch() {
                    if (!activeStream || !this.hasTorch) return;
                    const track = activeStream.getVideoTracks()[0];
                    if (!track) return;
                    try {
                        this.torchOn = !this.torchOn;
                        await track.applyConstraints({ advanced: [{ torch: this.torchOn }] });
                    } catch(e) {
                        this.torchOn = false;
                    }
                },

                async switchCamera() {
                    if (this.camerasList.length <= 1) return;
                    const currentIdx = this.camerasList.findIndex(c => c.deviceId === this.selectedCameraId);
                    const nextIdx = (currentIdx + 1) % this.camerasList.length;
                    this.selectedCameraId = this.camerasList[nextIdx].deviceId;
                    this.openScanner();
                },

                startDecodingLoop(video) {
                    clearTimeout(scanLoopTimer);

                    // 1. Moteur ZXing universel
                    if (window.ZXingBrowser && window.ZXingBrowser.BrowserMultiFormatReader) {
                        try {
                            const reader = new window.ZXingBrowser.BrowserMultiFormatReader();
                            reader.decodeFromVideoElement(video, (result, error, controls) => {
                                activeZxingControls = controls;
                                if (result && result.getText()) {
                                    this.onCameraDecode(result.getText());
                                }
                            });
                            return;
                        } catch(e) {
                            console.warn('ZXing fallback to manual frame detect', e);
                        }
                    }

                    // 2. Fallback avec BarcodeDetector
                    let detector = null;
                    if ('BarcodeDetector' in window) {
                        try {
                            const formats = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'qr_code'];
                            detector = new window.BarcodeDetector({ formats });
                        } catch(e) {}
                    }

                    const scanFrame = async () => {
                        if (!this.scannerOpen || !video.srcObject) return;
                        if (video.readyState >= 2 && video.videoWidth > 0 && detector) {
                            try {
                                const codes = await detector.detect(video);
                                if (codes && codes.length > 0) {
                                    this.onCameraDecode(codes[0].rawValue);
                                }
                            } catch(e) {}
                        }
                        scanLoopTimer = setTimeout(scanFrame, 180);
                    };
                    scanFrame();
                },

                async scanImageFile(file) {
                    if (!file) return;
                    this.scannerStatus = 'Analyse de la photo en cours…';
                    this.scannerError = '';

                    const imgUrl = URL.createObjectURL(file);
                    const img = new Image();
                    img.src = imgUrl;

                    img.onload = async () => {
                        let detectedCode = null;

                        // 1. Décodage ZXing
                        if (window.ZXingBrowser && window.ZXingBrowser.BrowserMultiFormatReader) {
                            try {
                                const reader = new window.ZXingBrowser.BrowserMultiFormatReader();
                                const res = await reader.decodeFromImageElement(img);
                                if (res && res.getText()) {
                                    detectedCode = res.getText();
                                }
                            } catch(e) {}
                        }

                        // 2. Décodage BarcodeDetector
                        if (!detectedCode && ('BarcodeDetector' in window)) {
                            try {
                                const detector = new window.BarcodeDetector();
                                const barcodes = await detector.detect(img);
                                if (barcodes && barcodes.length > 0) {
                                    detectedCode = barcodes[0].rawValue;
                                }
                            } catch(e) {}
                        }

                        URL.revokeObjectURL(imgUrl);

                        if (detectedCode) {
                            this.notifyScan('success', 'Code détecté avec succès !');
                            this.handleScannedCode(detectedCode);
                            if (!this.continuousScan) {
                                this.closeScanner();
                            } else {
                                this.scannerStatus = 'Code ' + detectedCode + ' ajouté !';
                            }
                        } else {
                            this.scannerError = "Aucun code-barres ou QR code n'a pu être lu sur cette photo. Assurez-vous que l'image est nette et bien cadrée.";
                        }
                    };

                    img.onerror = () => {
                        URL.revokeObjectURL(imgUrl);
                        this.scannerError = "Impossible de charger la photo.";
                    };
                },

                stopCamera() {
                    clearTimeout(scanLoopTimer);
                    scanLoopTimer = null;

                    if (activeZxingControls) {
                        try {
                            activeZxingControls.stop();
                        } catch(e) {}
                        activeZxingControls = null;
                    }

                    if (activeStream) {
                        try {
                            activeStream.getTracks().forEach(track => track.stop());
                        } catch(e) {}
                        activeStream = null;
                    }

                    const video = this.$refs.scannerVideo || document.getElementById('posScannerVideo');
                    if (video) {
                        try {
                            video.pause();
                            video.srcObject = null;
                        } catch(e) {}
                    }
                    this.torchOn = false;
                },

                closeScanner() {
                    this.scannerOpen = false;
                    this.scannerStatus = '';
                    this.stopCamera();
                },
            };
        }

        function posTerminal(config) {
            return {
                ...posBarcodeScanner(),
                search: '',
                selectedDomain: '',
                cart: [],
                cartOpen: false, // Panier en feuille coulissante (mobile / tablette)
                customerId: '',
                promoCode: '',
                discountAmount: 0,
                paymentMethod: 'especes',
                mobileMoneyProvider: 'wave',
                amountPaid: 0,
                products: config.products || [],
                promotions: config.promotions || [],
                domains: config.domains || [],

                money(value) {
                    return new Intl.NumberFormat('fr-FR').format(Math.round(Number(value) || 0));
                },

                stockOf(product) {
                    return Number(product.available_stock ?? product.current_stock ?? 0);
                },

                get cartCount() {
                    return this.cart.reduce((n, i) => n + i.quantity, 0);
                },

                get selectedDomainName() {
                    if (!this.selectedDomain) {
                        return 'Tous les domaines';
                    }
                    const found = this.domains.find(d => String(d.id) === String(this.selectedDomain));
                    return found ? found.name : 'Tous les domaines';
                },

                countProductsForDomain(domainId) {
                    if (!domainId) {
                        return this.products.length;
                    }
                    return this.products.filter(p => String(p.domain_id) === String(domainId)).length;
                },

                get filteredProducts() {
                    return this.products.filter(p => {
                        const matchDomain = !this.selectedDomain || p.domain_id == this.selectedDomain;
                        const query = this.search.toLowerCase().trim();
                        const matchSearch = !query ||
                            p.name.toLowerCase().includes(query) ||
                            (p.sku && p.sku.toLowerCase().includes(query)) ||
                            (p.barcode && p.barcode.toLowerCase().includes(query));
                        return matchDomain && matchSearch;
                    });
                },

                get stockErrors() {
                    return this.cart.filter(item => item.quantity > Number(item.available_stock ?? 0));
                },

                get hasStockErrors() {
                    return this.stockErrors.length > 0;
                },

                addToCart(product) {
                    const available = Number(product.available_stock ?? product.current_stock ?? 0);
                    if (available <= 0) {
                        this.notifyScan('error', 'Article en rupture de stock : "' + product.name + '" (0 disponible)');
                        return false;
                    }

                    const existing = this.cart.find(i => i.product_id === product.id);
                    if (existing) {
                        if (existing.quantity >= available) {
                            this.notifyScan('warning', 'Stock maximum atteint (' + available + ') pour "' + product.name + '"');
                            return false;
                        }
                        existing.quantity++;
                    } else {
                        this.cart.push({
                            product_id: product.id,
                            name: product.name,
                            sku: product.sku,
                            image: product.image,
                            available_stock: available,
                            unit_price: parseFloat(product.selling_price),
                            tax_rate: parseFloat(product.tax_rate || 0),
                            quantity: 1,
                            discount: 0
                        });
                    }
                    this.updateTotals();
                    return true;
                },

                updateQty(index, delta) {
                    const item = this.cart[index];
                    if (!item) return;

                    const available = Number(item.available_stock ?? 0);
                    if (delta > 0 && item.quantity >= available) {
                        this.notifyScan('warning', 'Stock maximum atteint (' + available + ') pour "' + item.name + '"');
                        return;
                    }

                    item.quantity += delta;
                    if (item.quantity <= 0) {
                        this.cart.splice(index, 1);
                    }
                    this.updateTotals();
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                    this.updateTotals();
                },

                clearCart() {
                    this.cart = [];
                    this.discountAmount = 0;
                    this.promoCode = '';
                    this.amountPaid = 0;
                },

                applyPromo() {
                    const code = this.promoCode.trim().toUpperCase();
                    const promo = this.promotions.find(p => p.code.toUpperCase() === code);
                    if (!promo) {
                        alert('Code promo introuvable ou inactif.');
                        this.discountAmount = 0;
                        return;
                    }
                    const sub = this.subtotal;
                    const minAmt = parseFloat(promo.min_amount || promo.min_order_amount || 0);
                    if (minAmt > 0 && sub < minAmt) {
                        alert('Montant minimum requis pour ce code : ' + minAmt + ' FCFA');
                        this.discountAmount = 0;
                        return;
                    }
                    if (promo.type === 'percent' || promo.type === 'percentage') {
                        let d = sub * (parseFloat(promo.value) / 100);
                        if (promo.max_discount_amount && d > promo.max_discount_amount) {
                            d = promo.max_discount_amount;
                        }
                        this.discountAmount = d;
                    } else {
                        this.discountAmount = Math.min(sub, parseFloat(promo.value));
                    }
                    this.updateTotals();
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
                },

                get taxAmount() {
                    return this.cart.reduce((sum, item) => {
                        const lineSub = (item.quantity * item.unit_price) - (item.discount || 0);
                        return sum + (lineSub * (item.tax_rate / 100));
                    }, 0);
                },

                get netTotal() {
                    const total = Math.max(0, (this.subtotal - this.discountAmount) + this.taxAmount);
                    return Math.round(total);
                },

                get changeToReturn() {
                    if (this.amountPaid > this.netTotal) {
                        return this.amountPaid - this.netTotal;
                    }
                    return 0;
                },

                setExactAmount() {
                    this.amountPaid = this.netTotal;
                },

                updateTotals() {
                    if (this.amountPaid === 0 || this.amountPaid < this.netTotal) {
                        this.amountPaid = this.netTotal;
                    }
                },

                submitCheckout(event) {
                    if (this.hasStockErrors) {
                        event.preventDefault();
                        this.notifyScan('error', 'Validation impossible : la quantité commandée dépasse le stock disponible.');
                    }
                }
            };
        }
    </script>

    @php
        // Modes de règlement : valeur => [libellé, tracé de l'icône]
        $paymentMethods = [
            'especes'      => ['Espèces',      'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            'mobile_money' => ['Mobile Money', 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'],
            'carte'        => ['Carte',        'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
            'virement'     => ['Virement',     'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
            'cheque'       => ['Chèque',       'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            'credit'       => ['Crédit',       'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];
    @endphp

    <div
        x-init="if (window.matchMedia('(pointer: fine)').matches) { $nextTick(() => $refs.searchInput?.focus()); }"
        x-data="posTerminal({
            products: {{ Js::from($products) }},
            promotions: {{ Js::from($activePromotions) }},
            domains: {{ Js::from($domains) }}
        })"
        @keydown.escape.window="cartOpen = false"
        class="space-y-5 pb-24 lg:pb-0"
    >
        <x-page-header title="Caisse & vente comptoir">
            <x-slot:actions>
                <x-button :href="route('commercial.customers.index')" variant="secondary" size="md">
                    Clients
                </x-button>
                <x-button :href="route('commercial.invoices.index')" variant="secondary" size="md">
                    Factures
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_400px] xl:grid-cols-[minmax(0,1fr)_440px] gap-6 items-start">

            {{-- ===================== CATALOGUE ===================== --}}
            <section class="min-w-0 space-y-4">
                {{-- Recherche + scanner --}}
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                        <input
                            type="text"
                            x-ref="searchInput"
                            x-model="search"
                            @keydown.enter.prevent="onSearchEnter()"
                            autocomplete="off"
                            enterkeyhint="search"
                            placeholder="Nom, référence ou code-barres"
                            class="w-full h-11 pl-10 pr-10 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/15 transition"
                        >
                        <button type="button" x-show="search" x-cloak @click="search = ''; $refs.searchInput.focus()"
                                class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded-md text-slate-400 hover:text-slate-700" aria-label="Effacer la recherche">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <button type="button" @click="openScanner()"
                            class="shrink-0 h-11 inline-flex items-center gap-2 px-4 rounded-lg bg-slate-900 hover:bg-[#0066FF] text-white text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-2"
                            aria-label="Scanner avec la caméra">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M7 8v8M10 8v8M13 8v8M16 8v8"/></svg>
                        <span class="hidden sm:inline">Scanner</span>
                    </button>
                </div>

                {{-- Domaines : puces défilantes (mobile) / retour à la ligne (desktop) --}}
                <div class="-mx-4 px-4 sm:mx-0 sm:px-0 flex sm:flex-wrap gap-2 overflow-x-auto sm:overflow-visible pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    <button type="button" @click="selectedDomain = ''"
                            :class="selectedDomain === '' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                            class="shrink-0 h-9 px-3.5 rounded-full border text-sm font-medium transition-colors whitespace-nowrap">
                        Tous <span class="ml-1 opacity-60" x-text="countProductsForDomain('')"></span>
                    </button>
                    @foreach($domains as $dom)
                        <button type="button" @click="selectedDomain = '{{ $dom->id }}'"
                                :class="selectedDomain == '{{ $dom->id }}' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                                class="shrink-0 h-9 px-3.5 rounded-full border text-sm font-medium transition-colors whitespace-nowrap">
                            {{ $dom->name }} <span class="ml-1 opacity-60" x-text="countProductsForDomain('{{ $dom->id }}')"></span>
                        </button>
                    @endforeach
                </div>

                {{-- Grille produits --}}
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button type="button"
                                @click="addToCart(product)"
                                :disabled="stockOf(product) <= 0"
                                class="group text-left flex flex-col bg-white border border-slate-200 rounded-xl overflow-hidden transition hover:border-[#0066FF] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:border-slate-200 disabled:active:scale-100">
                            <div class="aspect-[4/3] bg-slate-50 flex items-center justify-center overflow-hidden">
                                <template x-if="product.image">
                                    <img :src="'/storage/' + product.image" :alt="product.name" loading="lazy" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!product.image">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </template>
                            </div>
                            <div class="p-3 flex-1 flex flex-col gap-1">
                                <h4 class="text-sm font-medium text-slate-900 leading-snug line-clamp-2" x-text="product.name"></h4>
                                <p class="text-xs text-slate-400 truncate" x-text="product.sku || product.barcode || ''"></p>
                                <div class="mt-auto pt-2 flex items-end justify-between gap-2">
                                    <span class="text-sm font-semibold text-slate-900" x-text="money(product.selling_price) + ' F'"></span>
                                    <span class="flex items-center gap-1.5 text-xs shrink-0"
                                          :class="stockOf(product) <= 0 ? 'text-rose-600' : (stockOf(product) <= 5 ? 'text-amber-600' : 'text-slate-500')">
                                        <span class="w-1.5 h-1.5 rounded-full"
                                              :class="stockOf(product) <= 0 ? 'bg-rose-500' : (stockOf(product) <= 5 ? 'bg-amber-500' : 'bg-emerald-500')"></span>
                                        <span x-text="stockOf(product) <= 0 ? 'Épuisé' : stockOf(product)"></span>
                                    </span>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>

                <div x-show="filteredProducts.length === 0" x-cloak class="py-16 text-center">
                    <p class="text-sm font-medium text-slate-900">Aucun article trouvé</p>
                    <p class="text-sm text-slate-500 mt-1">Modifiez la recherche ou choisissez un autre domaine.</p>
                </div>
            </section>

            {{-- ===================== PANIER (colonne fixe desktop / feuille mobile) ===================== --}}
            <div x-show="cartOpen" x-cloak x-transition.opacity @click="cartOpen = false"
                 class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"></div>

            <aside
                :class="cartOpen ? 'translate-y-0' : 'translate-y-full lg:translate-y-0'"
                class="fixed inset-x-0 bottom-0 z-50 h-[92dvh] rounded-t-2xl transition-transform duration-300 ease-out
                       lg:static lg:z-auto lg:h-auto lg:rounded-xl lg:sticky lg:top-4 lg:max-h-[calc(100dvh-2rem)]
                       bg-white border border-slate-200 shadow-xl lg:shadow-none flex flex-col overflow-hidden"
                aria-label="Panier"
            >
                {{-- En-tête --}}
                <div class="shrink-0 px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-base font-semibold text-slate-900">Panier</h2>
                        <span class="min-w-6 h-6 px-2 rounded-full text-xs font-semibold inline-flex items-center justify-center"
                              :class="cartCount > 0 ? 'bg-[#0066FF] text-white' : 'bg-slate-100 text-slate-500'"
                              x-text="cartCount"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="clearCart()" x-show="cart.length > 0" x-cloak
                                class="text-sm text-slate-500 hover:text-rose-600 px-2 py-1 rounded-md transition-colors">Vider</button>
                        <button type="button" @click="cartOpen = false" class="lg:hidden p-2 -mr-2 rounded-lg text-slate-500 hover:bg-slate-100" aria-label="Fermer le panier">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                </div>

                <form action="{{ route('commercial.pos.checkout') }}" method="POST" @submit="submitCheckout($event)" class="flex-1 min-h-0 flex flex-col">
                    @csrf

                    {{-- Zone défilante --}}
                    <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain divide-y divide-slate-100">

                        {{-- Client --}}
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-2">
                                <label for="customer_id" class="text-sm font-medium text-slate-700">Client</label>
                                <a href="{{ route('commercial.customers.create') }}" target="_blank" class="text-sm text-[#0066FF] hover:underline">Nouveau client</a>
                            </div>
                            <select id="customer_id" name="customer_id" x-model="customerId"
                                    class="w-full h-11 px-3 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/15">
                                <option value="">Client comptoir (par défaut)</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }} • {{ $c->loyalty_level ?? 'BRONZE' }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Articles --}}
                        <div class="p-5">
                            <template x-if="cart.length === 0">
                                <div class="py-8 text-center">
                                    <div class="w-11 h-11 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-slate-900">Le panier est vide</p>
                                    <p class="text-sm text-slate-500">Touchez un article ou scannez un code-barres.</p>
                                </div>
                            </template>

                            <ul class="space-y-3" x-show="cart.length > 0">
                                <template x-for="(item, index) in cart" :key="item.product_id">
                                    <li class="flex items-start gap-3">
                                        <template x-if="item.image">
                                            <img :src="'/storage/' + item.image" alt="" class="w-12 h-12 rounded-lg object-cover bg-slate-50 border border-slate-200 shrink-0">
                                        </template>
                                        <template x-if="!item.image">
                                            <div class="w-12 h-12 rounded-lg bg-slate-100 shrink-0"></div>
                                        </template>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="text-sm font-medium text-slate-900 leading-snug line-clamp-2" x-text="item.name"></p>
                                                <button type="button" @click="removeFromCart(index)" class="p-1 -mr-1 rounded-md text-slate-400 hover:text-rose-600 shrink-0" aria-label="Retirer l'article">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <p class="text-xs text-slate-500 mt-0.5" x-text="money(item.unit_price) + ' F / unité'"></p>

                                            <div class="mt-2 flex items-center justify-between">
                                                <div class="inline-flex items-center border border-slate-200 rounded-lg">
                                                    <button type="button" @click="updateQty(index, -1)" class="w-9 h-9 flex items-center justify-center text-slate-600 hover:bg-slate-50 rounded-l-lg" aria-label="Diminuer">−</button>
                                                    <span class="w-9 text-center text-sm font-semibold tabular-nums"
                                                          :class="item.quantity > Number(item.available_stock ?? 0) ? 'text-rose-600' : 'text-slate-900'"
                                                          x-text="item.quantity"></span>
                                                    <button type="button" @click="updateQty(index, 1)"
                                                            :disabled="item.quantity >= Number(item.available_stock ?? 0)"
                                                            class="w-9 h-9 flex items-center justify-center text-slate-600 hover:bg-slate-50 rounded-r-lg disabled:opacity-30 disabled:cursor-not-allowed" aria-label="Augmenter">+</button>
                                                </div>
                                                <span class="text-sm font-semibold text-slate-900 tabular-nums" x-text="money(item.quantity * item.unit_price) + ' F'"></span>
                                            </div>

                                            <p x-show="item.quantity > Number(item.available_stock ?? 0)" x-cloak class="mt-1.5 text-xs text-rose-600">
                                                Stock insuffisant : <span x-text="item.available_stock ?? 0"></span> disponible(s)
                                            </p>
                                        </div>

                                        <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id">
                                        <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                                        <input type="hidden" :name="'items[' + index + '][unit_price]'" :value="item.unit_price">
                                        <input type="hidden" :name="'items[' + index + '][discount]'" :value="item.discount">
                                    </li>
                                </template>
                            </ul>
                        </div>

                        {{-- Règlement --}}
                        <div class="p-5 space-y-4">
                            <p class="text-sm font-medium text-slate-700">Mode de règlement</p>

                            <div class="grid grid-cols-3 gap-2">
                                @foreach($paymentMethods as $value => [$label, $icon])
                                    <label class="relative cursor-pointer select-none">
                                        <input type="radio" name="payment_method" value="{{ $value }}" x-model="paymentMethod" class="peer sr-only">
                                        <span class="flex flex-col items-center justify-center gap-1 h-[68px] rounded-lg border border-slate-200 bg-white text-slate-600 text-xs font-medium text-center px-1 transition
                                                     hover:border-slate-300 peer-checked:border-[#0066FF] peer-checked:bg-[#0066FF]/5 peer-checked:text-[#0066FF] peer-focus-visible:ring-2 peer-focus-visible:ring-[#0066FF]">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                                            {{ $label }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            {{-- Opérateur Mobile Money --}}
                            <div x-show="paymentMethod === 'mobile_money'" x-cloak x-transition.opacity class="space-y-3">
                                <input type="hidden" name="mobile_money_provider" :value="mobileMoneyProvider">
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="mobileMoneyProvider = 'wave'"
                                            :class="mobileMoneyProvider === 'wave' ? 'border-[#1DC4FF] bg-sky-50 ring-1 ring-[#1DC4FF]' : 'border-slate-200 hover:border-slate-300'"
                                            class="flex items-center gap-3 p-3 rounded-lg border bg-white text-left transition">
                                        <img src="{{ asset('images/payments/wave.png') }}" alt="" class="w-9 h-9 object-contain rounded-md shrink-0">
                                        <span class="min-w-0">
                                            <span class="block text-sm font-medium text-slate-900">Wave</span>
                                            <span class="block text-xs text-slate-500 truncate">QR code</span>
                                        </span>
                                    </button>
                                    <button type="button" @click="mobileMoneyProvider = 'orange_money'"
                                            :class="mobileMoneyProvider === 'orange_money' ? 'border-[#FF7900] bg-orange-50 ring-1 ring-[#FF7900]' : 'border-slate-200 hover:border-slate-300'"
                                            class="flex items-center gap-3 p-3 rounded-lg border bg-white text-left transition">
                                        <img src="{{ asset('images/payments/orange-money.png') }}" alt="" class="w-9 h-9 object-contain rounded-md shrink-0">
                                        <span class="min-w-0">
                                            <span class="block text-sm font-medium text-slate-900">Orange Money</span>
                                            <span class="block text-xs text-slate-500 truncate">QR ou #144#</span>
                                        </span>
                                    </button>
                                </div>
                                <p class="text-xs text-slate-500" x-text="mobileMoneyProvider === 'wave'
                                    ? 'Faites scanner le QR Wave au client, puis validez à réception.'
                                    : 'Le client paie par QR code ou avec le code marchand #144#.'"></p>
                            </div>

                            {{-- Espèces --}}
                            <template x-if="paymentMethod === 'especes'">
                                <div class="space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <label for="amount_paid" class="text-sm text-slate-600">Montant reçu</label>
                                        <button type="button" @click="setExactAmount()" class="text-sm text-[#0066FF] hover:underline">Montant exact</button>
                                    </div>
                                    <div class="relative">
                                        <input id="amount_paid" type="number" inputmode="numeric" min="0" name="amount_paid" x-model.number="amountPaid"
                                               class="w-full h-12 pl-3 pr-16 bg-white border border-slate-200 rounded-lg text-lg font-semibold tabular-nums text-slate-900 focus:outline-none focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/15">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 pointer-events-none">FCFA</span>
                                    </div>
                                    <div x-show="changeToReturn > 0" x-cloak class="flex items-center justify-between px-3 py-2.5 rounded-lg bg-emerald-50 text-emerald-800 text-sm">
                                        <span>Monnaie à rendre</span>
                                        <span class="font-semibold tabular-nums" x-text="money(changeToReturn) + ' FCFA'"></span>
                                    </div>
                                </div>
                            </template>

                            <template x-if="paymentMethod !== 'especes'">
                                <input type="hidden" name="amount_paid" :value="paymentMethod === 'credit' ? 0 : netTotal">
                            </template>
                        </div>
                    </div>

                    {{-- Pied fixe : alerte stock + total + validation --}}
                    <div class="shrink-0 border-t border-slate-200 bg-white p-5 space-y-3 pb-[max(1.25rem,env(safe-area-inset-bottom))]">
                        <div x-show="hasStockErrors" x-cloak class="px-3 py-2.5 rounded-lg bg-rose-50 text-rose-800 text-sm">
                            <p class="font-medium">Stock insuffisant</p>
                            <ul class="mt-1 space-y-0.5 text-xs">
                                <template x-for="err in stockErrors" :key="err.product_id">
                                    <li class="flex justify-between gap-2">
                                        <span class="truncate" x-text="err.name"></span>
                                        <span class="shrink-0" x-text="err.quantity + ' / ' + (err.available_stock ?? 0)"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <dl class="space-y-1.5 text-sm">
                            <div class="flex justify-between text-slate-500">
                                <dt>Sous-total</dt>
                                <dd class="tabular-nums text-slate-700" x-text="money(subtotal) + ' FCFA'"></dd>
                            </div>
                            <div class="flex justify-between text-emerald-700" x-show="discountAmount > 0" x-cloak>
                                <dt>Remise</dt>
                                <dd class="tabular-nums" x-text="'− ' + money(discountAmount) + ' FCFA'"></dd>
                            </div>
                            <div class="flex justify-between text-slate-500" x-show="taxAmount > 0" x-cloak>
                                <dt>TVA</dt>
                                <dd class="tabular-nums text-slate-700" x-text="'+ ' + money(taxAmount) + ' FCFA'"></dd>
                            </div>
                            <div class="flex justify-between items-baseline pt-2 border-t border-slate-100">
                                <dt class="font-medium text-slate-900">Total à payer</dt>
                                <dd class="text-xl font-semibold text-slate-900 tabular-nums" x-text="money(netTotal) + ' FCFA'"></dd>
                            </div>
                        </dl>

                        <button type="submit" :disabled="cart.length === 0 || hasStockErrors"
                                class="w-full h-12 rounded-lg bg-[#0066FF] hover:bg-[#0052CC] text-white text-sm font-semibold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-2 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed">
                            <span x-text="hasStockErrors ? 'Corrigez le stock pour valider' : 'Valider l’encaissement'"></span>
                        </button>
                    </div>
                </form>
            </aside>
        </div>

        {{-- Barre panier (mobile / tablette) --}}
        <div x-show="!cartOpen" x-cloak
             class="lg:hidden fixed inset-x-0 bottom-0 z-30 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] bg-white/95 backdrop-blur border-t border-slate-200">
            <button type="button" @click="cartOpen = true"
                    class="w-full h-12 px-4 rounded-lg bg-[#0066FF] hover:bg-[#0052CC] text-white flex items-center justify-between text-sm font-semibold transition-colors">
                <span class="flex items-center gap-2">
                    <span class="min-w-6 h-6 px-1.5 rounded-full bg-white/20 inline-flex items-center justify-center text-xs" x-text="cartCount"></span>
                    Voir le panier
                </span>
                <span class="tabular-nums" x-text="money(netTotal) + ' F'"></span>
            </button>
        </div>

        {{-- ===================== SCANNER CAMÉRA ===================== --}}
        <div x-show="scannerOpen" x-cloak
             @keydown.escape.window="if (scannerOpen) closeScanner()"
             class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center bg-slate-900/75 backdrop-blur-xs sm:p-4">
            <div class="w-full sm:max-w-md bg-white rounded-t-2xl sm:rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[92dvh]">
                {{-- En-tête modal --}}
                <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-white">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" :class="activeStream ? 'bg-emerald-500 animate-pulse' : (scannerError ? 'bg-rose-500' : 'bg-amber-500')"></span>
                        <h3 class="text-sm font-bold text-slate-900">Scanner un code-barres & QR Code</h3>
                    </div>
                    <div class="flex items-center gap-1">
                        {{-- Bouton Flash / Torche --}}
                        <button type="button" x-show="hasTorch" @click="toggleTorch()"
                                :class="torchOn ? 'bg-amber-100 text-amber-800' : 'text-slate-500 hover:bg-slate-100'"
                                class="p-2 rounded-lg text-xs font-semibold transition" title="Activer / désactiver la torche">
                            ⚡
                        </button>

                        {{-- Inverser caméra si multiple --}}
                        <button type="button" x-show="camerasList.length > 1" @click="switchCamera()"
                                class="p-2 rounded-lg text-slate-500 hover:bg-slate-100" title="Changer de caméra">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>

                        <button type="button" @click="closeScanner()" class="p-2 -mr-2 rounded-lg text-slate-500 hover:bg-slate-100" aria-label="Fermer le scanner">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Viseur vidéo caméra --}}
                <div class="relative bg-black aspect-[4/3] flex items-center justify-center overflow-hidden">
                    <video x-ref="scannerVideo" id="posScannerVideo" class="absolute inset-0 w-full h-full object-cover" playsinline muted autoplay></video>

                    {{-- Viseur réticule laser --}}
                    <div x-show="!scannerError" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="relative w-4/5 h-2/5 rounded-xl border-2 border-white/90 shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]">
                            <div class="absolute left-2 right-2 top-1/2 h-0.5 bg-emerald-500 shadow-[0_0_8px_#10b981] animate-pulse"></div>
                            <span class="absolute -top-6 inset-x-0 text-center text-[11px] font-semibold text-white/90 drop-shadow">EAN-13 • Code-barres • QR Code</span>
                        </div>
                    </div>

                    {{-- Statut discret --}}
                    <div x-show="scannerStatus && !scannerError" class="absolute bottom-3 inset-x-0 text-center pointer-events-none">
                        <span class="inline-block px-3 py-1 rounded-full bg-black/70 backdrop-blur-xs text-xs font-medium text-white shadow-xs" x-text="scannerStatus"></span>
                    </div>

                    {{-- En cas d'erreur caméra : message explicatif et actions alternatives --}}
                    <div x-show="scannerError" x-cloak class="absolute inset-0 flex flex-col items-center justify-center text-center p-6 bg-slate-900/95 text-white z-10">
                        <div class="w-12 h-12 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-sm font-bold">Caméra vidéo directe indisponible</p>
                        <p class="text-xs text-slate-300 mt-1 max-w-xs leading-relaxed" x-text="scannerError"></p>

                        <div class="mt-4 flex flex-wrap gap-2 justify-center">
                            <button type="button" @click="openScanner()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition border border-slate-700">
                                🔄 Réessayer la caméra
                            </button>
                            <button type="button" @click="$refs.photoScanInput.click()" class="px-3.5 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-xs font-semibold text-white transition shadow-sm">
                                📷 Prendre une photo du code
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Corps du modal : options, saisie & tests --}}
                <div class="p-5 space-y-3.5 overflow-y-auto pb-[max(1.25rem,env(safe-area-inset-bottom))] bg-white">
                    {{-- Bouton Prise de photo (compatible 100% smartphones même sur LAN HTTP sans HTTPS) --}}
                    <input type="file" accept="image/*" capture="environment" x-ref="photoScanInput" class="hidden"
                           @change="if ($event.target.files.length) { scanImageFile($event.target.files[0]); $event.target.value = ''; }">

                    <div class="flex items-center justify-between gap-2">
                        <button type="button" @click="$refs.photoScanInput.click()"
                                class="flex-1 h-10 px-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-800 text-xs font-semibold inline-flex items-center justify-center gap-2 transition">
                            <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Prendre / Importer une photo</span>
                        </button>

                        <label class="flex items-center gap-2 px-3 h-10 rounded-xl border border-slate-200 bg-slate-50 text-xs cursor-pointer select-none">
                            <input type="checkbox" x-model="continuousScan" class="w-4 h-4 rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                            <span class="text-slate-700 font-medium">Scan continu</span>
                        </label>
                    </div>

                    {{-- Saisie manuelle immédiate --}}
                    <form @submit.prevent="if (manualCode.trim()) { handleScannedCode(manualCode); manualCode = ''; }" class="flex gap-2">
                        <input type="text" x-model="manualCode" inputmode="text" placeholder="Ou tapez un code / EAN / SKU..."
                               class="flex-1 h-11 px-3.5 rounded-xl border border-slate-200 text-xs font-mono font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/15">
                        <button type="submit" class="h-11 px-4 rounded-xl bg-slate-900 hover:bg-[#0066FF] text-white text-xs font-bold transition-colors">
                            Ajouter
                        </button>
                    </form>

                    {{-- Chips de test rapide avec les produits en stock --}}
                    <div class="pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1.5">
                            <span class="font-semibold text-slate-600">Articles en stock (cliquez pour tester le scan) :</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5 max-h-20 overflow-y-auto no-scrollbar">
                            <template x-for="sample in products.slice(0, 6)" :key="sample.id">
                                <button type="button" @click="handleScannedCode(sample.barcode || sample.sku)"
                                        class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-[#0066FF] border border-slate-200 text-[10px] font-mono transition text-left">
                                    <span class="font-bold text-slate-800" x-text="sample.sku"></span>: <span class="text-slate-500" x-text="sample.barcode || 'Sans EAN'"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
                        <span>Dernier code : <span class="font-mono font-bold text-slate-900" x-text="lastScannedCode || '—'"></span></span>
                        <span>Panier : <span class="font-bold text-[#0066FF]" x-text="cartCount"></span> article(s)</span>
                    </div>

                    <button type="button" @click="closeScanner()" class="w-full h-10 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Fermer le scanner
                    </button>
                </div>
            </div>
        </div>

        {{-- Notification après scan / action --}}
        <div x-show="scanFeedback" x-cloak
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             role="status" aria-live="polite"
             class="fixed z-[80] left-1/2 -translate-x-1/2 bottom-24 lg:bottom-8 w-[calc(100%-2rem)] max-w-sm px-4 py-3 rounded-lg shadow-lg text-sm font-medium text-white"
             :class="scanFeedback?.type === 'success' ? 'bg-emerald-600' : (scanFeedback?.type === 'warning' ? 'bg-amber-600' : 'bg-rose-600')">
            <span x-text="scanFeedback?.message"></span>
        </div>
    </div>
</x-layouts.app>