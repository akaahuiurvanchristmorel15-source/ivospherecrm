<x-layouts.app :title="isset($event) ? 'Modifier Événement ' . $event->reference . ' — MEDIA' : 'Nouvel Événement — MEDIA'">
    @php
        $isEdit = isset($event);
        $defaultRef = 'EVT-MED-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        $types = [
            'Mariage & Célébration'             => 'Mariage & Célébration privée',
            'Conférence & Séminaire'            => 'Conférence & Séminaire Corporate',
            'Concert & Festival'                => 'Concert, Spectacle & Festival',
            'Inauguration & Lancement'          => 'Inauguration & Lancement de Produit',
            'Gala & Dîner de Prestige'          => 'Gala & Dîner de Prestige',
            'Team Building & Soirée Entreprise' => 'Team Building & Événement Interne',
            'Autre Événement'                   => 'Autre Événement',
        ];

        $statuses = [
            'planifié'        => 'Planifié',
            'en_preparation' => 'En préparation logistique',
            'en_cours'        => 'En cours de déroulement',
            'clôturé'         => 'Clôturé / Facturé',
            'annulé'          => 'Annulé',
        ];
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <a href="{{ route('media.events.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux événements</span>
                </a>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                    {{ $isEdit ? 'Modifier l\'Événement ' . $event->reference : 'Organiser un Nouvel Événement' }}
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Planification, régie technique, sonorisation, éclairage et couverture audiovisuelle complète.
                </p>
            </div>
        </div>

        <form 
            action="{{ $isEdit ? route('media.events.update', $event) : route('media.events.store') }}" 
            method="POST" 
            class="bg-white rounded-2xl p-6 sm:p-8 border border-[#E2E8F0] shadow-xs space-y-6 text-xs"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Section 1 : Identification & Client -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">1. Identification & Organisateur</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="reference" class="block font-semibold text-[#0B0F14] mb-1">Référence Unique *</label>
                        <input 
                            id="reference" 
                            type="text" 
                            name="reference" 
                            value="{{ old('reference', $event->reference ?? $defaultRef) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-mono font-semibold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('reference') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="name" class="block font-semibold text-[#0B0F14] mb-1">Nom / Intitulé de l'Événement *</label>
                        <input 
                            id="name" 
                            type="text" 
                            name="name" 
                            placeholder="Ex: Gala Annuel BNI, Mariage Koffi & Awa, Concert Live..."
                            value="{{ old('name', $event->name ?? '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-semibold focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('name') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="customer_id" class="block font-semibold text-[#0B0F14] mb-1">Client / Organisateur</label>
                        <select 
                            id="customer_id" 
                            name="customer_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Sélectionner un client</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected(old('customer_id', $event->customer_id ?? null) == $c->id)>
                                    {{ $c->name }} ({{ $c->phone ?? $c->email ?? 'Sans contact' }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="type" class="block font-semibold text-[#0B0F14] mb-1">Type d'Événement</label>
                        <select 
                            id="type" 
                            name="type" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Sélectionner un type</option>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" @selected(old('type', $event->type ?? '') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('type') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2 : Lieu, Dates & Invités -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">2. Lieu, Dates & Capacité</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-2">
                        <label for="location" class="block font-semibold text-[#0B0F14] mb-1">Lieu / Salle</label>
                        <input 
                            id="location" 
                            type="text" 
                            name="location" 
                            placeholder="Ex: Palais des Congrès Hôtel Ivoire, Salle des Fêtes..."
                            value="{{ old('location', $event->location ?? '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('location') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="date" class="block font-semibold text-[#0B0F14] mb-1">Date Début</label>
                        <input 
                            id="date" 
                            type="date" 
                            name="date" 
                            value="{{ old('date', isset($event->date) ? \Carbon\Carbon::parse($event->date)->format('Y-m-d') : date('Y-m-d')) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block font-semibold text-[#0B0F14] mb-1">Date Fin</label>
                        <input 
                            id="end_date" 
                            type="date" 
                            name="end_date" 
                            value="{{ old('end_date', isset($event->end_date) ? \Carbon\Carbon::parse($event->end_date)->format('Y-m-d') : date('Y-m-d')) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('end_date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="guests_count" class="block font-semibold text-[#0B0F14] mb-1">Nombre d'Invités prévu</label>
                        <input 
                            id="guests_count" 
                            type="number" 
                            min="0"
                            name="guests_count" 
                            value="{{ old('guests_count', $event->guests_count ?? 100) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('guests_count') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="budget" class="block font-semibold text-[#0B0F14] mb-1">Budget Total Estimé (FCFA)</label>
                        <input 
                            id="budget" 
                            type="number" 
                            step="1000"
                            min="0"
                            name="budget" 
                            value="{{ old('budget', $event->budget ?? 0) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-semibold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('budget') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="status" class="block font-semibold text-[#0B0F14] mb-1">Statut Logistique *</label>
                        <select 
                            id="status" 
                            name="status" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-medium focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                            required
                        >
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $event->status ?? 'planifié') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3 : Consignes & Notes -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">3. Cahier des Charges & Prestations</h3>
                <div>
                    <label for="notes" class="block font-semibold text-[#0B0F14] mb-1">Détails de la Régie & Besoins Audiovisuels</label>
                    <textarea 
                        id="notes" 
                        name="notes" 
                        rows="4" 
                        placeholder="Besoins en sonorisation, captation multi-caméras, diffusion en direct (live streaming), pupitre, éclairage d'ambiance..."
                        class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                    >{{ old('notes', $event->notes ?? '') }}</textarea>
                    @error('notes') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between border-t border-[#E2E8F0] pt-5">
                <a href="{{ route('media.events.index') }}" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] font-medium transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#0066FF] hover:bg-[#0052CC] text-white font-semibold transition-colors shadow-xs flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $isEdit ? 'Mettre à jour l\'événement' : 'Enregistrer l\'événement' }}</span>
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
