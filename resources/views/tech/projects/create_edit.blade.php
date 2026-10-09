<x-layouts.app :title="isset($project) ? 'Modifier Projet ' . $project->reference . ' — TECH' : 'Nouveau Projet Digital — TECH'">
    @php
        $isEdit = isset($project);
        $defaultRef = 'PRJ-TECH-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        $types = [
            'dev_web_mobile'        => 'Développement Web & Mobile',
            'logiciel_sur_mesure'   => 'Logiciel SaaS, ERP & Solutions sur mesure',
            'integration_api'       => 'Intégration Systèmes, API & Passerelles',
            'maintenance_it'        => 'Maintenance, Support & Infogérance IT',
            'infrastructure_reseau' => 'Réseaux, Serveurs & Cloud',
            'consulting_audit'      => 'Audit, Conseil IT & Cybersécurité',
        ];

        $statuses = [
            'en_cours'   => 'En cours',
            'planifie'   => 'Planifié',
            'en_attente' => 'En attente / Revue client',
            'termine'    => 'Terminé',
            'suspendu'   => 'Suspendu',
            'annule'     => 'Annulé',
        ];
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <a href="{{ route('tech.projects.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux projets</span>
                </a>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                    {{ $isEdit ? 'Modifier le Projet ' . $project->reference : 'Nouveau Projet Digital (TECH)' }}
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Développement web/mobile, intégration d'API, maintenance et solutions IT d'entreprise.
                </p>
            </div>
        </div>

        <form 
            action="{{ $isEdit ? route('tech.projects.update', $project) : route('tech.projects.store') }}" 
            method="POST" 
            x-data="{
                progress: {{ old('progress', $project->progress ?? 0) }},
                budget: {{ old('budget', $project->budget ?? 0) }},
                spent: {{ old('spent', $project->spent ?? 0) }},
                get balance() {
                    return Math.max(0, (parseFloat(this.budget) || 0) - (parseFloat(this.spent) || 0));
                }
            }"
            class="bg-white rounded-2xl p-6 sm:p-8 border border-[#E2E8F0] shadow-xs space-y-6 text-xs"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Section 1 : Identification & Périmètre -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">1. Identification & Client</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="reference" class="block font-semibold text-[#0B0F14] mb-1">Référence Unique *</label>
                        <input 
                            id="reference" 
                            type="text" 
                            name="reference" 
                            value="{{ old('reference', $project->reference ?? $defaultRef) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-mono font-semibold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('reference') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="name" class="block font-semibold text-[#0B0F14] mb-1">Nom du Projet *</label>
                        <input 
                            id="name" 
                            type="text" 
                            name="name" 
                            placeholder="Ex: Application Mobile E-Commerce, ERP Interne..."
                            value="{{ old('name', $project->name ?? '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('name') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="customer_id" class="block font-semibold text-[#0B0F14] mb-1">Client Associé</label>
                        <select 
                            id="customer_id" 
                            name="customer_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Client interne / Projet interne</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $project->customer_id ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} {{ $c->company ? '— ' . $c->company : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="type" class="block font-semibold text-[#0B0F14] mb-1">Type de Prestation IT *</label>
                        <select 
                            id="type" 
                            name="type" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required
                        >
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ old('type', $project->type ?? 'dev_web_mobile') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('type') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2 : Statut, Planning & Progression -->
            <div class="border-t border-[#E2E8F0] pt-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">2. Statut, Planning & Avancement</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="status" class="block font-semibold text-[#0B0F14] mb-1">Statut Opérationnel *</label>
                        <select 
                            id="status" 
                            name="status" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required
                        >
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $project->status ?? 'en_cours') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="start_date" class="block font-semibold text-[#0B0F14] mb-1">Date de Démarrage</label>
                        <input 
                            id="start_date" 
                            type="date" 
                            name="start_date" 
                            value="{{ old('start_date', isset($project->start_date) ? $project->start_date->format('Y-m-d') : date('Y-m-d')) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('start_date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block font-semibold text-[#0B0F14] mb-1">Date de Livraison Prévue</label>
                        <input 
                            id="end_date" 
                            type="date" 
                            name="end_date" 
                            value="{{ old('end_date', isset($project->end_date) ? $project->end_date->format('Y-m-d') : '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('end_date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Curseur d'avancement -->
                <div class="mt-4 p-4 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] space-y-2">
                    <div class="flex justify-between items-center">
                        <label for="progress" class="font-semibold text-[#0B0F14]">Avancement du Projet</label>
                        <span class="font-bold text-sm text-[#0066FF]"><span x-text="progress"></span>%</span>
                    </div>
                    <input 
                        id="progress" 
                        type="range" 
                        name="progress" 
                        min="0" 
                        max="100" 
                        step="5" 
                        x-model="progress" 
                        class="w-full h-2 bg-[#E2E8F0] rounded-lg appearance-none cursor-pointer accent-[#0066FF]"
                    />
                    <div class="w-full h-1.5 rounded-full bg-[#E2E8F0] overflow-hidden">
                        <div class="h-full bg-[#0066FF] rounded-full transition-all duration-200" :style="'width: ' + progress + '%'"></div>
                    </div>
                </div>
            </div>

            <!-- Section 3 : Budget & Suivi Financier -->
            <div class="border-t border-[#E2E8F0] pt-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">3. Budget & Suivi Financier</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="budget" class="block font-semibold text-[#0B0F14] mb-1">Budget Alloué (FCFA)</label>
                        <input 
                            id="budget" 
                            type="number" 
                            step="100" 
                            min="0" 
                            name="budget" 
                            x-model="budget"
                            value="{{ old('budget', $project->budget ?? 0) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-mono text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('budget') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="spent" class="block font-semibold text-[#0B0F14] mb-1">Coûts Engagés / Dépensés (FCFA)</label>
                        <input 
                            id="spent" 
                            type="number" 
                            step="100" 
                            min="0" 
                            name="spent" 
                            x-model="spent"
                            value="{{ old('spent', $project->spent ?? 0) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-mono text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('spent') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="p-3 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] flex flex-col justify-center">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Marge / Solde Restant</span>
                        <span class="text-base font-bold text-emerald-600 font-mono mt-0.5" x-text="new Intl.NumberFormat('fr-FR').format(balance) + ' FCFA'"></span>
                    </div>
                </div>
            </div>

            <!-- Section 4 : Périmètre & Notes Techniques -->
            <div class="border-t border-[#E2E8F0] pt-6 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B]">4. Spécifications & Notes</h3>
                <div>
                    <label for="description" class="block font-semibold text-[#0B0F14] mb-1">Description / Cahier des Charges Sommaire</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="3" 
                        placeholder="Objectifs, fonctionnalités principales, stack technique (Laravel, Vue, Flutter...)" 
                        class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                    >{{ old('description', $project->description ?? '') }}</textarea>
                    @error('description') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="notes" class="block font-semibold text-[#0B0F14] mb-1">Notes Internes & Instructions Techniques</label>
                    <textarea 
                        id="notes" 
                        name="notes" 
                        rows="2" 
                        placeholder="Environnement d'hébergement, clés API, identifiants de test, remarques de livraison..." 
                        class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                    >{{ old('notes', $project->notes ?? '') }}</textarea>
                    @error('notes') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Boutons d'Action -->
            <div class="border-t border-[#E2E8F0] pt-6 flex items-center justify-between">
                <a 
                    href="{{ route('tech.projects.index') }}" 
                    class="px-4 py-2.5 rounded-xl border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-slate-50 font-medium transition-colors"
                >
                    Annuler
                </a>

                <button 
                    type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white font-semibold shadow-xs hover:shadow-sm active:scale-98 transition-all flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $isEdit ? 'Mettre à jour le projet' : 'Créer le projet tech' }}</span>
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
