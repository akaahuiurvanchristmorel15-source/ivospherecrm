<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-pwa-meta theme-color="#000000" />
    <title>{{ config('app.name', 'IVOSPHERE ERP') }} — {{ $title ?? 'Connexion' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Même thème que la page d'accueil (nuit par défaut), appliqué avant l'affichage.
         La classe .dark est synchronisée pour les pages invitées qui s'appuient sur le thème de l'application. --}}
    <script>
        (function () {
            var theme = 'night';
            try { theme = localStorage.getItem('ivo-offer-theme') || 'night'; } catch (e) {}
            document.documentElement.setAttribute('data-offer-theme', theme);
            document.documentElement.classList.toggle('dark', theme === 'night');
        })();
    </script>
    <style>
        [x-cloak]{display:none !important}
        .font-display{font-family:'Inter',system-ui,sans-serif;letter-spacing:-0.035em}
        .guest-body{background-color:#F5F7FA;color:#0B0F14}
        [data-offer-theme=night] .guest-body{background-color:#000;color:#fff}
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="guest-body relative isolate flex min-h-dvh flex-col overflow-x-clip font-sans antialiased transition-colors selection:bg-[#0066FF] selection:text-white"
      x-data="{
          night: document.documentElement.getAttribute('data-offer-theme') === 'night',
          toggleTheme() {
              this.night = ! this.night;
              const theme = this.night ? 'night' : 'day';
              document.documentElement.setAttribute('data-offer-theme', theme);
              document.documentElement.classList.toggle('dark', this.night);
              try { localStorage.setItem('ivo-offer-theme', theme); } catch (e) {}
          }
      }">

    {{-- Halo lumineux et nom en filigrane, comme sur l'accueil --}}
    <div class="pointer-events-none absolute inset-0 -z-10 select-none overflow-hidden" aria-hidden="true">
        <div class="absolute -top-40 left-1/2 h-[600px] w-[1000px] -translate-x-1/2 rounded-full bg-[#0066FF]/[0.07] blur-3xl night:bg-white/[0.06]"></div>
        <p class="absolute inset-x-0 bottom-0 text-center font-display text-[19vw] font-extrabold leading-[0.8] text-black/[0.05] night:text-white/[0.06] lg:text-[250px]">IVOSPHERE</p>
    </div>

    {{-- Navigation en pilule --}}
    <header class="flex justify-center px-4 pt-[max(1rem,env(safe-area-inset-top))]">
        <nav class="flex max-w-full items-center gap-1 rounded-full border border-black/10 bg-white/75 p-1.5 pl-2 shadow-xl shadow-black/5 backdrop-blur-xl night:border-white/15 night:bg-black/60 night:shadow-black/50" aria-label="Navigation">
            <a href="{{ url('/') }}" class="flex items-center gap-2 rounded-full pr-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]" aria-label="IVOSPHERE, retour à l'accueil">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/[0.97] ring-1 ring-black/5">
                    <img src="{{ asset('images/logo-emblem.png') }}" alt="" class="h-5 w-auto">
                </span>
                <span class="text-xs font-semibold tracking-tight">IVOSPHERE</span>
            </a>
            <button type="button" @click="toggleTheme()"
                    class="flex h-8 w-8 items-center justify-center rounded-full text-black/60 transition hover:bg-black/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] night:text-white/70 night:hover:bg-white/10"
                    :aria-label="night ? 'Passer en mode jour' : 'Passer en mode nuit'" :title="night ? 'Mode jour' : 'Mode nuit'">
                <svg x-show="night" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
                <svg x-show="! night" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
            </button>
            <a href="{{ url('/') }}" class="ml-0.5 flex h-8 items-center whitespace-nowrap rounded-full bg-neutral-950 px-4 text-xs font-semibold text-white transition hover:bg-black night:bg-white night:text-black night:hover:bg-white/90">Accueil</a>
        </nav>
    </header>

    {{-- Contenu --}}
    <main class="flex flex-1 items-start justify-center px-4 py-8 sm:items-center sm:px-6 sm:py-12">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </main>

    {{-- Pied de page --}}
    <footer class="mx-auto w-full max-w-md space-y-1 px-4 pb-[max(1.25rem,env(safe-area-inset-bottom))] pt-4 text-center text-xs text-black/45 night:text-white/45">
        <p class="inline-flex items-center justify-center gap-1.5">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
            Connexion sécurisée
        </p>
        <p>© {{ date('Y') }} Groupe IVOSPHERE. Tous droits réservés.</p>
    </footer>

    @livewireScriptConfig
</body>
</html>
