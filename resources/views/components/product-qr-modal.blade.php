@props([])

<div
    x-data="productQrModal()"
    @open-product-qr.window="open($event.detail)"
    @keydown.escape.window="close()"
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-qr-title"
    role="dialog"
    aria-modal="true"
    style="display: none;"
>
    {{-- Arrière-plan flouté --}}
    <div 
        x-show="isOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-[#0B0F14]/70 backdrop-blur-xs transition-opacity"
        @click="close()"
    ></div>

    <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
        <div 
            x-show="isOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-2xl transition-all border border-slate-200"
            @click.stop
        >
            {{-- En-tête du modal --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 bg-slate-50/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#0066FF]/10 text-[#0066FF] flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 id="modal-qr-title" class="text-sm font-bold text-[#0B0F14]">Code QR & Barcode EAN</h3>
                        <p class="text-[11px] text-[#64748B]">Traçabilité caisse POS, inventaire et rayonnage</p>
                    </div>
                </div>

                <button 
                    type="button" 
                    @click="close()" 
                    class="rounded-lg p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors focus:outline-none"
                    aria-label="Fermer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-5 space-y-4">
                {{-- Fiche Résumé Produit --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3.5 flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-blue-100 text-[#0066FF]" x-text="product.domain || 'GÉNÉRAL'"></span>
                            <span class="text-[10px] font-mono text-slate-500 font-semibold" x-text="product.sku"></span>
                        </div>
                        <h4 class="text-sm font-bold text-[#0B0F14] truncate" x-text="product.name"></h4>
                        <p class="text-[11px] text-slate-500 mt-0.5" x-show="product.unit">Unité : <span x-text="product.unit"></span></p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[9px] uppercase font-bold text-slate-400 block tracking-wider">Prix Vente</span>
                        <span class="text-sm font-extrabold text-[#0B0F14]" x-text="product.price"></span>
                    </div>
                </div>

                {{-- Zone Visuelle QR Code (Canvas dynamique) --}}
                <div class="flex flex-col items-center justify-center p-4 rounded-xl border border-dashed border-slate-200 bg-white">
                    <div class="relative p-2 rounded-xl bg-white shadow-2xs border border-slate-100">
                        <div id="modal-product-qrcode" class="flex items-center justify-center min-w-[210px] min-h-[210px]">
                            {{-- Injecté par QRCode.js --}}
                        </div>
                    </div>

                    {{-- Code-barres EAN en clair avec bouton copier --}}
                    <div class="mt-3.5 flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200/80">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500">EAN-13 :</span>
                        <span class="font-mono text-xs font-bold text-[#0B0F14] tracking-widest" x-text="formattedBarcode()"></span>
                        <button 
                            type="button" 
                            @click="copyBarcode()" 
                            class="ml-1 p-1 rounded hover:bg-white text-slate-500 hover:text-[#0066FF] transition-colors"
                            :title="copied ? 'Copié !' : 'Copier le code EAN'"
                        >
                            <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <svg x-show="copied" x-cloak class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </div>

                    <p class="mt-2 text-[11px] text-slate-400 text-center">
                        Intègre directement le code EAN du produit pour scan douchette et téléphone.
                    </p>
                </div>

                {{-- Actions de Téléchargement PNG & Impression --}}
                <div class="space-y-2 pt-1">
                    {{-- Bouton Principal 1 : Télécharger QR Code PNG --}}
                    <button 
                        type="button" 
                        @click="downloadQrPng()" 
                        class="w-full h-10 px-4 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-bold inline-flex items-center justify-center gap-2 transition-all shadow-xs active:scale-[0.99]"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>Télécharger le QR Code (PNG)</span>
                    </button>

                    <div class="grid grid-cols-2 gap-2">
                        {{-- Bouton Secondaire 2 : Télécharger Étiquette Complète (PNG) --}}
                        <button 
                            type="button" 
                            @click="downloadLabelPng()" 
                            class="h-9 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold inline-flex items-center justify-center gap-1.5 transition-colors"
                            title="Télécharger l'étiquette rayon complète avec titre, prix, QR code et EAN"
                        >
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span>Étiquette Rayon (PNG)</span>
                        </button>

                        {{-- Bouton Impression directe --}}
                        <button 
                            type="button" 
                            @click="printLabel()" 
                            class="h-9 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold inline-flex items-center justify-center gap-1.5 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            <span>Imprimer Étiquette</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Pied de page informatif --}}
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-[11px] text-slate-400">
                <span>Format PNG 100% vectorisé & haute netteté</span>
                <button type="button" @click="close()" class="font-medium text-slate-600 hover:text-[#0066FF]">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function productQrModal() {
        return {
            isOpen: false,
            copied: false,
            product: {
                id: null,
                name: '',
                sku: '',
                barcode: '',
                price: '',
                domain: '',
                unit: '',
                qrDownloadUrl: '',
                labelDownloadUrl: ''
            },

            open(data) {
                this.product = Object.assign({}, data);
                if (!this.product.barcode) {
                    this.product.barcode = this.product.sku || 'IVOSPHERE-PRD';
                }
                this.copied = false;
                this.isOpen = true;

                this.$nextTick(() => {
                    this.renderQrCode();
                });
            },

            close() {
                this.isOpen = false;
            },

            formattedBarcode() {
                const b = (this.product.barcode || '').toString();
                if (b.length === 13) {
                    return b.slice(0, 1) + ' ' + b.slice(1, 7) + ' ' + b.slice(7, 13);
                }
                return b;
            },

            renderQrCode() {
                const container = document.getElementById('modal-product-qrcode');
                if (!container) return;
                container.innerHTML = '';

                if (typeof QRCode !== 'undefined') {
                    new QRCode(container, {
                        text: this.product.barcode,
                        width: 210,
                        height: 210,
                        colorDark: "#0B0F14",
                        colorLight: "#ffffff",
                        correctLevel: (typeof QRCode.CorrectLevel !== 'undefined') ? QRCode.CorrectLevel.H : 2
                    });
                }
            },

            copyBarcode() {
                if (!this.product.barcode) return;
                navigator.clipboard.writeText(this.product.barcode).then(() => {
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 2000);
                });
            },

            downloadQrPng() {
                // Si une URL serveur dédiée est configurée, privilégier le PNG haute-résolution du serveur
                if (this.product.qrDownloadUrl) {
                    window.location.href = this.product.qrDownloadUrl;
                    return;
                }

                // Sinon, exporter directement le canvas généré localement
                const container = document.getElementById('modal-product-qrcode');
                const canvas = container ? container.querySelector('canvas') : null;
                if (canvas) {
                    const a = document.createElement('a');
                    a.download = `qr-code-${this.product.sku || 'produit'}-ean-${this.product.barcode}.png`;
                    a.href = canvas.toDataURL('image/png');
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
            },

            downloadLabelPng() {
                if (this.product.labelDownloadUrl) {
                    window.location.href = this.product.labelDownloadUrl;
                    return;
                }

                // Génération locale sur un canvas étiquette 600x750 px si pas de route serveur
                const container = document.getElementById('modal-product-qrcode');
                const qrCanvas = container ? container.querySelector('canvas') : null;
                if (!qrCanvas) return;

                const c = document.createElement('canvas');
                c.width = 600;
                c.height = 750;
                const ctx = c.getContext('2d');

                // Fond blanc
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, 600, 750);

                // Bordure
                ctx.strokeStyle = '#E2E8F0';
                ctx.lineWidth = 3;
                ctx.strokeRect(12, 12, 576, 726);

                // Bandeau d'en-tête
                ctx.fillStyle = '#F8FAFC';
                ctx.fillRect(14, 14, 572, 80);
                ctx.fillStyle = '#0066FF';
                ctx.fillRect(14, 90, 572, 4);

                // Textes en-tête
                ctx.fillStyle = '#0066FF';
                ctx.font = 'bold 22px Inter, sans-serif';
                ctx.fillText('IVOSPHERE ERP', 30, 48);

                ctx.fillStyle = '#64748B';
                ctx.font = '12px Inter, sans-serif';
                ctx.fillText('RAYON ARTICLE • ' + (this.product.domain || 'COMMERCIAL').toUpperCase(), 30, 72);

                ctx.fillStyle = '#0B0F14';
                ctx.font = 'bold 13px monospace';
                ctx.fillText('RÉF: ' + (this.product.sku || ''), 440, 55);

                // Désignation produit
                ctx.fillStyle = '#0B0F14';
                ctx.font = 'bold 20px Inter, sans-serif';
                ctx.fillText((this.product.name || '').substring(0, 36), 30, 130);

                ctx.fillStyle = '#64748B';
                ctx.font = '13px Inter, sans-serif';
                ctx.fillText('SKU : ' + (this.product.sku || '') + '  •  Unité : ' + (this.product.unit || 'pièce'), 30, 155);

                // Dessin du QR code centré
                ctx.drawImage(qrCanvas, 160, 185, 280, 280);

                // Cadre EAN
                ctx.fillStyle = '#F1F5F9';
                ctx.fillRect(40, 485, 520, 50);
                ctx.fillStyle = '#0B0F14';
                ctx.font = 'bold 20px monospace';
                ctx.textAlign = 'center';
                ctx.fillText('EAN-13 : ' + this.formattedBarcode(), 300, 518);

                // Bloc Prix
                ctx.fillStyle = '#0066FF';
                ctx.font = 'bold 24px Inter, sans-serif';
                ctx.fillText('PRIX : ' + (this.product.price || '0 FCFA'), 300, 575);

                // Bas de page
                ctx.fillStyle = '#94A3B8';
                ctx.font = '11px Inter, sans-serif';
                ctx.fillText('Scannable en caisse POS & terminal magasinier IVOSPHERE', 300, 640);
                ctx.textAlign = 'left';

                const a = document.createElement('a');
                a.download = `etiquette-${this.product.sku || 'produit'}-ean-${this.product.barcode}.png`;
                a.href = c.toDataURL('image/png');
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            },

            printLabel() {
                const container = document.getElementById('modal-product-qrcode');
                const qrCanvas = container ? container.querySelector('canvas') : null;
                if (!qrCanvas) return;

                const c = document.createElement('canvas');
                c.width = 600;
                c.height = 750;
                const ctx = c.getContext('2d');

                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, 600, 750);
                ctx.strokeStyle = '#0B0F14';
                ctx.lineWidth = 4;
                ctx.strokeRect(12, 12, 576, 726);

                ctx.fillStyle = '#0066FF';
                ctx.font = 'bold 24px Inter, sans-serif';
                ctx.fillText('IVOSPHERE ERP', 30, 52);

                ctx.fillStyle = '#64748B';
                ctx.font = '13px Inter, sans-serif';
                ctx.fillText('RAYON ARTICLE • ' + (this.product.domain || 'COMMERCIAL').toUpperCase(), 30, 78);

                ctx.fillStyle = '#0B0F14';
                ctx.font = 'bold 22px Inter, sans-serif';
                ctx.fillText((this.product.name || '').substring(0, 34), 30, 135);

                ctx.fillStyle = '#64748B';
                ctx.font = '14px Inter, sans-serif';
                ctx.fillText('RÉF : ' + (this.product.sku || '') + '  •  ' + (this.product.unit || 'pièce'), 30, 162);

                ctx.drawImage(qrCanvas, 150, 185, 300, 300);

                ctx.fillStyle = '#F1F5F9';
                ctx.fillRect(40, 500, 520, 54);
                ctx.fillStyle = '#0B0F14';
                ctx.font = 'bold 22px monospace';
                ctx.textAlign = 'center';
                ctx.fillText('EAN-13 : ' + this.formattedBarcode(), 300, 536);

                ctx.fillStyle = '#0066FF';
                ctx.font = 'bold 26px Inter, sans-serif';
                ctx.fillText('PRIX : ' + (this.product.price || '0 FCFA'), 300, 595);

                ctx.fillStyle = '#94A3B8';
                ctx.font = '12px Inter, sans-serif';
                ctx.fillText('Scannable en caisse POS & terminal magasinier IVOSPHERE', 300, 660);
                ctx.textAlign = 'left';

                const printWindow = window.open('', '_blank');
                if (!printWindow) {
                    alert("Veuillez autoriser les fenêtres pop-up pour imprimer l'étiquette.");
                    return;
                }

                const img = printWindow.document.createElement('img');
                img.src = c.toDataURL('image/png');
                img.style.maxWidth = '100%';
                img.style.display = 'block';
                img.style.margin = '20px auto';
                printWindow.document.body.style.margin = '0';
                printWindow.document.body.style.padding = '10px';
                printWindow.document.body.appendChild(img);

                setTimeout(() => {
                    try {
                        printWindow.focus();
                        printWindow.print();
                    } catch (e) {
                        console.error('Print failed', e);
                    }
                }, 300);
            }
        };
    }
</script>
