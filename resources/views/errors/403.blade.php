<!DOCTYPE html>
<html lang="fr" class="h-full bg-[#F6F9FC]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>403 — Accès Refusé | {{ config('app.name', 'IVOSPHERE ERP') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Ambient subtle grid background */
        .bg-grid-pattern {
            background-image: radial-gradient(rgba(100, 116, 139, 0.12) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="h-full antialiased font-sans text-[#0B0F14] bg-[#F6F9FC] flex flex-col justify-between selection:bg-[#0066FF] selection:text-white relative overflow-x-hidden">

    <!-- Ambient Glow Top Background -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-4xl h-72 bg-gradient-to-b from-amber-500/10 via-[#0066FF]/5 to-transparent blur-3xl pointer-events-none -z-10"></div>
    <div class="absolute inset-0 bg-grid-pattern opacity-40 pointer-events-none -z-10"></div>

    <!-- 1. En-tête / Brand Header -->
    <header class="w-full max-w-5xl mx-auto px-4 sm:px-6 py-6 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group" title="Retour à l'accueil">
            <div class="w-9 h-9 rounded-xl bg-[#0066FF] flex items-center justify-center font-extrabold text-white text-xs shadow-md shadow-[#0066FF]/25 group-hover:scale-105 transition-transform">
                IVO
            </div>
            <div class="flex flex-col">
                <span class="text-sm font-extrabold tracking-tight text-[#0B0F14] leading-tight flex items-center gap-1">
                    IVOSPHERE
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0066FF]"></span>
                </span>
                <span class="text-[10px] text-slate-400 uppercase tracking-wider font-bold -mt-0.5">Contrôle d'accès</span>
            </div>
        </a>

        @auth
            <div class="flex items-center gap-2">
                <span class="hidden sm:inline-block text-xs text-slate-500 font-medium">Session active :</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-white border border-[#E2E8F0] text-slate-700 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    {{ auth()->user()->name }}
                </span>
            </div>
        @else
            <a href="{{ route('login') }}" class="text-xs font-bold text-[#0066FF] hover:underline flex items-center gap-1">
                <span>Connexion</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        @endauth
    </header>

    <!-- 2. Contenu Principal de l'Erreur 403 -->
    <main class="w-full max-w-lg mx-auto px-4 sm:px-6 py-6 my-auto">
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-[#E2E8F0] shadow-sm sm:shadow-lg p-6 sm:p-9 text-center relative overflow-hidden">
            
            <!-- Bande d'accent supérieure de sécurité -->
            <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-amber-500 via-rose-500 to-[#0066FF]"></div>

            <!-- Icône & Bouclier de Sécurité -->
            <div class="relative mx-auto w-20 h-20 sm:w-24 sm:h-24 mb-5 flex items-center justify-center">
                <!-- Halo animé -->
                <div class="absolute inset-0 rounded-3xl bg-amber-500/10 animate-pulse"></div>
                <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center shadow-inner">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>

            <!-- Code HTTP & Badge Statut -->
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200/80 uppercase tracking-wider mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                <span>Code HTTP 403 • Accès Interdit</span>
            </div>

            <!-- Titre Principal -->
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0B0F14] tracking-tight mb-2.5">
                Privilèges Insuffisants
            </h1>

            <!-- Message d'explication -->
            <p class="text-sm text-slate-600 leading-relaxed max-w-md mx-auto mb-5">
                Vous n'avez pas l'autorisation d'accéder à cette page, à ce pôle ou d'exécuter cette action avec votre compte actuel.
            </p>

            <!-- Motif spécifique si exception fournie -->
            @if(!empty($exception?->getMessage()))
                <div class="mb-5 p-3 rounded-xl bg-slate-50 border border-slate-200 text-left text-xs font-mono text-slate-700 break-words flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <span class="font-bold text-[#0B0F14] block mb-0.5">Détail de la politique de sécurité :</span>
                        <span class="text-slate-600">{{ $exception->getMessage() }}</span>
                    </div>
                </div>
            @endif

            <!-- Carte Info Utilisateur Connecté -->
            @auth
                <div class="mb-6 p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-left flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-[#0066FF]/10 text-[#0066FF] font-bold flex items-center justify-center text-sm shrink-0 border border-[#0066FF]/20">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-[#0B0F14] truncate">
                                {{ auth()->user()->name }}
                            </div>
                            <div class="text-[11px] text-slate-500 truncate">
                                {{ auth()->user()->email }}
                            </div>
                            <div class="mt-0.5">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-white border border-[#E2E8F0] text-slate-700">
                                    Rôle : {{ auth()->user()->roles->pluck('name')->join(', ') ?: 'Utilisateur Standard' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button 
                            type="submit" 
                            class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-slate-100 border border-[#E2E8F0] text-[11px] font-bold text-slate-700 hover:text-rose-600 transition-colors shadow-2xs touch-target"
                            title="Se déconnecter pour changer de compte"
                        >
                            Changer
                        </button>
                    </form>
                </div>
            @else
                <div class="mb-6 p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-left text-xs text-blue-900 flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-[#0066FF] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Vous n'êtes pas authentifié. Veuillez vous connecter pour accéder à vos accès applicatifs.</span>
                </div>
            @endauth

            <!-- Boutons d'Action (Responsive & Touch Targets >= 44px) -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3">
                <a 
                    href="{{ route('dashboard') }}" 
                    class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#0066FF] hover:bg-blue-600 text-white font-bold text-xs sm:text-sm shadow-xs shadow-[#0066FF]/20 transition-all touch-target select-none"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Tableau de bord</span>
                </a>

                <button 
                    type="button" 
                    onclick="window.history.length > 1 ? window.history.back() : window.location.href = '{{ route('dashboard') }}'" 
                    class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-semibold text-xs sm:text-sm border border-[#E2E8F0] transition-all touch-target select-none"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Page précédente</span>
                </button>
            </div>

            <!-- Détails Techniques Dépliables (Alpine.js) pour le support / admin -->
            <div x-data="{ showTech: false }" class="mt-6 pt-4 border-t border-[#E2E8F0] text-left">
                <button 
                    type="button" 
                    @click="showTech = !showTech" 
                    class="w-full flex items-center justify-between text-[11px] font-semibold text-slate-500 hover:text-slate-800 transition-colors py-1"
                >
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Informations techniques
                    </span>
                    <svg class="w-3.5 h-3.5 transform transition-transform" :class="showTech ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="showTech" x-cloak class="mt-2.5 p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] font-mono text-[10px] text-slate-600 space-y-1.5">
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-400">URL :</span>
                        <span class="text-slate-800 truncate font-semibold">{{ request()->fullUrl() }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-400">Méthode :</span>
                        <span class="text-slate-800 font-semibold">{{ request()->method() }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-400">Horodatage :</span>
                        <span>{{ now()->timezone('Africa/Abidjan')->format('d/m/Y H:i:s') }} GMT</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-400">IP Client :</span>
                        <span>{{ request()->ip() }}</span>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- 3. Pied de page / Support Footer -->
    <footer class="w-full max-w-5xl mx-auto px-4 sm:px-6 py-6 text-center text-xs text-slate-500">
        <p>
            Vous devez accéder à cette section pour vos missions ? Contactez votre <a href="mailto:admin@ivosphere.com" class="text-[#0066FF] font-semibold hover:underline">administrateur système</a> ou votre responsable de pôle.
        </p>
        <p class="text-[11px] text-slate-400 mt-1">
            &copy; {{ date('Y') }} IVOSPHERE CRM • Sécurité RBAC & Multi-Domaines
        </p>
    </footer>

</body>
</html>
