@props([])

@if(!request()->routeIs('commercial.pos.*'))
@php $isTenantSpace = auth()->check() && ! auth()->user()->belongsToMainTenant(); @endphp
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
            @if(\App\Support\TenantAccess::allows('rh.portal.index'))
            <a href="{{ route('rh.portal.index') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-[#0066FF] text-white flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Mon Espace</p>
                    <p class="text-[10px] text-[#64748B] truncate">Pointage & Note</p>
                </div>
            </a>
            @endif

            @if(\App\Support\TenantAccess::allows('rh.attendance.scanner'))
            <a href="{{ route('rh.attendance.scanner') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200/60">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Pointage QR</p>
                    <p class="text-[10px] text-[#64748B] truncate">Borne d'accueil</p>
                </div>
            </a>
            @endif

            @if(\App\Support\TenantAccess::allows('commercial.quotations.create'))
            <a href="{{ route('commercial.quotations.create') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-[#0066FF] flex items-center justify-center shrink-0 border border-blue-200/60">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Nouveau Devis</p>
                    <p class="text-[10px] text-[#64748B] truncate">Proposition client</p>
                </div>
            </a>
            @endif

            @if(\App\Support\TenantAccess::allows('commercial.pos.index'))
            <a href="{{ route('commercial.pos.index') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200/60">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Vente POS</p>
                    <p class="text-[10px] text-[#64748B] truncate">Caisse comptoir</p>
                </div>
            </a>
            @endif

            @if(\App\Support\TenantAccess::allows('commercial.customers.create'))
            <a href="{{ route('commercial.customers.create') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-slate-100 text-[#0B0F14] flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Nouveau Client</p>
                    <p class="text-[10px] text-[#64748B] truncate">Fiche tiers</p>
                </div>
            </a>
            @endif

            @if(\App\Support\TenantAccess::allows('stock.products.create') && auth()->user()->hasPermission('products.create'))
            <a href="{{ route('stock.products.create') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 border border-amber-200/60">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Ajout Produit</p>
                    <p class="text-[10px] text-[#64748B] truncate">Gestion catalogue</p>
                </div>
            </a>
            @endif

            @if(\App\Support\TenantAccess::allows('support.create'))
            <a href="{{ route('support.create') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0 border border-teal-200/60">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Ticket Support</p>
                    <p class="text-[10px] text-[#64748B] truncate">Assistance</p>
                </div>
            </a>
            @endif

            @if(\App\Support\TenantAccess::allows('finance.expenses.create'))
            <a href="{{ route('finance.expenses.create') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-700 flex items-center justify-center shrink-0 border border-rose-200/60">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-[#0B0F14] truncate text-[11px]">Nouvelle Dépense</p>
                    <p class="text-[10px] text-[#64748B] truncate">Note de frais / achat</p>
                </div>
            </a>
            @endif
        </div>
    </div>

    @php
        $homeActive = request()->routeIs('dashboard', 'space.home') && ! request('domain');
        $salesActive = request()->routeIs('commercial.orders.*', 'commercial.quotations.*');
        $customersActive = request()->routeIs('commercial.customers.*', 'commercial.prospects.*');
        $menuActive = request()->routeIs('menu.*', 'space.subscription');
        $tabItem = 'relative flex h-12 flex-1 items-center justify-center rounded-full transition-colors active:scale-95';
        $tabIdle = 'text-[#A1A1AA] hover:text-[#52525B] dark:text-white/35 dark:hover:text-white/60 night:text-white/35 night:hover:text-white/60';
        $tabActive = 'text-[#18181B] dark:text-white night:text-white';
    @endphp

    <!-- Barre d'onglets mobile : pilule flottante + bouton de création séparé -->
    <nav
        class="fixed inset-x-0 bottom-0 z-40 flex items-center gap-3 px-4 pt-2 select-none pointer-events-none"
        style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));"
        aria-label="Navigation principale mobile"
    >
        <div class="pointer-events-auto flex h-16 flex-1 items-center rounded-full border border-white/80 bg-[#F4F4F5]/90 px-2 shadow-[0_12px_32px_-12px_rgba(0,0,0,0.28),inset_0_1px_0_rgba(255,255,255,0.9)] backdrop-blur-xl dark:border-white/10 dark:bg-[#111827]/90 dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,0.7)] night:border-white/10 night:bg-white/[0.08] night:shadow-[0_12px_32px_-12px_rgba(0,0,0,0.8)]">
            <a href="{{ $isTenantSpace ? route('space.home') : route('dashboard') }}" class="{{ $tabItem }} {{ $homeActive ? $tabActive : $tabIdle }}" aria-label="Accueil" @if($homeActive) aria-current="page" @endif>
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.689z"/><path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z"/></svg>
            </a>

            @if(\App\Support\TenantAccess::allows('commercial.orders.index'))
            <a href="{{ route('commercial.orders.index') }}" class="{{ $tabItem }} {{ $salesActive ? $tabActive : $tabIdle }}" aria-label="Ventes et commandes" @if($salesActive) aria-current="page" @endif>
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.375 2.625a1.875 1.875 0 00-1.875 1.875v15c0 1.036.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875v-15c0-1.036-.84-1.875-1.875-1.875h-.75zM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 01-1.875-1.875V8.625zM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 013 19.875v-6.75z"/></svg>
            </a>
            @endif

            @if(\App\Support\TenantAccess::allows('commercial.customers.index'))
            <a href="{{ route('commercial.customers.index') }}" class="{{ $tabItem }} {{ $customersActive ? $tabActive : $tabIdle }}" aria-label="Clients et prospects" @if($customersActive) aria-current="page" @endif>
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd"/></svg>
            </a>
            @endif

            <a href="{{ $isTenantSpace ? route('space.subscription') : route('menu.index') }}" class="{{ $tabItem }} {{ $menuActive ? $tabActive : $tabIdle }}" aria-label="{{ $isTenantSpace ? 'Mon abonnement' : 'Menu général' }}" @if($menuActive) aria-current="page" @endif>
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 6a3 3 0 013-3h2.25a3 3 0 013 3v2.25a3 3 0 01-3 3H6a3 3 0 01-3-3V6zm9.75 0a3 3 0 013-3H18a3 3 0 013 3v2.25a3 3 0 01-3 3h-2.25a3 3 0 01-3-3V6zM3 15.75a3 3 0 013-3h2.25a3 3 0 013 3V18a3 3 0 01-3 3H6a3 3 0 01-3-3v-2.25zm9.75 0a3 3 0 013-3H18a3 3 0 013 3V18a3 3 0 01-3 3h-2.25a3 3 0 01-3-3v-2.25z" clip-rule="evenodd"/></svg>
            </a>
        </div>

        <button
            type="button"
            @click="quickActionsOpen = ! quickActionsOpen"
            class="pointer-events-auto flex h-16 w-16 shrink-0 items-center justify-center rounded-full border border-white/80 bg-[#F4F4F5]/90 text-[#3F3F46] shadow-[0_12px_32px_-12px_rgba(0,0,0,0.28),inset_0_1px_0_rgba(255,255,255,0.9)] backdrop-blur-xl transition active:scale-95 dark:border-white/10 dark:bg-[#111827]/90 dark:text-white/80 night:border-white/10 night:bg-white/[0.08] night:text-white/80"
            :class="quickActionsOpen && 'bg-[#18181B]! text-white! dark:bg-white! dark:text-[#18181B]! night:bg-white! night:text-black!'"
            title="Création rapide"
            aria-label="Création rapide"
        >
            <svg class="h-6 w-6 transition-transform duration-200" :class="quickActionsOpen && 'rotate-45'" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
        </button>
    </nav>
</div>
@endif
