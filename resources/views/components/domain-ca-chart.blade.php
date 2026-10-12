@props([
    'domainPerformance' => [],
    'totalConsolidatedCa' => 0,
    'currency' => 'FCFA',
    'year' => null,
])

@php
    $n = fn ($v) => number_format($v ?? 0, 0, ',', ' ');
    $year = $year ?? date('Y');

    $chartItems = \App\View\DomainCaChartData::items($domainPerformance);
    $activeCount = count(array_filter($chartItems, fn ($i) => $i['ca'] > 0));
@endphp

<div 
    x-data="{
        viewMode: 'donut', // 'donut' ou 'bars'
        activeFilter: 'all', // 'all' ou 'active'
        hovered: null,
        totalCa: {{ (float) $totalConsolidatedCa }},
        totalFormatted: '{{ $n($totalConsolidatedCa) }}',
        currency: '{{ $currency }}',
        items: {{ Js::from($chartItems) }},

        get activeCount() {
            return this.items.filter(i => i.ca > 0).length;
        },
        
        get displayedItems() {
            if (this.activeFilter === 'active') {
                return this.items.filter(i => i.ca > 0);
            }
            return this.items;
        },

        get currentHover() {
            if (this.hovered) {
                return this.items.find(i => i.key === this.hovered) || null;
            }
            return null;
        }
    }"
    x-on:dashboard-chart.window="items = $event.detail.items; totalCa = $event.detail.totalCa; totalFormatted = $event.detail.totalFormatted"
    class="flex flex-col h-full"
