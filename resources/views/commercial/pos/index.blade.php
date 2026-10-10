<x-layouts.app>
    <script src="{{ asset('vendor/zxing/zxing-browser.min.js') }}"></script>
    <script>
        /**
         * Scanner universel codes-barres & QR pour le comptoir POS IVOSPHERE.
         * Caméra (ZXing / BarcodeDetector), photo instantanée (capture), douchette USB/Bluetooth.
         */
        function posBarcodeScanner() {
            const DUPLICATE_SCAN_DELAY_MS = 1400;

            // Handles natifs hors de l'état Alpine pour ne pas casser MediaStream
            let activeStream = null;
            let activeZxingControls = null;
            let scanLoopTimer = null;

            return {
                scannerOpen: false,
                cameraActive: false,
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

                    // QR code au format JSON
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

                    try {
                        if (navigator.mediaDevices?.enumerateDevices) {
                            const devices = await navigator.mediaDevices.enumerateDevices();
                            this.camerasList = devices.filter(d => d.kind === 'videoinput');
                        }
                    } catch(e) {}

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
                        this.scannerError = "L'accès direct à la caméra est bloqué ou indisponible sur cette connexion. Utilisez « Prendre une photo » ou la saisie manuelle.";
                        return;
                    }

                    activeStream = stream;
                    this.cameraActive = true;
                    video.srcObject = stream;
                    try {
                        await video.play();
                    } catch(e) {}

                    const track = stream.getVideoTracks()[0];
                    if (track?.getCapabilities?.()?.torch) {
                        this.hasTorch = true;
                    }

                    this.scannerStatus = 'Placez le code dans le viseur';
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

                    // 1. ZXing
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

                    // 2. BarcodeDetector
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
                    this.scannerStatus = 'Analyse de la photo…';
                    this.scannerError = '';

                    const imgUrl = URL.createObjectURL(file);
                    const img = new Image();
                    img.src = imgUrl;

                    img.onload = async () => {
                        let detectedCode = null;

                        if (window.ZXingBrowser && window.ZXingBrowser.BrowserMultiFormatReader) {
                            try {
                                const reader = new window.ZXingBrowser.BrowserMultiFormatReader();
                                const res = await reader.decodeFromImageElement(img);
                                if (res && res.getText()) {
                                    detectedCode = res.getText();
                                }
                            } catch(e) {}
                        }

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
                            this.scannerError = "Aucun code n'a pu être lu sur cette photo. Vérifiez que l'image est nette et bien cadrée.";
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
                        try { activeZxingControls.stop(); } catch(e) {}
                        activeZxingControls = null;
                    }

                    if (activeStream) {
                        try { activeStream.getTracks().forEach(track => track.stop()); } catch(e) {}
                        activeStream = null;
                    }

                    const video = this.$refs.scannerVideo || document.getElementById('posScannerVideo');
                    if (video) {
                        try {
                            video.pause();
                            video.srcObject = null;
                        } catch(e) {}
                    }
                    this.cameraActive = false;
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
                cartOpen: false,
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

                getItemCartQty(productId) {
                    const item = this.cart.find(i => i.product_id === productId);
                    return item ? item.quantity : 0;
                },

                setExactAmount() {
                    this.amountPaid = this.netTotal;
                },

                setCashAmount(amount) {
                    this.amountPaid = Math.max(0, Math.round(Number(amount) || 0));
                },

                addCashAmount(amount) {
                    this.amountPaid = Math.max(0, Math.round((Number(this.amountPaid) || 0) + (Number(amount) || 0)));
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
        $cashPresets = [1000, 2000, 5000, 10000, 20000];
    @endphp

    <div
        x-init="if (window.matchMedia('(pointer: fine)').matches) { $nextTick(() => $refs.searchInput?.focus()); }"
        x-data="posTerminal({
            products: {{ Js::from($products) }},
            promotions: {{ Js::from($activePromotions) }},
            domains: {{ Js::from($domains) }}
        })"
        @keydown.escape.window="cartOpen = false"
        class="space-y-4 pb-24 md:pb-0"
    >
        <x-page-header title="Caisse & vente comptoir">
            <x-slot:actions>
                <x-button :href="route('commercial.customers.index')" variant="secondary" size="md">Clients</x-button>
                <x-button :href="route('commercial.invoices.index')" variant="secondary" size="md">Factures</x-button>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 items-start gap-4 md:grid-cols-[minmax(0,1fr)_340px] lg:grid-cols-[minmax(0,1fr)_380px] lg:gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">

            {{-- ===================== CATALOGUE ===================== --}}
            <section class="min-w-0 space-y-3">

                {{-- Recherche + scanner + panier (mobile) --}}
                <div class="flex items-center gap-2">
                    <div class="relative min-w-0 flex-1">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                        <input type="text" x-ref="searchInput" x-model="search"
                               @keydown.enter.prevent="onSearchEnter()"
                               autocomplete="off" enterkeyhint="search"
                               placeholder="Nom, référence ou code-barres"
                               class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-9 text-sm text-slate-900 placeholder-slate-400 transition focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15">
                        <button type="button" x-show="search" x-cloak @click="search = ''; $refs.searchInput.focus()"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-400 hover:text-slate-700" aria-label="Effacer la recherche">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <button type="button" @click="openScanner()"
                            class="inline-flex h-11 shrink-0 items-center gap-2 rounded-xl bg-slate-900 px-3 text-sm font-semibold text-white transition-colors hover:bg-[#0066FF] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] sm:px-4"
                            aria-label="Scanner avec la caméra">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M7 8v8M10 8v8M13 8v8M16 8v8"/></svg>
                        <span class="hidden sm:inline">Scanner</span>
                    </button>

                    <button type="button" @click="cartOpen = true"
                            class="relative flex h-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-slate-700 transition hover:bg-slate-50 md:hidden"
                            aria-label="Ouvrir le panier">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span x-show="cartCount > 0" x-cloak
                              class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#0066FF] px-1 text-[11px] font-bold text-white"
                              x-text="cartCount"></span>
                    </button>
                </div>

                {{-- Domaines --}}
                <div class="no-scrollbar -mx-4 flex items-center gap-1.5 overflow-x-auto px-4 py-0.5 sm:mx-0 sm:px-0">
                    <button type="button" @click="selectedDomain = ''"
                            :class="selectedDomain === '' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                            class="h-8 shrink-0 whitespace-nowrap rounded-full border px-3 text-xs font-medium transition">
                        Tous <span class="ml-1 opacity-60" x-text="countProductsForDomain('')"></span>
                    </button>
                    @foreach($domains as $dom)
                        <button type="button" @click="selectedDomain = '{{ $dom->id }}'"
                                :class="selectedDomain == '{{ $dom->id }}' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                                class="h-8 shrink-0 whitespace-nowrap rounded-full border px-3 text-xs font-medium transition">
                            {{ $dom->name }} <span class="ml-1 opacity-60" x-text="countProductsForDomain('{{ $dom->id }}')"></span>
                        </button>
                    @endforeach
                </div>

                {{-- Grille produits --}}
                <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 sm:gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button type="button" @click="addToCart(product)" :disabled="stockOf(product) <= 0"
                                class="group relative flex touch-manipulation flex-col overflow-hidden rounded-xl border border-slate-200 bg-white text-left transition hover:border-[#0066FF] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:border-slate-200 disabled:active:scale-100">

                            <div class="relative aspect-[4/3] overflow-hidden bg-slate-50">
                                <template x-if="product.image">
                                    <img :src="'/storage/' + product.image" :alt="product.name" loading="lazy" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                </template>
                                <template x-if="!product.image">
                                    <div class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-300">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                </template>

                                <div x-show="getItemCartQty(product.id) > 0" x-cloak
                                     class="absolute right-2 top-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#0066FF] px-1.5 text-[11px] font-bold text-white shadow-md">
                                    <span x-text="getItemCartQty(product.id)"></span>
                                </div>

                                <div x-show="stockOf(product) <= 0" x-cloak class="absolute inset-0 flex items-center justify-center bg-slate-900/40">
                                    <span class="rounded-md bg-rose-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Épuisé</span>
                                </div>
                            </div>

                            <div class="flex flex-1 flex-col justify-between gap-1.5 p-2.5 sm:p-3">
                                <div>
                                    <h4 class="line-clamp-2 text-xs font-semibold leading-snug text-slate-900 sm:text-sm" x-text="product.name"></h4>
                                    <p class="mt-0.5 truncate text-[10px] text-slate-400 sm:text-xs" x-text="product.sku || product.barcode || ''"></p>
                                </div>
                                <div class="flex items-end justify-between gap-1 border-t border-slate-100 pt-1.5">
                                    <span class="truncate text-xs font-bold text-slate-900 sm:text-sm" x-text="money(product.selling_price) + ' F'"></span>
                                    <span class="flex shrink-0 items-center gap-1 text-[10px] font-medium sm:text-xs"
                                          :class="stockOf(product) <= 0 ? 'text-rose-600' : (stockOf(product) <= 5 ? 'text-amber-600' : 'text-slate-500')">
                                        <span class="h-1.5 w-1.5 rounded-full"
                                              :class="stockOf(product) <= 0 ? 'bg-rose-500' : (stockOf(product) <= 5 ? 'bg-amber-500' : 'bg-emerald-500')"></span>
                                        <span x-text="stockOf(product) <= 0 ? '0' : stockOf(product)"></span>
                                    </span>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>

                <div x-show="filteredProducts.length === 0" x-cloak class="py-16 text-center">
                    <p class="text-sm font-medium text-slate-900">Aucun article trouvé</p>
                    <p class="mt-1 text-sm text-slate-500">Modifiez la recherche ou le domaine.</p>
                </div>
            </section>

            {{-- ===================== PANIER ===================== --}}
            <div x-show="cartOpen" x-cloak x-transition.opacity @click="cartOpen = false"
                 class="fixed inset-0 z-40 bg-slate-900/60 md:hidden"></div>

            <aside
                :class="cartOpen ? 'translate-y-0' : 'translate-y-full md:translate-y-0'"
                class="fixed inset-x-0 bottom-0 z-50 flex h-[92dvh] flex-col overflow-hidden rounded-t-3xl border border-slate-200 bg-white shadow-2xl transition-transform duration-300 ease-out
                       md:sticky md:top-4 md:z-auto md:h-auto md:max-h-[calc(100dvh-5.5rem)] md:rounded-2xl md:shadow-none"
                aria-label="Panier">

                <div class="flex shrink-0 justify-center bg-white pb-1 pt-2.5 md:hidden" @click="cartOpen = false">
                    <div class="h-1.5 w-10 rounded-full bg-slate-300"></div>
                </div>

                {{-- En-tête --}}
                <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-4 py-3 sm:px-5">
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-bold text-slate-900 sm:text-base">Panier</h2>
                        <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full px-2 text-xs font-bold transition-colors"
                              :class="cartCount > 0 ? 'bg-[#0066FF] text-white' : 'bg-slate-100 text-slate-500'"
                              x-text="cartCount"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="clearCart()" x-show="cart.length > 0" x-cloak
                                class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-500 transition-colors hover:bg-rose-50 hover:text-rose-600">Vider</button>
                        <button type="button" @click="cartOpen = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 md:hidden" aria-label="Fermer le panier">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <form action="{{ route('commercial.pos.checkout') }}" method="POST" @submit="submitCheckout($event)" class="flex min-h-0 flex-1 flex-col">
                    @csrf

                    <div class="min-h-0 flex-1 divide-y divide-slate-100 overflow-y-auto overscroll-contain">

                        {{-- Client --}}
                        <div class="p-4 sm:p-5">
                            <div class="mb-1.5 flex items-center justify-between">
                                <label for="customer_id" class="text-xs font-semibold text-slate-700 sm:text-sm">Client</label>
                                <a href="{{ route('commercial.customers.create') }}" target="_blank" class="text-xs font-medium text-[#0066FF] hover:underline sm:text-sm">Nouveau client</a>
                            </div>
                            <select id="customer_id" name="customer_id" x-model="customerId"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-900 transition focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15 sm:h-11 sm:text-sm">
                                <option value="">Client comptoir (par défaut)</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }} • {{ $c->loyalty_level ?? 'BRONZE' }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Articles --}}
                        <div class="p-4 sm:p-5">
                            <template x-if="cart.length === 0">
                                <div class="py-8 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-slate-900">Le panier est vide</p>
                                    <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Touchez un article ou scannez un code.</p>
                                </div>
                            </template>

                            <ul class="divide-y divide-slate-100" x-show="cart.length > 0">
                                <template x-for="(item, index) in cart" :key="item.product_id">
                                    <li class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                                        <template x-if="item.image">
                                            <img :src="'/storage/' + item.image" alt="" class="h-12 w-12 shrink-0 rounded-lg border border-slate-200 bg-white object-cover">
                                        </template>
                                        <template x-if="!item.image">
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            </div>
                                        </template>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-1.5">
                                                <p class="line-clamp-2 text-xs font-semibold leading-snug text-slate-900 sm:text-sm" x-text="item.name"></p>
                                                <button type="button" @click="removeFromCart(index)" class="-mr-1 shrink-0 rounded-md p-1 text-slate-400 transition-colors hover:text-rose-600" aria-label="Retirer l'article">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <p class="text-[11px] text-slate-500" x-text="money(item.unit_price) + ' F / unité'"></p>

                                            <div class="mt-2 flex items-center justify-between gap-2">
                                                <div class="inline-flex items-center rounded-lg border border-slate-200 bg-white">
                                                    <button type="button" @click="updateQty(index, -1)" class="flex h-8 w-8 touch-manipulation items-center justify-center rounded-l-lg text-base font-bold text-slate-700 transition hover:bg-slate-100 active:scale-95" aria-label="Diminuer">−</button>
                                                    <span class="min-w-8 px-1 text-center text-xs font-bold tabular-nums sm:text-sm"
                                                          :class="item.quantity > Number(item.available_stock ?? 0) ? 'text-rose-600' : 'text-slate-900'"
                                                          x-text="item.quantity"></span>
                                                    <button type="button" @click="updateQty(index, 1)" :disabled="item.quantity >= Number(item.available_stock ?? 0)"
                                                            class="flex h-8 w-8 touch-manipulation items-center justify-center rounded-r-lg text-base font-bold text-slate-700 transition hover:bg-slate-100 active:scale-95 disabled:cursor-not-allowed disabled:opacity-30" aria-label="Augmenter">+</button>
                                                </div>
                                                <span class="shrink-0 text-xs font-bold tabular-nums text-slate-900 sm:text-sm" x-text="money(item.quantity * item.unit_price) + ' F'"></span>
                                            </div>

                                            <p x-show="item.quantity > Number(item.available_stock ?? 0)" x-cloak class="mt-1 text-[11px] font-semibold text-rose-600">
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
                        <div class="space-y-3.5 p-4 sm:p-5">
                            <p class="text-xs font-semibold text-slate-700 sm:text-sm">Mode de règlement</p>

                            <div class="grid grid-cols-3 gap-1.5 sm:gap-2">
                                @foreach($paymentMethods as $value => [$label, $icon])
                                    <label class="relative cursor-pointer select-none">
                                        <input type="radio" name="payment_method" value="{{ $value }}" x-model="paymentMethod" class="peer sr-only">
                                        <span class="flex h-16 flex-col items-center justify-center gap-1 rounded-xl border border-slate-200 bg-white px-1 text-center text-[11px] font-semibold text-slate-600 transition hover:border-slate-300 peer-checked:border-[#0066FF] peer-checked:bg-[#0066FF]/5 peer-checked:text-[#0066FF] sm:text-xs">
                                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                                            <span class="block w-full truncate">{{ $label }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            {{-- Mobile Money --}}
                            <div x-show="paymentMethod === 'mobile_money'" x-cloak x-transition.opacity class="space-y-2">
                                <input type="hidden" name="mobile_money_provider" :value="mobileMoneyProvider">
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="mobileMoneyProvider = 'wave'"
                                            :class="mobileMoneyProvider === 'wave' ? 'border-[#1DC4FF] bg-sky-50 ring-2 ring-[#1DC4FF]/30' : 'border-slate-200 hover:border-slate-300'"
                                            class="flex items-center gap-2.5 rounded-xl border bg-white p-2.5 text-left transition">
                                        <img src="{{ asset('images/payments/wave.png') }}" alt="" class="h-8 w-8 shrink-0 rounded-lg object-contain">
                                        <span class="min-w-0">
                                            <span class="block text-xs font-bold leading-tight text-slate-900 sm:text-sm">Wave</span>
                                            <span class="block truncate text-[10px] text-slate-500 sm:text-xs">QR code</span>
                                        </span>
                                    </button>
                                    <button type="button" @click="mobileMoneyProvider = 'orange_money'"
                                            :class="mobileMoneyProvider === 'orange_money' ? 'border-[#FF7900] bg-orange-50 ring-2 ring-[#FF7900]/30' : 'border-slate-200 hover:border-slate-300'"
                                            class="flex items-center gap-2.5 rounded-xl border bg-white p-2.5 text-left transition">
                                        <img src="{{ asset('images/payments/orange-money.png') }}" alt="" class="h-8 w-8 shrink-0 rounded-lg object-contain">
                                        <span class="min-w-0">
                                            <span class="block text-xs font-bold leading-tight text-slate-900 sm:text-sm">Orange</span>
                                            <span class="block truncate text-[10px] text-slate-500 sm:text-xs">QR / #144#</span>
                                        </span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-slate-500" x-text="mobileMoneyProvider === 'wave'
                                    ? 'Faites scanner le QR Wave au client, puis validez à réception.'
                                    : 'Le client paie par QR code ou avec le code marchand #144#.'"></p>
                            </div>

                            {{-- Espèces --}}
                            <template x-if="paymentMethod === 'especes'">
                                <div class="space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <label for="amount_paid" class="text-xs font-semibold text-slate-700 sm:text-sm">Montant reçu</label>
                                        <button type="button" @click="setExactAmount()" class="text-xs font-semibold text-[#0066FF] hover:underline sm:text-sm">Montant exact</button>
                                    </div>
                                    <div class="relative">
                                        <input id="amount_paid" type="number" inputmode="numeric" min="0" name="amount_paid" x-model.number="amountPaid"
                                               class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-3 pr-16 text-base font-bold tabular-nums text-slate-900 transition focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15 sm:h-12 sm:text-lg">
                                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400 sm:text-sm">FCFA</span>
                                    </div>

                                    <div class="flex flex-wrap gap-1.5">
                                        <button type="button" @click="setExactAmount()"
                                                :class="amountPaid === netTotal ? 'bg-[#0066FF] text-white border-[#0066FF]' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200'"
                                                class="rounded-lg border px-2.5 py-1 text-[11px] font-semibold transition active:scale-95">Exact</button>
                                        @foreach($cashPresets as $preset)
                                            <button type="button" @click="setCashAmount({{ $preset }})"
                                                    class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-50 active:scale-95">
                                                {{ number_format($preset, 0, ',', ' ') }} F
                                            </button>
                                        @endforeach
                                    </div>

                                    <div x-show="changeToReturn > 0" x-cloak class="flex items-center justify-between rounded-xl bg-emerald-50 px-3 py-2 text-xs text-emerald-900 sm:text-sm">
                                        <span class="font-medium">Monnaie à rendre</span>
                                        <span class="font-bold tabular-nums text-emerald-700" x-text="money(changeToReturn) + ' FCFA'"></span>
                                    </div>
                                </div>
                            </template>

                            <template x-if="paymentMethod !== 'especes'">
                                <input type="hidden" name="amount_paid" :value="paymentMethod === 'credit' ? 0 : netTotal">
                            </template>
                        </div>
                    </div>

                    {{-- Pied : alerte stock + total + validation --}}
                    <div class="shrink-0 space-y-3 border-t border-slate-200 bg-white p-4 pb-[max(1rem,env(safe-area-inset-bottom))] sm:p-5 sm:pb-[max(1.25rem,env(safe-area-inset-bottom))]">
                        <div x-show="hasStockErrors" x-cloak class="rounded-xl bg-rose-50 px-3 py-2.5 text-xs text-rose-800 sm:text-sm">
                            <p class="font-semibold">Stock insuffisant</p>
                            <ul class="mt-1 space-y-0.5 text-xs">
                                <template x-for="err in stockErrors" :key="err.product_id">
                                    <li class="flex justify-between gap-2">
                                        <span class="truncate" x-text="err.name"></span>
                                        <span class="shrink-0 font-bold" x-text="err.quantity + ' / ' + (err.available_stock ?? 0)"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <dl class="space-y-1.5 text-xs sm:text-sm">
                            <div class="flex justify-between text-slate-500">
                                <dt>Sous-total</dt>
                                <dd class="font-medium tabular-nums text-slate-700" x-text="money(subtotal) + ' FCFA'"></dd>
                            </div>
                            <div class="flex justify-between text-emerald-700" x-show="discountAmount > 0" x-cloak>
                                <dt>Remise</dt>
                                <dd class="font-semibold tabular-nums" x-text="'− ' + money(discountAmount) + ' FCFA'"></dd>
                            </div>
                            <div class="flex justify-between text-slate-500" x-show="taxAmount > 0" x-cloak>
                                <dt>TVA</dt>
                                <dd class="font-medium tabular-nums text-slate-700" x-text="'+ ' + money(taxAmount) + ' FCFA'"></dd>
                            </div>
                            <div class="flex items-baseline justify-between border-t border-slate-100 pt-2">
                                <dt class="text-sm font-bold text-slate-900 sm:text-base">Total à payer</dt>
                                <dd class="text-lg font-bold tabular-nums text-slate-900 sm:text-xl" x-text="money(netTotal) + ' FCFA'"></dd>
                            </div>
                        </dl>

                        <button type="submit" :disabled="cart.length === 0 || hasStockErrors"
                                class="h-11 w-full rounded-xl bg-[#0066FF] text-sm font-bold text-white transition hover:bg-[#0052CC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400 sm:h-12">
                            <span x-text="hasStockErrors ? 'Corrigez le stock pour valider' : 'Valider l’encaissement'"></span>
                        </button>
                    </div>
                </form>
            </aside>
        </div>

        {{-- Barre panier fixe (mobile) --}}
        <div x-show="!cartOpen" x-cloak
             class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] backdrop-blur md:hidden">
            <button type="button" @click="cartOpen = true"
                    class="flex h-12 w-full items-center justify-between rounded-xl bg-[#0066FF] px-4 text-sm font-bold text-white transition hover:bg-[#0052CC] active:scale-[0.99]">
                <span class="flex items-center gap-2.5">
                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-white/25 px-1.5 text-xs font-bold" x-text="cartCount"></span>
                    <span>Voir le panier</span>
                </span>
                <span class="tabular-nums" x-text="money(netTotal) + ' FCFA'"></span>
            </button>
        </div>

        {{-- ===================== SCANNER ===================== --}}
        <div x-show="scannerOpen" x-cloak
             @keydown.escape.window="if (scannerOpen) closeScanner()"
             class="fixed inset-0 z-[70] flex items-end justify-center bg-slate-900/75 sm:items-center sm:p-4">
            <div class="flex max-h-[92dvh] w-full flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl sm:max-w-md sm:rounded-2xl">

                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full" :class="cameraActive ? 'bg-emerald-500 animate-pulse' : (scannerError ? 'bg-rose-500' : 'bg-amber-500')"></span>
                        <h3 class="text-sm font-bold text-slate-900">Scanner un code</h3>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" x-show="hasTorch" @click="toggleTorch()"
                                :class="torchOn ? 'bg-amber-100 text-amber-800' : 'text-slate-500 hover:bg-slate-100'"
                                class="rounded-lg p-2 text-xs font-semibold transition" title="Torche">⚡</button>
                        <button type="button" x-show="camerasList.length > 1" @click="switchCamera()"
                                class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" title="Changer de caméra">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                        <button type="button" @click="closeScanner()" class="-mr-2 rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Fermer le scanner">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Viseur --}}
                <div class="relative flex aspect-[4/3] max-h-[35vh] items-center justify-center overflow-hidden bg-black sm:max-h-[42vh]">
                    <video x-ref="scannerVideo" id="posScannerVideo" class="absolute inset-0 h-full w-full object-cover" playsinline muted autoplay></video>

                    <div x-show="!scannerError" class="pointer-events-none absolute inset-0 flex items-center justify-center">
                        <div class="relative h-2/5 w-4/5 rounded-xl border-2 border-white/90 shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]">
                            <div class="absolute inset-x-2 top-1/2 h-0.5 animate-pulse bg-emerald-500 shadow-[0_0_8px_#10b981]"></div>
                        </div>
                    </div>

                    <div x-show="scannerStatus && !scannerError" class="pointer-events-none absolute inset-x-0 bottom-3 text-center">
                        <span class="inline-block rounded-full bg-black/70 px-3 py-1 text-xs font-medium text-white" x-text="scannerStatus"></span>
                    </div>

                    <div x-show="scannerError" x-cloak class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-slate-900/95 p-6 text-center text-white">
                        <p class="text-sm font-bold">Caméra indisponible</p>
                        <p class="mt-1 max-w-xs text-xs leading-relaxed text-slate-300" x-text="scannerError"></p>
                        <div class="mt-4 flex flex-wrap justify-center gap-2">
                            <button type="button" @click="openScanner()" class="rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-slate-700">Réessayer</button>
                            <button type="button" @click="$refs.photoScanInput.click()" class="rounded-xl bg-[#0066FF] px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-[#0052cc]">Prendre une photo</button>
                        </div>
                    </div>
                </div>

                {{-- Corps --}}
                <div class="space-y-3 overflow-y-auto p-5 pb-[max(1.25rem,env(safe-area-inset-bottom))]">
                    <input type="file" accept="image/*" capture="environment" x-ref="photoScanInput" class="hidden"
                           @change="if ($event.target.files.length) { scanImageFile($event.target.files[0]); $event.target.value = ''; }">

                    <div class="flex items-center gap-2">
                        <button type="button" @click="$refs.photoScanInput.click()"
                                class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-800 transition hover:bg-slate-100">
                            <svg class="h-4 w-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Photo
                        </button>
                        <label class="flex h-10 cursor-pointer select-none items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs">
                            <input type="checkbox" x-model="continuousScan" class="h-4 w-4 rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                            <span class="font-medium text-slate-700">Scan continu</span>
                        </label>
                    </div>

                    <form @submit.prevent="if (manualCode.trim()) { handleScannedCode(manualCode); manualCode = ''; }" class="flex gap-2">
                        <input type="text" x-model="manualCode" inputmode="text" placeholder="Code / EAN / SKU…"
                               class="h-11 flex-1 rounded-xl border border-slate-200 px-3.5 font-mono text-xs font-medium text-slate-900 placeholder-slate-400 focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15">
                        <button type="submit" class="h-11 rounded-xl bg-slate-900 px-4 text-xs font-bold text-white transition-colors hover:bg-[#0066FF]">Ajouter</button>
                    </form>

                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Dernier code : <span class="font-mono font-bold text-slate-900" x-text="lastScannedCode || '—'"></span></span>
                        <span>Panier : <span class="font-bold text-[#0066FF]" x-text="cartCount"></span></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notification --}}
        <div x-show="scanFeedback" x-cloak
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             role="status" aria-live="polite"
             class="fixed bottom-20 left-1/2 z-[80] w-[calc(100%-2rem)] max-w-sm -translate-x-1/2 rounded-xl px-4 py-3 text-xs font-semibold text-white shadow-xl sm:text-sm md:bottom-6"
             :class="scanFeedback?.type === 'success' ? 'bg-emerald-600' : (scanFeedback?.type === 'warning' ? 'bg-amber-600' : 'bg-rose-600')">
            <span x-text="scanFeedback?.message"></span>
        </div>
    </div>
</x-layouts.app>