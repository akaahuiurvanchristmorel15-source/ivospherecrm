<x-layouts.app>
    <x-slot:title>Gestion des Commandes — IVOSPHERE ERP</x-slot>

    <div class="space-y-6">
        <x-page-header 
            title="Commandes Clients" 
            description="Suivi du traitement, préparation, expédition et facturation des commandes"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                    ['label' => 'Commandes']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button variant="primary" href="{{ route('commercial.orders.create') }}" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Nouvelle Commande</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <!-- Responsive Filters Toolbar (Desktop 1440px + Mobile 390px) -->
        <div 
            x-data="{ 
                showDates: {{ request()->hasAny(['date_from', 'date_to']) ? 'true' : 'false' }},
                currentStatus: '{{ request('status', '') }}'
            }" 
            class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs p-3.5 sm:p-4 space-y-3"
        >
            <!-- Ligne 1: Recherche principale + Bouton Filtres Dates + Bouton Filtrer + Reset -->
            <form method="GET" action="{{ route('commercial.orders.index') }}" class="space-y-3" id="orders-filter-form">
                <input type="hidden" name="status" :value="currentStatus">

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                    <!-- Champ de Recherche Rapide -->
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}" 
                            placeholder="Réf., client..." 
                            class="w-full pl-10 pr-9 py-2.5 bg-[#F5F7FA] border border-[#E2E8F0] rounded-xl text-xs sm:text-sm text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0066FF] focus:border-[#0066FF] transition-all"
                        />
                        @if(request('search'))
                            <a href="{{ route('commercial.orders.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600" title="Effacer la recherche">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>

                    <!-- Actions du Toolbar (Bouton Dates & Submit) -->
                    <div class="flex items-center gap-2 shrink-0">
                        <!-- Toggle Filtres Dates -->
                        <button 
                            type="button" 
                            @click="showDates = !showDates" 
                            :class="showDates || '{{ request()->hasAny(['date_from', 'date_to']) ? 'true' : '' }}' ? 'bg-[#0066FF]/10 text-[#0066FF] border-[#0066FF]/30 font-bold' : 'bg-white text-slate-700 border-[#E2E8F0] hover:bg-slate-50 font-medium'"
                            class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl border text-xs transition-all touch-target"
                            title="Filtrer par dates (Du / Au)"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Dates</span>
                            @if(request()->hasAny(['date_from', 'date_to']))
                                <span class="w-2 h-2 rounded-full bg-[#0066FF]"></span>
                            @endif
                        </button>

                        <!-- Bouton Filtrer / Soumettre -->
                        <button 
                            type="submit" 
                            class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl bg-[#0066FF] hover:bg-blue-600 text-white text-xs font-bold transition-all shadow-xs touch-target flex items-center justify-center gap-1.5"
                        >
                            <span>Filtrer</span>
                        </button>

                        @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                            <a 
                                href="{{ route('commercial.orders.index') }}" 
                                class="px-2.5 py-2.5 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-all flex items-center gap-1 touch-target"
                                title="Réinitialiser tous les filtres"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Reset</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Ligne Déroulante : Plage de dates (Responsive Grid) -->
                <div 
                    x-show="showDates" 
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="pt-3 border-t border-[#E2E8F0] grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 text-xs"
                >
                    <div>
                        <label class="block text-[11px] font-semibold text-[#64748B] mb-1">Du</label>
                        <input 
                            type="date" 
                            name="date_from" 
                            value="{{ request('date_from') }}" 
                            class="w-full px-3 py-2 bg-[#F5F7FA] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14] focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        />
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-[#64748B] mb-1">Au</label>
                        <input 
                            type="date" 
                            name="date_to" 
                            value="{{ request('date_to') }}" 
                            class="w-full px-3 py-2 bg-[#F5F7FA] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14] focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        />
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1 flex items-end">
                        <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-[#0066FF] text-white rounded-xl text-xs font-semibold transition-colors">
                            Appliquer
                        </button>
                    </div>
                </div>
            </form>

            <!-- Ligne 2 : Pastilles de Statut Défilables Horizontalement sur Mobile (Swipable Chips) -->
            <div class="pt-2 border-t border-[#E2E8F0]/70 flex items-center justify-between gap-3">
                <div class="flex items-center gap-1.5 overflow-x-auto [scrollbar-width:none] -mx-1 px-1 py-0.5 text-xs max-w-full">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider shrink-0 mr-1 hidden sm:inline">État :</span>
                    
                    @php
                        $statusList = [
                            '' => ['label' => 'Tous', 'color' => 'bg-slate-100 text-slate-700 hover:bg-slate-200'],
                            'en_attente' => ['label' => 'Attente', 'color' => 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100'],
                            'confirmée' => ['label' => 'Confirm.', 'color' => 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100'],
                            'en_cours' => ['label' => 'En cours', 'color' => 'bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100'],
                            'livrée' => ['label' => 'Livré', 'color' => 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100'],
                            'annulée' => ['label' => 'Annul.', 'color' => 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100'],
                        ];
                        $activeStatus = request('status', '');
                    @endphp

                    @foreach($statusList as $stVal => $stMeta)
                        <a 
                            href="{{ route('commercial.orders.index', array_merge(request()->except(['status', 'page']), $stVal ? ['status' => $stVal] : [])) }}"
                            class="px-2.5 sm:px-3 py-1.5 rounded-full text-xs font-semibold transition-all shrink-0 select-none {{ $activeStatus === $stVal ? 'bg-[#0066FF] text-white shadow-xs font-bold ring-2 ring-[#0066FF]/20' : 'bg-[#F5F7FA] text-slate-600 hover:text-[#0B0F14] hover:bg-slate-200/70 border border-[#E2E8F0]' }}"
                        >
                            {{ $stMeta['label'] }}
                        </a>
                    @endforeach
                </div>

                <!-- Compteur de résultats abrégé -->
                <span class="text-[11px] text-[#64748B] font-semibold shrink-0 hidden md:block">
                    <strong>{{ $orders->total() }}</strong> cmds
                </span>
            </div>
        </div>

        <x-card :noPadding="true">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Référence</th>
                            <th class="py-3 px-4">Client</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4">Statut</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($orders as $o)
                            <tr class="hover:bg-[#F5F7FA]/70 transition">
                                <td class="py-3.5 px-4 font-mono">
                                    <a href="{{ route('commercial.orders.show', $o) }}" class="font-bold text-[#0066FF] hover:underline">
                                        {{ $o->reference }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-[#0B0F14]">
                                    {{ $o->customer->name ?? 'Client Particulier' }}
                                </td>
                                <td class="py-3.5 px-4 text-[#64748B] font-mono text-[11px]">
                                    {{ $o->date?->format('d/m/Y') }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-[#0B0F14]">
                                    {{ number_format($o->total, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $stNorm = strtolower($o->status);
                                        $stBadge = match(true) {
                                            str_contains($stNorm, 'livr') => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            str_contains($stNorm, 'annul') => 'bg-rose-50 text-rose-700 border-rose-200',
                                            str_contains($stNorm, 'cours') || str_contains($stNorm, 'confirm') => 'bg-blue-50 text-blue-700 border-blue-200',
                                            default => 'bg-[#F5F7FA] text-[#64748B] border-[#E2E8F0]'
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $stBadge }}">
                                        {{ str_replace('_', ' ', ucfirst($o->status)) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('commercial.orders.show', $o) }}" class="p-1.5 rounded-lg text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] transition border border-transparent hover:border-[#E2E8F0]" title="Consulter">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('commercial.orders.edit', $o) }}" class="p-1.5 rounded-lg text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] transition border border-transparent hover:border-[#E2E8F0]" title="Modifier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12">
                                    <x-empty-state 
                                        title="Aucune commande enregistrée"
                                        description="Toutes les commandes passées par vos clients apparaîtront ici."
                                    >
                                        <x-slot:action>
                                            <x-button variant="primary" href="{{ route('commercial.orders.create') }}">
                                                Créer une commande
                                            </x-button>
                                        </x-slot:action>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Amplified Cards View (< md) -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($orders as $o)
                    @php
                        $stNorm = strtolower($o->status);
                        $stBadge = match(true) {
                            str_contains($stNorm, 'livr') => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            str_contains($stNorm, 'annul') => 'bg-rose-50 text-rose-700 border-rose-200',
                            str_contains($stNorm, 'cours') || str_contains($stNorm, 'confirm') => 'bg-blue-50 text-blue-700 border-blue-200',
                            default => 'bg-[#F5F7FA] text-[#64748B] border-[#E2E8F0]'
                        };
                    @endphp
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ route('commercial.orders.show', $o) }}" class="font-mono font-bold text-sm text-[#0066FF] hover:underline flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#0066FF] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span>{{ $o->reference }}</span>
                            </a>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $stBadge }}">
                                {{ str_replace('_', ' ', ucfirst($o->status)) }}
                            </span>
                        </div>

                        <div>
                            <p class="text-[11px] text-slate-400 font-medium">Client</p>
                            <p class="text-sm font-semibold text-[#0B0F14] leading-snug">{{ $o->customer->name ?? 'Client Particulier' }}</p>
                        </div>

                        <div class="flex items-center justify-between bg-[#F5F7FA] p-3 rounded-lg border border-[#E2E8F0]">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Montant Commande</span>
                                <span class="text-sm font-bold text-[#0B0F14]">{{ number_format($o->total, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Date</span>
                                <span class="text-xs font-semibold text-slate-700 font-mono">{{ $o->date?->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-100">
                            <a href="{{ route('commercial.orders.edit', $o) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-medium border border-slate-200 transition">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Modifier</span>
                            </a>
                            <a href="{{ route('commercial.orders.show', $o) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-semibold shadow-xs transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Consulter</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-8">
                        <x-empty-state 
                            title="Aucune commande enregistrée"
                            description="Toutes les commandes passées par vos clients apparaîtront ici."
                        />
                    </div>
                @endforelse
            </div>

            @if($orders->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $orders->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
