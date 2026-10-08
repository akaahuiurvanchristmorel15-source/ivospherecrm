<section class="relative overflow-hidden pt-8 pb-16 sm:pt-14 sm:pb-24 lg:pt-20 lg:pb-28 bg-[#F5F7FA]">
    <!-- Background subtle mesh accents (strictly minimalist & clean) -->
    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[850px] h-[450px] bg-gradient-to-b from-[#0066FF]/8 to-transparent rounded-full blur-3xl"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            <!-- Colonne Gauche : Argumentaire & CTA -->
            <div class="lg:col-span-6 space-y-6 sm:space-y-8 text-left">
                <!-- Petit badge -->
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-[#E2E8F0] shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-[#0066FF] animate-pulse"></span>
                    <span class="text-[11px] sm:text-xs font-semibold tracking-wider text-[#0B0F14] uppercase">
                        PLATEFORME DE GESTION D'ENTREPRISE
                    </span>
                </div>

                <!-- Grand Titre -->
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-[#0B0F14] leading-[1.12]">
                    Pilotez toute votre entreprise depuis <span class="text-[#0066FF]">un seul espace.</span>
                </h1>

                <!-- Description -->
                <p class="text-base sm:text-lg text-[#64748B] leading-relaxed max-w-xl">
                    IVOSPHERE centralise vos ressources humaines, ventes, finances, stocks, clients, projets et opérations dans une plateforme unique, simple et intelligente.
                </p>

                <!-- Boutons CTA -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                    <button type="button" 
                            @click="$dispatch('open-demo-modal')"
                            class="h-12 px-6 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-sm font-semibold text-white shadow-xs hover:shadow transition flex items-center justify-center gap-2 group focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#0066FF]">
                        <span>Commencer maintenant</span>
                        <svg class="w-4 h-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </button>

                    <a href="#solutions" 
                       class="h-12 px-6 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 text-sm font-semibold text-[#0B0F14] transition flex items-center justify-center focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#0066FF]">
                        Découvrir la plateforme
                    </a>
                </div>

                <!-- Réassurance -->
                <div class="pt-2 flex items-center gap-2 text-xs sm:text-sm font-medium text-[#64748B]">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#0066FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Gestion centralisée
                    </span>
                    <span class="text-slate-300">•</span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#0066FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Données sécurisées
                    </span>
                    <span class="text-slate-300">•</span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#0066FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Interface intuitive
                    </span>
                </div>
            </div>

            <!-- Colonne Droite : Dashboard Mockup SaaS Réaliste & Premium -->
            <div class="lg:col-span-6 relative">
                <!-- Décoration d'arrière plan -->
                <div class="absolute -inset-4 bg-gradient-to-r from-blue-100/50 via-slate-100 to-blue-50/30 rounded-3xl -z-10 blur-xl opacity-70"></div>

                <!-- Fenêtre SaaS Principale -->
                <div class="relative bg-white rounded-2xl border border-[#E2E8F0] shadow-xl overflow-hidden transition hover:shadow-2xl">
                    <!-- Top Window Bar (Chrome) -->
                    <div class="h-11 bg-slate-50 border-b border-[#E2E8F0] px-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-400"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                            <span class="ml-2 text-[11px] font-mono text-[#64748B]">app.ivosphere.com/dashboard</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-[10px] font-semibold text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Synchronisé
                            </span>
                        </div>
                    </div>

                    <!-- Inner Dashboard Surface -->
                    <div class="p-4 sm:p-6 space-y-4 bg-[#F5F7FA]">
                        <!-- Barre d'en-tête du Dashboard interne -->
                        <div class="bg-white p-3 sm:p-4 rounded-xl border border-[#E2E8F0] flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-[#0B0F14] text-white flex items-center justify-center font-bold text-xs">
                                    DG
                                </div>
                                <div>
                                    <h4 class="text-xs sm:text-sm font-semibold text-[#0B0F14]">Cockpit Direction Générale</h4>
                                    <p class="text-[11px] text-[#64748B]">Groupe IVOSPHERE • 5 pôles actifs</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-[11px] font-semibold text-[#0066FF] border border-blue-100">
                                    Direct Live
                                </span>
                            </div>
                        </div>

                        <!-- Ligne 1 : KPI Cards Principaux -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <!-- Chiffre d'affaires -->
                            <div class="bg-white p-3 rounded-xl border border-[#E2E8F0]">
                                <span class="text-[10px] font-medium text-[#64748B]">Chiffre d'affaires</span>
                                <div class="text-xs sm:text-base font-bold text-[#0B0F14] tabular-nums mt-0.5">
                                    128,4M <span class="text-[9px] font-normal text-slate-400">FCFA</span>
                                </div>
                                <span class="text-[10px] font-semibold text-emerald-600 flex items-center gap-0.5 mt-1">
                                    ↑ +18.4%
                                </span>
                            </div>

                            <!-- Trésorerie -->
                            <div class="bg-white p-3 rounded-xl border border-[#E2E8F0]">
                                <span class="text-[10px] font-medium text-[#64748B]">Trésorerie nette</span>
                                <div class="text-xs sm:text-base font-bold text-[#0066FF] tabular-nums mt-0.5">
                                    42,8M <span class="text-[9px] font-normal text-slate-400">FCFA</span>
                                </div>
                                <span class="text-[10px] font-semibold text-[#64748B] mt-1 block">
                                    Banques & Caisses
                                </span>
                            </div>

                            <!-- Ventes du jour -->
                            <div class="bg-white p-3 rounded-xl border border-[#E2E8F0]">
                                <span class="text-[10px] font-medium text-[#64748B]">Ventes du jour</span>
                                <div class="text-xs sm:text-base font-bold text-[#0B0F14] tabular-nums mt-0.5">
                                    3,85M <span class="text-[9px] font-normal text-slate-400">FCFA</span>
                                </div>
                                <span class="text-[10px] font-medium text-[#64748B] mt-1 block">
                                    48 commandes
                                </span>
                            </div>

                            <!-- Présence RH -->
                            <div class="bg-white p-3 rounded-xl border border-[#E2E8F0]">
                                <span class="text-[10px] font-medium text-[#64748B]">RH / Pointage QR</span>
                                <div class="text-xs sm:text-base font-bold text-emerald-600 tabular-nums mt-0.5">
                                    34 / 36
                                </div>
                                <span class="text-[10px] font-semibold text-emerald-700 mt-1 block">
                                    94% présents
                                </span>
                            </div>
                        </div>

                        <!-- Ligne 2 : Graphique et Activités Récentes -->
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                            <!-- Mini Graphique Chiffre d'affaires & Trésorerie -->
                            <div class="sm:col-span-7 bg-white p-3.5 rounded-xl border border-[#E2E8F0] space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-[#0B0F14]">Évolution des revenus (S1 2026)</span>
                                    <span class="text-[10px] font-mono text-[#64748B]">+24% vs N-1</span>
                                </div>
                                <!-- Visual Chart Mockup SVG -->
                                <div class="h-24 w-full pt-1">
                                    <svg class="w-full h-full" viewBox="0 0 300 80" fill="none" preserveAspectRatio="none">
                                        <defs>
                                            <linearGradient id="heroGradient" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#0066FF" stop-opacity="0.25"/>
                                                <stop offset="100%" stop-color="#0066FF" stop-opacity="0.0"/>
                                            </linearGradient>
                                        </defs>
                                        <path d="M0,65 Q40,55 80,45 T160,35 T220,20 T300,10 L300,80 L0,80 Z" fill="url(#heroGradient)" />
                                        <path d="M0,65 Q40,55 80,45 T160,35 T220,20 T300,10" stroke="#0066FF" stroke-width="2.5" stroke-linecap="round" />
                                        <!-- Data Points -->
                                        <circle cx="80" cy="45" r="3.5" fill="#0066FF" stroke="#FFFFFF" stroke-width="1.5" />
                                        <circle cx="160" cy="35" r="3.5" fill="#0066FF" stroke="#FFFFFF" stroke-width="1.5" />
                                        <circle cx="220" cy="20" r="3.5" fill="#0066FF" stroke="#FFFFFF" stroke-width="1.5" />
                                        <circle cx="300" cy="10" r="4" fill="#0066FF" stroke="#FFFFFF" stroke-width="2" />
                                    </svg>
                                </div>
                                <div class="flex items-center justify-between text-[10px] text-[#64748B] pt-1 border-t border-slate-100 font-medium">
                                    <span>Janvier</span>
                                    <span>Février</span>
                                    <span>Mars</span>
                                    <span>Avril (En cours)</span>
                                </div>
                            </div>

                            <!-- Activités Récentes & Alertes -->
                            <div class="sm:col-span-5 bg-white p-3.5 rounded-xl border border-[#E2E8F0] space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-[#0B0F14]">Activités récentes</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                </div>
                                <div class="space-y-2 text-[11px]">
                                    <div class="flex items-start gap-2 pb-1.5 border-b border-slate-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#0066FF] mt-1 shrink-0"></span>
                                        <div>
                                            <p class="font-medium text-[#0B0F14] leading-tight">Commande PRINT validée</p>
                                            <span class="text-[10px] text-[#64748B]">850 000 FCFA • Il y a 4 min</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2 pb-1.5 border-b border-slate-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1 shrink-0"></span>
                                        <div>
                                            <p class="font-medium text-[#0B0F14] leading-tight">Contrat Assurance émis</p>
                                            <span class="text-[10px] text-[#64748B]">Atlanta • Il y a 18 min</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1 shrink-0"></span>
                                        <div>
                                            <p class="font-medium text-[#0B0F14] leading-tight">Stock alerte papier 80g</p>
                                            <span class="text-[10px] text-amber-600 font-semibold">Réassort suggéré</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floating Card 1 (Haut Droite) : Badge Pointage QR -->
                <div class="absolute -top-6 -right-3 sm:-right-6 bg-white p-3 rounded-xl border border-[#E2E8F0] shadow-lg flex items-center gap-3 animate-bounce [animation-duration:5s]">
                    <div class="w-8 h-8 rounded-lg bg-[#0066FF]/10 text-[#0066FF] flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-medium text-[#64748B]">Pointage QR validé</div>
                        <div class="text-xs font-bold text-[#0B0F14]">Équipe Prod • 08:02</div>
                    </div>
                </div>

                <!-- Floating Card 2 (Bas Gauche) : Indicateur Rentabilité Actif -->
                <div class="absolute -bottom-6 -left-3 sm:-left-6 bg-[#0B0F14] text-white p-3 sm:p-3.5 rounded-xl shadow-xl flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#0066FF] flex items-center justify-center text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-300 font-medium">Immobilisations & Actifs</div>
                        <div class="text-xs font-bold text-white tabular-nums">ROI Actifs : +240%</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
