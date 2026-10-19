<div 
    x-data="spotlightSearch()"
    @open-spotlight.window="openModal()"
    @keydown.window.prevent.cmd.k="openModal()"
    @keydown.window.prevent.ctrl.k="openModal()"
    @keydown.escape.window="closeModal()"
    class="relative z-50"
    role="dialog"
    aria-modal="true"
    x-cloak
>
    <!-- Backdrop -->
    <div 
        x-show="isOpen"
        x-transition:enter="ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeModal()"
        class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-xs transition-opacity"
        style="display: none;"
    ></div>

    <!-- Modal Box -->
    <div 
        x-show="isOpen"
        x-transition:enter="ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20 flex justify-center items-start"
        style="display: none;"
    >
        <div 
            @click.outside="closeModal()"
            @keydown.arrow-down.prevent="selectedIndex = (selectedIndex + 1) % (flatResults.length || 1); scrollToSelected()"
            @keydown.arrow-up.prevent="selectedIndex = (selectedIndex - 1 + flatResults.length) % (flatResults.length || 1); scrollToSelected()"
            @keydown.enter.prevent="goToSelected()"
            class="mx-auto max-w-2xl w-full transform rounded-3xl bg-white shadow-2xl ring-1 ring-black/5 overflow-hidden transition-all text-[#0B0F14]"
        >
            <!-- Search Header -->
            <div class="border-b border-[#E2E8F0] bg-[#F5F7FA]/60 p-3 sm:p-4">
                <div class="relative flex h-14 items-center gap-3 rounded-full border border-slate-200 bg-white pl-2 pr-3 shadow-sm transition-all focus-within:border-[#0066FF] focus-within:ring-4 focus-within:ring-[#0066FF]/15">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#0066FF] text-white shadow-sm">
                        <svg x-show="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <svg x-show="isLoading" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                    </span>
                    <input 
                        x-ref="searchInput"
                        type="text" 
                        x-model="query"
                        @input.debounce.250ms="performSearch()"
                        placeholder="Rechercher client, facture, devis, produit, contrat, ticket..." 
                        class="min-w-0 flex-1 border-0 bg-transparent p-0 text-base text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:ring-0"
                    />
                    <button type="button" x-show="query.length > 0" x-cloak @click="query = ''; performSearch(); $refs.searchInput.focus()"
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700" aria-label="Effacer la recherche">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <kbd class="hidden sm:inline-flex shrink-0 items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-semibold text-slate-500">ESC</kbd>
                </div>
            </div>

            <!-- Quick Suggestions if empty -->
            <div x-show="query.trim().length < 2" class="p-6 text-center">
                <p class="text-xs font-medium text-slate-500 mb-4 uppercase tracking-wider">Accès rapides fréquents</p>
                <div class="flex flex-wrap justify-center gap-2">
                    <a href="{{ route('command-center.index') }}" class="px-3 py-1.5 rounded-lg bg-[#F5F7FA] hover:bg-[#0066FF] hover:text-white text-xs text-[#0B0F14] border border-[#E2E8F0] transition-colors">
                        📊 Command Center Direction
                    </a>
                    @if(auth()->user()?->hasAnyPermission(['ai.assistant', 'ai.studio']))
                    <a href="{{ route('ai.index') }}" class="px-3 py-1.5 rounded-lg bg-[#F5F7FA] hover:bg-[#0066FF] hover:text-white text-xs text-[#0B0F14] border border-[#E2E8F0] transition-colors">
                        🤖 IVOSPHERE AI
                    </a>
                    @endif
                    <a href="{{ route('commercial.invoices.index') }}" class="px-3 py-1.5 rounded-lg bg-[#F5F7FA] hover:bg-[#0066FF] hover:text-white text-xs text-[#0B0F14] border border-[#E2E8F0] transition-colors">
                        📄 Factures Récentes
                    </a>
                    <a href="{{ route('contracts.index') }}" class="px-3 py-1.5 rounded-lg bg-[#F5F7FA] hover:bg-[#0066FF] hover:text-white text-xs text-[#0B0F14] border border-[#E2E8F0] transition-colors">
                        📑 Contrats Actifs
                    </a>
                    <a href="{{ route('client-portal.index') }}" class="px-3 py-1.5 rounded-lg bg-[#F5F7FA] hover:bg-[#0066FF] hover:text-white text-xs text-[#0B0F14] border border-[#E2E8F0] transition-colors">
                        📱 Portail Client
                    </a>
                </div>
            </div>

            <!-- Results container -->
            <div x-show="query.trim().length >= 2" x-ref="results" class="max-h-[26rem] overflow-y-auto overscroll-contain px-3 py-3">
                {{-- Chargement --}}
                <template x-if="isLoading && totalCount === 0">
                    <div class="space-y-2 p-1">
                        <template x-for="i in 4" :key="i">
                            <div class="flex animate-pulse items-center gap-3 rounded-2xl p-2.5">
                                <div class="h-10 w-10 rounded-xl bg-slate-100"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="h-3 w-1/2 rounded bg-slate-100"></div>
                                    <div class="h-2.5 w-1/3 rounded bg-slate-100"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Aucun résultat --}}
                <template x-if="totalCount === 0 && !isLoading">
                    <div class="flex flex-col items-center py-12 text-center">
                        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM8 10h4"/></svg>
                        </span>
                        <p class="mt-3 text-sm font-semibold text-[#0B0F14]">Aucun résultat</p>
                        <p class="mt-1 text-xs text-slate-500">Rien ne correspond à « <span class="font-medium text-slate-700" x-text="query"></span> ». Essayez un autre mot-clé.</p>
                    </div>
                </template>

                {{-- Résultats groupés --}}
                <template x-for="(items, category) in results" :key="category">
                    <section class="mb-3 last:mb-0">
                        <div class="sticky top-0 z-10 flex items-center gap-2 bg-white/95 px-2 pb-1.5 pt-1 backdrop-blur">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500" x-text="category"></span>
                            <span class="rounded-full bg-slate-100 px-1.5 text-[10px] font-semibold text-slate-500" x-text="items.length"></span>
                            <span class="h-px flex-1 bg-slate-100"></span>
                        </div>
                        <div class="space-y-1">
                            <template x-for="item in items" :key="item.url">
                                <a
                                    :href="item.url"
                                    @mouseenter="selectedIndex = indexOf(item)"
                                    :data-selected="indexOf(item) === selectedIndex"
                                    :class="indexOf(item) === selectedIndex ? 'bg-[#0066FF]/[0.06] ring-1 ring-[#0066FF]/25' : 'hover:bg-slate-50'"
                                    class="group flex items-center gap-3 rounded-2xl p-2.5 transition-colors"
                                >
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" :class="styleFor(category).tile">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="styleFor(category).icon"/></svg>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-[#0B0F14]" x-html="highlight(item.title)"></p>
                                        <p class="truncate text-xs text-slate-500" x-html="highlight(item.subtitle)"></p>
                                    </div>
                                    <span class="hidden shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-semibold ring-1 ring-inset sm:inline-flex" :class="styleFor(category).badge" x-text="item.badge"></span>
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full transition-all"
                                          :class="indexOf(item) === selectedIndex ? 'bg-[#0066FF] text-white' : 'text-slate-300 group-hover:text-slate-500'">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                </a>
                            </template>
                        </div>
                    </section>
                </template>
            </div>

            <!-- Footer Help -->
            <div class="flex items-center justify-between gap-3 border-t border-[#E2E8F0] bg-[#F5F7FA] px-5 py-3 text-[11px] text-slate-500">
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1.5">
                        <kbd class="inline-flex h-5 min-w-5 items-center justify-center rounded-md border border-slate-200 bg-white px-1 text-[10px] font-semibold shadow-xs">↑</kbd>
                        <kbd class="inline-flex h-5 min-w-5 items-center justify-center rounded-md border border-slate-200 bg-white px-1 text-[10px] font-semibold shadow-xs">↓</kbd>
                        Naviguer
                    </span>
                    <span class="flex items-center gap-1.5">
                        <kbd class="inline-flex h-5 min-w-5 items-center justify-center rounded-md border border-slate-200 bg-white px-1 text-[10px] font-semibold shadow-xs">↵</kbd>
                        Ouvrir
                    </span>
                    <span class="hidden items-center gap-1.5 sm:flex">
                        <kbd class="inline-flex h-5 items-center justify-center rounded-md border border-slate-200 bg-white px-1.5 text-[10px] font-semibold shadow-xs">Esc</kbd>
                        Fermer
                    </span>
                </div>
                <span x-show="query.trim().length >= 2 && totalCount > 0" class="font-medium text-slate-600"><span x-text="totalCount"></span> résultat(s)</span>
                <span x-show="query.trim().length < 2" class="hidden sm:inline">Recherche globale</span>
            </div>
        </div>
    </div>
