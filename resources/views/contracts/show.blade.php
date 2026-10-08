<x-layouts.app :title="'Contrat ' . $contract->reference">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Top breadcrumbs & actions -->
        <div class="pb-4 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('contracts.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour au registre des contrats</span>
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">{{ $contract->reference }}</h1>
                    <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase {{ $contract->status === 'actif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $contract->status }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">{{ $contract->name }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('contracts.edit', $contract) }}" class="px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] hover:bg-slate-50 text-xs font-semibold text-[#0B0F14] transition-colors">
                    Modifier
                </a>
                <form action="{{ route('contracts.destroy', $contract) }}" method="POST" onsubmit="return confirm('Confirmer la suppression de ce contrat ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3.5 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition-colors">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        <!-- Expiration Alert Callout (if soon) -->
        @if($contract->days_remaining !== null && $contract->days_remaining <= 30 && $contract->days_remaining >= 0)
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-rose-200 flex items-center justify-center shrink-0 text-rose-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                    <div>
                        <p class="font-bold text-sm">Échéance contractuelle imminente : J-{{ $contract->days_remaining }}</p>
                        <p class="text-[11px] text-rose-700">Ce contrat expire le {{ $contract->end_date->format('d/m/Y') }}. Action requise pour reconduction ou clôture.</p>
                    </div>
                </div>
                <span class="px-3 py-1.5 rounded-lg bg-rose-600 text-white font-bold text-[11px] shrink-0 text-center">Alerte Active</span>
            </div>
        @endif

        <!-- Contract Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="md:col-span-2 bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4 text-xs">
                <h2 class="text-sm font-bold text-[#0B0F14] uppercase tracking-wider pb-2 border-b border-[#E2E8F0]">Informations Contractuelles</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Partie Contractante (Tiers)</span>
                        <span class="font-bold text-[#0B0F14] text-sm">{{ $contract->party_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Type d'Engagement</span>
                        <span class="font-semibold text-[#0B0F14] capitalize">{{ $contract->type }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Date d'Effet (Début)</span>
                        <span class="font-semibold text-[#0B0F14]">{{ $contract->start_date->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Date de Fin (Échéance)</span>
                        <span class="font-semibold text-[#0B0F14]">
                            {{ $contract->end_date ? $contract->end_date->format('d/m/Y') : 'Durée indéterminée (CDI)' }}
                        </span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <span class="text-slate-400 block text-[11px]">Montant de l'Engagement</span>
                    <span class="text-xl font-extrabold text-[#0066FF] mt-0.5 block">
                        {{ number_format($contract->amount, 0, ',', ' ') }} {{ $contract->currency }}
                    </span>
                </div>

                @if($contract->notes)
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-400 block text-[11px] mb-1">Notes & Conditions Particulières</span>
                        <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-slate-700 whitespace-pre-line leading-relaxed">
                            {{ $contract->notes }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Side Card: Vigilance & Jours d'Alerte -->
            <div class="space-y-4">
                <div class="bg-white rounded-2xl p-5 border border-[#E2E8F0] shadow-xs text-xs">
                    <h3 class="font-bold text-[#0B0F14] uppercase tracking-wider text-[11px] mb-3">Paliers d'Alerte Programmés</h3>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2 rounded-lg bg-[#F5F7FA]">
                            <span>Alerte J-90</span>
                            <span class="font-bold text-slate-700">Activée</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-[#F5F7FA]">
                            <span>Alerte J-60</span>
                            <span class="font-bold text-slate-700">Activée</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-[#F5F7FA]">
                            <span>Alerte J-30</span>
                            <span class="font-bold text-amber-600">Priorité Haute</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-[#F5F7FA]">
                            <span>Alerte J-7</span>
                            <span class="font-bold text-rose-600">Urgence Absolue</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-[#E2E8F0] shadow-xs text-xs">
                    <h3 class="font-bold text-[#0B0F14] uppercase tracking-wider text-[11px] mb-2">Pôle & Responsable</h3>
                    <p class="text-slate-500">Pôle : <strong class="text-[#0B0F14]">{{ $contract->domain ? $contract->domain->name : 'Général' }}</strong></p>
                    <p class="text-slate-500 mt-1">Créé par : <strong class="text-[#0B0F14]">{{ $contract->user ? $contract->user->name : 'Admin' }}</strong></p>
                    <p class="text-slate-400 text-[10px] mt-2">Dernière mise à jour : {{ $contract->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

        </div>

    </div>
</x-layouts.app>
