<section class="py-16 sm:py-24 bg-[#F5F7FA]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative bg-[#0B0F14] rounded-3xl p-8 sm:p-14 lg:p-20 text-center overflow-hidden border border-white/10 shadow-2xl">
            <!-- Background mesh & abstract connected network dots -->
            <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
                <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-[#0066FF]/20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 right-1/4 w-[400px] h-[300px] bg-[#0066FF]/15 rounded-full blur-3xl"></div>
                
                <!-- Abstract module connection lines & nodes SVG -->
                <svg class="absolute inset-0 w-full h-full opacity-10" viewBox="0 0 1000 400" fill="none">
                    <line x1="100" y1="100" x2="300" y2="200" stroke="#0066FF" stroke-width="1.5" stroke-dasharray="4 4" />
                    <line x1="300" y1="200" x2="500" y2="120" stroke="#0066FF" stroke-width="1.5" />
                    <line x1="500" y1="120" x2="700" y2="250" stroke="#0066FF" stroke-width="1.5" stroke-dasharray="4 4" />
                    <line x1="700" y1="250" x2="900" y2="150" stroke="#0066FF" stroke-width="1.5" />
                    <circle cx="100" cy="100" r="5" fill="#0066FF" />
                    <circle cx="300" cy="200" r="6" fill="#FFFFFF" />
                    <circle cx="500" cy="120" r="7" fill="#0066FF" />
                    <circle cx="700" cy="250" r="6" fill="#FFFFFF" />
                    <circle cx="900" cy="150" r="5" fill="#0066FF" />
                </svg>
            </div>

            <!-- Content -->
            <div class="max-w-3xl mx-auto space-y-6 relative">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 text-white text-xs font-semibold tracking-wider uppercase border border-white/15">
                    <span class="w-2 h-2 rounded-full bg-[#0066FF]"></span>
                    Déploiement rapide & accompagnement dédié
                </span>

                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                    Passez à une gestion plus intelligente.
                </h2>

                <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl mx-auto">
                    Centralisez vos opérations, maîtrisez vos finances et donnez à vos équipes les outils nécessaires pour mieux travailler.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-4">
                    <button type="button" 
                            @click="$dispatch('open-demo-modal')"
                            class="w-full sm:w-auto h-12 px-8 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-sm font-semibold text-white shadow-lg transition flex items-center justify-center gap-2 group focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-white">
                        <span>Commencer avec IVOSPHERE</span>
                        <svg class="w-4 h-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </button>

                    <button type="button" 
                            @click="$dispatch('open-demo-modal')"
                            class="w-full sm:w-auto h-12 px-8 rounded-xl border border-white/20 hover:border-white hover:bg-white/10 text-sm font-semibold text-white transition flex items-center justify-center focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-white">
                        Demander une démonstration
                    </button>
                </div>

                <div class="pt-6 flex flex-wrap items-center justify-center gap-6 text-xs text-slate-400">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#0066FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Aucune carte bancaire requise
                    </span>
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#0066FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Configuration sur mesure
                    </span>
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#0066FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Support réactif en Côte d'Ivoire & Afrique
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>
