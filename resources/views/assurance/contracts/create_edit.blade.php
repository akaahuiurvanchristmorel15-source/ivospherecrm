<x-layouts.app :title="isset($contract) ? 'Modifier Contrat ' . $contract->reference . ' — ASSURANCE' : 'Nouveau Contrat d\'Assurance — ASSURANCE'">
    @php
        $isEdit = isset($contract);
        $defaultRef = 'POL-ASSUR-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        $frequencies = [
            'mensuel'     => 'Mensuel (12 prélèvements / an)',
            'trimestriel' => 'Trimestriel (4 prélèvements / an)',
            'semestriel'  => 'Semestriel (2 prélèvements / an)',
            'annuel'      => 'Annuel (1 paiement unique / an)',
            'unique'      => 'Prime Unique (Comptant à la souscription)',
        ];

        $statuses = [
            'actif'       => 'Actif (En cours de couverture)',
            'en_attente'  => 'En attente de signature / pièce',
            'resilie'     => 'Résilié',
            'expire'      => 'Expiré / Échu',
        ];

        $partners = ['NSIA Assurances', 'SUNU Assurances', 'SANLAM', 'AXA Côte d\'Ivoire', 'ALLIANZ', 'LEADWAY Assurance', 'GNA Assurances', 'Autre Compagnie'];
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <a href="{{ route('assurance.contracts.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux contrats</span>
                </a>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                    {{ $isEdit ? 'Modifier la Police ' . $contract->reference : 'Nouveau Contrat d\'Assurance' }}
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Enregistrement de police d'assurance, courtage partenaire, primes et périodicité.
                </p>
            </div>
        </div>

        <form 
            action="{{ $isEdit ? route('assurance.contracts.update', $contract) : route('assurance.contracts.store') }}" 
            method="POST" 
            x-data="{
                premium: {{ old('premium', $contract->premium ?? 0) }}
            }"
            class="bg-white rounded-2xl p-6 sm:p-8 border border-[#E2E8F0] shadow-xs space-y-6 text-xs"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Section 1 : Identification & Assuré -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">1. Police & Souscripteur</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="reference" class="block font-semibold text-[#0B0F14] mb-1">Numéro de Police / Référence *</label>
                        <input 
                            id="reference" 
                            type="text" 
                            name="reference" 
                            value="{{ old('reference', $contract->reference ?? $defaultRef) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-mono font-semibold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('reference') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="customer_id" class="block font-semibold text-[#0B0F14] mb-1">Souscripteur / Assuré</label>
                        <select 
                            id="customer_id" 
                            name="customer_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Sélectionner l'assuré</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected(old('customer_id', $contract->customer_id ?? null) == $c->id)>
                                    {{ $c->name }} ({{ $c->phone ?? $c->email ?? 'Sans contact' }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="product_id" class="block font-semibold text-[#0B0F14] mb-1">Produit d'Assurance *</label>
                        <select 
                            id="product_id" 
                            name="product_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                            required
                        >
                            <option value="">Sélectionner une garantie / produit</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" @selected(old('product_id', $contract->product_id ?? null) == $p->id)>
                                    {{ $p->name }} — {{ $p->partner }} ({{ $p->type }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="partner" class="block font-semibold text-[#0B0F14] mb-1">Compagnie Partenaire / Assureur</label>
                        <input 
                            id="partner" 
                            type="text" 
                            name="partner" 
                            placeholder="Ex: NSIA, SUNU, SANLAM..."
                            value="{{ old('partner', $contract->partner ?? '') }}" 
                            list="partners-list"
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        <datalist id="partners-list">
                            @foreach($partners as $part)
                                <option value="{{ $part }}">
                            @endforeach
                        </datalist>
                        @error('partner') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2 : Durée & Prime -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">2. Durée de Couverture & Prime</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label for="start_date" class="block font-semibold text-[#0B0F14] mb-1">Date d'Effet *</label>
                        <input 
                            id="start_date" 
                            type="date" 
                            name="start_date" 
                            value="{{ old('start_date', isset($contract->start_date) ? \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') : date('Y-m-d')) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('start_date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block font-semibold text-[#0B0F14] mb-1">Date d'Échéance</label>
                        <input 
                            id="end_date" 
                            type="date" 
                            name="end_date" 
                            value="{{ old('end_date', isset($contract->end_date) ? \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d') : date('Y-m-d', strtotime('+1 year'))) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('end_date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="premium" class="block font-semibold text-[#0B0F14] mb-1">Montant Prime (FCFA) *</label>
                        <input 
                            id="premium" 
                            type="number" 
                            step="500" 
                            min="0"
                            name="premium" 
                            x-model="premium"
                            value="{{ old('premium', $contract->premium ?? 0) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-semibold focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('premium') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="frequency" class="block font-semibold text-[#0B0F14] mb-1">Périodicité *</label>
                        <select 
                            id="frequency" 
                            name="frequency" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                            required
                        >
                            @foreach($frequencies as $key => $label)
                                <option value="{{ $key }}" @selected(old('frequency', $contract->frequency ?? 'annuel') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('frequency') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3 : Statut & Remarques -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">3. Statut & Conditions Particulières</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block font-semibold text-[#0B0F14] mb-1">Statut du Contrat *</label>
                        <select 
                            id="status" 
                            name="status" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-medium focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                            required
                        >
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $contract->status ?? 'actif') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="block font-semibold text-[#0B0F14] mb-1">Clauses Particulières & Bénéficiaires</label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            rows="4" 
                            placeholder="Mentions particulières, clauses suspensives, ayants-droit désignés, avenants éventuels..."
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >{{ old('notes', $contract->notes ?? '') }}</textarea>
                        @error('notes') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Actions de soumission -->
            <div class="flex items-center justify-between border-t border-[#E2E8F0] pt-5">
                <a href="{{ route('assurance.contracts.index') }}" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] font-medium transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#0066FF] hover:bg-[#0052CC] text-white font-semibold transition-colors shadow-xs flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $isEdit ? 'Mettre à jour le contrat' : 'Souscrire le contrat' }}</span>
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
