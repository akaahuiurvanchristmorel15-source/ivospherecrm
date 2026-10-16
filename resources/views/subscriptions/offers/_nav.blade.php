{{-- Thème (nuit par défaut) appliqué avant l'affichage pour éviter un flash, puis navigation en pilule. --}}
<script>
    (function () {
        var theme = 'night';
        try { theme = localStorage.getItem('ivo-offer-theme') || 'night'; } catch (e) {}
        document.documentElement.setAttribute('data-offer-theme', theme);
    })();
</script>
<div class="fixed inset-x-0 top-4 z-40 flex justify-center px-4"
     x-data="{
         night: document.documentElement.getAttribute('data-offer-theme') === 'night',
         toggle() {
             this.night = ! this.night;
             const theme = this.night ? 'night' : 'day';
             document.documentElement.setAttribute('data-offer-theme', theme);
             try { localStorage.setItem('ivo-offer-theme', theme); } catch (e) {}
         }
     }">
    <nav class="flex items-center gap-1 rounded-full border border-black/10 bg-white/70 p-1.5 pl-2 shadow-xl shadow-black/5 backdrop-blur-xl night:border-white/15 night:bg-black/60 night:shadow-black/50" aria-label="Navigation">
        <a href="{{ route('home') }}" class="w-8 h-8 rounded-full bg-white ring-1 ring-black/5 flex items-center justify-center shrink-0" aria-label="Accueil IVOSPHERE">
            <img src="{{ asset('images/logo-emblem.png') }}" alt="" class="h-5 w-auto">
        </a>
        <a href="{{ route('home') }}" class="hidden sm:block px-3 py-1.5 text-xs text-black/50 hover:text-black night:text-white/50 night:hover:text-white transition">Accueil</a>
        <a href="{{ route('offres.index') }}#offres" class="px-3 py-1.5 text-xs {{ request()->routeIs('offres.index') ? 'text-black night:text-white' : 'text-black/50 hover:text-black night:text-white/50 night:hover:text-white' }} transition">Offres</a>
        <a href="{{ route('offres.index') }}#comparatif" class="hidden sm:block px-3 py-1.5 text-xs text-black/50 hover:text-black night:text-white/50 night:hover:text-white transition">Comparatif</a>
        <a href="{{ route('offres.index') }}#faq" class="px-3 py-1.5 text-xs text-black/50 hover:text-black night:text-white/50 night:hover:text-white transition">FAQ</a>
        <button type="button" @click="toggle()" class="w-8 h-8 rounded-full flex items-center justify-center text-black/60 hover:bg-black/5 night:text-white/70 night:hover:bg-white/10 transition"
                :aria-label="night ? 'Passer en mode jour' : 'Passer en mode nuit'" :title="night ? 'Mode jour' : 'Mode nuit'">
            <svg x-show="night" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
            <svg x-show="! night" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
        </button>
        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="ml-0.5 h-8 px-4 rounded-full bg-[#0B0F14] text-xs font-semibold text-white flex items-center hover:bg-black night:bg-white night:text-black night:hover:bg-white/90 transition">
            {{ auth()->check() ? 'Mon espace' : 'Connexion' }}
        </a>
    </nav>
</div>
