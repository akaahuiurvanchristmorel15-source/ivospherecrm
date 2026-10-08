<x-layouts.app>
    <x-slot:title>{{ isset($quotation) ? 'Modifier' : 'Nouveau' }} Devis — IVOSPHERE ERP</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <x-page-header 
            title="{{ isset($quotation) ? 'Modifier le Devis : ' . $quotation->reference : 'Nouveau Devis Commercial' }}"
            description="Établissez une offre commerciale proforma avec calcul dynamique des taxes et remises"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                    ['label' => 'Devis', 'url' => route('commercial.quotations.index')],
                    ['label' => isset($quotation) ? 'Modification' : 'Nouveau']
                ]" />
            </x-slot:breadcrumbs>
        </x-page-header>

        <form action="{{ isset($quotation) ? route('commercial.quotations.update', $quotation) : route('commercial.quotations.store') }}" method="POST" class="space-y-6 text-xs">
            @csrf
            @if(isset($quotation)) @method('PUT') @endif

            <!-- 1. Informations Générales -->
            <x-card title="1. Informations du Devis">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Client Référent *</label>
                        <select name="customer_id" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $quotation->customer_id ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date d'émission *</label>
                        <input type="date" name="date" value="{{ old('date', isset($quotation) ? $quotation->date->format('Y-m-d') : date('Y-m-d')) }}" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Statut de la proposition *</label>
                        <select name="status" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition">
                            <option value="brouillon" {{ old('status', $quotation->status ?? 'brouillon') == 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                            <option value="envoyé" {{ old('status', $quotation->status ?? '') == 'envoyé' ? 'selected' : '' }}>Envoyé</option>
                            <option value="accepté" {{ old('status', $quotation->status ?? '') == 'accepté' ? 'selected' : '' }}>Accepté</option>
                            <option value="refusé" {{ old('status', $quotation->status ?? '') == 'refusé' ? 'selected' : '' }}>Refusé</option>
                        </select>
                    </div>
                </div>
            </x-card>

            <!-- 2. Lignes du Devis -->
            <x-card 
                title="2. Articles & Lignes de Chiffrage"
                x-data="quotationItems({{ isset($quotation) ? json_encode($quotation->items->map(function($i) { return ['description' => $i->description, 'quantity' => $i->quantity, 'unit_price' => $i->unit_price, 'tax_rate' => $i->tax_rate]; })) : '[]' }})"
            >
                <div class="space-y-3">
                    <div class="hidden sm:grid sm:grid-cols-12 gap-3 text-[#64748B] font-semibold text-[11px] uppercase tracking-wider pb-1 border-b border-[#E2E8F0]">
                        <div class="sm:col-span-5">Désignation / Prestation</div>
                        <div class="sm:col-span-2 text-right">Quantité</div>
                        <div class="sm:col-span-2 text-right">Prix Unitaire</div>
                        <div class="sm:col-span-1 text-right">TVA %</div>
                        <div class="sm:col-span-2 text-right">Total Ligne</div>
                    </div>

                    <template x-for="(item, index) in items" :key="index">
                        <div class="flex flex-col sm:grid sm:grid-cols-12 gap-2 sm:gap-3 items-center p-2 rounded-lg bg-[#F5F7FA]/50 border border-[#E2E8F0]">
                            <div class="w-full sm:col-span-5">
                                <input type="text" x-model="item.description" :name="'items['+index+'][description]'" placeholder="Désignation du produit ou service" required class="w-full px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs focus:outline-none focus:border-[#0066FF] transition">
                            </div>
                            <div class="w-full sm:col-span-2">
                                <input type="number" step="0.01" x-model="item.quantity" :name="'items['+index+'][quantity]'" placeholder="Qté" required class="w-full px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-right focus:outline-none focus:border-[#0066FF] transition">
                            </div>
                            <div class="w-full sm:col-span-2">
                                <input type="number" step="0.01" x-model="item.unit_price" :name="'items['+index+'][unit_price]'" placeholder="Prix unitaire" required class="w-full px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-right focus:outline-none focus:border-[#0066FF] transition">
                            </div>
                            <div class="w-full sm:col-span-1">
                                <input type="number" step="0.01" x-model="item.tax_rate" :name="'items['+index+'][tax_rate]'" placeholder="TVA" class="w-full px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-right focus:outline-none focus:border-[#0066FF] transition">
                            </div>
                            <div class="w-full sm:col-span-2 flex items-center justify-between sm:justify-end gap-2 text-right">
                                <span class="font-bold text-[#0B0F14]" x-text="formatMoney((item.quantity * item.unit_price) * (1 + item.tax_rate/100))"></span>
                                <button type="button" @click="removeItem(index)" class="p-1 rounded-md text-[#64748B] hover:text-rose-600 hover:bg-rose-50 transition" title="Supprimer la ligne">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <button type="button" @click="addItem" class="px-3 py-1.5 rounded-lg border border-[#E2E8F0] bg-white hover:bg-[#F5F7FA] text-[#0B0F14] font-medium text-xs transition flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Ajouter une ligne</span>
                    </button>

                    <div class="w-72 space-y-1.5 bg-[#F5F7FA] p-3.5 rounded-xl border border-[#E2E8F0] text-xs">
                        <div class="flex justify-between text-[#64748B]">
                            <span>Sous-total HT</span>
                            <span class="text-[#0B0F14] font-bold" x-text="formatMoney(calculateSubtotal())"></span>
                        </div>
                        <div class="flex justify-between text-[#64748B]">
                            <span>Montant TVA</span>
                            <span class="text-[#0B0F14] font-bold" x-text="formatMoney(calculateTax())"></span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-[#0B0F14] pt-2 border-t border-[#E2E8F0]">
                            <span>Total TTC</span>
                            <span class="text-[#0066FF]" x-text="formatMoney(calculateTotal())"></span>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-[#E2E8F0] mt-6 flex justify-end gap-3">
                    <x-button variant="secondary" href="{{ route('commercial.quotations.index') }}">
                        Annuler
                    </x-button>
                    <x-button variant="primary" type="submit">
                        {{ isset($quotation) ? 'Mettre à jour le devis' : 'Enregistrer le devis' }}
                    </x-button>
                </div>
            </x-card>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('quotationItems', (initialItems) => ({
                items: initialItems.length ? initialItems : [{description: '', quantity: 1, unit_price: 0, tax_rate: 18}],
                addItem() { this.items.push({description: '', quantity: 1, unit_price: 0, tax_rate: 18}); },
                removeItem(index) { if(this.items.length > 1) this.items.splice(index, 1); },
                calculateSubtotal() { return this.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0); },
                calculateTax() { return this.items.reduce((sum, item) => sum + ((item.quantity * item.unit_price) * (item.tax_rate/100)), 0); },
                calculateTotal() { return this.calculateSubtotal() + this.calculateTax(); },
                formatMoney(amount) { return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(amount).replace('XOF', 'FCFA'); }
            }));
        });
    </script>
    @endpush
</x-layouts.app>
