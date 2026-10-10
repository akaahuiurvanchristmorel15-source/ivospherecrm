<x-layouts.app>
    <x-slot:title>{{ isset($product) ? 'Modifier' : 'Nouveau' }} Produit — IVOSPHERE ERP</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-page-header 
            title="{{ isset($product) ? 'Modifier le Produit : ' . $product->name : 'Nouveau Produit au Catalogue' }}"
            description="Définissez les codes références, code-barres pour la caisse POS, prix, TVA et domaine d'activité"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                    ['label' => 'Catalogue Produits', 'url' => route('commercial.products.index')],
                    ['label' => isset($product) ? 'Modification' : 'Nouveau']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button variant="secondary" href="{{ route('commercial.pos.index') }}" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>Accéder au POS</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <form 
            action="{{ isset($product) ? route('commercial.products.update', $product) : route('commercial.products.store') }}" 
            method="POST" 
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
                barcode: '{{ old('barcode', $product->barcode ?? '') }}'
            }"
        >
            @csrf
            @if(isset($product)) @method('PUT') @endif

            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
                    <p class="font-bold">Des erreurs ont été détectées dans le formulaire :</p>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-card title="1. Identification & Codes">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Code SKU / Référence *</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku ?? 'PRD-' . strtoupper(Str::random(6))) }}" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] font-mono focus:outline-none focus:border-[#0066FF] text-xs transition">
                        <p class="text-[10px] text-[#64748B] mt-1">Identifiant unique interne (ex: PRD-7X29A)</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Nom de l'article / Désignation *</label>
                        <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="Ex: T-Shirt Sport Pro">
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label class="block text-xs font-semibold text-[#0B0F14]">Code-barres (EAN-13 / Scanner POS)</label>
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
                                placeholder="Scannez ou saisissez (ex: 618403198186)"
                            >
                            <svg class="w-4 h-4 text-[#64748B] absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <p class="text-[10px] text-[#64748B] mt-1">Reconnu par la douchette et le scanner caméra sur le POS</p>

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
                                            domain: '{{ addslashes($product->domain?->name ?? 'Commercial') }}', 
                                            unit: '{{ addslashes($product->unit ?? 'pièce') }}',
                                            qrDownloadUrl: '{{ route('commercial.products.qr-download', $product) }}',
                                            labelDownloadUrl: '{{ route('commercial.products.qr-download', ['product' => $product, 'label' => 1]) }}'
                                        })"
                                        class="h-8 px-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold inline-flex items-center gap-1.5 shadow-2xs transition"
                                    >
                                        <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                        <span>Aperçu QR</span>
                                    </button>

                                    <a 
                                        href="{{ route('commercial.products.qr-download', $product) }}" 
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
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Domaine d'activité IVOSPHERE</label>
                        <select name="domain_id" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition cursor-pointer">
                            <option value="">-- Aucun domaine spécifique --</option>
                            @foreach($domains as $dom)
                                <option value="{{ $dom->id }}" {{ old('domain_id', $product->domain_id ?? '') == $dom->id ? 'selected' : '' }}>
                                    {{ $dom->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-[#64748B] mt-1">Permet le filtrage automatique sur la caisse POS</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Catégorie</label>
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
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Unité de mesure</label>
                        <input type="text" name="unit" value="{{ old('unit', $product->unit ?? 'pièce') }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="ex: pièce, carton, heure, m²">
                    </div>
                </div>
            </x-card>

            <x-card title="2. Tarification & Fiscalité">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Prix d'achat HT (FCFA)</label>
                        <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price ?? '') }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="0">
                        <p class="text-[10px] text-[#64748B] mt-1">Coût de revient pour calcul de marge</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Prix de vente public TTC (FCFA) *</label>
                        <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', $product->selling_price ?? '') }}" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] font-bold focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="0">
                        <p class="text-[10px] text-[#64748B] mt-1">Prix appliqué sur la caisse POS</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Taux de TVA (%)</label>
                        <input type="number" step="0.01" name="tax_rate" value="{{ old('tax_rate', $product->tax_rate ?? \App\Models\Setting::get('default_tax_rate', 0)) }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                    </div>
                </div>
            </x-card>

            <x-card title="3. Stock & Statut">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Seuil d'alerte stock minimum</label>
                        <input type="number" name="min_stock" value="{{ old('min_stock', $product->min_stock ?? '5') }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="5">
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }} class="rounded border-[#E2E8F0] text-[#0066FF] focus:ring-[#0066FF]">
                            <span class="text-xs font-medium text-[#0B0F14]">Produit actif et immédiatement disponible à la vente POS</span>
                        </label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Description / Spécifications</label>
                        <textarea name="description" rows="3" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="Détails optionnels sur l'article...">{{ old('description', $product->description ?? '') }}</textarea>
                    </div>
                </div>

                <div class="pt-6 border-t border-[#E2E8F0] mt-6 flex justify-end gap-3">
                    <x-button variant="secondary" href="{{ route('commercial.products.index') }}">
                        Annuler
                    </x-button>
                    <x-button variant="primary" type="submit">
                        {{ isset($product) ? 'Mettre à jour l\'article' : 'Enregistrer le produit' }}
                    </x-button>
                </div>
            </x-card>
        </form>
    </div>
</x-layouts.app>
