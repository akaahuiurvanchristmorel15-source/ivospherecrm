<x-layouts.app title="Support & Ticketing Multi-Pôles">
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E2E8F0]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-[#0066FF]/10 text-[#0066FF] font-bold uppercase tracking-wider">Service Client & SLA</span>
                    <span class="text-xs text-slate-500">Tickets Multi-Pôles</span>
                </div>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight mt-1">Support & Ticketing</h1>
                <p class="text-xs text-slate-500">Prise en charge des réclamations, incidents techniques et demandes commerciales avec suivi SLA.</p>
            </div>

            <a href="{{ route('support.create') }}" class="px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-bold shadow-md shadow-[#0066FF]/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nouveau Ticket</span>
            </a>
        </div>

        <!-- 4 Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Tickets</span>
                <p class="text-2xl font-extrabold text-[#0B0F14] mt-2">{{ $counts['total'] }}</p>
                <span class="text-[11px] text-slate-400">Toutes catégories</span>
            </div>

            <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tickets Ouverts</span>
                <p class="text-2xl font-extrabold text-[#0066FF] mt-2">{{ $counts['open'] }}</p>
                <span class="text-[11px] text-slate-400">En cours de traitement</span>
            </div>

            <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Urgences SLA</span>
                <p class="text-2xl font-extrabold text-rose-600 mt-2">{{ $counts['urgent'] }}</p>
                <span class="text-[11px] text-slate-400">Priorité critique</span>
            </div>

            <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tickets Résolus</span>
                <p class="text-2xl font-extrabold text-emerald-600 mt-2">{{ $counts['resolved'] }}</p>
                <span class="text-[11px] text-slate-400">Demandes closes</span>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <a 
                href="{{ route('support.index') }}" 
                class="px-3.5 py-1.5 rounded-full font-medium transition-colors {{ !request('status') && !request('priority') ? 'bg-[#0B0F14] text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
            >
                Tous les tickets
            </a>
            <a 
                href="{{ route('support.index', ['status' => 'nouveau']) }}" 
                class="px-3.5 py-1.5 rounded-full font-medium transition-colors {{ request('status') === 'nouveau' ? 'bg-[#0066FF] text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
            >
                Nouveaux
            </a>
            <a 
                href="{{ route('support.index', ['status' => 'en_cours']) }}" 
                class="px-3.5 py-1.5 rounded-full font-medium transition-colors {{ request('status') === 'en_cours' ? 'bg-[#0066FF] text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
            >
                En cours
            </a>
            <a 
                href="{{ route('support.index', ['priority' => 'urgente']) }}" 
                class="px-3.5 py-1.5 rounded-full font-medium transition-colors {{ request('priority') === 'urgente' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-rose-600 hover:bg-rose-50' }}"
            >
                Urgents
            </a>
            <a 
                href="{{ route('support.index', ['status' => 'resolu']) }}" 
                class="px-3.5 py-1.5 rounded-full font-medium transition-colors {{ request('status') === 'resolu' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
            >
                Résolus
            </a>
        </div>

        <!-- Tickets Table -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs text-[#0B0F14]">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-slate-500 uppercase tracking-wider text-[10px] font-bold">
                        <tr>
                            <th class="py-3.5 px-4">Ticket & Sujet</th>
                            <th class="py-3.5 px-4">Client / Demandeur</th>
                            <th class="py-3.5 px-4">Catégorie & Domaine</th>
                            <th class="py-3.5 px-4">Priorité</th>
                            <th class="py-3.5 px-4">Statut</th>
                            <th class="py-3.5 px-4">Date de Création</th>
                            <th class="py-3.5 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tickets as $tk)
                            <tr class="hover:bg-[#F5F7FA] transition-colors">
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('support.show', $tk) }}" class="font-bold text-[#0066FF] hover:underline block">
                                        {{ $tk->ticket_number }}
                                    </a>
                                    <span class="font-medium text-[#0B0F14]">{{ $tk->subject }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($tk->customer)
                                        <span class="font-semibold block">{{ $tk->customer->company ? $tk->customer->name . ' (' . $tk->customer->company . ')' : $tk->customer->name }}</span>
                                        <span class="text-[11px] text-slate-400">{{ $tk->customer->phone }}</span>
                                    @else
                                        <span class="text-slate-500">Demande interne</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-medium capitalize block">{{ $tk->category }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $tk->domain ? $tk->domain->name : 'Général' }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $tk->priority === 'urgente' ? 'bg-rose-100 text-rose-700' : ($tk->priority === 'haute' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">
                                        {{ $tk->priority }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $tk->status === 'resolu' ? 'bg-emerald-100 text-emerald-700' : ($tk->status === 'nouveau' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ str_replace('_', ' ', $tk->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-[11px] text-slate-400">
                                    {{ $tk->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('support.show', $tk) }}" class="px-3 py-1 rounded bg-[#F5F7FA] hover:bg-[#0066FF] hover:text-white font-semibold transition-colors">
                                        Ouvrir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    Aucun ticket de support enregistré.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Amplified Cards View (< md) -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($tickets as $tk)
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ route('support.show', $tk) }}" class="font-mono font-bold text-sm text-[#0066FF] hover:underline flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#0066FF] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span>{{ $tk->ticket_number }}</span>
                            </a>
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $tk->priority === 'urgente' ? 'bg-rose-100 text-rose-700' : ($tk->priority === 'haute' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $tk->priority }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $tk->status === 'resolu' ? 'bg-emerald-100 text-emerald-700' : ($tk->status === 'nouveau' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ str_replace('_', ' ', $tk->status) }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-bold text-[#0B0F14] leading-snug">{{ $tk->subject }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                @if($tk->customer)
                                    Demandeur : <span class="font-medium text-slate-800">{{ $tk->customer->company ? $tk->customer->name . ' (' . $tk->customer->company . ')' : $tk->customer->name }}</span>
                                @else
                                    <span class="text-slate-500">Demande interne</span>
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center justify-between bg-[#F5F7FA] p-2.5 rounded-lg border border-[#E2E8F0] text-xs">
                            <span class="font-medium capitalize text-slate-700">{{ $tk->category }}</span>
                            <span class="text-slate-500">{{ $tk->domain ? $tk->domain->name : 'Général' }}</span>
                            <span class="text-slate-400 font-mono text-[11px]">{{ $tk->created_at->format('d/m H:i') }}</span>
                        </div>

                        <div class="pt-1 border-t border-slate-100 flex justify-end">
                            <a href="{{ route('support.show', $tk) }}" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-semibold shadow-xs transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Ouvrir le ticket</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Aucun ticket de support enregistré.
                    </div>
                @endforelse
            </div>

            @if($tickets->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
