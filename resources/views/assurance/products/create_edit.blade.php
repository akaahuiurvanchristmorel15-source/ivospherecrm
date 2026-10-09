<x-layouts.app :title="isset($product) ? 'Modifier Produit ' . $product->name . ' — ASSURANCE' : 'Nouveau Produit d\'Assurance — ASSURANCE'">
    @php
        $isEdit = isset($product);

        $types = [
            'Santé & Prévoyance'           => 'Santé & Prévoyance (Maladie, Hospitalisation)',
            'Automobile & Flotte'          => 'Automobile & Flotte de véhicules',
            'Multirisque Professionnelle'  => 'Multirisque Professionnelle & Locaux',
            'Responsabilité Civile Pro'    => 'Responsabilité Civile (RC Exploitation & Pro)',
            'Habitation & Biens'           => 'Multirisque Habitation & Biens',
            'Transport & Marchandises'     => 'Transport & Fret (Maritime, Terrestre, Aérien)',
            'Épargne & Retraite'           => 'Épargne, Retraite & Assurance-Vie',
            'Voyage & Assistance'          => 'Assistance Voyage & Rapatriement',
        ];

        $partners = ['NSIA Assurances', 'SUNU Assurances', 'SANLAM', 'AXA Côte d\'Ivoire', 'ALLIANZ', 'LEADWAY Assurance', 'GNA Assurances', 'Autre Partenaire'];
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <a href="{{ route('assurance.products.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour au catalogue produits</span>
                </a>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                    {{ $isEdit ? 'Modifier le Produit ' . $product->name : 'Nouveau Produit d\'Assurance' }}
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Garantie d'assurance distribuée en courtage, compagnie partenaire et commissionnement.
                </p>
            </div>
        </div>

        <form 
            action="{{ $isEdit ? route('assurance.products.update', $product) : route('assurance.products.store') }}" 
            method="POST" 
            class="bg-white rounded-2xl p-6 sm:p-8 border border-[#E2E8F0] shadow-xs space-y-6 text-xs"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Section 1 : Identification du Produit -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">1. Identification & Partenaire</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block font-semibold text-[#0B0F14] mb-1">Nom du Produit / Garantie *</label>
                        <input 
                            id="name" 
                            type="text" 
                            name="name" 
                            placeholder="Ex: Assurance Santé Famille Plus, Flotte Auto Entreprise..."
                            value="{{ old('name', $product->name ?? '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-semibold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('name') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="partner" class="block font-semibold text-[#0B0F14] mb-1">Compagnie Partenaire / Assureur *</label>
                        <input 
                            id="partner" 
                            type="text" 
                            name="partner" 
                            placeholder="Ex: NSIA, SUNU, SANLAM..."
                            value="{{ old('partner', $product->partner ?? '') }}" 
                            list="partners-list"
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        <datalist id="partners-list">
                            @foreach($partners as $part)
                                <option value="{{ $part }}">
                            @endforeach
                        </datalist>
                        @error('partner') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="type" class="block font-semibold text-[#0B0F14] mb-1">Branche / Type de Couverture *</label>
                        <select 
                            id="type" 
                            name="type" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                            required
                        >
                            <option value="">Sélectionner une branche</option>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" @selected(old('type', $product->type ?? '') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('type') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="status" class="block font-semibold text-[#0B0F14] mb-1">Statut de Commercialisation *</label>
                        <select 
                            id="status" 
                            name="status" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-medium focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                            required
                        >
                            <option value="actif" @selected(old('status', $product->status ?? 'actif') == 'actif')>Actif (Disponible à la souscription)</option>
                            <option value="inactif" @selected(old('status', $product->status ?? '') == 'inactif')>Inactif (Offre suspendue)</option>
                        </select>
                        @error('status') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2 : Primes & Commissions -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">2. Tarification Indicative & Rémunération Courtier</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="premium_range" class="block font-semibold text-[#0B0F14] mb-1">Fourchette de Prime Indicative</label>
                        <input 
                            id="premium_range" 
                            type="text" 
                            name="premium_range" 
                            placeholder="Ex: À partir de 25 000 FCFA / an, Sur devis..."
                            value="{{ old('premium_range', $product->premium_range ?? '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('premium_range') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="commission_rate" class="block font-semibold text-[#0B0F14] mb-1">Taux de Commission Courtier (%) *</label>
                        <input 
                            id="commission_rate" 
                            type="number" 
                            step="0.1" 
                            min="0" 
                            max="100"
                            name="commission_rate" 
                            placeholder="Ex: 10, 15..."
                            value="{{ old('commission_rate', $product->commission_rate ?? 10) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-semibold focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('commission_rate') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3 : Description & Conditions -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">3. Description Commerciale & Conditions d'Éligibilité</h3>
                <div class="space-y-4">
                    <div>
                        <label for="description" class="block font-semibold text-[#0B0F14] mb-1">Description Synthétique de l'Offre</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="3" 
                            placeholder="Plafonds de garantie, personnes couvertes, avantages clés..."
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >{{ old('description', $product->description ?? '') }}</textarea>
                        @error('description') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="conditions" class="block font-semibold text-[#0B0F14] mb-1">Conditions de Souscription & Pièces Requises</label>
                        <textarea 
                            id="conditions" 
                            name="conditions" 
                            rows="3" 
                            placeholder="Ex: Âge limite 65 ans, questionnaire médical requis, pièce d'identité valide..."
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >{{ old('conditions', $product->conditions ?? '') }}</textarea>
                        @error('conditions') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Actions de soumission -->
            <div class="flex items-center justify-between border-t border-[#E2E8F0] pt-5">
                <a href="{{ route('assurance.products.index') }}" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] font-medium transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#0066FF] hover:bg-[#0052CC] text-white font-semibold transition-colors shadow-xs flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $isEdit ? 'Mettre à jour le produit' : 'Enregistrer le produit' }}</span>
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
