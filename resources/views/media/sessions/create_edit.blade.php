<x-layouts.app :title="isset($session) ? 'Modifier Séance ' . $session->reference . ' — MEDIA' : 'Nouvelle Séance Photo & Vidéo — MEDIA'">
    @php
        $isEdit = isset($session);
        $defaultRef = 'SES-MED-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        $packages = [
            'Studio Standard'                      => 'Studio Standard (Portraits pro, CV, identité)',
            'Shooting Mode / Lookbook'             => 'Shooting Mode / Lookbook (Éditorial & Marques)',
            'Reportage Corporate & Entreprise'    => 'Reportage Corporate (Séminaires, conférences, locaux)',
            'Couverture Mariage & Événements'      => 'Couverture Mariage & Célébrations privées',
            'Production Vidéo & Interviews'        => 'Production Vidéo (Spots, interviews, reels promo)',
            'Shooting Produit & Packshot'          => 'Shooting Produit & Packshot (E-commerce, culinaire)',
            'Formule Sur-Mesure'                   => 'Formule Sur-Mesure / VIP',
        ];

        $statuses = [
            'planifié'       => 'Planifié',
            'en_cours'       => 'En cours (Prise de vue)',
            'en_traitement'  => 'En post-production (Retouche / Montage)',
            'livré'          => 'Livré au client',
            'annulé'         => 'Annulé',
        ];
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <a href="{{ route('media.sessions.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux séances</span>
                </a>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                    {{ $isEdit ? 'Modifier la Séance ' . $session->reference : 'Nouvelle Séance Photo & Vidéo' }}
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Planification de shooting, gestion du photographe, formule tarifaire et livraison.
                </p>
            </div>
        </div>

        <form 
            action="{{ $isEdit ? route('media.sessions.update', $session) : route('media.sessions.store') }}" 
            method="POST" 
            x-data="{
                price: {{ old('price', $session->price ?? 0) }}
            }"
            class="bg-white rounded-2xl p-6 sm:p-8 border border-[#E2E8F0] shadow-xs space-y-6 text-xs"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Section 1 : Informations Principales -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">1. Informations Générales</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="reference" class="block font-semibold text-[#0B0F14] mb-1">Référence de Séance *</label>
                        <input 
                            id="reference" 
                            type="text" 
                            name="reference" 
                            value="{{ old('reference', $session->reference ?? $defaultRef) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-mono font-semibold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('reference') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="customer_id" class="block font-semibold text-[#0B0F14] mb-1">Client Associé</label>
                        <select 
                            id="customer_id" 
                            name="customer_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Sélectionner un client (ou Particulier)</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected(old('customer_id', $session->customer_id ?? null) == $c->id)>
                                    {{ $c->name }} ({{ $c->email ?? $c->phone ?? 'Sans contact' }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="package" class="block font-semibold text-[#0B0F14] mb-1">Formule / Prestation</label>
                        <select 
                            id="package" 
                            name="package" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Sélectionner une formule</option>
                            @foreach($packages as $key => $label)
                                <option value="{{ $key }}" @selected(old('package', $session->package ?? '') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('package') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="photographer" class="block font-semibold text-[#0B0F14] mb-1">Photographe / Réalisateur</label>
                        <input 
                            id="photographer" 
                            type="text" 
                            name="photographer" 
                            placeholder="Ex: Marc K., Studio Team..."
                            value="{{ old('photographer', $session->photographer ?? '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('photographer') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2 : Date, Lieu & Finances -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">2. Planning & Logistique</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="date" class="block font-semibold text-[#0B0F14] mb-1">Date de la Séance</label>
                        <input 
                            id="date" 
                            type="date" 
                            name="date" 
                            value="{{ old('date', isset($session->date) ? \Carbon\Carbon::parse($session->date)->format('Y-m-d') : date('Y-m-d')) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="location" class="block font-semibold text-[#0B0F14] mb-1">Lieu du Shooting</label>
                        <input 
                            id="location" 
                            type="text" 
                            name="location" 
                            placeholder="Ex: Studio Principal, Extérieur Plateau..."
                            value="{{ old('location', $session->location ?? 'Studio') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('location') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="price" class="block font-semibold text-[#0B0F14] mb-1">Tarif Forfait (FCFA)</label>
                        <input 
                            id="price" 
                            type="number" 
                            step="500" 
                            min="0"
                            name="price" 
                            x-model="price"
                            value="{{ old('price', $session->price ?? 0) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-semibold focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('price') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3 : Statut & Remarques -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">3. Statut & Consignes Spécifiques</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block font-semibold text-[#0B0F14] mb-1">Statut d'Avancement *</label>
                        <select 
                            id="status" 
                            name="status" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-medium focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                            required
                        >
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $session->status ?? 'planifié') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="block font-semibold text-[#0B0F14] mb-1">Notes, Moodboard & Consignes Client</label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            rows="4" 
                            placeholder="Précisions sur les tenues, le style d'éclairage, les livrables attendus (formats HD, retouches avancées)..."
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >{{ old('notes', $session->notes ?? '') }}</textarea>
                        @error('notes') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Actions de soumission -->
            <div class="flex items-center justify-between border-t border-[#E2E8F0] pt-5">
                <a href="{{ route('media.sessions.index') }}" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] font-medium transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#0066FF] hover:bg-[#0052CC] text-white font-semibold transition-colors shadow-xs flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $isEdit ? 'Mettre à jour la séance' : 'Enregistrer la séance' }}</span>
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
