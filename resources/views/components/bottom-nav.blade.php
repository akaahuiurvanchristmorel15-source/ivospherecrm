@props([])

@if(!request()->routeIs('commercial.pos.*'))
<div x-data="{ quickActionsOpen: false }" @open-quick-actions.window="quickActionsOpen = true" class="md:hidden">
    <!-- Backdrop pour la feuille d'actions mobile -->
    <div 
        x-show="quickActionsOpen" 
        x-cloak
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="quickActionsOpen = false" 
        class="fixed inset-0 bg-[#0B0F14]/50 backdrop-blur-xs z-50"
        style="display: none;"
    ></div>

    <!-- Feuille d'actions rapides glissante (Mobile Bottom Sheet) -->
    <div 
        x-show="quickActionsOpen" 
        x-cloak
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="fixed bottom-0 inset-x-0 bg-white rounded-t-3xl border-t border-[#E2E8F0] p-5 z-50 select-none max-h-[85vh] overflow-y-auto"
        style="display: none; padding-bottom: max(1.5rem, env(safe-area-inset-bottom));"
    >
        <!-- Poignée et En-tête -->
        <div class="h-1 w-10 rounded-full bg-slate-200 mx-auto mb-4"></div>
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-[#E2E8F0]">
            <div>
                <h3 class="text-sm font-semibold text-[#0B0F14]">Actions rapides</h3>
                <p class="text-[11px] text-[#64748B]">Création et opérations express</p>
            </div>
            <button 
                type="button" 
                @click="quickActionsOpen = false" 
                class="p-1.5 -mr-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100"
                aria-label="Fermer"
            >
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Grille de raccourcis 2 colonnes -->
        <div class="grid grid-cols-2 gap-2 text-xs">
            <a href="{{ route('rh.portal.index') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-[#0066FF] text-white flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Mon Espace</p>
                    <p class="text-[10px] text-[#64748B] truncate">Pointage & Note</p>
                </div>
            </a>

            <a href="{{ route('rh.attendance.scanner') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200/60">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Pointage QR</p>
                    <p class="text-[10px] text-[#64748B] truncate">Borne d'accueil</p>
                </div>
            </a>

            <a href="{{ route('commercial.quotations.create') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-[#0066FF] flex items-center justify-center shrink-0 border border-blue-200/60">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Nouveau Devis</p>
                    <p class="text-[10px] text-[#64748B] truncate">Proposition client</p>
                </div>
            </a>

            <a href="{{ route('commercial.pos.index') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200/60">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Vente POS</p>
                    <p class="text-[10px] text-[#64748B] truncate">Caisse comptoir</p>
                </div>
            </a>

            <a href="{{ route('commercial.customers.create') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-slate-100 text-[#0B0F14] flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Nouveau Client</p>
                    <p class="text-[10px] text-[#64748B] truncate">Fiche tiers</p>
                </div>
            </a>

            <a href="{{ route('stock.products.create') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 border border-amber-200/60">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Ajout Produit</p>
                    <p class="text-[10px] text-[#64748B] truncate">Gestion catalogue</p>
                </div>
            </a>

            <a href="{{ route('support.create') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0 border border-teal-200/60">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Ticket Support</p>
                    <p class="text-[10px] text-[#64748B] truncate">Assistance</p>
                </div>
            </a>

            <a href="{{ route('finance.expenses.create') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-700 flex items-center justify-center shrink-0 border border-rose-200/60">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Nouvelle Dépense</p>
                    <p class="text-[10px] text-[#64748B] truncate">Note de frais / achat</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Mobile Bottom Navigation Bar (Minimalist & Crisp) -->
    <nav 
        class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-[#E2E8F0] px-2 py-1 grid grid-cols-5 items-center select-none"
        style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));"
        aria-label="Navigation principale mobile"
    >
        <!-- 1. Accueil / Dashboard -->
        <a 
            href="{{ route('dashboard') }}" 
            class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('dashboard') && !request('domain') ? 'text-[#0066FF]' : 'text-slate-400 hover:text-[#0B0F14]' }}"
            aria-label="Tableau de bord"
        >
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('dashboard') && !request('domain') ? 'font-semibold text-[#0066FF]' : 'font-medium' }}">Accueil</span>
        </a>

        <!-- 2. Ventes / Commandes / Devis -->
        <a 
            href="{{ route('commercial.orders.index') }}" 
            class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('commercial.orders.*') || request()->routeIs('commercial.quotations.*') ? 'text-[#0066FF]' : 'text-slate-400 hover:text-[#0B0F14]' }}"
            aria-label="Ventes et Commandes"
        >
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('commercial.orders.*') || request()->routeIs('commercial.quotations.*') ? 'font-semibold text-[#0066FF]' : 'font-medium' }}">Ventes</span>
        </a>

        <!-- 3. Bouton Central Action Express (+) -->
        <div class="flex flex-col items-center justify-center -mt-4">
            <button 
                type="button" 
                @click="quickActionsOpen = !quickActionsOpen" 
                class="w-11 h-11 rounded-full bg-[#0066FF] hover:bg-[#0052cc] text-white flex items-center justify-center active:scale-95 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]"
                title="Création rapide"
                aria-label="Création rapide"
            >
                <svg class="w-5 h-5 transition-transform duration-200" :class="quickActionsOpen ? 'rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
            <span class="text-[9px] text-[#64748B] font-medium mt-0.5">Créer</span>
        </div>

        <!-- 4. Contacts / Clients -->
        <a 
            href="{{ route('commercial.customers.index') }}" 
            class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('commercial.customers.*') || request()->routeIs('commercial.prospects.*') ? 'text-[#0066FF]' : 'text-slate-400 hover:text-[#0B0F14]' }}"
            aria-label="Clients et Contacts"
        >
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('commercial.customers.*') || request()->routeIs('commercial.prospects.*') ? 'font-semibold text-[#0066FF]' : 'font-medium' }}">Clients</span>
        </a>

        <!-- 5. Hub / Menu des 8 Pôles -->
        <a 
            href="{{ route('menu.index') }}" 
            class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('menu.*') ? 'text-[#0066FF]' : 'text-slate-400 hover:text-[#0B0F14]' }}"
            aria-label="Menu Général"
        >
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('menu.*') ? 'font-semibold text-[#0066FF]' : 'font-medium' }}">Menu</span>
        </a>
    </nav>
</div>
@endif
