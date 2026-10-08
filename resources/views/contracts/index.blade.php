<x-layouts.app title="Gestion Centrale des Contrats">
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E2E8F0]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-[#0066FF]/10 text-[#0066FF] font-bold uppercase tracking-wider">Registre Juridique</span>
                    <span class="text-xs text-slate-500">Alertes J-90, J-60, J-30, J-7</span>
                </div>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight mt-1">Gestion Centrale des Contrats</h1>
                <p class="text-xs text-slate-500">Supervision de tous les engagements (Employés, Clients, Fournisseurs, Partenariats, Baux & Assurances).</p>
            </div>

            <a href="{{ route('contracts.create') }}" class="px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-bold shadow-md shadow-[#0066FF]/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nouveau Contrat</span>
            </a>
        </div>

        <!-- Stat Cards (4 Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Contrats</span>
                <p class="text-2xl font-extrabold text-[#0B0F14] mt-2">{{ $stats['total'] }}</p>
                <span class="text-[11px] text-slate-400">Tous types confondus</span>
            </div>

            <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Contrats Actifs</span>
                <p class="text-2xl font-extrabold text-emerald-600 mt-2">{{ $stats['active'] }}</p>
                <span class="text-[11px] text-slate-400">En cours d'exécution</span>
            </div>

            <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Échéances sous 30 Jours</span>
                <p class="text-2xl font-extrabold text-rose-600 mt-2">{{ $stats['expiring'] }}</p>
                <span class="text-[11px] text-slate-400">Nécessite reconduction</span>
            </div>

            <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Valeur Engagée</span>
                <p class="text-2xl font-extrabold text-[#0066FF] mt-2">{{ number_format($stats['total_amount'], 0, ',', ' ') }}</p>
                <span class="text-[11px] text-slate-400">FCFA cumulés actifs</span>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <a 
                href="{{ route('contracts.index') }}" 
                class="px-3.5 py-1.5 rounded-full font-medium transition-colors {{ !request('type') && !request('expiring_soon') ? 'bg-[#0B0F14] text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
            >
                Tous les contrats
            </a>
            <a 
                href="{{ route('contracts.index', ['expiring_soon' => 1]) }}" 
                class="px-3.5 py-1.5 rounded-full font-medium transition-colors {{ request('expiring_soon') ? 'bg-rose-600 text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-rose-600 hover:bg-rose-50' }}"
            >
                ⚠️ Échéance imminente (≤ 30 jours)
            </a>
            @foreach(['client' => 'Clients', 'fournisseur' => 'Fournisseurs', 'employe' => 'Salariés', 'partenaire' => 'Partenaires', 'location' => 'Baux & Locations', 'assurance' => 'Assurances'] as $tp => $lbl)
                <a 
                    href="{{ route('contracts.index', ['type' => $tp]) }}" 
                    class="px-3.5 py-1.5 rounded-full font-medium transition-colors {{ request('type') === $tp ? 'bg-[#0066FF] text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
                >
                    {{ $lbl }}
                </a>
            @endforeach
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs text-[#0B0F14]">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-slate-500 uppercase tracking-wider text-[10px] font-bold">
                        <tr>
                            <th class="py-3.5 px-4">Référence & Intitulé</th>
                            <th class="py-3.5 px-4">Type & Tiers</th>
                            <th class="py-3.5 px-4">Période</th>
                            <th class="py-3.5 px-4">Jours Restants</th>
                            <th class="py-3.5 px-4">Montant</th>
                            <th class="py-3.5 px-4">Statut</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($contracts as $c)
                            <tr class="hover:bg-[#F5F7FA] transition-colors">
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('contracts.show', $c) }}" class="font-bold text-[#0066FF] hover:underline block">
                                        {{ $c->reference }}
                                    </a>
                                    <span class="text-slate-600">{{ $c->name }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold block">{{ $c->party_name }}</span>
                                    <span class="text-[11px] text-slate-400 capitalize">{{ $c->type }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-[11px] text-slate-500">
                                    <span>Du {{ $c->start_date->format('d/m/Y') }}</span>
                                    @if($c->end_date)
                                        <span class="block">Au {{ $c->end_date->format('d/m/Y') }}</span>
                                    @else
                                        <span class="block text-emerald-600 font-medium">Indéterminée (CDI)</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($c->days_remaining !== null)
                                        @if($c->days_remaining < 0)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-slate-200 text-slate-700">Expiré</span>
                                        @elseif($c->days_remaining <= 7)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-600 text-white animate-pulse">J-{{ $c->days_remaining }} (Urgent)</span>
                                        @elseif($c->days_remaining <= 30)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-100 text-rose-700">J-{{ $c->days_remaining }} (30j)</span>
                                        @elseif($c->days_remaining <= 60)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">J-{{ $c->days_remaining }}</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-100 text-emerald-800">{{ $c->days_remaining }} jours</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-bold">
                                    {{ number_format($c->amount, 0, ',', ' ') }} {{ $c->currency }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $c->status === 'actif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $c->status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('contracts.show', $c) }}" class="px-2.5 py-1 rounded bg-[#F5F7FA] hover:bg-slate-200 text-slate-700 font-medium transition-colors">
                                        Consulter
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    Aucun contrat enregistré. Cliquez sur « Nouveau Contrat » pour ajouter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Amplified Cards View (< md) -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($contracts as $c)
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ route('contracts.show', $c) }}" class="font-mono font-bold text-sm text-[#0066FF] hover:underline flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#0066FF] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>{{ $c->reference }}</span>
                            </a>
                            <div class="flex items-center gap-1.5">
                                @if($c->days_remaining !== null)
                                    @if($c->days_remaining < 0)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-slate-200 text-slate-700">Expiré</span>
                                    @elseif($c->days_remaining <= 7)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-600 text-white animate-pulse">J-{{ $c->days_remaining }}</span>
                                    @elseif($c->days_remaining <= 30)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-100 text-rose-700">J-{{ $c->days_remaining }}</span>
                                    @endif
                                @endif
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $c->status === 'actif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $c->status }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-bold text-[#0B0F14] leading-snug">{{ $c->name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">Tiers : <span class="font-semibold text-slate-800">{{ $c->party_name }}</span> • <span class="capitalize">{{ $c->type }}</span></p>
                        </div>

                        <div class="flex items-center justify-between bg-[#F5F7FA] p-3 rounded-lg border border-[#E2E8F0] text-xs">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Montant Engagé</span>
                                <span class="text-sm font-extrabold text-[#0B0F14]">{{ number_format($c->amount, 0, ',', ' ') }} {{ $c->currency }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Période</span>
                                <span class="text-xs text-slate-600">
                                    {{ $c->start_date->format('d/m/y') }} &ndash; {{ $c->end_date ? $c->end_date->format('d/m/y') : 'CDI' }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-1 border-t border-slate-100 flex justify-end">
                            <a href="{{ route('contracts.show', $c) }}" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-semibold shadow-xs transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Consulter le contrat</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Aucun contrat enregistré.
                    </div>
                @endforelse
            </div>

            @if($contracts->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
