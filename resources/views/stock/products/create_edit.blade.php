<x-layouts.app>
    <x-slot:title>{{ isset($product) ? 'Modifier Article' : 'Nouveau Produit' }} — Stocks & Logistique (WMS)</x-slot>

    <div class="max-w-4xl mx-auto space-y-6 pb-12">
        <x-page-header 
            title="{{ isset($product) ? 'Modifier la Fiche Article : ' . $product->name : 'Nouveau Produit — Stocks & Logistique' }}"
            description="Création de référence au catalogue WMS, valorisation d'inventaire, code-barres et initialisation du stock"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Stocks & Logistique', 'url' => route('stock.index')],
                    ['label' => 'Stock Disponible', 'url' => route('stock.index', ['tab' => 'disponibilite'])],
                    ['label' => isset($product) ? 'Modification' : 'Nouveau Produit']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button variant="secondary" href="{{ route('stock.index', ['tab' => 'disponibilite']) }}" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour au Stock</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <form 
            action="{{ isset($product) ? route('stock.products.update', $product) : route('stock.products.store') }}" 
            method="POST" 
            enctype="multipart/form-data"
            class="space-y-6 text-xs"
            x-data="{
                generateBarcode() {
                    const prefix = '618';
                    const middle = Math.floor(100000000 + Math.random() * 900000000).toString();
                    const twelve = prefix + middle;
                    let sum = 0;
                    for (let i = 0; i < 12; i++) {
                        sum += parseInt(twelve[i], 10) * (i % 2 === 0 ? 1 : 3);
                    }
                    const checksum = (10 - (sum % 10)) % 10;
                    this.barcode = twelve + checksum;
                },
                barcode: '{{ old('barcode', $product->barcode ?? '') }}',
                initialQty: {{ old('initial_quantity', 0) }},
                warehouseId: '{{ old('initial_warehouse_id', $warehouses->first()?->id ?? '') }}'
            }"
        >
            @csrf
            @if(isset($product)) @method('PUT') @endif

            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
                    <p class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Erreurs de validation :
                    </p>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 1. Identification & Traçabilité -->
            <x-card title="1. Identification & Traçabilité WMS">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Code SKU / Référence interne *</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku ?? 'STK-' . strtoupper(Str::random(6))) }}" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] font-mono focus:outline-none focus:border-[#0066FF] text-xs transition">
                        <p class="text-[10px] text-[#64748B] mt-1">Identifiant unique de gestion des stocks</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Désignation / Nom de l'article *</label>
                        <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="Ex: Câble Fibre Optique 100m">
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label class="block text-xs font-semibold text-[#0B0F14]">Code-barres (EAN-13 / Scanner)</label>
                            <button type="button" @click="generateBarcode()" class="text-[11px] text-[#0066FF] hover:underline font-medium">
                                Générer un code EAN
                            </button>
                        </div>
                        <div class="relative">
                            <input 
                                type="text" 
                                name="barcode" 
                                x-model="barcode"
                                class="w-full pl-9 pr-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] font-mono focus:outline-none focus:border-[#0066FF] text-xs transition" 
                                placeholder="Scannez avec la douchette ou saisissez"
                            >
                            <svg class="w-4 h-4 text-[#64748B] absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m8-8H4"/></svg>
                        </div>
                        <p class="text-[10px] text-[#64748B] mt-1">Reconnu par les douchettes code-barres et le scanner WMS</p>

                        @if(isset($product))
                            <div class="mt-3 p-3 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">QR Code & EAN Intégré</span>
                                    <span class="font-mono text-xs font-bold text-[#0B0F14]">{{ $product->formatted_ean }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button 
                                        type="button" 
                                        @click="$dispatch('open-product-qr', { 
                                            id: {{ $product->id }}, 
                                            name: '{{ addslashes($product->name) }}', 
                                            sku: '{{ $product->sku }}', 
                                            barcode: '{{ $product->ean }}', 
                                            price: '{{ number_format((float) $product->selling_price, 0, ',', ' ') }} FCFA', 
                                            domain: '{{ addslashes($product->domain?->name ?? 'Général') }}', 
                                            unit: '{{ addslashes($product->unit ?? 'pièce') }}',
                                            qrDownloadUrl: '{{ route('stock.products.qr-download', $product) }}',
                                            labelDownloadUrl: '{{ route('stock.products.qr-download', ['product' => $product, 'label' => 1]) }}'
                                        })"
                                        class="h-8 px-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold inline-flex items-center gap-1.5 shadow-2xs transition"
                                    >
                                        <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                        <span>Aperçu QR</span>
                                    </button>

                                    <a 
                                        href="{{ route('stock.products.qr-download', $product) }}" 
                                        class="h-8 px-2.5 rounded-lg bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-semibold inline-flex items-center gap-1 shadow-xs transition"
                                        title="Télécharger le QR Code PNG"
                                        download
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span>Télécharger PNG</span>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Domaine d'activité rattaché</label>
                        <select name="domain_id" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition cursor-pointer">
                            <option value="">-- Multi-Domaines / Général --</option>
                            @foreach($domains as $dom)
                                <option value="{{ $dom->id }}" {{ old('domain_id', $product->domain_id ?? '') == $dom->id ? 'selected' : '' }}>
                                    {{ $dom->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-[#64748B] mt-1">Permet le filtrage par pôle d'activité dans le WMS et la caisse POS</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Catégorie d'article</label>
                        <select name="category_id" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition cursor-pointer">
                            <option value="">-- Aucune catégorie --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Unité de mesure de stockage</label>
                        <input type="text" name="unit" value="{{ old('unit', $product->unit ?? 'pièce') }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="pièce, carton, mètre, rouleau, kg...">
                    </div>
                </div>
            </x-card>

            <!-- 2. Photo & Visuel de l'Article -->
            <x-card title="2. Photo & Visuel de l'Article" subtitle="Image de référence affichée dans le catalogue des stocks et la caisse POS">
                <div 
                    x-data="{
                        imagePreview: '{{ isset($product) && $product->image ? asset('storage/' . $product->image) : '' }}',
                        deleteImage: '0',
                        onImageChange(event, source = 'file') {
                            const file = event.target.files[0];
                            if (file) {
                                this.deleteImage = '0';
                                const reader = new FileReader();
                                reader.onload = (e) => { this.imagePreview = e.target.result; };
                                reader.readAsDataURL(file);

                                if (source === 'camera') {
                                    try {
                                        if (window.DataTransfer && this.$refs.fileInput) {
                                            const dt = new DataTransfer();
                                            dt.items.add(file);
                                            this.$refs.fileInput.files = dt.files;
                                        }
                                    } catch (err) {}
                                } else if (source === 'file') {
                                    if (this.$refs.cameraInput) {
                                        this.$refs.cameraInput.value = '';
                                    }
                                }
                            }
                        },
                        removeImage() {
                            this.imagePreview = '';
                            if (this.$refs.fileInput) {
                                this.$refs.fileInput.value = '';
                            }
                            if (this.$refs.cameraInput) {
                                this.$refs.cameraInput.value = '';
                            }
                            this.deleteImage = '1';
                        }
                    }"
                    class="space-y-3"
                >
                    <input type="hidden" name="delete_image" :value="deleteImage">

                    <div class="flex flex-col sm:flex-row items-center gap-5 p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                        <!-- Zone d'aperçu d'image -->
                        <div class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-2xl border-2 border-dashed border-[#CBD5E1] bg-white overflow-hidden flex items-center justify-center shrink-0 shadow-2xs group">
                            <template x-if="imagePreview">
                                <img :src="imagePreview" alt="Aperçu du produit" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!imagePreview">
                                <div class="text-center p-3 text-slate-400">
                                    <svg class="w-8 h-8 mx-auto mb-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="text-[10px] font-medium block">Aucune photo</span>
                                </div>
                            </template>
                            <button 
                                type="button" 
                                x-show="imagePreview" 
                                @click="removeImage()" 
                                class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs font-bold hover:bg-rose-700 shadow-md transition-transform active:scale-90"
                                title="Supprimer la photo"
                            >
                                ✕
                            </button>
                        </div>

                        <!-- Contrôles d'upload & Prise de photo -->
                        <div class="flex-1 space-y-2.5 text-center sm:text-left">
                            <div>
                                <label class="block text-xs font-bold text-[#0B0F14]">Photo ou visuel de l'article</label>
                                <p class="text-[11px] text-[#64748B] mt-0.5">Prenez une photo en direct avec votre smartphone ou importez une image (JPG, PNG, WEBP, SVG · Max. 5 Mo).</p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 pt-1 justify-center sm:justify-start">
                                <!-- Bouton Caméra / Appareil photo Smartphone -->
                                <label class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white font-semibold text-xs cursor-pointer transition-all shadow-sm active:scale-95">
                                    <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Prendre une photo</span>
                                    <input 
                                        type="file" 
                                        name="image_camera" 
                                        x-ref="cameraInput" 
                                        @change="onImageChange($event, 'camera')" 
                                        accept="image/*" 
                                        capture="environment" 
                                        class="sr-only"
                                    >
                                </label>

                                <!-- Bouton Galerie / Fichiers -->
                                <label class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white border border-[#E2E8F0] hover:border-[#0066FF] hover:text-[#0066FF] text-[#0B0F14] font-semibold text-xs cursor-pointer transition-all shadow-2xs active:scale-95">
                                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span x-text="imagePreview ? 'Changer de fichier' : 'Galerie / Fichiers'"></span>
                                    <input 
                                        type="file" 
                                        name="image" 
                                        x-ref="fileInput" 
                                        @change="onImageChange($event, 'file')" 
                                        accept="image/png,image/jpeg,image/webp,image/svg+xml" 
                                        class="sr-only"
                                    >
                                </label>

                                <!-- Bouton Supprimer -->
                                <button 
                                    type="button" 
                                    x-show="imagePreview" 
                                    @click="removeImage()" 
                                    class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 font-semibold text-xs transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Supprimer</span>
                                </button>
                            </div>

                            <p class="text-[11px] text-[#64748B] flex items-center justify-center sm:justify-start gap-1.5 pt-0.5">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>Sur smartphone, <strong>« Prendre une photo »</strong> ouvre directement l'appareil photo du téléphone.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- 3. Valorisation & Prix de Vente -->
            <x-card title="3. Valorisation du Stock & Prix Public">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Prix d'achat HT unitaire (FCFA) *</label>
                        <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price ?? '') }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] font-semibold focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="0">
                        <p class="text-[10px] text-[#64748B] mt-1">Sert à la valorisation financière du stock WMS</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Prix de vente public TTC (FCFA) *</label>
                        <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', $product->selling_price ?? '') }}" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] font-bold focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="0">
                        <p class="text-[10px] text-[#64748B] mt-1">Tarif appliqué en caisse POS et facturation</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Taux de TVA (%)</label>
                        <input type="number" step="0.01" name="tax_rate" value="{{ old('tax_rate', $product->tax_rate ?? \App\Models\Setting::get('default_tax_rate', 0)) }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                    </div>
                </div>
            </x-card>

            <!-- 4. Seuils d'Alerte & Stock Initial Entrant -->
            <x-card title="4. Seuils d'Alerte WMS & Stock Initial">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Seuil Stock Minimum (Alerte réapprovisionnement)</label>
                        <input type="number" name="min_stock" value="{{ old('min_stock', $product->min_stock ?? '5') }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="5">
                        <p class="text-[10px] text-[#64748B] mt-1">Déclenche les alertes et les suggestions d'achat IA</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Seuil Stock Maximum (Surstock)</label>
                        <input type="number" name="max_stock" value="{{ old('max_stock', $product->max_stock ?? '100') }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="100">
                        <p class="text-[10px] text-[#64748B] mt-1">Plafond pour éviter les capitaux immobilisés</p>
                    </div>

                    @if(!isset($product))
                        <!-- Initialisation du Stock à la création -->
                        <div class="md:col-span-2 p-4 rounded-xl bg-blue-50/60 border border-blue-200/80 space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#0066FF]"></span>
                                <h4 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">Entrée de Stock Initiale (Optionnel)</h4>
                            </div>
                            <p class="text-[11px] text-slate-600">
                                Si vous disposez déjà d'unités physiques au magasin, vous pouvez initialiser le stock immédiatement. Un mouvement d'entrée officiel sera consigné dans le journal d'audit WMS.
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1">Entrepôt de dépôt initial</label>
                                    <select name="initial_warehouse_id" x-model="warehouseId" class="w-full px-3 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs focus:outline-none focus:border-[#0066FF]">
                                        <option value="">-- Aucun stock initial pour le moment --</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1">Quantité initiale en stock</label>
                                    <input type="number" name="initial_quantity" x-model.number="initialQty" min="0" class="w-full px-3 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] font-bold text-xs focus:outline-none focus:border-[#0066FF]" placeholder="0">
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Gestion et Modification Directe des Stocks par Entrepôt -->
                        <div class="md:col-span-2 p-4 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#E2E8F0] pb-2.5">
                                <div>
                                    <h4 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider flex items-center gap-2">
                                        <span>📦 Stocks & Répartition par Entrepôt</span>
                                        <span class="rounded bg-blue-100 text-[#0066FF] px-2 py-0.5 text-[10px] font-semibold">Modifiable en direct</span>
                                    </h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Modifiez directement les quantités physiques par entrepôt ou assignez un nouvel entrepôt. Les ajustements seront automatiquement inscrits au journal WMS.
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-[11px] text-slate-500 font-medium">Stock Total :</span>
                                    <span class="text-xs font-bold text-[#0066FF] ml-1">{{ $product->current_stock }} {{ $product->unit ?? 'pièces' }}</span>
                                </div>
                            </div>

                            @if($warehouseStocks->isNotEmpty())
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($warehouseStocks as $ws)
                                        <div class="p-3 bg-white rounded-xl border border-[#E2E8F0] shadow-2xs space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-xs text-[#0B0F14] truncate">{{ $ws->warehouse->name }}</span>
                                                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold">{{ $ws->warehouse->code }}</span>
                                            </div>
                                            <div class="flex items-center justify-between text-[11px] text-slate-500">
                                                <span>Disponible : <strong class="text-[#0066FF]">{{ $ws->available_quantity }}</strong></span>
                                                <span>Réservé : <strong class="text-amber-700">{{ $ws->reserved_quantity }}</strong></span>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-600 uppercase mb-1">Stock Physique Actuel</label>
                                                <div class="relative">
                                                    <input 
                                                        type="number" 
                                                        name="warehouse_stocks[{{ $ws->warehouse_id }}]" 
                                                        value="{{ old('warehouse_stocks.'.$ws->warehouse_id, $ws->physical_quantity) }}" 
                                                        min="0" 
                                                        required
                                                        class="w-full px-3 py-1.5 rounded-lg bg-slate-50 border border-[#E2E8F0] text-xs font-bold text-[#0B0F14] focus:bg-white focus:outline-none focus:border-[#0066FF]"
                                                    >
                                                    <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-semibold">
                                                        {{ $product->unit ?? 'unités' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                                    Cet article n'est actuellement affecté à aucun entrepôt physique (Stock = 0).
                                </div>
                            @endif

                            {{-- Option d'assignation à un nouvel entrepôt --}}
                            <div class="pt-3 border-t border-[#E2E8F0]">
                                <h5 class="text-xs font-bold text-[#0B0F14] mb-2 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Affecter à un autre entrepôt / Initialiser du stock</span>
                                </h5>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Sélectionner un entrepôt</label>
                                        <select name="new_warehouse_id" class="w-full px-3 py-2 rounded-lg bg-white border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                                            <option value="">-- Choisir un entrepôt à ajouter --</option>
                                            @foreach($warehouses as $wh)
                                                @if(! $warehouseStocks->pluck('warehouse_id')->contains($wh->id))
                                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Quantité à injecter</label>
                                        <div class="relative">
                                            <input type="number" name="new_warehouse_quantity" min="0" placeholder="0" class="w-full px-3 py-2 rounded-lg bg-white border border-[#E2E8F0] text-xs font-bold text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-semibold">{{ $product->unit ?? 'unités' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Description & Remarques logistiques</label>
                        <textarea name="description" rows="2" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="Spécifications, dimensions, conditions de stockage...">{{ old('description', $product->description ?? '') }}</textarea>
                    </div>

                    <div class="flex items-center pt-2 md:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }} class="rounded border-[#E2E8F0] text-[#0066FF] focus:ring-[#0066FF]">
                            <span class="text-xs font-medium text-[#0B0F14]">Article actif et opérationnel (visible en stock, mouvements et POS)</span>
                        </label>
                    </div>
                </div>

                <div class="pt-6 border-t border-[#E2E8F0] mt-6 flex justify-end gap-3">
                    <x-button variant="secondary" href="{{ route('stock.index', ['tab' => 'disponibilite']) }}">
                        Annuler
                    </x-button>
                    <x-button variant="primary" type="submit">
                        {{ isset($product) ? 'Mettre à jour l\'article' : 'Enregistrer et intégrer au Stock' }}
                    </x-button>
                </div>
            </x-card>
        </form>
    </div>
</x-layouts.app>
