<x-layouts.app :title="isset($equipment) ? 'Modifier Matériel ' . $equipment->name . ' — MEDIA' : 'Ajouter un Matériel / Équipement — MEDIA'">
    @php
        $isEdit = isset($equipment);

        $categories = [
            'Boîtiers & Caméras'          => 'Boîtiers & Caméras (Sony, Canon, Blackmagic...)',
            'Objectifs & Optiques'         => 'Objectifs & Optiques (Focales fixes, Zooms)',
            'Éclairage & Lumière'          => 'Éclairage (LED, Softbox, Flashs, Réflecteurs)',
            'Prise de Son & Audio'         => 'Audio & Son (Micros sans fil, Enregistreurs)',
            'Stabilisation & Machinerie'   => 'Stabilisation (Gimbals DJI, Trépieds, Sliders)',
            'Drones & Prise de vue aérienne' => 'Drones & Aérien (DJI Mavic, Avata...)',
            'Accessoires & Énergie'        => 'Accessoires & Énergie (Batteries, Cartes mémoires)',
            'Autre Matériel'               => 'Autre Matériel Événementiel',
        ];

        $conditions = [
            'neuf'            => 'Neuf',
            'excellent_etat'  => 'Excellent état',
            'bon_etat'        => 'Bon état',
            'etat_moyen'      => 'État moyen',
            'a_reviser'       => 'À réviser / Entretien requis',
        ];

        $statuses = [
            'disponible'      => 'Disponible au stock',
            'en_location'     => 'En location / En tournage',
            'maintenance'     => 'En révision / Maintenance',
            'hors_service'    => 'Hors service / Défectueux',
        ];
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <a href="{{ route('media.equipment.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour au parc matériel</span>
                </a>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                    {{ $isEdit ? 'Modifier le Matériel ' . $equipment->name : 'Ajouter un Nouvel Équipement' }}
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Inventaire du parc audiovisuel, tarification journalière de location et suivi technique.
                </p>
            </div>
        </div>

        <form 
            action="{{ $isEdit ? route('media.equipment.update', $equipment) : route('media.equipment.store') }}" 
            method="POST" 
            class="bg-white rounded-2xl p-6 sm:p-8 border border-[#E2E8F0] shadow-xs space-y-6 text-xs"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Section 1 : Identification du matériel -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">1. Identification & Catégorie</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block font-semibold text-[#0B0F14] mb-1">Nom / Modèle de l'Équipement *</label>
                        <input 
                            id="name" 
                            type="text" 
                            name="name" 
                            placeholder="Ex: Sony A7 IV, DJI Ronin RS3, Flash Godox AD600..."
                            value="{{ old('name', $equipment->name ?? '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-semibold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('name') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="category" class="block font-semibold text-[#0B0F14] mb-1">Catégorie *</label>
                        <select 
                            id="category" 
                            name="category" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                            required
                        >
                            <option value="">Sélectionner une catégorie</option>
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}" @selected(old('category', $equipment->category ?? '') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('category') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="serial_number" class="block font-semibold text-[#0B0F14] mb-1">Numéro de Série (S/N)</label>
                        <input 
                            id="serial_number" 
                            type="text" 
                            name="serial_number" 
                            placeholder="Ex: SN-849204859"
                            value="{{ old('serial_number', $equipment->serial_number ?? '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-mono text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('serial_number') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="condition" class="block font-semibold text-[#0B0F14] mb-1">État Physique</label>
                        <select 
                            id="condition" 
                            name="condition" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            @foreach($conditions as $key => $label)
                                <option value="{{ $key }}" @selected(old('condition', $equipment->condition ?? 'bon_etat') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('condition') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2 : Tarifs & Valeur -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">2. Tarification Journalière & Valeur</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="daily_rate" class="block font-semibold text-[#0B0F14] mb-1">Tarif Journalier Location (FCFA) *</label>
                        <input 
                            id="daily_rate" 
                            type="number" 
                            step="500" 
                            min="0"
                            name="daily_rate" 
                            value="{{ old('daily_rate', $equipment->daily_rate ?? 0) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-semibold focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('daily_rate') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="value" class="block font-semibold text-[#0B0F14] mb-1">Valeur Matériel / Caution (FCFA)</label>
                        <input 
                            id="value" 
                            type="number" 
                            step="1000" 
                            min="0"
                            name="value" 
                            value="{{ old('value', $equipment->value ?? 0) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('value') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="status" class="block font-semibold text-[#0B0F14] mb-1">Disponibilité *</label>
                        <select 
                            id="status" 
                            name="status" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-medium focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                            required
                        >
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $equipment->status ?? 'disponible') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3 : Description & Remarques -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">3. Spécifications & Accessoires Inclus</h3>
                <div>
                    <label for="description" class="block font-semibold text-[#0B0F14] mb-1">Description Technique & Kit Fourni</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="3" 
                        placeholder="Ex: Fourni avec 2 batteries, chargeur double, étui de transport rigide et pare-soleil..."
                        class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                    >{{ old('description', $equipment->description ?? '') }}</textarea>
                    @error('description') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Actions de soumission -->
            <div class="flex items-center justify-between border-t border-[#E2E8F0] pt-5">
                <a href="{{ route('media.equipment.index') }}" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] font-medium transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#0066FF] hover:bg-[#0052CC] text-white font-semibold transition-colors shadow-xs flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $isEdit ? 'Mettre à jour le matériel' : 'Ajouter au parc matériel' }}</span>
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