</div>

<script>
    function spotlightSearch() {
        return {
            isOpen: false,
            query: '',
            results: {},
            totalCount: 0,
            isLoading: false,
            selectedIndex: 0,
            flatResults: [],
            openModal() {
                this.isOpen = true;
                this.$nextTick(() => {
                    this.$refs.searchInput.focus();
                });
            },
            closeModal() {
                this.isOpen = false;
                this.query = '';
                this.results = {};
                this.flatResults = [];
                this.totalCount = 0;
            },
            async performSearch() {
                if (this.query.trim().length < 2) {
                    this.results = {};
                    this.flatResults = [];
                    this.totalCount = 0;
                    return;
                }
                this.isLoading = true;
                try {
                    const response = await fetch(`{{ route('api.search') }}?q=${encodeURIComponent(this.query)}`);
                    const data = await response.json();
                    this.results = data.results || {};
                    this.totalCount = data.count || 0;
                
                    // Flatten for arrow navigation
                    let flat = [];
                    Object.values(this.results).forEach(group => {
                        flat = flat.concat(group);
                    });
                    this.flatResults = flat;
                    this.selectedIndex = 0;
                } catch (e) {
                    console.error('Search error:', e);
                } finally {
                    this.isLoading = false;
                }
            },
            categoryStyles: {
                'Clients': { tile: 'bg-sky-50 text-sky-600', badge: 'bg-sky-50 text-sky-700 ring-sky-200', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
                'Factures': { tile: 'bg-emerald-50 text-emerald-600', badge: 'bg-emerald-50 text-emerald-700 ring-emerald-200', icon: 'M9 14l2 2 4-4M7 3h7l5 5v11a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z' },
                'Devis': { tile: 'bg-violet-50 text-violet-600', badge: 'bg-violet-50 text-violet-700 ring-violet-200', icon: 'M9 12h6m-6 4h6M7 3h7l5 5v11a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z' },
                'Commandes': { tile: 'bg-amber-50 text-amber-600', badge: 'bg-amber-50 text-amber-700 ring-amber-200', icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z' },
                'Produits': { tile: 'bg-blue-50 text-[#0066FF]', badge: 'bg-blue-50 text-[#0066FF] ring-blue-200', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
                'Contrats': { tile: 'bg-slate-100 text-slate-600', badge: 'bg-slate-100 text-slate-700 ring-slate-200', icon: 'M15.232 5.232l3.536 3.536M9 13l6.232-6.232a2.5 2.5 0 113.536 3.536L12.536 16.536 8 18l1.464-4.536z' },
                'Support': { tile: 'bg-rose-50 text-rose-600', badge: 'bg-rose-50 text-rose-700 ring-rose-200', icon: 'M18.364 5.636a9 9 0 11-12.728 0M12 3v9' },
                'Projets TECH': { tile: 'bg-cyan-50 text-cyan-600', badge: 'bg-cyan-50 text-cyan-700 ring-cyan-200', icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4' },
            },
            styleFor(category) {
                return this.categoryStyles[category] || { tile: 'bg-slate-100 text-slate-600', badge: 'bg-slate-100 text-slate-700 ring-slate-200', icon: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' };
            },
            indexOf(item) {
                return this.flatResults.indexOf(item);
            },
            highlight(text) {
                const escape = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
                const safeText = escape(text);
                const term = this.query.trim();
                if (term.length < 2) {
                    return safeText;
                }
                const pattern = new RegExp('(' + escape(term).replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                return safeText.replace(pattern, '<mark class="rounded bg-yellow-100 px-0.5 text-inherit">$1</mark>');
            },
            scrollToSelected() {
                this.$nextTick(() => {
                    this.$refs.results?.querySelector('[data-selected=true]')?.scrollIntoView({ block: 'nearest' });
                });
            },
            goToSelected() {
                if (this.flatResults.length > 0 && this.flatResults[this.selectedIndex]) {
                    window.location.href = this.flatResults[this.selectedIndex].url;
                }
            }
        };
    }
</script>
