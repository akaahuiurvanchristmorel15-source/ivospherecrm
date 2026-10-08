<div 
    x-data="{
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
        goToSelected() {
            if (this.flatResults.length > 0 && this.flatResults[this.selectedIndex]) {
                window.location.href = this.flatResults[this.selectedIndex].url;
            }
        }
    }"
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
            @keydown.arrow-down.prevent="selectedIndex = (selectedIndex + 1) % (flatResults.length || 1)"
            @keydown.arrow-up.prevent="selectedIndex = (selectedIndex - 1 + flatResults.length) % (flatResults.length || 1)"
            @keydown.enter.prevent="goToSelected()"
            class="mx-auto max-w-2xl w-full transform rounded-2xl bg-white shadow-2xl border border-[#E2E8F0] overflow-hidden transition-all text-[#0B0F14]"
        >
            <!-- Search Header -->
            <div class="relative flex items-center border-b border-[#E2E8F0] px-4 py-3 bg-[#F5F7FA]/50">
                <svg class="w-5 h-5 text-slate-400 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input 
                    x-ref="searchInput"
                    type="text" 
                    x-model="query"
                    @input.debounce.250ms="performSearch()"
                    placeholder="Rechercher client, facture, devis, produit, contrat, ticket..." 
                    class="w-full bg-transparent border-0 text-sm text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:ring-0"
                />
                
                <div class="flex items-center gap-2">
                    <span x-show="isLoading" class="text-xs text-slate-400 animate-pulse">Recherche...</span>
                    <kbd class="px-2 py-0.5 text-[10px] font-semibold text-slate-500 bg-white border border-slate-200 rounded">ESC</kbd>
                </div>
            </div>

            <!-- Quick Suggestions if empty -->
            <div x-show="query.trim().length < 2" class="p-6 text-center">
                <p class="text-xs font-medium text-slate-500 mb-4 uppercase tracking-wider">Accès rapides fréquents</p>
                <div class="flex flex-wrap justify-center gap-2">
                    <a href="{{ route('command-center.index') }}" class="px-3 py-1.5 rounded-lg bg-[#F5F7FA] hover:bg-[#0066FF] hover:text-white text-xs text-[#0B0F14] border border-[#E2E8F0] transition-colors">
                        📊 Command Center Direction
                    </a>
                    <a href="{{ route('ai.index') }}" class="px-3 py-1.5 rounded-lg bg-[#F5F7FA] hover:bg-[#0066FF] hover:text-white text-xs text-[#0B0F14] border border-[#E2E8F0] transition-colors">
                        🤖 IVOSPHERE AI
                    </a>
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
            <div x-show="query.trim().length >= 2" class="max-h-96 overflow-y-auto p-2 divide-y divide-slate-100">
                <template x-if="totalCount === 0 && !isLoading">
                    <div class="py-10 text-center">
                        <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2 text-xs text-slate-500 font-medium">Aucun résultat trouvé pour « <span x-text="query"></span> »</p>
                    </div>
                </template>

                <template x-for="(items, category) in results" :key="category">
                    <div class="py-2">
                        <div class="px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-400" x-text="category"></div>
                        <div class="space-y-0.5">
                            <template x-for="item in items" :key="item.url">
                                <a 
                                    :href="item.url" 
                                    class="group flex items-center justify-between px-3 py-2 rounded-xl text-xs hover:bg-[#F5F7FA] transition-colors"
                                >
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#0066FF] shrink-0"></span>
                                        <div class="truncate">
                                            <p class="font-medium text-[#0B0F14] group-hover:text-[#0066FF] truncate" x-text="item.title"></p>
                                            <p class="text-slate-500 text-[11px] truncate" x-text="item.subtitle"></p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-600 font-medium shrink-0 ml-2" x-text="item.badge"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer Help -->
            <div class="px-4 py-2.5 bg-[#F5F7FA] border-t border-[#E2E8F0] flex items-center justify-between text-[11px] text-slate-500">
                <div class="flex items-center gap-3">
                    <span><kbd class="px-1 py-0.5 bg-white border border-slate-200 rounded text-[10px]">↑</kbd> <kbd class="px-1 py-0.5 bg-white border border-slate-200 rounded text-[10px]">↓</kbd> Naviguer</span>
                    <span><kbd class="px-1 py-0.5 bg-white border border-slate-200 rounded text-[10px]">↵</kbd> Ouvrir</span>
                </div>
                <span>Recherche globale universelle</span>
            </div>
        </div>
    </div>
</div>
