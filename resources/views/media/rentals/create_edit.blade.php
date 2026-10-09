<x-layouts.app :title="isset($rental) ? 'Modifier Location ' . $rental->reference . ' — MEDIA' : 'Nouvelle Location de Matériel — MEDIA'">
    @php
        $isEdit = isset($rental);
        $defaultRef = 'LOC-MED-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        $conditions = [
            'neuf'            => 'Neuf',
            'excellent_etat'  => 'Excellent état',
            'bon_etat'        => 'Bon état',
            'etat_moyen'      => 'État d\'usage',
            'rayures_mineures'=> 'Légères rayures d\'usage',
        ];

        $statuses = [
            'en_cours'   => 'En cours (Matériel sorti)',
            'retourné'   => 'Retourné & vérifié',
            'en_retard'  => 'En retard de restitution',
            'termine'    => 'Terminé / Caution restituée',
        ];
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <a href="{{ route('media.rentals.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux locations</span>
                </a>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                    {{ $isEdit ? 'Modifier le Contrat ' . $rental->reference : 'Nouveau Contrat de Location de Matériel' }}
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Mise à disposition de matériel audiovisuel, retenue de caution et contrôle de l'état du parc.
                </p>
            </div>
        </div>

        <form 
            action="{{ $isEdit ? route('media.rentals.update', $rental) : route('media.rentals.store') }}" 
            method="POST" 
            x-data="{
                dailyRate: {{ old('daily_rate', $rental->daily_rate ?? 0) }},
                startDate: '{{ old('start_date', isset($rental->start_date) ? \Carbon\Carbon::parse($rental->start_date)->format('Y-m-d') : date('Y-m-d')) }}',
                endDate: '{{ old('end_date', isset($rental->end_date) ? \Carbon\Carbon::parse($rental->end_date)->format('Y-m-d') : date('Y-m-d', strtotime('+1 day'))) }}',
                get days() {
                    if (!this.startDate || !this.endDate) return 1;
                    const d1 = new Date(this.startDate);
                    const d2 = new Date(this.endDate);
                    const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
                    return Math.max(1, isNaN(diff) ? 1 : diff);
                },
                get total() {
                    return this.days * (parseFloat(this.dailyRate) || 0);
                }
            }"
            class="bg-white rounded-2xl p-6 sm:p-8 border border-[#E2E8F0] shadow-xs space-y-6 text-xs"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Section 1 : Identification & Matériel -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">1. Matériel & Locataire</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="reference" class="block font-semibold text-[#0B0F14] mb-1">Numéro de Contrat *</label>
                        <input 
                            id="reference" 
                            type="text" 
                            name="reference" 
                            value="{{ old('reference', $rental->reference ?? $defaultRef) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-mono font-semibold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('reference') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="equipment_id" class="block font-semibold text-[#0B0F14] mb-1">Équipement / Matériel *</label>
                        <select 
                            id="equipment_id" 
                            name="equipment_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                            required
                        >
                            <option value="">Sélectionner un équipement</option>
                            @foreach($equipment as $eq)
                                <option value="{{ $eq->id }}" @selected(old('equipment_id', $rental->equipment_id ?? null) == $eq->id)>
                                    {{ $eq->name }} ({{ number_format($eq->daily_rate, 0, ',', ' ') }} FCFA/j) - {{ $eq->status }}
                                </option>
                            @endforeach
                        </select>
                        @error('equipment_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="customer_id" class="block font-semibold text-[#0B0F14] mb-1">Client Locataire</label>
                        <select 
                            id="customer_id" 
                            name="customer_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Particulier ou sélectionner un client</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected(old('customer_id', $rental->customer_id ?? null) == $c->id)>
                                    {{ $c->name }} ({{ $c->phone ?? $c->email ?? 'Sans contact' }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="condition_before" class="block font-semibold text-[#0B0F14] mb-1">État des Lieux à la Sortie</label>
                        <select 
                            id="condition_before" 
                            name="condition_before" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            @foreach($conditions as $key => $label)
                                <option value="{{ $key }}" @selected(old('condition_before', $rental->condition_before ?? 'bon_etat') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('condition_before') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2 : Dates & Tarifs -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">2. Période & Tarification</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label for="start_date" class="block font-semibold text-[#0B0F14] mb-1">Date de Départ *</label>
                        <input 
                            id="start_date" 
                            type="date" 
                            name="start_date" 
                            x-model="startDate"
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('start_date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block font-semibold text-[#0B0F14] mb-1">Date de Restitution *</label>
                        <input 
                            id="end_date" 
                            type="date" 
                            name="end_date" 
                            x-model="endDate"
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('end_date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="daily_rate" class="block font-semibold text-[#0B0F14] mb-1">Tarif / Jour (FCFA)</label>
                        <input 
                            id="daily_rate" 
                            type="number" 
                            step="500" 
                            min="0"
                            name="daily_rate" 
                            x-model="dailyRate"
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-semibold focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('daily_rate') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="deposit" class="block font-semibold text-[#0B0F14] mb-1">Caution Déposée (FCFA)</label>
                        <input 
                            id="deposit" 
                            type="number" 
                            step="1000" 
                            min="0"
                            name="deposit" 
                            value="{{ old('deposit', $rental->deposit ?? 0) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('deposit') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-4 p-3 bg-[#F5F7FA] rounded-xl border border-[#E2E8F0] flex items-center justify-between">
                    <span class="text-xs text-[#64748B]">Durée estimée : <strong class="text-[#0B0F14]"><span x-text="days"></span> jour(s)</strong></span>
                    <span class="text-xs text-[#64748B]">Total Location estimé : <strong class="text-[#0066FF] text-sm"><span x-text="new Intl.NumberFormat('fr-FR').format(total)"></span> FCFA</strong></span>
                </div>
            </div>

            <!-- Section 3 : Statut & Notes -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">3. Statut & Accessoires Remis</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block font-semibold text-[#0B0F14] mb-1">Statut *</label>
                        <select 
                            id="status" 
                            name="status" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-medium focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                            required
                        >
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $rental->status ?? 'en_cours') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @if($isEdit)
                        <div>
                            <label for="condition_after" class="block font-semibold text-[#0B0F14] mb-1">État des Lieux au Retour</label>
                            <select 
                                id="condition_after" 
                                name="condition_after" 
                                class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                            >
                                <option value="">Non encore retourné</option>
                                @foreach($conditions as $key => $label)
                                    <option value="{{ $key }}" @selected(old('condition_after', $rental->condition_after ?? '') == $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="sm:col-span-2">
                        <label for="notes" class="block font-semibold text-[#0B0F14] mb-1">Accessoires Remis & Observations</label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            rows="3" 
                            placeholder="Cartes mémoires, câbles HDMI, batteries, valise de transport, pièces d'identité déposées..."
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >{{ old('notes', $rental->notes ?? '') }}</textarea>
                        @error('notes') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between border-t border-[#E2E8F0] pt-5">
                <a href="{{ route('media.rentals.index') }}" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] font-medium transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#0066FF] hover:bg-[#0052CC] text-white font-semibold transition-colors shadow-xs flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $isEdit ? 'Mettre à jour la location' : 'Valider la sortie du matériel' }}</span>
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
