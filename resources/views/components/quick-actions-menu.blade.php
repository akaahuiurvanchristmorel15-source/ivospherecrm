<div class="relative" x-data="{ open: false }" @open-quick-actions.window="open = true">
    <button 
        type="button" 
        @click="open = !open" 
        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#0066FF] hover:bg-[#0052cc] text-white shadow-sm shadow-[#0066FF]/20 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0066FF]"
        title="Création rapide (Actions express)"
        aria-label="Nouvelle action"
    >
        <svg class="w-4 h-4 transition-transform duration-150" :class="open ? 'rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div 
        x-show="open" 
        @click.outside="open = false" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-64 rounded-xl bg-white border border-[#E2E8F0] shadow-xl py-2 z-50 text-xs"
        style="display: none;"
    >
        <div class="px-3.5 py-1.5 border-b border-[#E2E8F0]">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Création express</span>
        </div>

        <div class="py-1">
            <a 
                href="{{ route('rh.portal.index') }}" 
                class="flex items-center gap-3 px-3.5 py-2 text-[#0B0F14] hover:bg-[#F5F7FA] hover:text-[#0066FF] transition-colors"
            >
                <div class="w-6 h-6 rounded-md bg-blue-50 text-[#0066FF] flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <p class="font-medium">Mon Espace Collaborateur</p>
                    <p class="text-[11px] text-slate-400">Pointage, Tâches, Planning & Note /30</p>
                </div>
            </a>

            <a 
                href="{{ route('rh.attendance.scanner') }}" 
                class="flex items-center gap-3 px-3.5 py-2 text-[#0B0F14] hover:bg-[#F5F7FA] hover:text-[#0066FF] transition-colors"
            >
                <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <div>
                    <p class="font-medium">Pointage Présence</p>
                    <p class="text-[11px] text-slate-400">Arrivée & Départ (QR & GPS)</p>
                </div>
            </a>

            <a 
                href="{{ route('commercial.quotations.create') }}" 
                class="flex items-center gap-3 px-3.5 py-2 text-[#0B0F14] hover:bg-[#F5F7FA] hover:text-[#0066FF] transition-colors"
            >
                <div class="w-6 h-6 rounded-md bg-[#0066FF]/10 text-[#0066FF] flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <p class="font-medium">Nouveau Devis</p>
                    <p class="text-[11px] text-slate-400">Offre commerciale client</p>
                </div>
            </a>

            <a 
                href="{{ route('commercial.pos.index') }}" 
                class="flex items-center gap-3 px-3.5 py-2 text-[#0B0F14] hover:bg-[#F5F7FA] hover:text-[#0066FF] transition-colors"
            >
                <div class="w-6 h-6 rounded-md bg-[#0066FF]/10 text-[#0066FF] flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div>
                    <p class="font-medium">Vente Comptoir / POS</p>
                    <p class="text-[11px] text-slate-400">Encaissement direct</p>
                </div>
            </a>

            <a 
                href="{{ route('commercial.customers.create') }}" 
                class="flex items-center gap-3 px-3.5 py-2 text-[#0B0F14] hover:bg-[#F5F7FA] hover:text-[#0066FF] transition-colors"
            >
                <div class="w-6 h-6 rounded-md bg-[#0066FF]/10 text-[#0066FF] flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <div>
                    <p class="font-medium">Nouveau Client</p>
                    <p class="text-[11px] text-slate-400">Compte B2B ou B2C</p>
                </div>
            </a>

            <a 
                href="{{ route('support.create') }}" 
                class="flex items-center gap-3 px-3.5 py-2 text-[#0B0F14] hover:bg-[#F5F7FA] hover:text-[#0066FF] transition-colors"
            >
                <div class="w-6 h-6 rounded-md bg-[#0066FF]/10 text-[#0066FF] flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <p class="font-medium">Nouveau Ticket Support</p>
                    <p class="text-[11px] text-slate-400">Assistance & réclamation</p>
                </div>
            </a>

            <a 
                href="{{ route('contracts.create') }}" 
                class="flex items-center gap-3 px-3.5 py-2 text-[#0B0F14] hover:bg-[#F5F7FA] hover:text-[#0066FF] transition-colors"
            >
                <div class="w-6 h-6 rounded-md bg-[#0066FF]/10 text-[#0066FF] flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <p class="font-medium">Nouveau Contrat</p>
                    <p class="text-[11px] text-slate-400">Tiers, prestation, partenariat</p>
                </div>
            </a>

            <a 
                href="{{ route('finance.expenses.create') }}" 
                class="flex items-center gap-3 px-3.5 py-2 text-[#0B0F14] hover:bg-[#F5F7FA] hover:text-[#0066FF] transition-colors"
            >
                <div class="w-6 h-6 rounded-md bg-[#0066FF]/10 text-[#0066FF] flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <p class="font-medium">Nouvelle Dépense</p>
                    <p class="text-[11px] text-slate-400">Note de frais ou achat</p>
                </div>
            </a>
        </div>
    </div>
</div>
