<x-layouts.app :title="isset($appointment) ? 'Modifier Rendez-vous #' . $appointment->id . ' — ASSURANCE' : 'Nouveau Rendez-vous Conseil — ASSURANCE'">
    @php
        $isEdit = isset($appointment);

        $statuses = [
            'planifié'   => 'Planifié',
            'confirmé'   => 'Confirmé par le client',
            'effectué'   => 'Effectué / Entretien réalisé',
            'reporté'    => 'Reporté',
            'annulé'     => 'Annulé',
        ];
    @endphp

    <div class="max-w-3xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <a href="{{ route('assurance.appointments.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux rendez-vous</span>
                </a>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                    {{ $isEdit ? 'Modifier le Rendez-vous #' . $appointment->id : 'Planifier un Rendez-vous Conseil' }}
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Entretien d'audit assurantiel, bilan des risques et propositions de couverture.
                </p>
            </div>
        </div>

        <form 
            action="{{ $isEdit ? route('assurance.appointments.update', $appointment) : route('assurance.appointments.store') }}" 
            method="POST" 
            class="bg-white rounded-2xl p-6 sm:p-8 border border-[#E2E8F0] shadow-xs space-y-6 text-xs"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Section 1 : Client & Conseiller -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">1. Interlocuteurs</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="customer_id" class="block font-semibold text-[#0B0F14] mb-1">Client ou Prospect</label>
                        <select 
                            id="customer_id" 
                            name="customer_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Sélectionner un client</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected(old('customer_id', $appointment->customer_id ?? null) == $c->id)>
                                    {{ $c->name }} ({{ $c->phone ?? $c->email ?? 'Sans contact' }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="advisor_id" class="block font-semibold text-[#0B0F14] mb-1">Conseiller Dédié</label>
                        <select 
                            id="advisor_id" 
                            name="advisor_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Assigner à moi-même</option>
                            @foreach($advisors as $adv)
                                <option value="{{ $adv->id }}" @selected(old('advisor_id', $appointment->advisor_id ?? auth()->id()) == $adv->id)>
                                    {{ $adv->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('advisor_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2 : Date & Heure -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">2. Date, Heure & Statut</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="date" class="block font-semibold text-[#0B0F14] mb-1">Date de l'Entretien *</label>
                        <input 
                            id="date" 
                            type="date" 
                            name="date" 
                            value="{{ old('date', isset($appointment->date) ? \Carbon\Carbon::parse($appointment->date)->format('Y-m-d') : date('Y-m-d')) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="time" class="block font-semibold text-[#0B0F14] mb-1">Heure de RDV</label>
                        <input 
                            id="time" 
                            type="time" 
                            name="time" 
                            value="{{ old('time', isset($appointment->time) ? substr($appointment->time, 0, 5) : '10:00') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('time') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="status" class="block font-semibold text-[#0B0F14] mb-1">Statut *</label>
                        <select 
                            id="status" 
                            name="status" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                            required
                        >
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $appointment->status ?? 'planifié') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3 : Notes -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">3. Ordre du Jour & Préparation</h3>
                <div>
                    <label for="notes" class="block font-semibold text-[#0B0F14] mb-1">Notes / Objet de l'Entretien</label>
                    <textarea 
                        id="notes" 
                        name="notes" 
                        rows="3" 
                        placeholder="Ex: Présentation de la couverture santé entreprise, renégociation de flotte automobile..."
                        class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                    >{{ old('notes', $appointment->notes ?? '') }}</textarea>
                    @error('notes') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between border-t border-[#E2E8F0] pt-5">
                <a href="{{ route('assurance.appointments.index') }}" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] font-medium transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#0066FF] hover:bg-[#0052CC] text-white font-semibold transition-colors shadow-xs flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $isEdit ? 'Mettre à jour le RDV' : 'Enregistrer le RDV' }}</span>
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
