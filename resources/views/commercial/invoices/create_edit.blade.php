<x-layouts.app>
    <x-slot:title>{{ isset($invoice) ? 'Modifier' : 'Nouvelle' }} Facture — IVOSPHERE ERP</x-slot>

    @php
        $initialItems = [];
        if (old('items')) {
            foreach (old('items') as $oldItem) {
                $initialItems[] = [
                    'product_id' => $oldItem['product_id'] ?? '',
                    'description' => $oldItem['description'] ?? '',
                    'quantity' => (float) ($oldItem['quantity'] ?? 1),
                    'unit_price' => (float) ($oldItem['unit_price'] ?? 0),
                    'tax_rate' => isset($oldItem['tax_rate']) && $oldItem['tax_rate'] !== '' ? (float) $oldItem['tax_rate'] : 18,
                    'discount' => (float) ($oldItem['discount'] ?? 0),
                ];
            }
        } elseif (isset($invoice) && $invoice->items->isNotEmpty()) {
            foreach ($invoice->items as $invItem) {
                $initialItems[] = [
                    'product_id' => $invItem->product_id ?? '',
                    'description' => $invItem->description ?? '',
                    'quantity' => (float) $invItem->quantity,
                    'unit_price' => (float) $invItem->unit_price,
                    'tax_rate' => (float) ($invItem->tax_rate ?? 18),
                    'discount' => (float) ($invItem->discount ?? 0),
                ];
            }
        } else {
            $initialItems[] = [
                'product_id' => '',
                'description' => '',
                'quantity' => 1,
                'unit_price' => 0,
                'tax_rate' => 18,
                'discount' => 0,
            ];
        }

        $productsData = ($products ?? collect())->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->selling_price,
                'tax_rate' => (float) ($p->tax_rate ?? 18),
                'sku' => $p->sku ?? '',
            ];
        })->values();

        $customersData = ($customers ?? collect())->map(function($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'company' => $c->company ?? '',
                'phone' => $c->phone ?? '',
                'email' => $c->email ?? '',
                'city' => $c->city ?? '',
            ];
        })->values();
    @endphp

    <div 
        class="max-w-5xl mx-auto space-y-6"
        x-data="invoiceForm({
            initialItems: {{ json_encode($initialItems) }},
            products: {{ json_encode($productsData) }},
            customers: {{ json_encode($customersData) }},
            selectedCustomerId: '{{ old('customer_id', $invoice->customer_id ?? '') }}'
        })"
    >
        <x-page-header 
            title="{{ isset($invoice) ? 'Modifier la Facture : ' . $invoice->reference : 'Nouvelle Facture de Vente' }}"
            description="{{ isset($invoice) ? 'Mise à jour des informations de facturation et des règlements' : 'Émission d\'une facture client officielle avec calcul dynamique des taxes et remises' }}"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                    ['label' => 'Factures', 'url' => route('commercial.invoices.index')],
                    ['label' => isset($invoice) ? 'Modification' : 'Nouvelle']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button variant="secondary" href="{{ route('commercial.invoices.index') }}" class="min-h-[44px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour à la liste</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        @if(isset($errors) && $errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-sm">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Veuillez corriger les erreurs de saisie :</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-rose-700 ml-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ isset($invoice) ? route('commercial.invoices.update', $invoice) : route('commercial.invoices.store') }}" method="POST" class="space-y-6 text-xs">
            @csrf
            @if(isset($invoice)) @method('PUT') @endif

            <!-- 1. Informations Générales de la Facture -->
            <x-card title="1. Informations Générales" subtitle="Client, dates d'émission et d'échéance">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Client -->
                    <div class="space-y-1.5 md:col-span-1">
                        <label class="block text-xs font-semibold text-[#0B0F14]">Client Référent <span class="text-rose-600">*</span></label>
                        <select 
                            name="customer_id" 
                            x-model="selectedCustomerId" 
                            @change="onCustomerChange()"
                            required 
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                        >
                            <option value="">-- Sélectionner un client --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $invoice->customer_id ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} {{ $c->company ? '('.$c->company.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <template x-if="selectedCustomer">
                            <div class="p-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[11px] text-[#64748B] flex items-center justify-between">
                                <span class="truncate" x-text="selectedCustomer.phone || selectedCustomer.email || 'Aucune coordonnée'"></span>
                                <span class="font-medium text-[#0066FF]" x-text="selectedCustomer.city || ''"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Date de Facturation -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[#0B0F14]">Date de Facturation <span class="text-rose-600">*</span></label>
                        <input 
                            type="date" 
                            name="date" 
                            value="{{ old('date', isset($invoice) && $invoice->date ? $invoice->date->format('Y-m-d') : date('Y-m-d')) }}" 
                            required 
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                        />
                    </div>

                    <!-- Date d'Échéance -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[#0B0F14]">Date d'Échéance</label>
                        <input 
                            type="date" 
                            name="due_date" 
                            value="{{ old('due_date', isset($invoice) && $invoice->due_date ? $invoice->due_date->format('Y-m-d') : date('Y-m-d', strtotime('+30 days'))) }}" 
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                        />
                    </div>

                    <!-- Statut -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[#0B0F14]">Statut de Paiement <span class="text-rose-600">*</span></label>
                        <select 
                            name="status" 
                            required 
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                        >
                            @php $currentStatus = old('status', $invoice->status ?? 'non_payee'); @endphp
                            <option value="non_payee" {{ $currentStatus === 'non_payee' ? 'selected' : '' }}>⏳ Non payée</option>
                            <option value="partielle" {{ $currentStatus === 'partielle' ? 'selected' : '' }}>🟡 Partiellement payée</option>
                            <option value="payee" {{ $currentStatus === 'payee' ? 'selected' : '' }}>✅ Entièrement réglée</option>
                            <option value="annulee" {{ $currentStatus === 'annulee' ? 'selected' : '' }}>❌ Annulée</option>
                        </select>
                    </div>

                    <!-- Domaine Métier (Optionnel) -->
                    @if(isset($domains) && $domains->count() > 0)
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[#0B0F14]">Pôle d'Activité / Domaine</label>
                        <select 
                            name="domain_id" 
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                        >
                            <option value="">-- Pôle global / non spécifié --</option>
                            @foreach($domains as $d)
                                <option value="{{ $d->id }}" {{ old('domain_id', $invoice->domain_id ?? '') == $d->id ? 'selected' : '' }}>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Notes & Mentions de Règlement -->
                    <div class="space-y-1.5 md:col-span-3">
                        <label class="block text-xs font-semibold text-[#0B0F14]">Notes & Conditions de Règlement</label>
                        <textarea 
                            name="notes" 
                            rows="2" 
                            placeholder="Conditions de paiement (virement, chèque, mobile money), coordonnées bancaires, mention de pénalités de retard..." 
                            class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                        >{{ old('notes', $invoice->notes ?? '') }}</textarea>
                    </div>
                </div>
            </x-card>

            <!-- 2. Articles & Lignes de Facture -->
            <x-card 
                title="2. Articles & Lignes de Facturation" 
                subtitle="Sélectionnez un article du catalogue ou saisissez une prestation libre"
            >
                <div class="space-y-3">
                    <!-- En-têtes du tableau (Desktop >= sm) -->
                    <div class="hidden sm:grid sm:grid-cols-12 gap-2 text-[#64748B] font-semibold text-[11px] uppercase tracking-wider pb-1 border-b border-[#E2E8F0]">
                        <div class="sm:col-span-4">Article / Désignation <span class="text-rose-600">*</span></div>
                        <div class="sm:col-span-2 text-right">Quantité <span class="text-rose-600">*</span></div>
                        <div class="sm:col-span-2 text-right">Prix Unitaire <span class="text-rose-600">*</span></div>
                        <div class="sm:col-span-1 text-right">Remise (F)</div>
                        <div class="sm:col-span-1 text-right">TVA %</div>
                        <div class="sm:col-span-2 text-right">Total TTC</div>
                    </div>

                    <!-- Lignes de facture (Boucle Alpine) -->
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-3 sm:p-2.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] space-y-2.5 sm:space-y-0 sm:grid sm:grid-cols-12 sm:gap-2 sm:items-center">
                            
                            <!-- Sélecteur Produit & Description -->
                            <div class="sm:col-span-4 space-y-1.5">
                                <div class="flex items-center gap-1.5">
                                    <template x-if="products.length > 0">
                                        <select 
                                            x-model="item.product_id" 
                                            :name="'items['+index+'][product_id]'" 
                                            @change="onProductSelect(index)"
                                            class="w-1/3 min-h-[36px] px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[11px] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] transition"
                                        >
                                            <option value="">Catalogue...</option>
                                            <template x-for="p in products" :key="p.id">
                                                <option :value="p.id" :selected="item.product_id == p.id" x-text="p.name"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <input 
                                        type="text" 
                                        x-model="item.description" 
                                        :name="'items['+index+'][description]'" 
                                        placeholder="Désignation du produit ou service *" 
                                        required 
                                        class="flex-1 min-h-[36px] px-2.5 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs focus:outline-none focus:border-[#0066FF] transition"
                                    />
                                </div>
                            </div>

                            <!-- Quantité -->
                            <div class="sm:col-span-2">
                                <div class="sm:hidden text-[10px] font-semibold text-[#64748B] mb-1">Quantité :</div>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0.01" 
                                    x-model.number="item.quantity" 
                                    :name="'items['+index+'][quantity]'" 
                                    placeholder="Qté" 
                                    required 
                                    class="w-full min-h-[36px] px-2.5 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-left sm:text-right focus:outline-none focus:border-[#0066FF] transition font-mono"
                                />
                            </div>

                            <!-- Prix Unitaire -->
                            <div class="sm:col-span-2">
                                <div class="sm:hidden text-[10px] font-semibold text-[#64748B] mb-1">Prix Unitaire (FCFA) :</div>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    x-model.number="item.unit_price" 
                                    :name="'items['+index+'][unit_price]'" 
                                    placeholder="Prix unit." 
                                    required 
                                    class="w-full min-h-[36px] px-2.5 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-left sm:text-right focus:outline-none focus:border-[#0066FF] transition font-mono"
                                />
                            </div>

                            <!-- Remise -->
                            <div class="sm:col-span-1">
                                <div class="sm:hidden text-[10px] font-semibold text-[#64748B] mb-1">Remise (FCFA) :</div>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    x-model.number="item.discount" 
                                    :name="'items['+index+'][discount]'" 
                                    placeholder="0" 
                                    class="w-full min-h-[36px] px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-left sm:text-right focus:outline-none focus:border-[#0066FF] transition font-mono"
                                />
                            </div>

                            <!-- TVA % -->
                            <div class="sm:col-span-1">
                                <div class="sm:hidden text-[10px] font-semibold text-[#64748B] mb-1">TVA (%) :</div>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    x-model.number="item.tax_rate" 
                                    :name="'items['+index+'][tax_rate]'" 
                                    placeholder="18" 
                                    class="w-full min-h-[36px] px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-left sm:text-right focus:outline-none focus:border-[#0066FF] transition font-mono"
                                />
                            </div>

                            <!-- Total Ligne & Bouton Suppression -->
                            <div class="sm:col-span-2 flex items-center justify-between sm:justify-end gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-[#E2E8F0]">
                                <div class="sm:hidden text-xs text-[#64748B]">Total Ligne TTC :</div>
                                <span class="font-bold text-[#0B0F14] text-xs sm:text-right font-mono" x-text="formatMoney(calculateItemTotal(item))"></span>
                                <button 
                                    type="button" 
                                    @click="removeItem(index)" 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-[#64748B] hover:text-rose-600 hover:bg-rose-50 transition shrink-0" 
                                    title="Supprimer cette ligne"
                                    :disabled="items.length <= 1"
                                    :class="{ 'opacity-30 cursor-not-allowed': items.length <= 1 }"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Bouton Ajouter + Récapitulatif Financier -->
                <div class="mt-5 flex flex-col sm:flex-row items-stretch sm:items-start justify-between gap-4">
                    <button 
                        type="button" 
                        @click="addItem()" 
                        class="min-h-[44px] px-4 py-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-[#F5F7FA] text-[#0B0F14] font-semibold text-xs transition flex items-center justify-center sm:justify-start gap-2 shadow-2xs"
                    >
                        <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Ajouter une ligne</span>
                    </button>

                    <!-- Carte Récapitulative Totaux -->
                    <div class="w-full sm:w-80 p-4 rounded-2xl bg-[#F5F7FA] border border-[#E2E8F0] space-y-2.5">
                        <div class="flex justify-between items-center text-[#64748B]">
                            <span>Sous-total HT</span>
                            <span class="font-bold text-[#0B0F14] font-mono text-xs" x-text="formatMoney(calculateSubtotal())"></span>
                        </div>
                        <div class="flex justify-between items-center text-[#64748B]">
                            <span>Total Remises</span>
                            <span class="font-bold text-rose-600 font-mono text-xs" x-text="'- ' + formatMoney(calculateTotalDiscount())"></span>
                        </div>
                        <div class="flex justify-between items-center text-[#64748B]">
                            <span>Montant TVA</span>
                            <span class="font-bold text-[#0B0F14] font-mono text-xs" x-text="formatMoney(calculateTotalTax())"></span>
                        </div>
                        <div class="pt-2.5 border-t border-[#E2E8F0] flex justify-between items-center">
                            <span class="font-bold text-[#0B0F14] text-sm">Net à Payer (TTC)</span>
                            <span class="font-extrabold text-[#0066FF] font-mono text-base" x-text="formatMoney(calculateGrandTotal())"></span>
                        </div>
                    </div>
                </div>

                <!-- Actions du Formulaire -->
                <div class="pt-6 border-t border-[#E2E8F0] mt-6 flex flex-col sm:flex-row items-center justify-end gap-3">
                    <x-button variant="secondary" href="{{ route('commercial.invoices.index') }}" class="w-full sm:w-auto min-h-[44px]">
                        Annuler
                    </x-button>
                    <x-button variant="primary" type="submit" class="w-full sm:w-auto min-h-[44px] flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ isset($invoice) ? 'Mettre à jour la facture' : 'Émettre la facture' }}</span>
                    </x-button>
                </div>
            </x-card>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('invoiceForm', (config) => ({
                items: config.initialItems || [],
                products: config.products || [],
                customers: config.customers || [],
                selectedCustomerId: config.selectedCustomerId || '',

                get selectedCustomer() {
                    if (!this.selectedCustomerId) return null;
                    return this.customers.find(c => c.id == this.selectedCustomerId) || null;
                },

                onCustomerChange() {
                    // Hook éventuel pour auto-sélection ou calculs spécifiques
                },

                onProductSelect(index) {
                    const item = this.items[index];
                    if (!item || !item.product_id) return;
                    const product = this.products.find(p => p.id == item.product_id);
                    if (product) {
                        item.description = product.name;
                        item.unit_price = product.price;
                        item.tax_rate = product.tax_rate;
                    }
                },

                addItem() {
                    this.items.push({
                        product_id: '',
                        description: '',
                        quantity: 1,
                        unit_price: 0,
                        tax_rate: 18,
                        discount: 0
                    });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },

                calculateItemTotal(item) {
                    const q = parseFloat(item.quantity) || 0;
                    const p = parseFloat(item.unit_price) || 0;
                    const d = parseFloat(item.discount) || 0;
                    const tr = parseFloat(item.tax_rate) || 0;
                    const base = Math.max(0, (q * p) - d);
                    const tax = base * (tr / 100);
                    return base + tax;
                },

                calculateSubtotal() {
                    return this.items.reduce((sum, item) => {
                        const q = parseFloat(item.quantity) || 0;
                        const p = parseFloat(item.unit_price) || 0;
                        return sum + (q * p);
                    }, 0);
                },

                calculateTotalDiscount() {
                    return this.items.reduce((sum, item) => {
                        return sum + (parseFloat(item.discount) || 0);
                    }, 0);
                },

                calculateTotalTax() {
                    return this.items.reduce((sum, item) => {
                        const q = parseFloat(item.quantity) || 0;
                        const p = parseFloat(item.unit_price) || 0;
                        const d = parseFloat(item.discount) || 0;
                        const tr = parseFloat(item.tax_rate) || 0;
                        const base = Math.max(0, (q * p) - d);
                        return sum + (base * (tr / 100));
                    }, 0);
                },

                calculateGrandTotal() {
                    return (this.calculateSubtotal() - this.calculateTotalDiscount()) + this.calculateTotalTax();
                },

                formatMoney(amount) {
                    return new Intl.NumberFormat('fr-FR', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(Math.round(amount || 0)) + ' FCFA';
                }
            }));
        });
    </script>
    @endpush
</x-layouts.app>
