@props([])

@php
    $unreadCount = $topbarUnreadCount ?? 0;
    $notificationsList = $topbarNotifications ?? collect();
    $isAdmin = auth()->user()?->isAdmin();

    // Pôles ERP : source unique pour le dropdown desktop et le menu mobile
    $poles = [
        ['route' => 'commercial.index', 'label' => 'Commercial & CRM',  'desc' => 'Ventes, devis, clients', 'tint' => 'text-[#0066FF]',   'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
        ['route' => 'finance.index',    'label' => 'Finance',           'desc' => 'Comptes, immobilisations', 'tint' => 'text-emerald-600', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['route' => 'stock.index',      'label' => 'Stocks',            'desc' => 'Articles, entrepôts',    'tint' => 'text-amber-600',   'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        ['route' => 'rh.index',         'label' => 'Ressources Humaines','desc' => 'Pointage, plannings',  'tint' => 'text-purple-600',  'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['route' => 'print.index',      'label' => 'Print',             'desc' => 'Imprimerie & BAT',       'tint' => 'text-cyan-600',    'icon' => 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z'],
        ['route' => 'sport.index',      'label' => 'Sport',             'desc' => 'Flocage & clubs',        'tint' => 'text-rose-500',    'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
        ['route' => 'tech.index',       'label' => 'Tech',              'desc' => 'Apps & logiciels',       'tint' => 'text-indigo-600',  'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
    ];
    $poles[] = $isAdmin
        ? ['route' => 'command-center.index', 'label' => 'Direction',     'desc' => 'Cockpit exécutif',     'tint' => 'text-slate-900', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z']
        : ['route' => 'rh.portal.index',      'label' => 'Mon Espace RH', 'desc' => 'Pointage, tâches',    'tint' => 'text-[#0066FF]', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'];

    // Espace abonné : seulement les modules de son plan (et son abonnement).
    $isTenantSpace = auth()->check() && ! auth()->user()->belongsToMainTenant();
    if ($isTenantSpace) {
        $poles = [
            ['route' => 'space.home',                'label' => 'Accueil',         'desc' => 'Mon espace',            'tint' => 'text-[#0066FF]',   'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['route' => 'commercial.pos.index',      'label' => 'Vente POS',       'desc' => 'Caisse & encaissement', 'tint' => 'text-[#0066FF]',   'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
            ['route' => 'commercial.invoices.index', 'label' => 'Facturation',     'desc' => 'Devis, factures',       'tint' => 'text-emerald-600', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['route' => 'stock.index',               'label' => 'Stock',           'desc' => 'Articles, entrepôts',   'tint' => 'text-amber-600',   'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['route' => 'admin.users.index',         'label' => 'Utilisateurs',    'desc' => 'Comptes de l\'équipe',  'tint' => 'text-purple-600',  'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ['route' => 'admin.logs.index',          'label' => 'Journal',         'desc' => 'Sécurité & activité',   'tint' => 'text-slate-700',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['route' => 'space.subscription',        'label' => 'Mon abonnement',  'desc' => 'Plan, échéance, PDF',   'tint' => 'text-rose-500',    'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
        ];
    }
    $poles = array_values(array_filter($poles, fn (array $p): bool => \App\Support\TenantAccess::allows($p['route'])));


    $navigation = app(\App\Services\NavigationService::class);
    $currentModuleKey = $navigation->currentModuleKey();
    $currentModule = $currentModuleKey ? $navigation->module($currentModuleKey) : null;

    // Rattache chaque pôle à ses sous-pages (config/navigation.php) pour le menu mobile
    $poles = array_map(function (array $p) use ($navigation, $currentModuleKey, $isTenantSpace): array {
        $key = strstr($p['route'], '.', true);
        $p['children'] = $isTenantSpace ? [] : ($navigation->module($key)['items'] ?? []);
        $p['current'] = $key === $currentModuleKey;

        return $p;
    }, $poles);
@endphp

<header
    x-data="{ mobileMenu: false }"
    x-effect="document.body.classList.toggle('overflow-hidden', mobileMenu)"
    @keydown.escape.window="mobileMenu = false"
    class="sticky top-0 z-30 flex items-center justify-between h-14 md:h-16 px-4 sm:px-6 md:px-8 bg-white/90 backdrop-blur-xl border-b border-slate-200/70"
>
    {{-- ───────── Gauche : burger (mobile) · logo · navigation (desktop) ───────── --}}
    <div class="flex items-center gap-3 md:gap-5 min-w-0 flex-1">

        {{-- Burger mobile --}}
        <button
            type="button"
            @click="mobileMenu = !mobileMenu"
            class="md:hidden -ml-1.5 p-2 rounded-full text-slate-700 active:bg-slate-100 transition-colors"
            :aria-expanded="mobileMenu"
            aria-label="Ouvrir le menu"
        >
            <svg x-show="!mobileMenu" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h10"/></svg>
            <svg x-show="mobileMenu" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/></svg>
        </button>

        {{-- Logo --}}
        <a href="{{ $isTenantSpace ? route('space.home') : route('dashboard') }}" class="flex items-center shrink-0 group rounded-lg dark:bg-white dark:px-2 dark:py-1" title="Tableau de bord">
            <img src="{{ asset('images/logo-emblem.png') }}" alt="IVOSPHERE" width="40" height="36" class="sm:hidden h-9 w-auto transition-transform group-hover:scale-105">
            <img src="{{ asset('images/logo-horizontal.png') }}" alt="IVOSPHERE" width="147" height="40" class="hidden sm:block h-9 lg:h-10 w-auto transition-transform group-hover:scale-[1.03]">
        </a>

        {{-- Menu pôles (desktop) --}}
        <div class="relative hidden md:block" x-data="{ menuOpen: false }">
            <button
                type="button"
                @click="menuOpen = !menuOpen"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ request()->routeIs('menu.*') ? 'bg-slate-900 text-white' : ($currentModule ? 'bg-blue-50/70 text-[#0066FF] hover:bg-blue-50' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900') }}"
                :aria-expanded="menuOpen"
                aria-label="Pôles et modules"
            >
                <span class="max-w-40 truncate">{{ $currentModule['label'] ?? 'Menu' }}</span>
                <svg class="w-3 h-3 transition-transform duration-150" :class="menuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div
                x-show="menuOpen" x-cloak @click.outside="menuOpen = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute left-0 mt-3 w-96 rounded-2xl bg-white border border-slate-200/80 shadow-[0_20px_40px_-12px_rgba(15,23,42,0.12)] p-2 z-50"
                style="display:none"
            >
                <div class="grid grid-cols-2">
                    @foreach($poles as $p)
                        <a href="{{ route($p['route']) }}" @if($p['current']) aria-current="true" @endif class="flex items-center gap-3 p-2.5 rounded-xl transition-colors {{ $p['current'] ? 'bg-blue-50/70' : 'hover:bg-slate-50' }}">
                            <svg class="w-[18px] h-[18px] shrink-0 {{ $p['tint'] }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $p['icon'] }}"/></svg>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-900 truncate">{{ $p['label'] }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ $p['desc'] }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
                @unless($isTenantSpace)
                <a href="{{ route('menu.index') }}" class="mt-1 flex items-center justify-center gap-1.5 py-2.5 border-t border-slate-100 text-xs font-medium text-slate-600 hover:text-[#0066FF] transition-colors">
                    Menu Général
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endunless
            </div>
        </div>

        {{-- Liens rapides (desktop large) --}}
        <nav class="hidden xl:flex items-center gap-0.5 text-xs font-medium text-slate-500">
            @php
                $links = [['dashboard', 'Dashboard', request()->routeIs('dashboard') && !request('domain')]];
                if ($isAdmin) $links[] = ['command-center.index', 'Direction', request()->routeIs('command-center.*')];
                $links[] = ['ai.index', 'IA Studio', request()->routeIs('ai.*')];
                $links[] = ['analytics.index', 'Analytics', request()->routeIs('analytics.*')];
                $links = $isTenantSpace ? [] : $links;
            @endphp
            @foreach($links as [$r, $label, $active])
                <a href="{{ route($r) }}" class="px-3 py-1.5 rounded-full whitespace-nowrap transition-colors {{ $active ? 'text-[#0066FF] bg-blue-50/70' : 'hover:text-slate-900 hover:bg-slate-100' }}">{{ $label }}</a>
            @endforeach
        </nav>

        {{-- Recherche (desktop) --}}
        <button
            type="button"
            @click="$dispatch('open-spotlight')"
            x-data="{ shortcut: /Mac|iPhone|iPad/.test(navigator.platform) ? '⌘ K' : 'Ctrl K' }"
            class="group hidden md:flex items-center gap-3 w-full max-w-md h-11 pl-1.5 pr-2 rounded-full bg-white border border-slate-200 shadow-sm text-sm text-slate-400 transition-all hover:border-[#0066FF]/40 hover:shadow-md focus:outline-none focus-visible:border-[#0066FF] focus-visible:ring-4 focus-visible:ring-[#0066FF]/15"
            aria-label="Ouvrir la recherche globale"
        >
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#0066FF]/10 text-[#0066FF] transition-colors group-hover:bg-[#0066FF] group-hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <span class="flex-1 truncate text-left">Rechercher un client, une facture, un produit…</span>
            <kbd class="hidden lg:inline-flex shrink-0 items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-500" x-text="shortcut">Ctrl K</kbd>
        </button>
    </div>

    {{-- ───────── Droite : actions ───────── --}}
    <div class="flex items-center gap-0.5 sm:gap-1 shrink-0 ml-2">

        {{-- Recherche (mobile) --}}
        <button type="button" @click="$dispatch('open-spotlight')" class="md:hidden flex h-9 w-9 items-center justify-center rounded-full bg-[#0066FF]/10 text-[#0066FF] active:bg-[#0066FF] active:text-white transition-colors" aria-label="Recherche">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </button>

        {{-- Aide + Messages (desktop) --}}
        @unless($isTenantSpace)
        <a href="{{ route('support.index') }}" class="hidden md:flex p-2 rounded-full text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors" title="Support & Aide">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </a>
        <a href="{{ route('messages.index') }}" class="hidden md:flex p-2 rounded-full text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors" title="Messagerie interne">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        </a>
        @endunless

        {{-- Installer l'application (PWA) : visible sur mobile ou quand le navigateur le propose, masqué une fois installée --}}
        <div
            class="md:relative"
            x-data="pwaInstall"
            x-show="visible"
            x-cloak
            style="display:none"
            @pwa-install-available.window="canPrompt = true"
            @pwa-installed.window="installed = true"
            @click.outside="helpOpen = false"
            @keydown.escape.window="helpOpen = false"
        >
            <button
                type="button"
                @click="install()"
                class="flex items-center gap-1.5 h-9 px-2.5 sm:px-3 rounded-full bg-[#0066FF] text-white text-xs font-semibold shadow-sm hover:bg-[#0052CC] active:bg-[#0040A8] transition-colors focus:outline-none focus-visible:ring-4 focus-visible:ring-[#0066FF]/25"
                aria-label="Installer l'application"
                title="Installer l'application sur cet appareil"
                :aria-expanded="canPrompt ? null : helpOpen.toString()"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span class="hidden sm:inline">Installer</span>
            </button>

            <div
                x-show="helpOpen"
                x-transition.origin.top.right
                x-cloak
                style="display:none"
                class="absolute right-2 left-2 md:left-auto md:right-0 top-full mt-2 md:w-72 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl z-50"
                role="dialog"
                aria-label="Installer l'application"
            >
                <p class="text-sm font-semibold text-slate-900">Installer IVOSPHERE</p>

                <template x-if="ios">
                    <ol class="mt-2 space-y-1.5 text-xs text-slate-600 list-decimal pl-4">
                        <li>Ouvrez cette page dans <strong>Safari</strong>.</li>
                        <li>Touchez le bouton <strong>Partager</strong> <svg class="inline w-3.5 h-3.5 -mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12H6a2 2 0 00-2 2v5a2 2 0 002 2h12a2 2 0 002-2v-5a2 2 0 00-2-2h-2M12 3v12m0-12L8 7m4-4l4 4"/></svg>.</li>
                        <li>Choisissez <strong>Sur l'écran d'accueil</strong>.</li>
                    </ol>
                </template>

                <template x-if="!ios">
                    <ol class="mt-2 space-y-1.5 text-xs text-slate-600 list-decimal pl-4">
                        <li>Ouvrez le menu <strong>⋮</strong> du navigateur (Chrome conseillé).</li>
                        <li>Choisissez <strong>Installer l'application</strong> ou <strong>Ajouter à l'écran d'accueil</strong>.</li>
                    </ol>
                </template>

                <p x-show="!secure" class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-[11px] text-amber-800">
                    Connexion non sécurisée : l'installation complète nécessite d'accéder au site en <strong>https://</strong>.
                </p>
            </div>
        </div>

        {{-- Thème jour / nuit --}}
        <button
            type="button"
            x-data="{
                isDark: document.documentElement.classList.contains('dark'),
                toggleTheme() {
                    this.isDark = !this.isDark;
                    document.documentElement.classList.toggle('dark', this.isDark);
                    try { localStorage.setItem('ivo-theme', this.isDark ? 'dark' : 'light'); } catch (e) {}
                }
            }"
            @click="toggleTheme()"
            class="p-2 rounded-full text-slate-500 hover:text-slate-900 hover:bg-slate-100 active:bg-slate-100 transition-colors"
            :aria-label="isDark ? 'Passer en mode jour' : 'Passer en mode nuit'"
        >
            <svg x-show="!isDark" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            <svg x-show="isDark" x-cloak class="w-[18px] h-[18px] text-amber-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </button>

        {{-- Notifications --}}
        @unless($isTenantSpace)
        <div class="md:relative" x-data="{ notifOpen: false }">
            <button
                type="button"
                @click="notifOpen = !notifOpen"
                class="relative p-2 rounded-full text-slate-500 hover:text-slate-900 hover:bg-slate-100 active:bg-slate-100 transition-colors"
                title="Notifications ({{ $unreadCount }} non lue{{ $unreadCount > 1 ? 's' : '' }})"
                aria-label="Centre de notifications"
            >
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                @if($unreadCount > 0)
                    <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-[#0066FF] ring-2 ring-white"></span>
                @endif
            </button>

            {{-- Panneau : plein écran en largeur sur mobile, flottant sur desktop --}}
            <div
                x-show="notifOpen" x-cloak @click.outside="notifOpen = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-x-3 top-16 md:absolute md:inset-x-auto md:right-0 md:top-full md:mt-3 md:w-96 rounded-2xl bg-white border border-slate-200/80 shadow-[0_20px_40px_-12px_rgba(15,23,42,0.15)] z-50 overflow-hidden"
                style="display:none"
            >
                <div class="px-4 py-3 flex items-center justify-between border-b border-slate-100">
                    <span class="text-xs font-semibold text-slate-900">Notifications</span>
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                            @csrf
                            <button type="submit" class="text-[11px] font-medium text-[#0066FF] hover:underline">Tout marquer lu</button>
                        </form>
                    @endif
                </div>

                <div class="divide-y divide-slate-50 max-h-[60vh] md:max-h-80 overflow-y-auto">
                    @forelse($notificationsList as $notif)
                        @php
                            $prioDot = match($notif->priority) {
                                'urgente' => 'bg-rose-500',
                                'haute' => 'bg-amber-500',
                                default => 'bg-[#0066FF]',
                            };
                        @endphp
                        <div class="px-4 py-3 flex items-start gap-3 group hover:bg-slate-50/70 transition-colors">
                            <span class="w-1.5 h-1.5 rounded-full {{ $prioDot }} mt-1.5 shrink-0"></span>
                            <div class="min-w-0 flex-1">
                                <a href="{{ $notif->action_url ?: route('notifications.index') }}" class="block text-xs font-medium text-slate-900 hover:text-[#0066FF] line-clamp-1 transition-colors">{{ $notif->title }}</a>
                                @if($notif->description)
                                    <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">{{ $notif->description }}</p>
                                @endif
                                <div class="flex items-center justify-between mt-1 text-[10px] text-slate-400">
                                    <span>{{ $notif->created_at->diffForHumans() }}</span>
                                    @if($notif->status === 'active')
                                        <form method="POST" action="{{ route('notifications.read', $notif) }}" class="md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                                            @csrf
                                            <button type="submit" class="text-[#0066FF] hover:underline">Marquer lu</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 px-4 text-center">
                            <svg class="w-5 h-5 mx-auto mb-2 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <p class="text-xs font-medium text-slate-900">Tout est à jour</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Aucune notification en attente.</p>
                        </div>
                    @endforelse
                </div>

                <a href="{{ route('notifications.index') }}" class="block px-4 py-2.5 border-t border-slate-100 text-center text-xs font-medium text-slate-600 hover:text-[#0066FF] transition-colors">Voir tout</a>
            </div>
        </div>

        <div class="h-4 w-px bg-slate-200 mx-1.5 hidden md:block"></div>
        @endunless

        {{-- Profil --}}
        <div class="md:relative" x-data="{ open: false }">
            <button
                type="button"
                @click="open = !open"
                class="flex items-center gap-2 p-0.5 md:pr-2 rounded-full hover:bg-slate-100 transition-colors focus:outline-none"
                aria-label="Menu profil"
            >
                <x-user-avatar :user="auth()->user()" size="sm" />
                <span class="hidden lg:block text-left leading-tight">
                    <span class="block text-xs font-medium text-slate-900">{{ auth()->user()->name ?? 'Utilisateur' }}</span>
                    <span class="block text-[10px] text-slate-400">{{ auth()->user()->primary_role ?? 'Collaborateur' }}</span>
                </span>
            </button>

            <div
                x-show="open" x-cloak @click.outside="open = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-x-3 top-16 md:absolute md:inset-x-auto md:right-0 md:top-full md:mt-3 md:w-64 rounded-2xl bg-white border border-slate-200/80 shadow-[0_20px_40px_-12px_rgba(15,23,42,0.15)] z-50 overflow-hidden text-xs"
                style="display:none"
            >
                <div class="px-4 py-3.5 border-b border-slate-100">
                    <p class="font-medium text-slate-900">{{ auth()->user()->name ?? '' }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                    <span class="inline-block mt-2 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">{{ auth()->user()->primary_role ?? 'Collaborateur' }}</span>
                </div>

                <div class="py-1.5">
                    @if($isTenantSpace)
                    <a href="{{ route('space.subscription') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-[#0066FF] transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Mon abonnement
                    </a>
                    @else
                    <a href="{{ route('rh.portal.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-[#0066FF] transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Mon Espace Collaborateur
                    </a>
                    @endif
                    <a href="{{ route('my-day') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-[#0066FF] transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                        Ma journée
                    </a>
                    <a href="{{ route('password.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-[#0066FF] transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        Mon compte (photo, mot de passe)
                    </a>
                    @unless($isTenantSpace)
                    <a href="{{ route('messages.index') }}" class="md:hidden flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Messagerie interne
                    </a>
                    <a href="{{ route('support.index') }}" class="md:hidden flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Support & Aide
                    </a>
                    @endunless
                    @if($isAdmin && ! $isTenantSpace)
                        <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-[#0066FF] transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Paramètres généraux
                        </a>
                        <a href="{{ route('admin.subscriptions.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-[#0066FF] transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            Abonnements clients
                        </a>
                        <a href="{{ route('admin.logs.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-[#0066FF] transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Journal d'audit
                        </a>
                    @endif
                </div>

                <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-rose-600 hover:bg-rose-50/60 transition-colors text-left">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ───────── Menu mobile plein écran ───────── --}}
    <div
        x-show="mobileMenu" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="md:hidden fixed inset-x-0 top-14 h-[calc(100dvh-3.5rem)] bg-white z-40 overflow-y-auto overscroll-contain"
        style="display:none"
    >
        <div class="px-4 pt-4 pb-28 space-y-6">
            <button
                type="button"
                @click="mobileMenu = false; $dispatch('open-spotlight')"
                class="flex items-center gap-3 w-full px-4 py-3 rounded-2xl bg-slate-50 text-sm text-slate-400"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Rechercher
            </button>

            <nav>
                <p class="px-2 mb-1 text-[10px] font-medium uppercase tracking-[0.18em] text-slate-400">Pôles</p>
                <ul class="divide-y divide-slate-100">
                    @foreach($poles as $p)
                        <li x-data="{ expanded: @js($p['current']) }">
                            <div class="flex items-center">
                                <a href="{{ route($p['route']) }}" class="flex items-center gap-4 flex-1 min-w-0 px-2 py-3.5 active:bg-slate-50 transition-colors">
                                    <svg class="w-5 h-5 shrink-0 {{ $p['tint'] }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $p['icon'] }}"/></svg>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium {{ $p['current'] ? 'text-[#0066FF]' : 'text-slate-900' }}">{{ $p['label'] }}</p>
                                        <p class="text-xs text-slate-400">{{ $p['desc'] }}</p>
                                    </div>
                                    @if(empty($p['children']))
                                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    @endif
                                </a>
                                @if(!empty($p['children']))
                                    <button
                                        type="button"
                                        @click="expanded = !expanded"
                                        class="p-3 -mr-1 rounded-full text-slate-400 active:bg-slate-100 transition-colors"
                                        :aria-expanded="expanded"
                                        aria-label="Afficher les pages {{ $p['label'] }}"
                                    >
                                        <svg class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                @endif
                            </div>
                            @if(!empty($p['children']))
                                <div
                                    x-show="expanded"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="pb-3 pl-11 pr-2"
                                    @if(!$p['current']) style="display:none" @endif
                                >
                                    <div class="grid grid-cols-2 gap-1.5">
                                        @foreach($p['children'] as $child)
                                            <a href="{{ $child['url'] }}" @if($child['active']) aria-current="page" @endif class="px-3 py-2.5 rounded-xl text-xs truncate transition-colors {{ $child['active'] ? 'bg-[#0066FF] text-white font-semibold' : 'bg-slate-50 text-slate-700 font-medium active:bg-slate-100' }}">{{ $child['label'] }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>

            <nav>
                <p class="px-2 mb-1 text-[10px] font-medium uppercase tracking-[0.18em] text-slate-400">Outils</p>
                <ul class="divide-y divide-slate-100 text-sm text-slate-700">
                    @foreach($links as [$r, $label, $active])
                        <li><a href="{{ route($r) }}" class="block px-2 py-3 {{ $active ? 'text-[#0066FF] font-medium' : '' }}">{{ $label }}</a></li>
                    @endforeach
                    @unless($isTenantSpace)
                    <li><a href="{{ route('menu.index') }}" class="block px-2 py-3 {{ request()->routeIs('menu.*') ? 'text-[#0066FF] font-medium' : '' }}">Menu Général</a></li>
                    @endunless
                </ul>
            </nav>
        </div>
    </div>
</header>