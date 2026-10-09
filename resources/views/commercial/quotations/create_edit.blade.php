<x-layouts.app>
    <x-slot:title>{{ isset($quotation) ? 'Modifier' : 'Nouveau' }} Devis — IVOSPHERE ERP</x-slot>

    @php
        $isEdit = isset($quotation);

        // Préparation des lignes d'articles (avec conservation en cas d'erreur de validation)
        $rawOldItems = old('items');
        if (!empty($rawOldItems) && is_array($rawOldItems)) {
            $initialItems = array_values($rawOldItems);
        } elseif ($isEdit && $quotation->items->isNotEmpty()) {
            $initialItems = $quotation->items->map(function ($item) {
                return [
                    'product_id'  => $item->product_id,
                    'description' => $item->description,
                    'quantity'    => (float) $item->quantity,
                    'unit_price'  => (float) $item->unit_price,
                    'tax_rate'    => (float) $item->tax_rate,
                    'discount'    => (float) ($item->discount ?? 0),
                ];
            })->toArray();
        } else {
            $initialItems = [
                [
                    'product_id'  => null,
                    'description' => '',
                    'quantity'    => 1,
                    'unit_price'  => 0,
                    'tax_rate'    => 18,
                    'discount'    => 0,
                ]
            ];
        }

        $defaultDate = date('Y-m-d');
        $defaultValidUntil = date('Y-m-d', strtotime('+30 days'));
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">
        <x-page-header 
            title="{{ $isEdit ? 'Modifier le Devis : ' . $quotation->reference : 'Nouveau Devis Commercial' }}"
            description="Établissez une proposition commerciale proforma chiffrée avec calcul automatique des taxes, remises et totaux TTC"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                    ['label' => 'Devis', 'url' => route('commercial.quotations.index')],
                    ['label' => $isEdit ? 'Modification' : 'Nouveau Devis']
                ]" />
            </x-slot:breadcrumbs>
        </x-page-header>

        <form 
            action="{{ $isEdit ? route('commercial.quotations.update', $quotation) : route('commercial.quotations.store') }}" 
            method="POST" 
            x-data="quotationForm({
                products: {{ Js::from($products ?? []) }},
                initialItems: {{ Js::from($initialItems) }}
            })"
            class="space-y-6 text-xs"
        >
            @csrf
            @if($isEdit) @method('PUT') @endif

            {{-- Erreurs globales si validation échoue --}}
            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 space-y-1">
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Veuillez corriger les erreurs ci-dessous avant d'enregistrer :</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 1. Informations Générales -->
            <x-card title="1. Informations Générales du Devis">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Client Référent --}}
                    <div class="sm:col-span-2">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="customer_id" class="block font-semibold text-[#0B0F14]">Client Référent *</label>
                            <a href="{{ route('commercial.customers.create') }}" target="_blank" class="text-[#0066FF] hover:underline font-medium text-[11px] inline-flex items-center gap-1">
                                <span>+ Nouveau Client</span>
                            </a>
                        </div>
                        <select 
                            id="customer_id" 
                            name="customer_id" 
                            x-model="selectedCustomer"
                            required 
                            class="w-full px-3 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                        >
                            <option value="">Sélectionnez un client...</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $quotation->customer_id ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} {{ $c->company ? '— (' . $c->company . ')' : '' }} [{{ $c->code }}]
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Domaine d'Activité Métier --}}
                    <div>
                        <label for="domain_id" class="block font-semibold text-[#0B0F14] mb-1.5">Pôle / Domaine d'Activité</label>
                        <select 
                            id="domain_id" 
                            name="domain_id" 
                            class="w-full px-3 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                        >
                            <option value="">Général / Non assigné</option>
                            @foreach($domains as $d)
                                <option value="{{ $d->id }}" {{ old('domain_id', $quotation->domain_id ?? '') == $d->id ? 'selected' : '' }}>
                                    {{ $d->name }} ({{ $d->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('domain_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Statut du Devis --}}
                    <div>
                        <label for="status" class="block font-semibold text-[#0B0F14] mb-1.5">Statut de la Proposition *</label>
                        <select 
                            id="status" 
                            name="status" 
                            required 
                            class="w-full px-3 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                        >
                            <option value="brouillon" {{ old('status', $quotation->status ?? 'brouillon') == 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                            <option value="envoyé" {{ old('status', $quotation->status ?? '') == 'envoyé' ? 'selected' : '' }}>Envoyé au client</option>
                            <option value="accepté" {{ old('status', $quotation->status ?? '') == 'accepté' ? 'selected' : '' }}>Accepté (Signé / Validé)</option>
                            <option value="refusé" {{ old('status', $quotation->status ?? '') == 'refusé' ? 'selected' : '' }}>Refusé</option>
                            <option value="expiré" {{ old('status', $quotation->status ?? '') == 'expiré' ? 'selected' : '' }}>Expiré</option>
                        </select>
                        @error('status') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Date d'émission --}}
                    <div>
                        <label for="date" class="block font-semibold text-[#0B0F14] mb-1.5">Date d'Émission *</label>
                        <input 
                            id="date" 
                            type="date" 
                            name="date" 
                            value="{{ old('date', isset($quotation) && $quotation->date ? $quotation->date->format('Y-m-d') : $defaultDate) }}" 
                            required 
                            class="w-full px-3 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                        >
                        @error('date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Date de Validité --}}
                    <div>
                        <label for="valid_until" class="block font-semibold text-[#0B0F14] mb-1.5">Validité de l'Offre Jusqu'au</label>
                        <input 
                            id="valid_until" 
                            type="date" 
                            name="valid_until" 
                            value="{{ old('valid_until', isset($quotation) && $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : $defaultValidUntil) }}" 
                            class="w-full px-3 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                        >
                        @error('valid_until') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </x-card>

            <!-- 2. Lignes du Devis & Calculs Dynamiques -->
            <x-card title="2. Articles & Lignes de Chiffrage">
                <div class="space-y-3">
                    {{-- En-tête des colonnes sur grand écran --}}
                    <div class="hidden lg:grid lg:grid-cols-12 gap-2 text-[#64748B] font-bold text-[11px] uppercase tracking-wider pb-2 border-b border-[#E2E8F0]">
                        <div class="lg:col-span-3">Sélection Article / Produit</div>
                        <div class="lg:col-span-3">Description / Libellé</div>
                        <div class="lg:col-span-1 text-center">Quantité</div>
                        <div class="lg:col-span-2 text-right">Prix Unitaire HT</div>
                        <div class="lg:col-span-1 text-right">Remise (F)</div>
                        <div class="lg:col-span-1 text-right">TVA %</div>
                        <div class="lg:col-span-1 text-right">Total TTC</div>
                    </div>

                    {{-- Liste dynamique des lignes Alpine --}}
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-3 sm:p-3.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-2 lg:space-y-0 lg:grid lg:grid-cols-12 lg:gap-2 lg:items-center transition-all hover:border-slate-300">
                            
                            {{-- 1. Sélecteur Produit du catalogue --}}
                            <div class="lg:col-span-3">
                                <label class="lg:hidden block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Article du Catalogue</label>
                                <select 
                                    :name="'items['+index+'][product_id]'" 
                                    x-model="item.product_id"
                                    @change="onProductSelect(index, $event)"
                                    class="w-full px-2.5 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs focus:outline-none focus:border-[#0066FF] transition"
                                >
                                    <option value="">— Article personnalisé / Libre —</option>
                                    <template x-for="p in products" :key="p.id">
                                        <option :value="p.id" :selected="String(item.product_id) === String(p.id)" x-text="p.name + ' (' + formatNumber(p.selling_price) + ' F)'"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- 2. Description libre --}}
                            <div class="lg:col-span-3">
                                <label class="lg:hidden block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Désignation *</label>
                                <input 
                                    type="text" 
                                    x-model="item.description" 
                                    :name="'items['+index+'][description]'" 
                                    placeholder="Désignation du produit ou service" 
                                    required 
                                    class="w-full px-2.5 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs focus:outline-none focus:border-[#0066FF] transition"
                                >
                            </div>

                            {{-- 3. Quantité --}}
                            <div class="lg:col-span-1">
                                <label class="lg:hidden block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Quantité *</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0.01" 
                                    x-model.number="item.quantity" 
                                    :name="'items['+index+'][quantity]'" 
                                    placeholder="Qté" 
                                    required 
                                    class="w-full px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-center font-bold focus:outline-none focus:border-[#0066FF] transition"
                                >
                            </div>

                            {{-- 4. Prix Unitaire HT --}}
                            <div class="lg:col-span-2">
                                <label class="lg:hidden block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Prix Unitaire HT (FCFA) *</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    x-model.number="item.unit_price" 
                                    :name="'items['+index+'][unit_price]'" 
                                    placeholder="Prix unitaire" 
                                    required 
                                    class="w-full px-2.5 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-right font-mono font-semibold focus:outline-none focus:border-[#0066FF] transition"
                                >
                            </div>

                            {{-- 5. Remise FCFA --}}
                            <div class="lg:col-span-1">
                                <label class="lg:hidden block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Remise Ligne (F)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    x-model.number="item.discount" 
                                    :name="'items['+index+'][discount]'" 
                                    placeholder="0" 
                                    class="w-full px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-right font-mono focus:outline-none focus:border-[#0066FF] transition"
                                >
                            </div>

                            {{-- 6. Taux TVA % --}}
                            <div class="lg:col-span-1">
                                <label class="lg:hidden block text-[10px] font-bold uppercase text-slate-500 mb-0.5">TVA %</label>
                                <input 
                                    type="number" 
                                    step="0.1" 
                                    min="0" 
                                    max="100" 
                                    x-model.number="item.tax_rate" 
                                    :name="'items['+index+'][tax_rate]'" 
                                    placeholder="18" 
                                    class="w-full px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-right font-mono focus:outline-none focus:border-[#0066FF] transition"
                                >
                            </div>

                            {{-- 7. Total Ligne TTC & Suppression --}}
                            <div class="lg:col-span-1 flex items-center justify-between lg:justify-end gap-2 pt-1 lg:pt-0 border-t border-slate-200 lg:border-t-0">
                                <div class="text-right">
                                    <span class="lg:hidden text-[11px] text-slate-500 mr-2">Total TTC :</span>
                                    <span class="font-bold text-[#0B0F14] font-mono text-xs whitespace-nowrap" x-text="formatNumber(lineTotal(item)) + ' F'"></span>
                                </div>
                                <button 
                                    type="button" 
                                    @click="removeItem(index)" 
                                    :disabled="items.length <= 1"
                                    class="p-1 rounded-md text-[#64748B] hover:text-rose-600 hover:bg-rose-50 disabled:opacity-30 disabled:cursor-not-allowed transition" 
                                    title="Supprimer la ligne"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Action ajout ligne & Totaux chiffrés --}}
                <div class="mt-4 flex flex-col sm:flex-row items-stretch sm:items-start justify-between gap-4 pt-2">
                    <button 
                        type="button" 
                        @click="addItem" 
                        class="px-3.5 py-2 rounded-xl border border-[#0066FF] text-[#0066FF] hover:bg-blue-50 font-semibold text-xs transition flex items-center justify-center gap-1.5 shadow-2xs active:scale-98"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Ajouter une ligne d'article</span>
                    </button>

                    {{-- Récapitulatif financier --}}
                    <div class="w-full sm:w-80 space-y-2 bg-[#F8FAFC] p-4 rounded-xl border border-[#E2E8F0] text-xs">
                        <div class="flex justify-between text-[#64748B]">
                            <span>Sous-total Brut HT :</span>
                            <span class="text-[#0B0F14] font-semibold font-mono" x-text="formatMoney(subtotal)"></span>
                        </div>
                        <div class="flex justify-between text-emerald-600" x-show="totalDiscount > 0" x-cloak>
                            <span>Total Remises :</span>
                            <span class="font-semibold font-mono" x-text="'− ' + formatMoney(totalDiscount)"></span>
                        </div>
                        <div class="flex justify-between text-[#64748B] pt-1 border-t border-slate-200">
                            <span>Net Commercial HT :</span>
                            <span class="text-[#0B0F14] font-semibold font-mono" x-text="formatMoney(netHt)"></span>
                        </div>
                        <div class="flex justify-between text-[#64748B]">
                            <span>Montant Total TVA :</span>
                            <span class="text-[#0B0F14] font-semibold font-mono" x-text="formatMoney(totalTax)"></span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-[#0B0F14] pt-2 border-t border-[#E2E8F0]">
                            <span>Total TTC à Payer :</span>
                            <span class="text-[#0066FF] font-mono text-base" x-text="formatMoney(totalTtc)"></span>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- 3. Conditions Commerciales & Notes -->
            <x-card title="3. Conditions & Modalités d'Exécution">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="conditions" class="block font-semibold text-[#0B0F14] mb-1.5">Conditions de Règlement & Modalités</label>
                        <textarea 
                            id="conditions" 
                            name="conditions" 
                            rows="3" 
                            placeholder="Ex: Acompte de 50% à la commande, solde à la livraison. Validité de l'offre 30 jours." 
                            class="w-full px-3 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] transition"
                        >{{ old('conditions', $quotation->conditions ?? "Offre valable 30 jours à compter de la date d'émission. Modalités de règlement : 50% d'acompte à la commande, solde à la livraison.") }}</textarea>
                        @error('conditions') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="notes" class="block font-semibold text-[#0B0F14] mb-1.5">Notes Internes & Instructions Particulières</label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            rows="3" 
                            placeholder="Remarques spécifiques, délais de fabrication, interlocuteur technique..." 
                            class="w-full px-3 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] transition"
                        >{{ old('notes', $quotation->notes ?? '') }}</textarea>
                        @error('notes') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Boutons d'action --}}
                <div class="pt-6 border-t border-[#E2E8F0] mt-6 flex items-center justify-between">
                    <a 
                        href="{{ route('commercial.quotations.index') }}" 
                        class="px-4 py-2.5 rounded-xl border border-[#E2E8F0] text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-semibold text-xs transition"
                    >
                        Annuler
                    </a>

                    <button 
                        type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white font-bold text-xs shadow-md active:scale-98 transition-all flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ $isEdit ? 'Mettre à jour le devis' : 'Enregistrer le devis' }}</span>
                    </button>
                </div>
            </x-card>
        </form>
    </div>

    {{-- Script Alpine autonome garanti d'exécution immédiate --}}
    <script>
        function quotationForm(config) {
            return {
                products: config.products || [],
                items: config.initialItems || [],
                selectedCustomer: '{{ old('customer_id', $quotation->customer_id ?? '') }}',

                onProductSelect(index, event) {
                    const pId = event.target.value;
                    if (!pId) return;
                    const p = this.products.find(x => String(x.id) === String(pId));
                    if (p) {
                        this.items[index].product_id = p.id;
                        if (!this.items[index].description || this.items[index].description === '') {
                            this.items[index].description = p.name;
                        }
                        this.items[index].unit_price = parseFloat(p.selling_price) || 0;
                        this.items[index].tax_rate = parseFloat(p.tax_rate ?? 18) || 0;
                    }
                },

                addItem() {
                    this.items.push({
                        product_id: null,
                        description: '',
                        quantity: 1,
                        unit_price: 0,
                        tax_rate: 18,
                        discount: 0,
                    });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },

                lineSubtotal(item) {
                    return (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
                },

                lineNetHt(item) {
                    return Math.max(0, this.lineSubtotal(item) - (parseFloat(item.discount) || 0));
                },

                lineTax(item) {
                    return this.lineNetHt(item) * ((parseFloat(item.tax_rate) || 0) / 100);
                },

                lineTotal(item) {
                    return this.lineNetHt(item) + this.lineTax(item);
                },

                get subtotal() {
                    return this.items.reduce((sum, item) => sum + this.lineSubtotal(item), 0);
                },

                get totalDiscount() {
                    return this.items.reduce((sum, item) => sum + (parseFloat(item.discount) || 0), 0);
                },

                get netHt() {
                    return Math.max(0, this.subtotal - this.totalDiscount);
                },

                get totalTax() {
                    return this.items.reduce((sum, item) => sum + this.lineTax(item), 0);
                },

                get totalTtc() {
                    return this.netHt + this.totalTax;
                },

                formatNumber(val) {
                    return new Intl.NumberFormat('fr-FR').format(Math.round(val || 0));
                },

                formatMoney(amount) {
                    return this.formatNumber(amount) + ' FCFA';
                }
            };
        }
    </script>
</x-layouts.app>
