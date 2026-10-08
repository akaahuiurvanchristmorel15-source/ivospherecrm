<x-layouts.app>
    <x-slot:title>{{ isset($prospect) ? 'Modifier' : 'Nouveau' }} Prospect — IVOSPHERE ERP</x-slot>

    <div class="max-w-4xl mx-auto space-y-4 sm:space-y-6 pb-20 md:pb-6">
        <!-- En-tête Responsive Épuré -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <a href="{{ route('commercial.prospects.index') }}" class="p-1.5 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-[#0B0F14] transition" title="Retour au pipeline">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <h1 class="text-xl sm:text-2xl font-black text-[#0B0F14] tracking-tight">
                        {{ isset($prospect) ? 'Modifier : ' . $prospect->name : 'Nouveau Prospect' }}
                    </h1>
                </div>
                <p class="text-xs text-slate-500">
                    Qualification du contact et positionnement dans le cycle de vente commercial
                </p>
            </div>

            <!-- Action de conversion rapide (si édition) -->
            @if(isset($prospect) && ($prospect->stage ?? '') !== 'gagne')
                <div class="flex items-center gap-2">
                    <form action="{{ route('commercial.prospects.convert', $prospect) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold transition shadow-2xs touch-target">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Convertir en Client</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <form action="{{ isset($prospect) ? route('commercial.prospects.update', $prospect) : route('commercial.prospects.store') }}" method="POST" class="space-y-4 sm:space-y-6">
            @csrf
            @if(isset($prospect)) @method('PUT') @endif

            <!-- 1. Coordonnées & Identification -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-6 shadow-2xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 text-[#0066FF] flex items-center justify-center font-bold text-xs">1</span>
                    <h2 class="text-xs sm:text-sm font-extrabold text-[#0B0F14] uppercase tracking-wide">
                        Coordonnées & Contact
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nom & Prénom *</label>
                        <input 
                            type="text" 
                            name="name" 
                            value="{{ old('name', $prospect->name ?? '') }}" 
                            required 
                            placeholder="Ex: Armand Kouassi" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAFC] border border-slate-200 text-xs font-medium text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:bg-white focus:border-[#0066FF] transition"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Entreprise / Société</label>
                        <input 
                            type="text" 
                            name="company" 
                            value="{{ old('company', $prospect->company ?? '') }}" 
                            placeholder="Ex: Cabinet Alpha Conseil" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAFC] border border-slate-200 text-xs font-medium text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:bg-white focus:border-[#0066FF] transition"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Téléphone / WhatsApp</label>
                        <input 
                            type="text" 
                            name="phone" 
                            value="{{ old('phone', $prospect->phone ?? '') }}" 
                            placeholder="+225 07 00 00 00 00" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAFC] border border-slate-200 text-xs font-medium text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:bg-white focus:border-[#0066FF] transition"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Adresse Email</label>
                        <input 
                            type="email" 
                            name="email" 
                            value="{{ old('email', $prospect->email ?? '') }}" 
                            placeholder="contact@entreprise.com" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAFC] border border-slate-200 text-xs font-medium text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:bg-white focus:border-[#0066FF] transition"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Canal d'Acquisition (Source)</label>
                        <select name="source" class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAFC] border border-slate-200 text-xs font-medium text-[#0B0F14] focus:outline-none focus:bg-white focus:border-[#0066FF] transition">
                            <option value="">-- Sélectionner une source --</option>
                            @foreach(['Site Web', 'Réseaux Sociaux', 'Appel Entrant', 'Recommandation / Réseau', 'Salon / Événement', 'Démarchage Commercial', 'Autre'] as $src)
                                <option value="{{ $src }}" {{ old('source', $prospect->source ?? '') == $src ? 'selected' : '' }}>{{ $src }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- 2. Qualification Pipeline & Valeur Financière -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-6 shadow-2xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs">2</span>
                    <h2 class="text-xs sm:text-sm font-extrabold text-[#0B0F14] uppercase tracking-wide">
                        Qualification Pipeline & Montants
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Étape du Pipeline *</label>
                        <select name="stage" required class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAFC] border border-slate-200 text-xs font-bold text-[#0066FF] focus:outline-none focus:bg-white focus:border-[#0066FF] transition">
                            @foreach($stages ?? \App\Http\Controllers\Commercial\ProspectController::STAGES as $key => $info)
                                <option value="{{ $key }}" {{ old('stage', $prospect->stage ?? 'nouveau') == $key ? 'selected' : '' }}>
                                    {{ $info['label'] }} ({{ $info['default_prob'] }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Probabilité de Signature (%)</label>
                        <input 
                            type="number" 
                            name="probability" 
                            min="0" 
                            max="100" 
                            value="{{ old('probability', $prospect->probability ?? 10) }}" 
                            placeholder="Ex: 50" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAFC] border border-slate-200 text-xs font-bold text-[#0B0F14] focus:outline-none focus:bg-white focus:border-[#0066FF] transition"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Valeur Estimée (FCFA)</label>
                        <input 
                            type="number" 
                            name="estimated_value" 
                            step="1000" 
                            min="0" 
                            value="{{ old('estimated_value', $prospect->estimated_value ?? 0) }}" 
                            placeholder="Ex: 2 500 000" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAFC] border border-slate-200 text-xs font-black text-[#0B0F14] focus:outline-none focus:bg-white focus:border-[#0066FF] transition"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pôle / Domaine d'activité</label>
                        <select name="domain_id" class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAFC] border border-slate-200 text-xs font-medium text-[#0B0F14] focus:outline-none focus:bg-white focus:border-[#0066FF] transition">
                            <option value="">Tous les domaines (Transversal)</option>
                            @foreach($domains ?? [] as $dom)
                                <option value="{{ $dom->id }}" {{ old('domain_id', $prospect->domain_id ?? '') == $dom->id ? 'selected' : '' }}>
                                    {{ $dom->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Commercial Assigné</label>
                        <select name="commercial_id" class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAFC] border border-slate-200 text-xs font-medium text-[#0B0F14] focus:outline-none focus:bg-white focus:border-[#0066FF] transition">
                            <option value="">-- Non assigné --</option>
                            @foreach($commercials ?? [] as $user)
                                <option value="{{ $user->id }}" {{ old('commercial_id', $prospect->commercial_id ?? $prospect->assigned_to ?? auth()->id()) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Date Prochaine Relance</label>
                        <input 
                            type="date" 
                            name="next_follow_up" 
                            value="{{ old('next_follow_up', isset($prospect) && $prospect->next_follow_up ? $prospect->next_follow_up->format('Y-m-d') : '') }}" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAFC] border border-slate-200 text-xs font-semibold text-[#0B0F14] focus:outline-none focus:bg-white focus:border-[#0066FF] transition"
                        >
                    </div>
                </div>
            </div>

            <!-- 3. Notes & Besoins -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-6 shadow-2xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-xs">3</span>
                    <h2 class="text-xs sm:text-sm font-extrabold text-[#0B0F14] uppercase tracking-wide">
                        Notes de Synthèse & Besoins
                    </h2>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Détails du besoin client & historique</label>
                    <textarea 
                        name="notes" 
                        rows="3" 
                        placeholder="Attentes spécifiques du prospect, budget exprimé, contraintes de délais, devis souhaité..." 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAFC] border border-slate-200 text-xs font-medium text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:bg-white focus:border-[#0066FF] transition"
                    >{{ old('notes', $prospect->notes ?? '') }}</textarea>
                </div>

                <!-- Boutons d'action Desktop -->
                <div class="pt-4 border-t border-slate-100 hidden md:flex items-center justify-end gap-3">
                    <a href="{{ route('commercial.prospects.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Annuler
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-bold shadow-xs shadow-[#0066FF]/25 transition">
                        {{ isset($prospect) ? 'Mettre à jour le prospect' : 'Enregistrer le prospect' }}
                    </button>
                </div>
            </div>

            <!-- 4. Barre d'action fixe sur Mobile (Sticky Footer Bottom Bar) -->
            <div class="md:hidden fixed bottom-0 inset-x-0 p-3 bg-white/95 backdrop-blur-md border-t border-slate-200 z-30 flex items-center gap-2.5 shadow-lg safe-area-pb">
                <a href="{{ route('commercial.prospects.index') }}" class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold text-center touch-target flex items-center justify-center">
                    Annuler
                </a>
                <button type="submit" class="flex-2 py-2.5 rounded-xl bg-[#0066FF] active:bg-[#0052cc] text-white text-xs font-bold shadow-xs shadow-[#0066FF]/25 touch-target flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ isset($prospect) ? 'Mettre à jour' : 'Enregistrer' }}</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
