<header x-data="{ mobileMenuOpen: false, scrolled: false }" 
        x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 })"
        :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-xs border-[#E2E8F0]' : 'bg-white/80 backdrop-blur-sm border-transparent'"
        class="sticky top-0 z-50 w-full transition-all duration-200 border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
            <!-- Logo & Brand -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#0066FF] rounded-xl p-1">
                <div class="w-10 h-10 rounded-xl bg-[#0B0F14] flex items-center justify-center text-white font-extrabold text-base tracking-wider shadow-xs transition group-hover:bg-[#0066FF]">
                    IVO<span class="text-[#0066FF] group-hover:text-white transition">.</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-base sm:text-lg font-bold tracking-tight text-[#0B0F14] leading-tight">
                        IVOSPHERE
                    </span>
                    <span class="text-[10px] sm:text-[11px] font-medium text-[#64748B] tracking-wide">
                        Centre de pilotage d'entreprise
                    </span>
                </div>
            </a>

            <!-- Navigation Desktop -->
            <nav class="hidden lg:flex items-center gap-1 xl:gap-2" aria-label="Navigation principale">
                <a href="#solutions" class="px-3 py-2 text-xs xl:text-sm font-medium text-[#0B0F14] hover:text-[#0066FF] hover:bg-slate-50 rounded-lg transition">
                    Solutions
                </a>
                <a href="#modules" class="px-3 py-2 text-xs xl:text-sm font-medium text-[#0B0F14] hover:text-[#0066FF] hover:bg-slate-50 rounded-lg transition">
                    Modules
                </a>
                <a href="#activites" class="px-3 py-2 text-xs xl:text-sm font-medium text-[#0B0F14] hover:text-[#0066FF] hover:bg-slate-50 rounded-lg transition">
                    Activités
                </a>
                <a href="#finance" class="px-3 py-2 text-xs xl:text-sm font-medium text-[#0B0F14] hover:text-[#0066FF] hover:bg-slate-50 rounded-lg transition">
                    Finance & Actifs
                </a>
                <a href="#tarifs" class="px-3 py-2 text-xs xl:text-sm font-medium text-[#0B0F14] hover:text-[#0066FF] hover:bg-slate-50 rounded-lg transition">
                    Tarifs
                </a>
                <a href="#a-propos" class="px-3 py-2 text-xs xl:text-sm font-medium text-[#0B0F14] hover:text-[#0066FF] hover:bg-slate-50 rounded-lg transition">
                    À propos
                </a>
                <a href="#faq" class="px-3 py-2 text-xs xl:text-sm font-medium text-[#0B0F14] hover:text-[#0066FF] hover:bg-slate-50 rounded-lg transition">
                    FAQ
                </a>
            </nav>

            <!-- Actions Droite Desktop -->
            <div class="hidden sm:flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" 
                       class="h-10 px-4 rounded-xl border border-[#E2E8F0] bg-white text-xs font-semibold text-[#0B0F14] hover:border-[#0B0F14] hover:bg-slate-50 transition flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Tableau de bord</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="h-10 px-4 rounded-xl border border-[#E2E8F0] bg-white text-xs font-semibold text-[#0B0F14] hover:text-[#0066FF] hover:border-[#0066FF] hover:bg-slate-50 transition flex items-center">
                        Se connecter
                    </a>
                @endauth

                <button type="button" 
                        @click="$dispatch('open-demo-modal')"
                        class="h-10 px-4 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-xs font-semibold text-white shadow-xs hover:shadow transition flex items-center gap-1.5 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#0066FF]">
                    <span>Commencer</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>

            <!-- Hamburger Button Mobile -->
            <div class="flex items-center gap-2 sm:hidden">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-[11px] font-semibold text-[#0B0F14]">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg border border-[#E2E8F0] text-[11px] font-semibold text-[#0B0F14]">
                        Connexion
                    </a>
                @endauth
                <button type="button" 
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        :aria-expanded="mobileMenuOpen.toString()"
                        aria-label="Ouvrir le menu de navigation"
                        class="p-2 rounded-xl border border-[#E2E8F0] bg-white text-[#0B0F14] hover:bg-slate-50 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#0066FF]">
                    <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer Menu -->
    <div x-show="mobileMenuOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         @click.away="mobileMenuOpen = false"
         class="lg:hidden border-b border-[#E2E8F0] bg-white px-4 pt-2 pb-6 space-y-3 shadow-lg">
        <nav class="flex flex-col space-y-1">
            <a href="#solutions" @click="mobileMenuOpen = false" class="px-3 py-2.5 rounded-xl text-sm font-medium text-[#0B0F14] hover:bg-slate-50 hover:text-[#0066FF]">
                Solutions
            </a>
            <a href="#modules" @click="mobileMenuOpen = false" class="px-3 py-2.5 rounded-xl text-sm font-medium text-[#0B0F14] hover:bg-slate-50 hover:text-[#0066FF]">
                Modules
            </a>
            <a href="#activites" @click="mobileMenuOpen = false" class="px-3 py-2.5 rounded-xl text-sm font-medium text-[#0B0F14] hover:bg-slate-50 hover:text-[#0066FF]">
                Activités
            </a>
            <a href="#finance" @click="mobileMenuOpen = false" class="px-3 py-2.5 rounded-xl text-sm font-medium text-[#0B0F14] hover:bg-slate-50 hover:text-[#0066FF]">
                Finance & Actifs
            </a>
            <a href="#tarifs" @click="mobileMenuOpen = false" class="px-3 py-2.5 rounded-xl text-sm font-medium text-[#0B0F14] hover:bg-slate-50 hover:text-[#0066FF]">
                Tarifs
            </a>
            <a href="#a-propos" @click="mobileMenuOpen = false" class="px-3 py-2.5 rounded-xl text-sm font-medium text-[#0B0F14] hover:bg-slate-50 hover:text-[#0066FF]">
                À propos
            </a>
            <a href="#faq" @click="mobileMenuOpen = false" class="px-3 py-2.5 rounded-xl text-sm font-medium text-[#0B0F14] hover:bg-slate-50 hover:text-[#0066FF]">
                FAQ
            </a>
        </nav>

        <div class="pt-3 border-t border-[#E2E8F0] flex flex-col gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="w-full h-11 rounded-xl bg-[#0B0F14] text-white text-xs font-semibold flex items-center justify-center gap-2">
                    <span>Accéder au Tableau de bord</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="w-full h-11 rounded-xl border border-[#E2E8F0] bg-white text-xs font-semibold text-[#0B0F14] flex items-center justify-center">
                    Se connecter
                </a>
            @endauth
            <button type="button" 
                    @click="mobileMenuOpen = false; $dispatch('open-demo-modal')"
                    class="w-full h-11 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-xs font-semibold flex items-center justify-center gap-1.5 shadow-xs">
                <span>Commencer maintenant</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </button>
        </div>
    </div>
</header>
