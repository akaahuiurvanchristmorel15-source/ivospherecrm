<header x-data="{ mobileMenuOpen: false, scrolled: false }" 
        x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 })"
        :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-xs border-[#E2E8F0]' : 'bg-white/80 backdrop-blur-sm border-transparent'"
        class="sticky top-0 z-50 w-full transition-all duration-300 border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-18 sm:h-20">
            
            <!-- Logo IVOSPHERE -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#0066FF] rounded-xl p-1">
                <div class="w-10 h-10 rounded-xl bg-[#0B0F14] text-white flex items-center justify-center font-black text-base tracking-wider shadow-xs transition group-hover:bg-[#0066FF]">
                    IVO<span class="text-[#0066FF] group-hover:text-white transition">.</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-lg font-extrabold tracking-tight text-[#0B0F14] leading-tight group-hover:text-[#0066FF] transition">
                        IVOSPHERE
                    </span>
                    <span class="text-[10px] sm:text-[11px] font-semibold text-[#64748B] tracking-wide">
                        Solutions Multi-Services
                    </span>
                </div>
            </a>

            <!-- Navigation Desktop -->
            <nav class="hidden lg:flex items-center gap-1 xl:gap-2" aria-label="Navigation principale">
                <a href="#hero" class="px-3.5 py-2 text-xs xl:text-sm font-semibold text-[#0B0F14] hover:text-[#0066FF] hover:bg-slate-50 rounded-lg transition">
                    Accueil
                </a>
                <a href="#domaines" class="px-3.5 py-2 text-xs xl:text-sm font-semibold text-[#0B0F14] hover:text-[#0066FF] hover:bg-slate-50 rounded-lg transition">
                    Nos services
                </a>
                <a href="#activites" class="px-3.5 py-2 text-xs xl:text-sm font-semibold text-[#0B0F14] hover:text-[#0066FF] hover:bg-slate-50 rounded-lg transition">
                    Nos activités
                </a>
                <a href="#realisations" class="px-3.5 py-2 text-xs xl:text-sm font-semibold text-[#0B0F14] hover:text-[#0066FF] hover:bg-slate-50 rounded-lg transition">
                    Réalisations
                </a>
                <a href="#a-propos" class="px-3.5 py-2 text-xs xl:text-sm font-semibold text-[#0B0F14] hover:text-[#0066FF] hover:bg-slate-50 rounded-lg transition">
                    À propos
                </a>
                <a href="#contact" class="px-3.5 py-2 text-xs xl:text-sm font-semibold text-[#0B0F14] hover:text-[#0066FF] hover:bg-slate-50 rounded-lg transition">
                    Contact
                </a>
            </nav>

            <!-- Actions Droite Desktop -->
            <div class="hidden sm:flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" 
                       class="h-10 px-4 rounded-xl border border-[#E2E8F0] bg-white text-xs font-semibold text-[#0B0F14] hover:border-[#0B0F14] hover:bg-slate-50 transition flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Mon Espace</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="h-10 px-4 rounded-xl border border-[#E2E8F0] bg-white text-xs font-semibold text-[#0B0F14] hover:text-[#0066FF] hover:border-[#0066FF] hover:bg-slate-50 transition flex items-center">
                        Se connecter
                    </a>
                @endauth

                <a href="#devis" 
                   class="h-10 px-5 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-xs font-bold text-white shadow-xs hover:shadow transition flex items-center gap-1.5 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#0066FF]">
                    <span>Demander un devis</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>

            <!-- Hamburger Button & Quick Contact Mobile -->
            <div class="flex items-center gap-2 lg:hidden">
                <a href="#devis" class="h-9 px-3 rounded-lg bg-[#0066FF] text-white text-xs font-bold flex items-center">
                    Devis
                </a>
                <button type="button" 
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        :aria-expanded="mobileMenuOpen.toString()"
                        aria-label="Menu principal"
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
         class="lg:hidden border-b border-[#E2E8F0] bg-white px-4 pt-2 pb-6 space-y-3 shadow-xl">
        <nav class="flex flex-col space-y-1">
            <a href="#hero" @click="mobileMenuOpen = false" class="px-3 py-2.5 rounded-xl text-sm font-semibold text-[#0B0F14] hover:bg-slate-50 hover:text-[#0066FF]">
                Accueil
            </a>
            <a href="#domaines" @click="mobileMenuOpen = false" class="px-3 py-2.5 rounded-xl text-sm font-semibold text-[#0B0F14] hover:bg-slate-50 hover:text-[#0066FF]">
                Nos services
            </a>
            <a href="#activites" @click="mobileMenuOpen = false" class="px-3 py-2.5 rounded-xl text-sm font-semibold text-[#0B0F14] hover:bg-slate-50 hover:text-[#0066FF]">
                Nos activités
            </a>
            <a href="#realisations" @click="mobileMenuOpen = false" class="px-3 py-2.5 rounded-xl text-sm font-semibold text-[#0B0F14] hover:bg-slate-50 hover:text-[#0066FF]">
                Réalisations
            </a>
            <a href="#a-propos" @click="mobileMenuOpen = false" class="px-3 py-2.5 rounded-xl text-sm font-semibold text-[#0B0F14] hover:bg-slate-50 hover:text-[#0066FF]">
                À propos
            </a>
            <a href="#contact" @click="mobileMenuOpen = false" class="px-3 py-2.5 rounded-xl text-sm font-semibold text-[#0B0F14] hover:bg-slate-50 hover:text-[#0066FF]">
                Contact
            </a>
        </nav>

        <div class="pt-3 border-t border-[#E2E8F0] flex flex-col gap-2">
            <a href="#devis" @click="mobileMenuOpen = false" class="w-full h-11 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-xs">
                <span>Demander un devis gratuit</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="w-full h-11 rounded-xl bg-[#0B0F14] text-white text-xs font-semibold flex items-center justify-center">
                    Accéder à mon espace
                </a>
            @else
                <a href="{{ route('login') }}" class="w-full h-11 rounded-xl border border-[#E2E8F0] bg-white text-xs font-semibold text-[#0B0F14] flex items-center justify-center">
                    Se connecter
                </a>
            @endauth
        </div>
    </div>
</header>
