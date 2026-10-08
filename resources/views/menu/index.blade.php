<x-layouts.app title="Menu Général — IVOSPHERE ERP">
    <div class="max-w-5xl mx-auto py-2 sm:py-4 space-y-8 sm:space-y-10">

        <!-- En-tête épuré -->
        <header class="text-center space-y-1">
            <h1 class="text-xl sm:text-2xl font-semibold text-[#0B0F14] tracking-tight">Menu Général</h1>
            <p class="text-xs sm:text-sm text-[#64748B]">Accédez rapidement aux pôles métiers et outils de la plateforme.</p>
        </header>

        @php $user = auth()->user(); @endphp

        <!-- Ligne 1 : Pilotage & Intelligence -->
        @if($user->isAdmin() || $user->hasAnyPermission(['logs.view', 'settings.manage']))
        <div>
            <div class="flex items-center justify-between mb-3 px-1">
                <p class="text-[10px] sm:text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Pilotage & Intelligence</p>
                <span class="sm:hidden text-[10px] text-[#64748B] flex items-center gap-1 font-medium">
                    <span>Glisser</span>
                    <svg class="w-3 h-3 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </span>
            </div>
            <div class="flex overflow-x-auto gap-3 pb-2 pt-0.5 -mx-3 px-3 sm:mx-0 sm:px-0 sm:pb-0 sm:pt-0 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:gap-4 snap-x snap-mandatory scroll-smooth no-scrollbar">

                <!-- Direction (admin only) -->
                @if($user->isAdmin())
                <a href="{{ route('command-center.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 hover:border-[#0066FF] transition-all w-[76vw] max-w-[280px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col justify-between">
                    <div>
                        <div class="w-9 h-9 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] flex items-center justify-center mb-3 group-hover:bg-[#0066FF] group-hover:text-white group-hover:border-[#0066FF] transition-all">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Direction</h3>
                        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">Cockpit exécutif, KPIs consolidés et alertes stratégiques.</p>
                    </div>
                </a>
                @endif

                <!-- IVOSPHERE AI (admin only) -->
                @if($user->isAdmin())
                <a href="{{ route('ai.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 hover:border-[#0066FF] transition-all w-[76vw] max-w-[280px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col justify-between">
                    <div>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200/80 text-[#0066FF] flex items-center justify-center mb-3 group-hover:bg-[#0066FF] group-hover:text-white group-hover:border-[#0066FF] transition-all">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">IVOSPHERE AI</h3>
                        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">Assistant IA, studio créatif et requêtes en langage naturel.</p>
                    </div>
                </a>
                @endif

                <!-- Analytics & BI (admin or finance) -->
                @if($user->isAdmin() || $user->hasAnyPermission(['finance.reports', 'sales.reports']))
                <a href="{{ route('analytics.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 hover:border-[#0066FF] transition-all w-[76vw] max-w-[280px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col justify-between">
                    <div>
                        <div class="w-9 h-9 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] flex items-center justify-center mb-3 group-hover:bg-[#0066FF] group-hover:text-white group-hover:border-[#0066FF] transition-all">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Prévisions & BI</h3>
                        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">Projections de ventes, tendances et modélisation prédictive.</p>
                    </div>
                </a>
                @endif

                <!-- Alertes & Automatisations (admin only) -->
                @if($user->isAdmin())
                <a href="{{ route('alerts.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 hover:border-[#0066FF] transition-all w-[76vw] max-w-[280px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col justify-between">
                    <div>
                        <div class="w-9 h-9 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] flex items-center justify-center mb-3 group-hover:bg-[#0066FF] group-hover:text-white group-hover:border-[#0066FF] transition-all">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Alertes & Automates</h3>
                        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">Règles automatisées, workflows d'approbation et alertes.</p>
                    </div>
                </a>
                @endif

            </div>
        </div>
        @endif

        <!-- Ligne 2 : Gestion Opérationnelle -->
        <div>
            <div class="flex items-center justify-between mb-3 px-1">
                <p class="text-[10px] sm:text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Gestion Opérationnelle</p>
                <span class="sm:hidden text-[10px] text-[#64748B] flex items-center gap-1 font-medium">
                    <span>Glisser</span>
                    <svg class="w-3 h-3 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </span>
            </div>
            <div class="flex overflow-x-auto gap-3 pb-2 pt-0.5 -mx-3 px-3 sm:mx-0 sm:px-0 sm:pb-0 sm:pt-0 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:gap-4 snap-x snap-mandatory scroll-smooth no-scrollbar">

                <!-- Commercial & CRM -->
                @if($user->isAdmin() || $user->hasAnyPermission(['customers.view', 'quotations.view', 'orders.manage']))
                <a href="{{ route('commercial.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 hover:border-[#0066FF] transition-all w-[76vw] max-w-[280px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col justify-between">
                    <div>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200/80 text-[#0066FF] flex items-center justify-center mb-3 group-hover:bg-[#0066FF] group-hover:text-white group-hover:border-[#0066FF] transition-all">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Commercial & CRM</h3>
                        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">Clients, devis, factures, commandes et vente comptoir.</p>
                    </div>
                </a>
                @endif

                <!-- Finance & Trésorerie -->
                @if($user->isAdmin() || $user->hasAnyPermission(['finance.cash', 'finance.expenses', 'finance.revenues', 'invoices.view']))
                <a href="{{ route('finance.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 hover:border-[#0066FF] transition-all w-[76vw] max-w-[280px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col justify-between">
                    <div>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 flex items-center justify-center mb-3 group-hover:bg-emerald-600 group-hover:text-white group-hover:border-emerald-600 transition-all">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Finance & Trésorerie</h3>
                        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">Multi-comptes, trésorerie, immobilisations, caisses, créances et budgets.</p>
                    </div>
                </a>
                @endif

                <!-- Stocks & Logistique -->
                @if($user->isAdmin() || $user->hasAnyPermission(['stocks.view', 'stocks.manage', 'stocks.movements']))
                <a href="{{ route('stock.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 hover:border-[#0066FF] transition-all w-[76vw] max-w-[280px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col justify-between">
                    <div>
                        <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-700 flex items-center justify-center mb-3 group-hover:bg-amber-600 group-hover:text-white group-hover:border-amber-600 transition-all">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Stocks & Logistique</h3>
                        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">Entrepôts, mouvements, alertes de rupture et livraisons.</p>
                    </div>
                </a>
                @endif

                <!-- Ressources Humaines -->
                @if($user->isAdmin() || $user->hasAnyPermission(['employees.view', 'leaves.manage', 'attendance.manage']))
                <a href="{{ route('rh.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 hover:border-[#0066FF] transition-all w-[76vw] max-w-[280px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col justify-between">
                    <div>
                        <div class="w-9 h-9 rounded-xl bg-purple-50 border border-purple-200/80 text-purple-700 flex items-center justify-center mb-3 group-hover:bg-purple-600 group-hover:text-white group-hover:border-purple-600 transition-all">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Ressources Humaines</h3>
                        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">Employés, congés, pointage et communication d'équipe.</p>
                    </div>
                </a>
                @endif

            </div>
        </div>

        <!-- Ligne 3 : Ateliers Métiers -->
        @if($user->isAdmin() || $user->hasAnyPermission(['print.manage', 'sport.manage', 'tech.manage', 'media.manage', 'insurance.manage']))
        <div>
            <div class="flex items-center justify-between mb-3 px-1">
                <p class="text-[10px] sm:text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Ateliers & Pôles Métiers</p>
                <span class="sm:hidden text-[10px] text-[#64748B] flex items-center gap-1 font-medium">
                    <span>Glisser</span>
                    <svg class="w-3 h-3 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </span>
            </div>
            <div class="flex overflow-x-auto gap-3 pb-2 pt-0.5 -mx-3 px-3 sm:mx-0 sm:px-0 sm:pb-0 sm:pt-0 sm:grid sm:grid-cols-3 lg:grid-cols-5 sm:gap-4 snap-x snap-mandatory scroll-smooth no-scrollbar">

                @if($user->isAdmin() || $user->hasPermission('print.manage'))
                <a href="{{ route('print.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 hover:border-[#0066FF] transition-all text-center w-[38vw] max-w-[150px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col items-center justify-center">
                    <div class="w-9 h-9 rounded-xl bg-cyan-50 border border-cyan-200/80 text-cyan-700 flex items-center justify-center mx-auto mb-2.5 group-hover:bg-cyan-600 group-hover:text-white group-hover:border-cyan-600 transition-all">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    </div>
                    <h3 class="text-xs font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">PRINT</h3>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Imprimerie & BAT</p>
                </a>
                @endif

                @if($user->isAdmin() || $user->hasPermission('sport.manage'))
                <a href="{{ route('sport.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 hover:border-[#0066FF] transition-all text-center w-[38vw] max-w-[150px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col items-center justify-center">
                    <div class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-600 flex items-center justify-center mx-auto mb-2.5 group-hover:bg-rose-600 group-hover:text-white group-hover:border-rose-600 transition-all">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-xs font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">SPORT</h3>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Flocage & Clubs</p>
                </a>
                @endif

                @if($user->isAdmin() || $user->hasPermission('tech.manage'))
                <a href="{{ route('tech.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 hover:border-[#0066FF] transition-all text-center w-[38vw] max-w-[150px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col items-center justify-center">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-200/80 text-indigo-700 flex items-center justify-center mx-auto mb-2.5 group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600 transition-all">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    </div>
                    <h3 class="text-xs font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">TECH</h3>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Apps & Projets IA</p>
                </a>
                @endif

                @if($user->isAdmin() || $user->hasPermission('media.manage'))
                <a href="{{ route('media.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 hover:border-[#0066FF] transition-all text-center w-[38vw] max-w-[150px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col items-center justify-center">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-700 flex items-center justify-center mx-auto mb-2.5 group-hover:bg-amber-600 group-hover:text-white group-hover:border-amber-600 transition-all">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-xs font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">MEDIA</h3>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Studio & Événements</p>
                </a>
                @endif

                @if($user->isAdmin() || $user->hasPermission('insurance.manage'))
                <a href="{{ route('assurance.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 hover:border-[#0066FF] transition-all text-center w-[38vw] max-w-[150px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col items-center justify-center">
                    <div class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-700 flex items-center justify-center mx-auto mb-2.5 group-hover:bg-rose-600 group-hover:text-white group-hover:border-rose-600 transition-all">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-xs font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">ASSURANCE</h3>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Courtage & Polices</p>
                </a>
                @endif

            </div>
        </div>
        @endif

        <!-- Ligne 4 : Services & Collaboration -->
        <div>
            <p class="text-[10px] sm:text-[11px] font-semibold text-[#64748B] uppercase tracking-wider mb-3 px-1">Services & Collaboration</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-2.5 sm:gap-3">

                <a href="{{ route('notifications.index') }}" class="group flex items-center gap-2.5 bg-white rounded-xl border border-[#E2E8F0] px-3.5 py-2.5 hover:border-[#0066FF] transition-all">
                    <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                    <span class="text-xs font-medium text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Notifications</span>
                </a>

                @if($user->isAdmin() || $user->hasPermission('contracts.manage'))
                <a href="{{ route('contracts.index') }}" class="group flex items-center gap-2.5 bg-white rounded-xl border border-[#E2E8F0] px-3.5 py-2.5 hover:border-[#0066FF] transition-all">
                    <span class="w-2 h-2 rounded-full bg-[#0066FF] shrink-0"></span>
                    <span class="text-xs font-medium text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Contrats</span>
                </a>
                @endif

                @if($user->isAdmin())
                <a href="{{ route('ged.index') }}" class="group flex items-center gap-2.5 bg-white rounded-xl border border-[#E2E8F0] px-3.5 py-2.5 hover:border-[#0066FF] transition-all">
                    <span class="w-2 h-2 rounded-full bg-[#64748B] shrink-0"></span>
                    <span class="text-xs font-medium text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">GED</span>
                </a>
                @endif

                <a href="{{ route('support.index') }}" class="group flex items-center gap-2.5 bg-white rounded-xl border border-[#E2E8F0] px-3.5 py-2.5 hover:border-[#0066FF] transition-all">
                    <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                    <span class="text-xs font-medium text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Support</span>
                </a>

                <a href="{{ route('calendar.index') }}" class="group flex items-center gap-2.5 bg-white rounded-xl border border-[#E2E8F0] px-3.5 py-2.5 hover:border-[#0066FF] transition-all">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                    <span class="text-xs font-medium text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Calendrier</span>
                </a>

                <a href="{{ route('messages.index') }}" class="group flex items-center gap-2.5 bg-white rounded-xl border border-[#E2E8F0] px-3.5 py-2.5 hover:border-[#0066FF] transition-all">
                    <span class="w-2 h-2 rounded-full bg-[#0066FF] shrink-0"></span>
                    <span class="text-xs font-medium text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Messages</span>
                </a>

                <a href="{{ route('simulators.index') }}" class="group flex items-center gap-2.5 bg-white rounded-xl border border-[#E2E8F0] px-3.5 py-2.5 hover:border-[#0066FF] transition-all">
                    <span class="w-2 h-2 rounded-full bg-[#64748B] shrink-0"></span>
                    <span class="text-xs font-medium text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Simulateurs</span>
                </a>

            </div>
        </div>

        <!-- Ligne 5 : Administration & Sécurité -->
        @if($user->isAdmin() || $user->hasAnyPermission(['roles.manage', 'settings.manage', 'users.view']))
        <div>
            <div class="flex items-center justify-between mb-3 px-1">
                <p class="text-[10px] sm:text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Administration & Sécurité</p>
                <span class="sm:hidden text-[10px] text-[#64748B] flex items-center gap-1 font-medium">
                    <span>Glisser</span>
                    <svg class="w-3 h-3 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </span>
            </div>
            <div class="flex overflow-x-auto gap-3 pb-2 pt-0.5 -mx-3 px-3 sm:mx-0 sm:px-0 sm:pb-0 sm:pt-0 sm:grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 sm:gap-4 snap-x snap-mandatory scroll-smooth no-scrollbar">

                <!-- Rôles & Permissions -->
                @if($user->isAdmin() || $user->hasPermission('roles.manage'))
                <a href="{{ route('admin.roles.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 hover:border-[#0066FF] transition-all w-[76vw] max-w-[280px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col justify-between">
                    <div>
                        <div class="w-9 h-9 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] flex items-center justify-center mb-3 group-hover:bg-[#0066FF] group-hover:text-white group-hover:border-[#0066FF] transition-all">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Rôles & Permissions</h3>
                        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">Matrice d'habilitation, attribution des accès et règles de sécurité.</p>
                    </div>
                </a>
                @endif

                <!-- Paramètres Généraux -->
                @if($user->isAdmin() || $user->hasPermission('settings.manage'))
                <a href="{{ route('admin.settings.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 hover:border-[#0066FF] transition-all w-[76vw] max-w-[280px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col justify-between">
                    <div>
                        <div class="w-9 h-9 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] flex items-center justify-center mb-3 group-hover:bg-[#0066FF] group-hover:text-white group-hover:border-[#0066FF] transition-all">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Paramètres Généraux</h3>
                        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">Devise, TVA, alertes de stock et configuration globale.</p>
                    </div>
                </a>
                @endif

                <!-- Comptes Utilisateurs -->
                @if($user->isAdmin() || $user->hasPermission('users.view'))
                <a href="{{ route('admin.users.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 hover:border-[#0066FF] transition-all w-[76vw] max-w-[280px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col justify-between">
                    <div>
                        <div class="w-9 h-9 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] flex items-center justify-center mb-3 group-hover:bg-[#0066FF] group-hover:text-white group-hover:border-[#0066FF] transition-all">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Comptes Utilisateurs</h3>
                        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">Gestion des accès, activation de comptes et profils.</p>
                    </div>
                </a>
                @endif

                <!-- Domaines Métiers -->
                @if($user->isAdmin())
                <a href="{{ route('admin.domains.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 hover:border-[#0066FF] transition-all w-[76vw] max-w-[280px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col justify-between">
                    <div>
                        <div class="w-9 h-9 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] flex items-center justify-center mb-3 group-hover:bg-[#0066FF] group-hover:text-white group-hover:border-[#0066FF] transition-all">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Domaines Métiers</h3>
                        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">Gestion et configuration multi-pôles d'activité IVOSPHERE.</p>
                    </div>
                </a>
                @endif

                <!-- Journal d'Audit & Sécurité -->
                @if($user->isAdmin() || $user->hasPermission('logs.view'))
                <a href="{{ route('admin.logs.index') }}" class="group bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 hover:border-[#0066FF] transition-all w-[76vw] max-w-[280px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink flex flex-col justify-between">
                    <div>
                        <div class="w-9 h-9 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] flex items-center justify-center mb-3 group-hover:bg-[#0066FF] group-hover:text-white group-hover:border-[#0066FF] transition-all">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition-colors">Journal d'Audit</h3>
                        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">Historique complet des actions, connexions et modifications.</p>
                    </div>
                </a>
                @endif

            </div>
        </div>
        @endif

    </div>
</x-layouts.app>
