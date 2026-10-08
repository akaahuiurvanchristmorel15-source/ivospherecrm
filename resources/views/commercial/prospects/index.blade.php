<x-layouts.app>
    <x-slot:title>Pipeline Commercial & Prospects — IVOSPHERE ERP</x-slot>

    @php
        $stageColors = [
            'nouveau' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400'],
            'contacte' => ['bg' => 'bg-blue-50', 'text' => 'text-[#0066FF]', 'border' => 'border-blue-200', 'dot' => 'bg-[#0066FF]'],
            'interesse' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'dot' => 'bg-indigo-500'],
            'devis_envoye' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
            'negociation' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'dot' => 'bg-purple-500'],
            'gagne' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
            'perdu' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'dot' => 'bg-rose-500'],
        ];

        $conversionRate = $totalCount > 0 ? round(($wonCount / $totalCount) * 100, 1) : 0;
        $activeStageKey = request('stage', array_key_first($kanban) ?? 'nouveau');
        if (!array_key_exists($activeStageKey, $kanban)) {
            $activeStageKey = array_key_first($kanban) ?? 'nouveau';
        }
    @endphp

    <div class="space-y-4 sm:space-y-6" x-data="{ 
        activeMobileStage: '{{ $activeStageKey }}',
        mobileFiltersOpen: false 
    }">

        <!-- 1. En-tête Responsive Minimaliste & Élégant -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 pb-1">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-[#0B0F14] tracking-tight">
                        Pipeline Commercial
                    </h1>
                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $totalCount }}
                    </span>
                </div>
                <p class="text-xs text-[#64748B] mt-0.5 hidden sm:block">
                    Cycle de vente complet en 7 étapes, pondération probabiliste et conversion en 1 clic
                </p>
            </div>

            <!-- Actions d'en-tête (Switcher Vue + Bouton Nouveau) -->
            <div class="flex items-center justify-between sm:justify-end gap-2">
                <!-- Switcher Kanban / Liste -->
                <div class="inline-flex items-center p-1 rounded-xl bg-white border border-[#E2E8F0] shadow-2xs text-xs">
                    <a 
                        href="{{ request()->fullUrlWithQuery(['view' => 'kanban']) }}" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold transition-all {{ $viewMode === 'kanban' ? 'bg-[#0066FF] text-white shadow-xs' : 'text-slate-600 hover:text-[#0B0F14]' }}"
                        title="Affichage Kanban"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                        <span>Kanban</span>
                    </a>
                    <a 
                        href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold transition-all {{ $viewMode === 'list' ? 'bg-[#0066FF] text-white shadow-xs' : 'text-slate-600 hover:text-[#0B0F14]' }}"
                        title="Affichage Liste"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        <span>Liste</span>
                    </a>
                </div>

                <!-- Bouton Création Prospect -->
                <a 
                    href="{{ route('commercial.prospects.create') }}" 
                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-bold shadow-xs shadow-[#0066FF]/25 transition-all touch-target"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Nouveau</span>
                </a>
            </div>
        </div>

        <!-- 2. Synthèse Chiffrée : KPIs (Desktop 4 colonnes / Mobile Ruban épuré) -->
        <!-- Version Desktop (md+) -->
        <div class="hidden md:grid grid-cols-4 gap-4">
            <x-stat-card 
                title="Total Opportunités"
                :value="number_format($totalPipelineValue, 0, ',', ' ') . ' FCFA'"
                :change="$totalCount . ' prospect(s) engagé(s)'"
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Valeur Pondérée"
                :value="number_format($weightedPipelineValue, 0, ',', ' ') . ' FCFA'"
                change="Ajusté selon probabilités"
                changeType="up"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Affaires Conclues"
                :value="number_format($wonValue, 0, ',', ' ') . ' FCFA'"
                :change="$wonCount . ' contrat(s) signé(s)'"
                changeType="up"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Taux de Conversion"
                :value="$conversionRate . '%'"
                change="Sur le volume total"
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </x-slot:icon>
            </x-stat-card>
        </div>

        <!-- Version Mobile Minimaliste : Micro-Cartes 2x2 Épurées -->
        <div class="grid grid-cols-2 gap-2.5 md:hidden">
            <div class="p-3 rounded-2xl bg-white border border-[#E2E8F0] shadow-2xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pipeline Total</span>
                <div class="text-sm font-black text-[#0B0F14] mt-0.5 truncate">
                    {{ number_format($totalPipelineValue, 0, ',', ' ') }} <span class="text-[10px] font-semibold text-slate-400">F</span>
                </div>
                <span class="text-[10px] text-slate-500 font-medium block mt-0.5">{{ $totalCount }} prospect(s)</span>
            </div>

            <div class="p-3 rounded-2xl bg-white border border-[#E2E8F0] shadow-2xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pondérée (Prob.)</span>
                <div class="text-sm font-black text-[#0066FF] mt-0.5 truncate">
                    {{ number_format($weightedPipelineValue, 0, ',', ' ') }} <span class="text-[10px] font-semibold text-blue-400">F</span>
                </div>
                <span class="text-[10px] text-slate-500 font-medium block mt-0.5">Ajustée à 100%</span>
            </div>

            <div class="p-3 rounded-2xl bg-white border border-[#E2E8F0] shadow-2xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Affaires Conclues</span>
                <div class="text-sm font-black text-emerald-700 mt-0.5 truncate">
                    {{ number_format($wonValue, 0, ',', ' ') }} <span class="text-[10px] font-semibold text-emerald-500">F</span>
                </div>
                <span class="text-[10px] text-emerald-600 font-bold block mt-0.5">{{ $wonCount }} gagné(s)</span>
            </div>

            <div class="p-3 rounded-2xl bg-white border border-[#E2E8F0] shadow-2xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Conversion</span>
                <div class="text-sm font-black text-[#0B0F14] mt-0.5">
                    {{ $conversionRate }}%
                </div>
                <span class="text-[10px] text-slate-500 font-medium block mt-0.5">Ratio global</span>
            </div>
        </div>

        <!-- 3. Barre de Recherche & Filtres Minimaliste -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-3 sm:p-3.5 shadow-2xs">
            <form method="GET" class="space-y-3">
                <input type="hidden" name="view" value="{{ $viewMode }}">

                <div class="flex items-center gap-2">
                    <!-- Recherche principale -->
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}" 
                            placeholder="Rechercher par nom, entreprise, téléphone..." 
                            class="w-full pl-9 pr-3 py-2 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:border-[#0066FF] focus:bg-white transition"
                        />
                    </div>

                    <!-- Bouton toggle filtres (Mobile) -->
                    <button 
                        type="button" 
                        @click="mobileFiltersOpen = !mobileFiltersOpen" 
                        class="md:hidden inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border text-xs font-semibold transition touch-target {{ request()->hasAny(['domain_id', 'commercial_id', 'stage']) ? 'bg-blue-50 border-[#0066FF] text-[#0066FF]' : 'bg-[#F8FAFC] border-[#E2E8F0] text-slate-600' }}"
                        title="Filtres avancés"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span class="hidden xs:inline">Filtres</span>
                        @if(request()->hasAny(['domain_id', 'commercial_id', 'stage']))
                            <span class="w-1.5 h-1.5 rounded-full bg-[#0066FF]"></span>
                        @endif
                    </button>

                    <!-- Filtres directs Desktop (md+) -->
                    <div class="hidden md:flex items-center gap-2">
                        <select name="domain_id" class="px-3 py-2 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:bg-white transition">
                            <option value="">Tous les domaines</option>
                            @foreach($domains ?? [] as $dom)
                                <option value="{{ $dom->id }}" {{ request('domain_id') == $dom->id ? 'selected' : '' }}>{{ $dom->name }}</option>
                            @endforeach
                        </select>

                        <select name="commercial_id" class="px-3 py-2 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:bg-white transition">
                            <option value="">Tous les commerciaux</option>
                            @foreach($commercials ?? [] as $user)
                                <option value="{{ $user->id }}" {{ request('commercial_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition shadow-xs">
                            Filtrer
                        </button>

                        @if(request()->hasAny(['search', 'domain_id', 'commercial_id', 'stage']))
                            <a href="{{ route('commercial.prospects.index', ['view' => $viewMode]) }}" class="px-2 text-xs font-semibold text-slate-500 hover:text-rose-600 transition">
                                Réinitialiser
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Tiroir de filtres dépliable sur Mobile -->
                <div x-show="mobileFiltersOpen" x-cloak class="md:hidden pt-3 border-t border-slate-100 space-y-2.5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1">Domaine d'activité</label>
                        <select name="domain_id" class="w-full px-3 py-2 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14]">
                            <option value="">Tous les domaines</option>
                            @foreach($domains ?? [] as $dom)
                                <option value="{{ $dom->id }}" {{ request('domain_id') == $dom->id ? 'selected' : '' }}>{{ $dom->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1">Commercial assigné</label>
                        <select name="commercial_id" class="w-full px-3 py-2 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14]">
                            <option value="">Tous les commerciaux</option>
                            @foreach($commercials ?? [] as $user)
                                <option value="{{ $user->id }}" {{ request('commercial_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <button type="submit" class="flex-1 py-2 rounded-xl bg-[#0066FF] text-white text-xs font-bold shadow-xs">
                            Appliquer les filtres
                        </button>
                        @if(request()->hasAny(['search', 'domain_id', 'commercial_id', 'stage']))
                            <a href="{{ route('commercial.prospects.index', ['view' => $viewMode]) }}" class="py-2 px-3 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold text-center">
                                Réinitialiser
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        @if($viewMode === 'kanban')
            <!-- 4. MODE KANBAN -->
            
            <!-- A. NAVIGATION KANBAN MOBILE (Sélecteur Horizontal de Colonne pour Éviter le Scroll Infini de 1850px) -->
            <div class="block md:hidden space-y-3">
                <!-- Pills de sélection d'étape (Barre horizontale douce à défilement tactile) -->
                <div class="overflow-x-auto pb-1 -mx-3.5 px-3.5 flex items-center gap-1.5 scrollbar-none snap-x">
                    @foreach($kanban as $stKey => $stCol)
                        @php
                            $colors = $stageColors[$stKey] ?? $stageColors['nouveau'];
                        @endphp
                        <button 
                            type="button" 
                            @click="activeMobileStage = '{{ $stKey }}'" 
                            class="snap-start shrink-0 px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border"
                            :class="activeMobileStage === '{{ $stKey }}' ? 'bg-white border-[#0066FF] text-[#0066FF] shadow-xs ring-1 ring-[#0066FF]' : 'bg-white/80 border-[#E2E8F0] text-slate-600'"
                        >
                            <span class="w-2 h-2 rounded-full {{ $colors['dot'] }}"></span>
                            <span>{{ $stCol['info']['label'] }}</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold" :class="activeMobileStage === '{{ $stKey }}' ? 'bg-blue-50 text-[#0066FF]' : 'bg-slate-100 text-slate-600'">
                                {{ $stCol['count'] }}
                            </span>
                        </button>
                    @endforeach
                </div>

                <!-- Colonnes Kanban Mobile (Une seule étape affichée en pleine largeur pour lisibilité maximale) -->
                @foreach($kanban as $stageKey => $col)
                    @php
                        $colors = $stageColors[$stageKey] ?? $stageColors['nouveau'];
                    @endphp
                    <div x-show="activeMobileStage === '{{ $stageKey }}'" x-cloak class="space-y-3">
                        <!-- En-tête de la colonne active sur Mobile -->
                        <div class="p-3.5 rounded-2xl bg-white border border-[#E2E8F0] flex items-center justify-between shadow-2xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $colors['dot'] }}"></span>
                                <h3 class="text-xs font-extrabold text-[#0B0F14] uppercase tracking-wide">
                                    {{ $col['info']['label'] }}
                                </h3>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                    {{ $col['count'] }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-black text-[#0B0F14]">
                                    {{ number_format($col['total_value'], 0, ',', ' ') }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-semibold ml-0.5">FCFA</span>
                            </div>
                        </div>

                        <!-- Liste des fiches prospects (Design Mobile Épuré & Minimaliste) -->
                        <div class="space-y-2.5">
                            @forelse($col['prospects'] as $p)
                                @include('commercial.prospects.partials.mobile_card', ['prospect' => $p, 'col' => $col, 'colors' => $colors])
                            @empty
                                <div class="py-12 text-center bg-white rounded-2xl border border-dashed border-slate-200 p-6">
                                    <div class="w-10 h-10 rounded-full bg-slate-50 text-slate-400 mx-auto flex items-center justify-center mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">Aucun prospect à l'étape {{ $col['info']['label'] }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Créez une opportunité ou déplacez un prospect vers cette colonne.</p>
                                    <a href="{{ route('commercial.prospects.create', ['stage' => $stageKey]) }}" class="inline-flex items-center gap-1 mt-3 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                        + Ajouter à cette étape
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- B. KANBAN DESKTOP COMPLET (Visible à partir de md:) -->
            <div class="hidden md:block overflow-x-auto pb-6">
                <div class="flex gap-4 min-w-max pb-2">
                    @foreach($kanban as $stageKey => $col)
                        @php
                            $colors = $stageColors[$stageKey] ?? $stageColors['nouveau'];
                        @endphp
                        <div class="w-[280px] shrink-0 rounded-2xl bg-white border border-[#E2E8F0] flex flex-col h-[600px] xl:h-[660px] 2xl:h-[720px] shadow-2xs overflow-hidden">
                            <!-- En-tête de colonne -->
                            <div class="p-3.5 border-b border-[#E2E8F0] bg-[#F8FAFC] shrink-0">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full {{ $colors['dot'] }}"></span>
                                        <h3 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wide">{{ $col['info']['label'] }}</h3>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white text-[#0B0F14] border border-[#E2E8F0]">
                                        {{ $col['count'] }}
                                    </span>
                                </div>
                                <div class="mt-2 text-xs font-black text-[#0B0F14]">
                                    {{ number_format($col['total_value'], 0, ',', ' ') }} <span class="text-[10px] font-normal text-[#64748B]">FCFA</span>
                                </div>
                            </div>

                            <!-- Cartes Kanban Desktop avec scroll vertical dédié -->
                            <div class="p-3 space-y-3 overflow-y-auto flex-1 min-h-0 bg-[#F8FAFC]/50 overscroll-contain">
                                @forelse($col['prospects'] as $p)
                                    <div class="p-3.5 rounded-xl bg-white border border-[#E2E8F0] hover:border-[#0066FF] transition-all space-y-2.5 shadow-2xs hover:shadow-xs">
                                        <!-- En-tête de la carte -->
                                        <div>
                                            <div class="flex justify-between items-start gap-2">
                                                <a href="{{ route('commercial.prospects.edit', $p) }}" class="font-bold text-[#0B0F14] hover:text-[#0066FF] transition-colors text-xs leading-snug">
                                                    {{ $p->name }}
                                                </a>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#F8FAFC] text-[#0B0F14] border border-[#E2E8F0] font-mono">
                                                    {{ $p->probability ?? $col['info']['default_prob'] }}%
                                                </span>
                                            </div>
                                            @if($p->company)
                                                <p class="text-[11px] text-[#64748B] mt-0.5 truncate">{{ $p->company }}</p>
                                            @endif
                                            @if($p->domain)
                                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[9px] bg-slate-100 text-slate-600 font-semibold">
                                                    {{ $p->domain->name }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Valeurs Financières -->
                                        <div class="bg-[#F8FAFC] p-2 rounded-lg border border-[#E2E8F0] flex justify-between items-center text-xs">
                                            <div>
                                                <span class="text-[9px] text-[#64748B] uppercase font-bold block">Valeur</span>
                                                <span class="font-bold text-[#0B0F14]">{{ number_format($p->estimated_value ?? 0, 0, ',', ' ') }} F</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-[9px] text-[#64748B] uppercase font-bold block">Pondérée</span>
                                                <span class="font-bold text-[#0066FF]">{{ number_format(($p->estimated_value ?? 0) * (($p->probability ?? $col['info']['default_prob']) / 100), 0, ',', ' ') }} F</span>
                                            </div>
                                        </div>

                                        <!-- Commercial & Date -->
                                        <div class="flex items-center justify-between text-[10px] text-[#64748B]">
                                            <span class="truncate max-w-[120px]">
                                                👤 {{ $p->commercial?->name ?? $p->assignedUser?->name ?? 'Non assigné' }}
                                            </span>
                                            @if($p->next_follow_up)
                                                <span class="text-[#0066FF] font-medium">📅 {{ $p->next_follow_up->format('d/m') }}</span>
                                            @endif
                                        </div>

                                        <!-- Sélecteur d'étape interactif -->
                                        <div class="pt-2 border-t border-[#E2E8F0]">
                                            <form action="{{ route('commercial.prospects.stage', $p) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <select name="stage" onchange="this.form.submit()" class="w-full bg-white border border-[#E2E8F0] text-[#0B0F14] text-[10px] rounded-lg py-1 px-2 focus:ring-1 focus:ring-[#0066FF]">
                                                    @foreach(\App\Http\Controllers\Commercial\ProspectController::STAGES as $stK => $stI)
                                                        <option value="{{ $stK }}" {{ ($p->stage ?? 'nouveau') === $stK ? 'selected' : '' }}>
                                                            {{ $stI['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </div>

                                        <!-- Bouton conversion -->
                                        @if(($p->stage ?? '') !== 'gagne')
                                            <form action="{{ route('commercial.prospects.convert', $p) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-full py-1.5 px-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[10px] font-bold transition flex items-center justify-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Convertir en Client
                                                </button>
                                            </form>
                                        @else
                                            <div class="text-center text-[10px] font-bold text-emerald-700 bg-emerald-50 py-1.5 rounded-lg border border-emerald-200">
                                                &check; Affaire Conclue
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="h-full flex flex-col items-center justify-center py-10 text-center text-[#64748B] text-xs">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center mb-2.5 text-slate-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        </div>
                                        <span class="font-medium text-slate-500">Aucun prospect</span>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        @else
            <!-- 5. MODE LISTE -->
            <!-- Tableau Desktop (md+) -->
            <div class="hidden md:block bg-white rounded-2xl border border-[#E2E8F0] overflow-hidden shadow-2xs">
                <table class="w-full text-left text-sm text-[#0B0F14]">
                    <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[11px] font-bold text-[#64748B] uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5">Prospect / Entreprise</th>
                            <th class="px-6 py-3.5">Étape</th>
                            <th class="px-6 py-3.5">Probabilité</th>
                            <th class="px-6 py-3.5">Valeur Estimée</th>
                            <th class="px-6 py-3.5">Valeur Pondérée</th>
                            <th class="px-6 py-3.5">Commercial</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] bg-white">
                        @forelse($allProspects as $p)
                            @php
                                $colors = $stageColors[$p->stage ?? 'nouveau'] ?? $stageColors['nouveau'];
                            @endphp
                            <tr class="hover:bg-[#F8FAFC] transition-colors text-xs">
                                <td class="px-6 py-4">
                                    <a href="{{ route('commercial.prospects.edit', $p) }}" class="font-bold text-[#0B0F14] hover:text-[#0066FF] transition-colors text-sm">
                                        {{ $p->name }}
                                    </a>
                                    @if($p->company)<p class="text-[#64748B] text-xs mt-0.5">{{ $p->company }}</p>@endif
                                    @if($p->phone)<span class="text-[#64748B] text-[11px] font-mono">{{ $p->phone }}</span>@endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $colors['bg'] }} {{ $colors['text'] }} border {{ $colors['border'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $colors['dot'] }}"></span>
                                        {{ \App\Http\Controllers\Commercial\ProspectController::STAGES[$p->stage ?? 'nouveau']['label'] ?? ucfirst($p->stage) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-slate-800">
                                    {{ $p->probability ?? 10 }}%
                                </td>
                                <td class="px-6 py-4 font-bold text-[#0B0F14]">
                                    {{ number_format($p->estimated_value ?? 0, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-4 font-bold text-[#0066FF]">
                                    {{ number_format(($p->estimated_value ?? 0) * (($p->probability ?? 10) / 100), 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-4 text-[#0B0F14]">
                                    {{ $p->commercial?->name ?? $p->assignedUser?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-right space-x-1.5">
                                    @if($p->stage !== 'gagne')
                                        <form action="{{ route('commercial.prospects.convert', $p) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-semibold transition border border-emerald-200">
                                                Convertir
                                            </button>
                                        </form>
                                    @endif
                                    <x-button :href="route('commercial.prospects.edit', $p)" variant="secondary" size="sm">
                                        Éditer
                                    </x-button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12">
                                    <x-empty-state 
                                        title="Aucun prospect enregistré" 
                                        description="Aucune opportunité dans le pipeline commercial pour l'instant."
                                        actionText="Créer un nouveau prospect"
                                        :actionUrl="route('commercial.prospects.create')"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Liste Mobile Épurée & Minimaliste (< md) -->
            <div class="block md:hidden space-y-2.5">
                @forelse($allProspects as $p)
                    @php
                        $colors = $stageColors[$p->stage ?? 'nouveau'] ?? $stageColors['nouveau'];
                    @endphp
                    @include('commercial.prospects.partials.mobile_card', ['prospect' => $p, 'col' => null, 'colors' => $colors])
                @empty
                    <div class="bg-white rounded-2xl p-8 border border-slate-200 text-center">
                        <x-empty-state 
                            title="Aucun prospect enregistré" 
                            description="Aucune opportunité dans le pipeline commercial pour l'instant."
                            actionText="Nouveau prospect"
                            :actionUrl="route('commercial.prospects.create')"
                        />
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</x-layouts.app>
