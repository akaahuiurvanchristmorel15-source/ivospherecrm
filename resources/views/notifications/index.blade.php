<x-layouts.app title="Centre de Notifications — IVOSPHERE">
    <div class="space-y-6">

        <!-- En-tête du Centre de Notifications -->
        <x-page-header 
            title="Centre de Notifications" 
            description="Surveillance opérationnelle en temps réel : alertes commerciales, stocks, approbations RH et support."
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Tableau de bord', 'url' => route('dashboard')],
                    ['label' => 'Centre de Notifications']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <div class="flex items-center gap-2">
                    @if($counts['active'] > 0)
                        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                            @csrf
                            <button type="submit" class="h-10 px-4 rounded-xl bg-white border border-[#E2E8F0] hover:bg-slate-50 text-xs font-medium text-[#0B0F14] transition flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7"/></svg>
                                <span>Tout marquer comme lu</span>
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('notifications.run-checks') }}">
                        @csrf
                        <button type="submit" class="h-10 px-4 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-medium transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Actualiser les alertes</span>
                        </button>
                    </form>
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- Cartes Statistiques Résumé -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <a href="{{ route('notifications.index', ['status' => 'active']) }}" class="p-4 sm:p-5 rounded-2xl bg-white border {{ request('status', 'active') === 'active' && !request('priority') ? 'border-[#0B0F14]' : 'border-[#E2E8F0]' }} hover:border-slate-400 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Non traitées</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0066FF]"></span>
                </div>
                <div class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight tabular-nums text-[#0B0F14]">{{ $counts['active'] }}</div>
                <span class="text-[11px] text-[#64748B] mt-0.5 block">Alertes en cours</span>
            </a>

            <a href="{{ route('notifications.index', ['status' => 'active', 'priority' => 'urgente']) }}" class="p-4 sm:p-5 rounded-2xl bg-white border {{ request('priority') === 'urgente' ? 'border-rose-500' : 'border-[#E2E8F0]' }} hover:border-rose-400 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-rose-700">Urgentes</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                </div>
                <div class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight tabular-nums text-rose-600">{{ $counts['urgente'] }}</div>
                <span class="text-[11px] text-[#64748B] mt-0.5 block">Action immédiate</span>
            </a>

            <a href="{{ route('notifications.index', ['status' => 'read']) }}" class="p-4 sm:p-5 rounded-2xl bg-white border {{ request('status') === 'read' ? 'border-[#0B0F14]' : 'border-[#E2E8F0]' }} hover:border-slate-400 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Lues</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                </div>
                <div class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight tabular-nums text-slate-700">{{ $counts['read'] }}</div>
                <span class="text-[11px] text-[#64748B] mt-0.5 block">Consultées</span>
            </a>

            <a href="{{ route('notifications.index', ['status' => 'resolved']) }}" class="p-4 sm:p-5 rounded-2xl bg-white border {{ request('status') === 'resolved' ? 'border-emerald-500' : 'border-[#E2E8F0]' }} hover:border-emerald-400 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-emerald-700">Résolues</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                </div>
                <div class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight tabular-nums text-emerald-600">{{ $counts['resolved'] }}</div>
                <span class="text-[11px] text-[#64748B] mt-0.5 block">Traitées avec succès</span>
            </a>
        </div>

        <!-- Filtres & Recherche -->
        <x-card>
            <form method="GET" action="{{ route('notifications.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 text-xs">
                <div class="md:col-span-4">
                    <label class="block text-[11px] font-medium text-[#64748B] uppercase tracking-wider mb-1.5">Rechercher</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Recherche par titre, mot-clé..." 
                        class="w-full h-10 px-3.5 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:border-[#0066FF] text-xs transition"
                    >
                </div>

                <div class="md:col-span-3">
                    <label class="block text-[11px] font-medium text-[#64748B] uppercase tracking-wider mb-1.5">Type de notification</label>
                    <select name="type" class="w-full h-10 px-3.5 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        <option value="">Tous les types</option>
                        <option value="unpaid_invoice" @selected(request('type') === 'unpaid_invoice')>Factures impayées</option>
                        <option value="low_stock" @selected(request('type') === 'low_stock')>Ruptures de stock</option>
                        <option value="leave_request" @selected(request('type') === 'leave_request')>Demandes de congés RH</option>
                        <option value="support_ticket" @selected(request('type') === 'support_ticket')>Tickets Support</option>
                        <option value="contract_expiry" @selected(request('type') === 'contract_expiry')>Échéances Contrats</option>
                        <option value="quote_followup" @selected(request('type') === 'quote_followup')>Devis à relancer</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-[11px] font-medium text-[#64748B] uppercase tracking-wider mb-1.5">Pôle Métier</label>
                    <select name="domain_id" class="w-full h-10 px-3.5 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        <option value="">Tous les pôles</option>
                        @foreach($domains as $domain)
                            <option value="{{ $domain->id }}" @selected(request('domain_id') == $domain->id)>{{ $domain->name }}</option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="status" value="{{ request('status', 'active') }}">
                @if(request('priority'))
                    <input type="hidden" name="priority" value="{{ request('priority') }}">
                @endif

                <div class="md:col-span-2 flex items-end gap-2">
                    <button type="submit" class="flex-1 h-10 px-4 bg-[#0B0F14] hover:bg-slate-800 text-white rounded-xl text-xs font-medium transition">
                        Filtrer
                    </button>
                    @if(request()->hasAny(['search', 'type', 'domain_id', 'priority']))
                        <a href="{{ route('notifications.index', ['status' => request('status', 'active')]) }}" class="h-10 px-3 flex items-center justify-center rounded-xl bg-[#F5F7FA] text-[#64748B] hover:text-[#0B0F14] border border-[#E2E8F0] text-xs transition" title="Réinitialiser">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Liste des Notifications -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] overflow-hidden">
            <div class="divide-y divide-slate-100">
                @forelse($notifications as $notif)
                    @php
                        $isUrgent = $notif->priority === 'urgente';
                        $isHigh = $notif->priority === 'haute';
                        
                        $prioBadge = match($notif->priority) {
                            'urgente' => 'bg-rose-50 text-rose-700 border-rose-200/80',
                            'haute' => 'bg-amber-50 text-amber-800 border-amber-200/80',
                            default => 'bg-blue-50 text-[#0066FF] border-blue-200/80',
                        };

                        $typeIcon = match($notif->type) {
                            'unpaid_invoice' => '<svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                            'low_stock' => '<svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                            'leave_request' => '<svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                            'support_ticket' => '<svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                            'contract_expiry' => '<svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                            default => '<svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>',
                        };
                    @endphp

                    <div class="p-4 sm:p-5 hover:bg-[#F5F7FA]/60 transition flex flex-col md:flex-row md:items-center justify-between gap-4 {{ $notif->status === 'active' ? 'bg-white' : 'bg-slate-50/40' }}">
                        <div class="flex items-start gap-3.5 min-w-0 flex-1">
                            <div class="w-9 h-9 rounded-xl bg-[#F5F7FA] flex items-center justify-center shrink-0 border border-slate-100 mt-0.5">
                                {!! $typeIcon !!}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider border {{ $prioBadge }}">
                                        {{ $notif->priority }}
                                    </span>

                                    @if($notif->domain)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#F5F7FA] text-[#64748B] border border-slate-100">
                                            {{ $notif->domain->name }}
                                        </span>
                                    @endif

                                    @if($notif->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 text-[10px] text-[#0066FF] font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#0066FF]"></span>
                                            <span>Non lue</span>
                                        </span>
                                    @elseif($notif->status === 'resolved')
                                        <span class="inline-flex items-center gap-1.5 text-[10px] text-emerald-600 font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Résolue</span>
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-sm font-semibold text-[#0B0F14] leading-snug">
                                    @if($notif->action_url)
                                        <a href="{{ $notif->action_url }}" class="hover:text-[#0066FF] transition-colors">
                                            {{ $notif->title }}
                                        </a>
                                    @else
                                        {{ $notif->title }}
                                    @endif
                                </h3>

                                @if($notif->description)
                                    <p class="text-xs text-[#64748B] mt-1 leading-relaxed">{{ $notif->description }}</p>
                                @endif

                                <div class="flex items-center gap-3 mt-2 text-[11px] text-[#64748B]">
                                    <span>Détectée {{ $notif->created_at->diffForHumans() }}</span>
                                    @if($notif->due_date)
                                        <span class="text-slate-300">·</span>
                                        <span>Échéance : {{ $notif->due_date->format('d/m/Y') }}</span>
                                    @endif
                                    @if($notif->assignee)
                                        <span class="text-slate-300">·</span>
                                        <span>Assigné à : {{ $notif->assignee->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Actions contextuelles -->
                        <div class="flex items-center gap-2 shrink-0 md:self-center pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                            @if($notif->action_url)
                                <a href="{{ $notif->action_url }}" class="h-8 px-3 rounded-lg bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-medium transition flex items-center gap-1">
                                    <span>Consulter</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endif

                            @if($notif->status === 'active')
                                <form method="POST" action="{{ route('notifications.read', $notif) }}">
                                    @csrf
                                    <button type="submit" class="h-8 px-2.5 rounded-lg border border-[#E2E8F0] hover:bg-slate-50 text-[#64748B] text-xs font-medium transition" title="Marquer comme lu">
                                        <span class="hidden sm:inline">Marquer lu</span>
                                        <svg class="w-3.5 h-3.5 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                            @endif

                            @if($notif->status !== 'resolved')
                                <form method="POST" action="{{ route('notifications.resolve', $notif) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="h-8 px-2.5 rounded-lg border border-emerald-200 text-emerald-700 hover:bg-emerald-50 text-xs font-medium transition" title="Résoudre l'alerte">
                                        <span class="hidden sm:inline">Résoudre</span>
                                        <svg class="w-3.5 h-3.5 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('notifications.dismiss', $notif) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="h-8 w-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition" title="Masquer / Archiver">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-12 px-4 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h4 class="text-sm font-semibold text-[#0B0F14]">Aucune notification en attente</h4>
                        <p class="text-xs text-[#64748B] mt-1 max-w-sm mx-auto">Toutes les opérations et alertes sont à jour. Le système continue de surveiller vos flux en arrière-plan.</p>
                        <form method="POST" action="{{ route('notifications.run-checks') }}" class="mt-4">
                            @csrf
                            <button type="submit" class="h-9 px-3.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 text-xs font-medium text-[#0B0F14] transition inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Lancer une vérification manuelle</span>
                            </button>
                        </form>
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
