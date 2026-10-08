<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'IVOSPHERE ERP') }} — Connexion</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        try { if (localStorage.getItem('ivo-theme') === 'dark') { document.documentElement.classList.add('dark'); } } catch (e) {}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh antialiased font-sans flex flex-col bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 selection:bg-[#0066FF] selection:text-white">

    {{-- Barre supérieure --}}
    <header class="w-full max-w-5xl mx-auto px-4 sm:px-6 pt-[max(1rem,env(safe-area-inset-top))] pb-4 flex items-center justify-between">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-950">
            <img src="{{ asset('images/logo.png') }}" alt="" class="w-8 h-8 object-contain">
            <span class="text-sm font-semibold tracking-tight">IVOSPHERE</span>
        </a>

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
            class="w-10 h-10 inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]"
            :title="isDark ? 'Passer en mode jour' : 'Passer en mode nuit'"
            :aria-label="isDark ? 'Passer en mode jour' : 'Passer en mode nuit'"
        >
            <svg x-show="!isDark" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            <svg x-show="isDark" x-cloak class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </button>
    </header>

    {{-- Contenu --}}
    <main class="flex-1 flex items-start sm:items-center justify-center px-4 sm:px-6 py-6 sm:py-10">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </main>

    {{-- Pied de page --}}
    <footer class="w-full max-w-md mx-auto px-4 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom))] text-center text-xs text-slate-500 dark:text-slate-400 space-y-1">
        <p class="inline-flex items-center justify-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Connexion sécurisée
        </p>
        <p class="text-slate-400 dark:text-slate-500">© {{ date('Y') }} Groupe IVOSPHERE. Tous droits réservés.</p>
    </footer>

</body>
</html>