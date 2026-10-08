<footer class="bg-[#0B0F14] text-white border-t border-white/10 pt-16 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 pb-12 border-b border-white/10">
            <!-- Colonne 1 : Marque & Présentation -->
            <div class="lg:col-span-4 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white text-[#0B0F14] flex items-center justify-center font-extrabold text-base tracking-wider">
                        IVO<span class="text-[#0066FF]">.</span>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-white">IVOSPHERE</span>
                </div>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-sm">
                    La plateforme qui centralise la gestion de votre entreprise. Une seule interface pour piloter l'ensemble de vos opérations commerciales, financières et humaines.
                </p>
                <div class="pt-2 flex items-center gap-3 text-slate-400">
                    <a href="#" aria-label="LinkedIn" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-[#0066FF] hover:text-white flex items-center justify-center transition border border-white/10">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                    </a>
                    <a href="#" aria-label="Twitter / X" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-[#0066FF] hover:text-white flex items-center justify-center transition border border-white/10">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="#" aria-label="Facebook" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-[#0066FF] hover:text-white flex items-center justify-center transition border border-white/10">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Colonne 2 : Plateforme -->
            <div class="lg:col-span-2 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white">Plateforme</h4>
                <ul class="space-y-2 text-xs sm:text-sm text-slate-400">
                    <li><a href="#modules" class="hover:text-white transition">Tableau de bord</a></li>
                    <li><a href="#rh" class="hover:text-white transition">RH & Pointage QR</a></li>
                    <li><a href="#modules" class="hover:text-white transition">CRM & Ventes</a></li>
                    <li><a href="#finance" class="hover:text-white transition">Finance & Trésorerie</a></li>
                    <li><a href="#modules" class="hover:text-white transition">Stocks & Entrepôts</a></li>
                    <li><a href="#finance" class="hover:text-white transition">Immobilisations</a></li>
                    <li><a href="#modules" class="hover:text-white transition">Projets & GED</a></li>
                </ul>
            </div>

            <!-- Colonne 3 : Activités -->
            <div class="lg:col-span-2 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white">Activités</h4>
                <ul class="space-y-2 text-xs sm:text-sm text-slate-400">
                    <li><a href="#activites" class="hover:text-white transition">PRINT</a></li>
                    <li><a href="#activites" class="hover:text-white transition">SPORT</a></li>
                    <li><a href="#activites" class="hover:text-white transition">TECH</a></li>
                    <li><a href="#activites" class="hover:text-white transition">MEDIA & ÉVÈNEMENTS</a></li>
                    <li><a href="#activites" class="hover:text-white transition">ASSURANCE (Atlanta)</a></li>
                </ul>
            </div>

            <!-- Colonne 4 : Entreprise -->
            <div class="lg:col-span-2 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white">Entreprise</h4>
                <ul class="space-y-2 text-xs sm:text-sm text-slate-400">
                    <li><a href="#a-propos" class="hover:text-white transition">À propos</a></li>
                    <li><button type="button" @click="$dispatch('open-demo-modal')" class="hover:text-white transition text-left">Contact & Démo</button></li>
                    <li><a href="#faq" class="hover:text-white transition">FAQ</a></li>
                    <li><a href="#tarifs" class="hover:text-white transition">Tarifs</a></li>
                    <li><a href="{{ route('login') }}" class="text-[#0066FF] hover:underline font-medium">Espace Collaborateur</a></li>
                </ul>
            </div>

            <!-- Colonne 5 : Légal -->
            <div class="lg:col-span-2 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white">Légal</h4>
                <ul class="space-y-2 text-xs sm:text-sm text-slate-400">
                    <li><a href="#" class="hover:text-white transition">Politique de confidentialité</a></li>
                    <li><a href="#" class="hover:text-white transition">Conditions d'utilisation</a></li>
                    <li><a href="#" class="hover:text-white transition">Gestion des cookies</a></li>
                    <li><a href="#" class="hover:text-white transition">Sécurité & RGPD</a></li>
                </ul>
            </div>
        </div>

        <!-- Bas de footer -->
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
            <p>&copy; 2026 IVOSPHERE. Tous droits réservés.</p>
            <div class="flex items-center gap-6">
                <span class="inline-flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    Système opérationnel & certifié
                </span>
                <span class="text-slate-600">|</span>
                <span>Abidjan, Côte d'Ivoire</span>
            </div>
        </div>
    </div>
</footer>