>
    <!-- En-tête avec métrique et sélecteurs de vue -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#E2E8F0] pb-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0066FF] animate-pulse"></span>
                <h3 class="text-sm font-bold text-[#0B0F14] tracking-tight">Chiffre d'affaires par domaine</h3>
            </div>
            <p class="text-xs text-[#64748B] mt-0.5">Exercice {{ $year }} • Répartition en temps réel</p>
        </div>

        <div class="flex items-center justify-between sm:justify-end gap-2.5">
            <!-- Total mis en avant -->
            <div class="text-right">
                <span class="text-[10px] text-[#64748B] font-semibold uppercase tracking-wider block">Total Réalisé</span>
                <span class="text-sm sm:text-base font-extrabold text-[#0066FF] tabular-nums">
                    <span x-text="totalFormatted">{{ $n($totalConsolidatedCa) }}</span> {{ $currency }}
                </span>
            </div>

            <!-- Boutons de bascule graphique (Donut / Barres) -->
            <div class="flex items-center bg-[#F5F7FA] border border-[#E2E8F0] rounded-lg p-0.5 shadow-xs">
                <button 
                    type="button" 
                    @click="viewMode = 'donut'" 
                    :class="viewMode === 'donut' ? 'bg-white text-[#0066FF] font-bold shadow-xs' : 'text-[#64748B] hover:text-[#0B0F14]'"
                    class="p-1.5 rounded-md text-xs transition-all flex items-center gap-1"
                    title="Vue graphique circulaire (Donut)"
                    aria-label="Vue Donut"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/>
                    </svg>
                    <span class="hidden md:inline text-[11px]">Anneau</span>
                </button>
                <button 
                    type="button" 
                    @click="viewMode = 'bars'" 
                    :class="viewMode === 'bars' ? 'bg-white text-[#0066FF] font-bold shadow-xs' : 'text-[#64748B] hover:text-[#0B0F14]'"
                    class="p-1.5 rounded-md text-xs transition-all flex items-center gap-1"
                    title="Vue barres comparatives"
                    aria-label="Vue Barres"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                    <span class="hidden md:inline text-[11px]">Barres</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Filtre rapide (Tous / Pôles actifs) -->
    <div class="flex items-center justify-between pt-3 pb-2 text-xs">
        <div class="flex items-center gap-1.5">
            <span class="text-[11px] font-semibold text-[#64748B]">Affichage :</span>
            <button 
                type="button" 
                @click="activeFilter = 'all'" 
                :class="activeFilter === 'all' ? 'bg-[#0B0F14] text-white font-bold' : 'bg-[#F5F7FA] text-[#64748B] hover:text-[#0B0F14]'"
                class="px-2 py-0.5 rounded-md text-[10px] transition-all"
            >
                Tous (<span x-text="items.length">{{ count($chartItems) }}</span>)
            </button>
            <button 
                type="button" 
                @click="activeFilter = 'active'" 
                :class="activeFilter === 'active' ? 'bg-[#0066FF] text-white font-bold' : 'bg-[#F5F7FA] text-[#64748B] hover:text-[#0B0F14]'"
                class="px-2 py-0.5 rounded-md text-[10px] transition-all"
            >
                Actifs (<span x-text="activeCount">{{ $activeCount }}</span>)
            </button>
        </div>
        <span class="text-[10px] text-[#64748B]" x-show="hovered">
            Survolez ou cliquez pour inspecter
        </span>
    </div>

    <!-- CORPS DU GRAPHIQUE DYNAMIQUE -->
    <div class="flex-1 mt-2">
        
        <!-- VUE 1 : DONUT INTERACTIF (ANNEAU DYNAMIQUE AVEC LÉGENDE RÉACTIVE) -->
        <div x-show="viewMode === 'donut'" x-transition:enter="transition ease-out duration-200" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
            
            <!-- Anneau SVG interactif (Centerpiece) -->
            <div class="md:col-span-5 flex flex-col items-center justify-center relative py-2">
                <div class="relative w-48 h-48 sm:w-52 sm:h-52 flex items-center justify-center select-none">
                    
                    <svg viewBox="0 0 160 160" class="w-full h-full transform -rotate-90">
                        <!-- Piste de fond neutre -->
                        <circle 
                            cx="80" 
                            cy="80" 
                            r="60" 
                            fill="transparent" 
                            stroke="#F1F5F9" 
                            stroke-width="16" 
                        />

                        <!-- Segments : un cercle par pôle (pas de <template> dans un SVG, qu'Alpine ne sait pas recréer) -->
                        @foreach($chartItems as $index => $chartItem)
                            <circle
                                x-data="{ get item() { return items[{{ $index }}] || { percent: 0 } } }"
                                x-show="item.percent > 0"
                                cx="80"
                                cy="80"
                                r="60"
                                fill="transparent"
                                :stroke="item.color"
                                :stroke-width="hovered === item.key ? 20 : 16"
                                :stroke-dasharray="`${item.dash} ${item.gap}`"
                                :stroke-dashoffset="item.offset"
                                stroke-linecap="round"
                                class="transition-all duration-300 cursor-pointer"
                                :class="hovered === item.key ? 'opacity-100 filter drop-shadow(0 2px 8px rgba(0,0,0,0.2))' : (hovered ? 'opacity-40' : 'opacity-95 hover:opacity-100')"
                                @mouseenter="hovered = item.key"
                                @mouseleave="hovered = null"
                                @click="hovered = (hovered === item.key ? null : item.key)"
                            />
                        @endforeach
                    </svg>

                    <!-- Centre d'information réactif (HUD central) -->
                    <div 
                        class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none p-3 text-center transition-all duration-200"
                    >
                        <!-- Vue au survol d'un pôle -->
                        <div x-show="hovered !== null" x-cloak class="flex flex-col items-center animate-in zoom-in-95 duration-150">
                            <span 
                                class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full text-white mb-1 shadow-xs"
                                :style="`background-color: ${currentHover ? currentHover.color : '#0066FF'}`"
                                x-text="currentHover ? currentHover.cleanName : ''"
                            ></span>
                            <span 
                                class="text-sm sm:text-base font-extrabold text-[#0B0F14] tabular-nums tracking-tight leading-tight"
                                x-text="currentHover ? currentHover.caFormatted + ' ' + currency : ''"
                            ></span>
                            <span 
                                class="text-xs font-bold text-[#0066FF] mt-0.5"
                                x-text="currentHover ? currentHover.percent + ' % du CA' : ''"
                            ></span>
                        </div>

                        <!-- Vue par défaut (Total consolidé) -->
                        <div x-show="hovered === null" class="flex flex-col items-center">
                            <span class="text-[10px] font-bold text-[#64748B] uppercase tracking-wider">Consolidé</span>
                            <span class="text-base sm:text-lg font-black text-[#0B0F14] tabular-nums tracking-tight">
                                <span x-text="totalFormatted">{{ $n($totalConsolidatedCa) }}</span>
                            </span>
                            <span class="text-[10px] font-semibold text-[#0066FF]">
                                {{ $currency }} • 100%
                            </span>
                        </div>
                    </div>
                </div>

                <span class="text-[11px] text-[#64748B] text-center mt-1 sm:hidden">
                    Touchez un segment pour voir le détail
                </span>
            </div>

            <!-- Liste détaillée interactive (Légende avec barres de progression) -->
            <div class="md:col-span-7 space-y-2.5">
                <template x-for="item in displayedItems" :key="item.key">
                    <div 
                        class="p-2.5 sm:p-3 rounded-xl border transition-all cursor-pointer select-none"
                        :class="hovered === item.key 
                            ? 'bg-blue-50/60 border-[#0066FF] shadow-xs translate-x-1' 
                            : 'bg-[#F8FAFC] border-[#E2E8F0]/70 hover:bg-white hover:border-[#CBD5E1]'"
                        @mouseenter="hovered = item.key"
                        @mouseleave="hovered = null"
                        @click="hovered = (hovered === item.key ? null : item.key)"
                    >
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <span 
                                    class="w-3 h-3 rounded-full shrink-0 shadow-xs transition-transform" 
                                    :class="hovered === item.key ? 'scale-125' : ''"
                                    :style="`background-color: ${item.color}`"
                                ></span>
                                <span 
                                    class="font-bold text-[#0B0F14] truncate"
                                    x-text="item.fullName"
                                ></span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span 
                                    class="font-extrabold text-[#0B0F14] tabular-nums"
                                    x-text="item.caFormatted + ' ' + currency"
                                ></span>
                                <span 
                                    class="text-[10px] font-bold px-1.5 py-0.5 rounded border"
                                    :class="item.percent > 0 ? 'bg-white text-[#0B0F14] border-[#E2E8F0]' : 'bg-slate-100 text-slate-400 border-transparent'"
                                    x-text="item.percent + ' %'"
                                ></span>
                            </div>
                        </div>

                        <!-- Mini barre de progression colorée -->
                        <div class="h-2 w-full bg-[#E2E8F0] rounded-full overflow-hidden">
                            <div 
                                class="h-full rounded-full transition-all duration-500"
                                :style="`width: ${item.percent}%; background-color: ${item.color}`"
                            ></div>
                        </div>
                    </div>
                </template>
            </div>

        </div>

        <!-- VUE 2 : BARRES COMPARATIVES DÉTAILLÉES -->
        <div x-show="viewMode === 'bars'" x-cloak x-transition:enter="transition ease-out duration-200" class="space-y-3 pt-2">
            <template x-for="item in displayedItems" :key="item.key">
                <div 
                    class="p-3 rounded-xl border transition-all cursor-pointer"
                    :class="hovered === item.key ? 'bg-blue-50/50 border-[#0066FF] shadow-xs' : 'bg-white border-[#E2E8F0] hover:border-slate-300'"
                    @mouseenter="hovered = item.key"
                    @mouseleave="hovered = null"
                >
                    <div class="flex items-center justify-between text-xs mb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full shrink-0" :style="`background-color: ${item.color}`"></span>
                            <span class="font-bold text-[#0B0F14]" x-text="item.fullName"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-extrabold text-[#0B0F14] text-sm tabular-nums" x-text="item.caFormatted + ' ' + currency"></span>
                            <span class="text-xs font-bold text-[#0066FF]" x-text="item.percent + ' %'"></span>
                        </div>
                    </div>

                    <!-- Barre large animée -->
                    <div class="w-full bg-[#F5F7FA] h-3.5 rounded-full overflow-hidden border border-[#E2E8F0]/60 p-0.5">
                        <div 
                            class="h-full rounded-full transition-all duration-700 ease-out shadow-xs"
                            :style="`width: ${Math.max(item.percent, item.ca > 0 ? 3 : 0)}%; background-color: ${item.color}`"
                        ></div>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-[#64748B] mt-1.5">
                        <span x-text="item.orders + ' commande(s) générée(s)'"></span>
                        <span x-text="item.ca > 0 ? 'Pôle contributeur' : 'Aucune vente enregistrée'"></span>
                    </div>
                </div>
            </template>
        </div>

    </div>

    <!-- Pied de carte interactif avec récapitulatif -->
    <div class="mt-4 pt-3 border-t border-[#E2E8F0] flex items-center justify-between text-xs text-[#64748B]">
        <span>Pôles actifs : <strong class="text-[#0B0F14] font-semibold"><span x-text="activeCount">{{ $activeCount }}</span> / <span x-text="items.length">{{ count($chartItems) }}</span></strong></span>
        <a href="{{ route('command-center.index') }}" class="font-semibold text-[#0066FF] hover:underline flex items-center gap-1">
            <span>Analyse détaillée</span>
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    </div>
</div>
