@php
    $user = auth()->user();
    $isSubscriber = $user && ! $user->belongsToMainTenant();
    $homeUrl = $user ? route($isSubscriber ? 'space.home' : 'dashboard') : route('home');
    $reason = $exception?->getMessage();
@endphp
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>403 — Accès refusé | {{ config('app.name', 'IVOSPHERE') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Thème jour / nuit (nuit par défaut), partagé avec l'accueil et les offres --}}
    <script>
        (function () {
            var theme = 'night';
            try { theme = localStorage.getItem('ivo-offer-theme') || 'night'; } catch (e) {}
            document.documentElement.setAttribute('data-offer-theme', theme);
        })();
    </script>
    <style>[x-cloak]{display:none !important}</style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full overflow-x-hidden bg-[#F5F7FA] font-sans text-[#0B0F14] antialiased transition-colors night:bg-black night:text-white"
      x-data="{
          night: document.documentElement.getAttribute('data-offer-theme') === 'night',
          toggleTheme() {
              this.night = ! this.night;
              const theme = this.night ? 'night' : 'day';
              document.documentElement.setAttribute('data-offer-theme', theme);
              try { localStorage.setItem('ivo-offer-theme', theme); } catch (e) {}
          }
      }">

    <div class="pointer-events-none fixed -top-40 left-1/2 h-[600px] w-[1000px] -translate-x-1/2 rounded-full bg-amber-500/[0.08] blur-3xl night:bg-white/[0.06]" aria-hidden="true"></div>

    {{-- Navigation en pilule --}}
    <header class="fixed inset-x-0 top-4 z-40 flex justify-center px-4">
        <nav class="flex items-center gap-1 rounded-full border border-black/10 bg-white/70 p-1.5 pl-2 shadow-xl shadow-black/5 backdrop-blur-xl night:border-white/15 night:bg-black/60 night:shadow-black/50" aria-label="Navigation">
            <a href="{{ $homeUrl }}" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white ring-1 ring-black/5" aria-label="Retour à l'accueil">
                <img src="{{ asset('images/logo-emblem.png') }}" alt="" class="h-5 w-auto">
            </a>
            <span class="px-3 text-xs text-black/50 night:text-white/50">Contrôle d'accès</span>
            <button type="button" @click="toggleTheme()" class="flex h-8 w-8 items-center justify-center rounded-full text-black/60 transition hover:bg-black/5 night:text-white/70 night:hover:bg-white/10"
                    :aria-label="night ? 'Passer en mode jour' : 'Passer en mode nuit'" :title="night ? 'Mode jour' : 'Mode nuit'">
                <svg x-show="night" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
                <svg x-show="! night" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
            </button>
            @auth
                <span class="ml-0.5 flex h-8 max-w-[10rem] items-center gap-2 rounded-full bg-[#0B0F14] px-3.5 text-xs font-semibold text-white night:bg-white night:text-black">
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"></span>
                    <span class="truncate">{{ $user->name }}</span>
                </span>
            @else
                <a href="{{ route('login') }}" class="ml-0.5 flex h-8 items-center rounded-full bg-[#0B0F14] px-4 text-xs font-semibold text-white transition hover:bg-black night:bg-white night:text-black night:hover:bg-white/90">Connexion</a>
            @endauth
        </nav>
    </header>

    <main class="relative mx-auto flex min-h-screen max-w-3xl flex-col items-center justify-center px-4 pb-16 pt-28">
        {{-- Code géant derrière la carte --}}
        <p class="pointer-events-none select-none text-center text-[42vw] font-extrabold leading-none tracking-tighter text-[#0B0F14] night:text-white sm:text-[260px]" aria-hidden="true">403</p>

        <div class="relative z-10 -mt-[18vw] w-full max-w-xl rounded-[28px] border border-black/[0.08] bg-white/60 p-6 text-center backdrop-blur-2xl sm:-mt-[110px] sm:p-9 night:border-white/15 night:bg-white/[0.06] night:shadow-[0_0_80px_-20px_rgba(255,255,255,0.2)]">
            <span class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-amber-700 night:text-amber-300">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                Accès refusé
            </span>

            <h1 class="mt-5 text-3xl font-extrabold tracking-tight sm:text-4xl">Cette page ne vous est pas accessible</h1>
            <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-black/55 night:text-white/55">
                Votre compte n'a pas l'autorisation d'ouvrir cette page ou d'exécuter cette action.
            </p>

            @if(filled($reason))
                <div class="mt-6 rounded-2xl border border-black/[0.08] bg-black/[0.03] p-4 text-left text-sm night:border-white/10 night:bg-white/[0.04]">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-black/40 night:text-white/40">Motif</p>
                    <p class="mt-1 font-medium">{{ $reason }}</p>
                </div>
            @endif

            @auth
                <div class="mt-4 flex items-center justify-between gap-3 rounded-2xl border border-black/[0.08] bg-black/[0.03] p-3.5 text-left night:border-white/10 night:bg-white/[0.04]">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#0B0F14] text-xs font-bold text-white night:bg-white night:text-black">
                            {{ strtoupper(mb_substr($user->name, 0, 2)) }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                            <p class="truncate text-xs text-black/45 night:text-white/45">{{ $user->email }} · {{ $user->roles->pluck('name')->join(', ') ?: 'Utilisateur' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="h-9 rounded-full border border-black/15 px-3.5 text-xs font-semibold transition hover:border-black/40 night:border-white/15 night:hover:border-white/40" title="Se déconnecter pour changer de compte">Changer</button>
                    </form>
                </div>
            @endauth

            <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ $homeUrl }}" class="inline-flex h-12 items-center justify-center rounded-full bg-[#0B0F14] px-7 text-sm font-semibold text-white transition hover:bg-black night:bg-white night:text-black night:hover:bg-white/90">
                    {{ $user ? ($isSubscriber ? 'Mon espace' : 'Tableau de bord') : 'Accueil' }}
                </a>
                @if($isSubscriber)
                    <a href="{{ route('space.subscription') }}" class="inline-flex h-12 items-center justify-center rounded-full border border-black/15 px-7 text-sm font-semibold transition hover:border-black/40 night:border-white/15 night:hover:border-white/40">Mon abonnement</a>
                @else
                    <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.href = '{{ $homeUrl }}'"
                            class="inline-flex h-12 items-center justify-center rounded-full border border-black/15 px-7 text-sm font-semibold transition hover:border-black/40 night:border-white/15 night:hover:border-white/40">
                        Page précédente
                    </button>
                @endif
            </div>

            <div x-data="{ open: false }" class="mt-7 border-t border-black/[0.08] pt-4 text-left night:border-white/10">
                <button type="button" @click="open = ! open" class="flex w-full items-center justify-between py-1 text-xs text-black/45 transition hover:text-black night:text-white/45 night:hover:text-white">
                    Informations techniques
                    <svg class="h-3.5 w-3.5 transition" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <dl x-show="open" x-cloak class="mt-2 space-y-1.5 rounded-2xl bg-black/[0.03] p-3.5 font-mono text-[11px] text-black/55 night:bg-white/[0.04] night:text-white/55">
                    <div class="flex justify-between gap-3"><dt class="opacity-60">URL</dt><dd class="truncate">{{ request()->fullUrl() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="opacity-60">Méthode</dt><dd>{{ request()->method() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="opacity-60">Horodatage</dt><dd>{{ now()->timezone('Africa/Abidjan')->format('d/m/Y H:i:s') }} GMT</dd></div>
                    <div class="flex justify-between gap-3"><dt class="opacity-60">IP</dt><dd>{{ request()->ip() }}</dd></div>
                </dl>
            </div>
        </div>

        <p class="relative z-10 mt-8 max-w-md text-center text-xs text-black/45 night:text-white/45">
            @if($isSubscriber)
                Cette fonctionnalité n'est peut-être pas incluse dans votre offre. Consultez « Mon abonnement » pour changer d'offre.
            @else
                Besoin de cet accès pour vos missions ? Contactez votre administrateur ou votre responsable de pôle.
            @endif
        </p>
        <p class="relative z-10 mt-2 text-[11px] text-black/35 night:text-white/35">© {{ now()->year }} IVOSPHERE</p>
    </main>

    @livewireScriptConfig
</body>
</html>
