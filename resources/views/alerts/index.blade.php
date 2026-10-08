<x-layouts.app title="Centre d'Alertes Intelligentes">
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E2E8F0]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-rose-100 text-rose-700 font-bold uppercase tracking-wider">Surveillance Active</span>
                </div>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight mt-1 mb-2">Alertes Intelligentes</h1>
                <p class="text-xs text-slate-500">Identification proactive des factures impayées, ruptures de stocks et échéances de contrats.</p>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-3">
                <a href="{{ route('automations.index') }}" class="px-3 py-2 rounded-xl bg-white border border-[#E2E8F0] text-xs font-semibold text-[#0B0F14] hover:bg-slate-50 shadow-xs transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Règles d'Automatisation</span>
                </a>

                <form method="POST" action="{{ route('alerts.run-checks') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-bold shadow-md shadow-[#0066FF]/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Vérifier maintenant</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Filter Stat Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <a 
                href="{{ route('alerts.index') }}" 
                class="px-3.5 py-2 rounded-xl font-medium transition-colors {{ !request('priority') && !request('status') ? 'bg-[#0B0F14] text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
            >
                Alerts ({{ $counts['active'] }})
            </a>
            <a 
                href="{{ route('alerts.index', ['priority' => 'urgente']) }}" 
                class="px-3.5 py-2 rounded-xl font-medium transition-colors {{ request('priority') === 'urgente' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
            >
                Urgentes ({{ $counts['urgente'] }})
            </a>
            <a 
                href="{{ route('alerts.index', ['priority' => 'haute']) }}" 
                class="px-3.5 py-2 rounded-xl font-medium transition-colors {{ request('priority') === 'haute' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
            >
                Hautes ({{ $counts['haute'] }})
            </a>
            <a 
                href="{{ route('alerts.index', ['status' => 'resolved']) }}" 
                class="px-3.5 py-2 rounded-xl font-medium transition-colors {{ request('status') === 'resolved' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
            >
                Résolues ({{ $counts['resolved'] }})
            </a>
        </div>

        <!-- Alerts List -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
            <div class="divide-y divide-slate-100">
                @forelse($alerts as $alert)
                    <div class="p-5 hover:bg-[#F5F7FA] transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <!-- Priority badge pill -->
                            <div class="mt-1">
                                @if($alert->priority === 'urgente')
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-700 border border-rose-200">
                                        Urgente
                                    </span>
                                @elseif($alert->priority === 'haute')
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                                        Haute
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-blue-100 text-blue-700 border border-blue-200">
                                        Moyenne
                                    </span>
                                @endif
                            </div>

                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm font-bold text-[#0B0F14]">{{ $alert->title }}</h3>
                                    @if($alert->domain)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                            {{ $alert->domain->name }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 mt-1">{{ $alert->description }}</p>
                                <div class="flex items-center gap-4 mt-2 text-[11px] text-slate-400">
                                    <span>Détectée {{ $alert->created_at->diffForHumans() }}</span>
                                    @if($alert->due_date)
                                        <span>Échéance : {{ $alert->due_date->format('d/m/Y') }}</span>
                                    @endif
                                    @if($alert->assignee)
                                        <span>Assigné à : {{ $alert->assignee->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Action controls -->
                        <div class="flex items-center gap-2 shrink-0">
                            @if($alert->action_url)
                                <a 
                                    href="{{ $alert->action_url }}" 
                                    class="px-3 py-1.5 rounded-lg bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5"
                                >
                                    <span>Traiter</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            @endif

                            @if($alert->status === 'active')
                                <form method="POST" action="{{ route('alerts.resolve', $alert) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button 
                                        type="submit" 
                                        class="px-3 py-1.5 rounded-lg border border-[#E2E8F0] hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 text-xs font-semibold text-slate-600 transition-colors"
                                    >
                                        Marquer résolue
                                    </button>
                                </form>
                            @else
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-100 text-emerald-700">Résolue</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-16 text-center text-slate-400">
                        <svg class="w-12 h-12 mx-auto text-emerald-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-xs font-bold text-[#0B0F14]">Aucune alerte dans cette catégorie</p>
                        <p class="text-[11px] text-slate-400 mt-1">Le système effectue des contrôles automatisés réguliers.</p>
                    </div>
                @endforelse
            </div>

            @if($alerts->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $alerts->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
