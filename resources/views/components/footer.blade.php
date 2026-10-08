@props([])

<footer class="mt-auto pt-8 pb-6 border-t border-[#E2E8F0] text-xs text-[#64748B]">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <!-- Gauche : Marque & Droits -->
        <div class="flex items-center gap-2 text-left">
            <img src="{{ asset('images/logo.png') }}" alt="IVOSPHERE" class="w-5 h-5 rounded object-contain">
            <span class="font-semibold text-[#0B0F14] tracking-tight">IVOSPHERE</span>
            <span class="text-[#64748B]">&bull; ERP & CRM</span>
            <span class="hidden md:inline text-slate-400">&bull; &copy; {{ date('Y') }} Tous droits réservés</span>
        </div>

        <!-- Droite : Statut système & Version -->
        <div class="flex items-center gap-4 text-xs">
            <span class="font-mono text-[11px] text-[#64748B]">v1.0.0</span>
            <span class="text-slate-300 hidden sm:inline">|</span>
            <a href="#" class="hidden sm:inline text-[#64748B] hover:text-[#0066FF] transition-colors">
                Support & Documentation
            </a>
        </div>
    </div>
</footer>
