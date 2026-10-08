<x-layouts.app>
    <x-slot:title>Tableau de bord RH — IVOSPHERE ERP</x-slot>

    <x-page-header 
        title="Ressources Humaines" 
        description="Gestion intégrale du personnel, plannings 6j, pointage QR géolocalisé, tâches & notations sur 30"
        :breadcrumbs="[['label' => 'Administration'], ['label' => 'RH']]"
    >
        <x-slot:actions>
            <div class="flex items-center flex-wrap gap-2">
                <x-button :href="route('rh.attendance.terminal')" variant="secondary" size="md">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Borne Réception</span>
                </x-button>
                <x-button :href="route('rh.evaluations.index')" variant="secondary" size="md">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <span>Évaluations (/30)</span>
                </x-button>
                <x-button :href="route('rh.settings.index')" variant="secondary" size="md">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Paramètres RH</span>
                </x-button>
                <x-button :href="route('rh.employees.create')" variant="primary" size="md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nouvel Employé</span>
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- KPI RH Dynamiques -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card 
            title="Total Employés" 
            :value="$totalEmployees ?? 0" 
            change="Salariés actifs" 
            changeType="neutral"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Présents Aujourd'hui" 
            :value="$presentToday ?? 0" 
            :change="($lateToday > 0 ? $lateToday . ' retard(s) noté(s)' : '100% à l\'heure')" 
            :changeType="$lateToday > 0 ? 'warning' : 'up'"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Tâches en Attente" 
            :value="$pendingTaskValidations ?? 0" 
            change="À valider par managers" 
            :changeType="$pendingTaskValidations > 0 ? 'warning' : 'neutral'"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Moyenne Notations /30" 
            :value="$monthlyEvaluationAvg > 0 ? $monthlyEvaluationAvg . ' / 30' : 'N/A'" 
            change="Campagne mensuelle en cours" 
            changeType="neutral"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    <!-- Modules RH Complets (13 Sous-modules) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Sous-modules Opérationnels & Présences -->
        <x-card title="Gestion Quotidienne & Plannings" subtitle="Pointage, plannings 6 jours et suivi des tâches">
            <div class="space-y-2.5">
                <a href="{{ route('rh.attendance.index') }}" class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-[#F5F7FA]/60 hover:bg-[#F5F7FA] hover:border-slate-200 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white border border-[#E2E8F0] text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-[#0B0F14] block">Pointages & Présences</span>
                            <span class="text-[11px] text-[#64748B]">Suivi des arrivées, départs auto à 20h et retards</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs text-[#0066FF] font-medium shrink-0">
                        <span>Consulter</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>

                <a href="{{ route('rh.attendance.terminal') }}" class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-[#F5F7FA]/60 hover:bg-[#F5F7FA] hover:border-slate-200 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white border border-[#E2E8F0] text-purple-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-[#0B0F14] block">Borne Réception (QR Dynamique)</span>
                            <span class="text-[11px] text-[#64748B]">QR Code tournant toutes les 30s pour scan à l'entrée</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs text-purple-600 font-medium shrink-0">
                        <span>Lancer</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>

                <a href="{{ route('rh.attendance.poster') }}" class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-[#F5F7FA]/60 hover:bg-[#F5F7FA] hover:border-slate-200 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white border border-[#E2E8F0] text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-[#0B0F14] block">Affiche QR Mensuelle (A4 Imprimable)</span>
                            <span class="text-[11px] text-[#64748B]">QR code mural sécurisé, réactualisé chaque 1er du mois</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs text-emerald-700 font-medium shrink-0">
                        <span>Imprimer</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>

                <a href="{{ route('rh.schedules.index') }}" class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-[#F5F7FA]/60 hover:bg-[#F5F7FA] hover:border-slate-200 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white border border-[#E2E8F0] text-[#0066FF] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-[#0B0F14] block">Plannings & Horaires (Max 6 Jours)</span>
                            <span class="text-[11px] text-[#64748B]">Matrice 7 jours, repos hebdomadaire garanti</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs text-[#0066FF] font-medium shrink-0">
                        <span>Gérer</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>

                <a href="{{ route('rh.daily-tasks.index') }}" class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-[#F5F7FA]/60 hover:bg-[#F5F7FA] hover:border-slate-200 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white border border-[#E2E8F0] text-amber-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-[#0B0F14] block">Fiches de Tâches Quotidiennes</span>
                            <span class="text-[11px] text-[#64748B]">Attribution, validation manager (0.23 max) et PDF</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs text-[#0066FF] font-medium shrink-0">
                        <span>Gérer</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>
            </div>
        </x-card>

        <!-- Sous-modules RH, Notations & Paramètres -->
        <x-card title="Notations, Évaluations & Carrières" subtitle="Barème sur 30, dossiers collaborateurs et paramètres">
            <div class="space-y-2.5">
                <div class="rounded-xl border border-amber-200/70 bg-amber-50/20 p-3 space-y-2">
                    <a href="{{ route('rh.evaluations.index') }}" class="flex items-center justify-between hover:opacity-85 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white border border-amber-200 text-amber-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-semibold text-[#0B0F14] block">Évaluations Mensuelles (/30)</span>
                                    <span class="px-1.5 py-0.5 text-[9px] font-medium rounded-full bg-amber-100/80 text-amber-800 border border-amber-200/60">Le 5 du mois</span>
                                </div>
                                <span class="text-[11px] text-[#64748B]">Système complet de notation normalisée, workflow & rapports</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 text-xs text-amber-700 font-medium shrink-0">
                            <span>Ouvrir</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                    
                    <!-- Raccourcis rapides des 16 fonctionnalités -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 pt-2 border-t border-amber-200/50 text-[11px]">
                        <a href="{{ route('rh.evaluations.criteria.index') }}" class="px-2 py-1.5 rounded-lg bg-white hover:bg-slate-50 text-[#0B0F14] font-medium text-center truncate border border-slate-200/80 transition">
                            Critères RH
                        </a>
                        <a href="{{ route('rh.goals.index') }}" class="px-2 py-1.5 rounded-lg bg-white hover:bg-slate-50 text-[#0B0F14] font-medium text-center truncate border border-slate-200/80 transition">
                            Objectifs
                        </a>
                        <a href="{{ route('rh.pips.index') }}" class="px-2 py-1.5 rounded-lg bg-white hover:bg-slate-50 text-[#0B0F14] font-medium text-center truncate border border-slate-200/80 transition">
                            Plans PIP
                        </a>
                        <a href="{{ route('rh.evaluations.ranking') }}" class="px-2 py-1.5 rounded-lg bg-white hover:bg-slate-50 text-[#0B0F14] font-medium text-center truncate border border-slate-200/80 transition">
                            Palmarès
                        </a>
                    </div>
                </div>

                <a href="{{ route('rh.employees.index') }}" class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-[#F5F7FA]/60 hover:bg-[#F5F7FA] hover:border-slate-200 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white border border-[#E2E8F0] text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-[#0B0F14] block">Annuaire des Employés</span>
                            <span class="text-[11px] text-[#64748B]">Dossiers complets, matricules, badges et QR codes</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs text-[#0066FF] font-medium shrink-0">
                        <span>Consulter</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>

                <a href="{{ route('rh.leaves.index') }}" class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-[#F5F7FA]/60 hover:bg-[#F5F7FA] hover:border-slate-200 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white border border-[#E2E8F0] text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-[#0B0F14] block">Congés & Absences</span>
                            <span class="text-[11px] text-[#64748B]">Circuit de validation hiérarchique et soldes</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs text-[#0066FF] font-medium shrink-0">
                        <span>Consulter</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>

                <a href="{{ route('rh.sales-targets.index') }}" class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-[#F5F7FA]/60 hover:bg-[#F5F7FA] hover:border-slate-200 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white border border-[#E2E8F0] text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-[#0B0F14] block">Objectifs des Ventes & Commissions</span>
                            <span class="text-[11px] text-[#64748B]">Suivi des paliers commerciaux et commissions</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs text-[#0066FF] font-medium shrink-0">
                        <span>Consulter</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>

                <a href="{{ route('rh.settings.index') }}" class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-[#F5F7FA]/60 hover:bg-[#F5F7FA] hover:border-slate-200 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white border border-[#E2E8F0] text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-[#0B0F14] block">Paramètres RH & Géofencing</span>
                            <span class="text-[11px] text-[#64748B]">Rayon 100m, clôture auto 20h, tolérance retard & pondérations</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs text-[#0066FF] font-medium shrink-0">
                        <span>Configurer</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>
            </div>
        </x-card>
    </div>

    <!-- Derniers Pointages & Activités Récentes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Derniers Pointages du Jour -->
        <x-card title="Derniers Pointages du Jour" subtitle="Arrivées et départs constatés en temps réel">
            <x-slot:actions>
                <a href="{{ route('rh.attendance.index') }}" class="inline-flex items-center gap-1 text-xs text-[#0066FF] hover:underline font-medium">
                    <span>Tout le registre</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
                </a>
            </x-slot:actions>

            @if(isset($latestAttendances) && $latestAttendances->count() > 0)
                <div class="divide-y divide-slate-100">
                    @foreach($latestAttendances as $att)
                        <div class="py-3 flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-[#0B0F14] text-white flex items-center justify-center font-semibold text-xs shrink-0">
                                    {{ mb_substr($att->employee->first_name ?? 'E', 0, 1) . mb_substr($att->employee->last_name ?? '', 0, 1) }}
                                </div>
                                <div class="truncate">
                                    <span class="font-medium text-[#0B0F14] block truncate">
                                        {{ $att->employee->full_name ?? 'Collaborateur' }}
                                    </span>
                                    <span class="text-[11px] text-[#64748B]">
                                        {{ $att->employee->matricule ?? 'EMP' }} <span class="mx-1 text-slate-300">·</span> {{ $att->employee->department ?? 'Général' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2.5 shrink-0">
                                <div class="text-right">
                                    <span class="tabular-nums font-semibold text-[#0B0F14] block">
                                        {{ $att->check_in_time ? substr($att->check_in_time, 0, 5) : '--:--' }}
                                        &rarr;
                                        {{ $att->check_out_time ? substr($att->check_out_time, 0, 5) : 'Présent' }}
                                    </span>
                                    @if($att->check_out_type === 'automatic')
                                        <span class="text-[10px] text-amber-600 font-medium block">Sortie auto (20h)</span>
                                    @endif
                                </div>

                                <x-badge :variant="$att->is_late ? 'warning' : 'success'" size="sm">
                                    {{ $att->is_late ? 'Retard' : 'À l\'heure' }}
                                </x-badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-[#64748B] text-xs">
                    Aucun pointage enregistré pour la journée en cours.
                </div>
            @endif
        </x-card>

        <!-- Activités RH Récentes -->
        <x-card title="Journal d'Activité RH" subtitle="Dernières actions administratives & validations">
            @if(isset($recentActivities) && $recentActivities->count() > 0)
                <div class="divide-y divide-slate-100">
                    @foreach($recentActivities as $log)
                        <div class="py-3 flex items-center justify-between text-xs gap-3">
                            <div class="min-w-0">
                                <span class="font-medium text-[#0B0F14] block truncate">
                                    {{ $log->description ?? $log->action }}
                                </span>
                                <span class="text-[11px] text-[#64748B]">
                                    Par {{ $log->user?->name ?? 'Système' }} <span class="mx-1 text-slate-300">·</span> {{ $log->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-mono bg-[#F5F7FA] text-[#64748B] border border-slate-100 shrink-0">
                                {{ $log->action }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-[#64748B] text-xs">
                    Aucune activité RH récente enregistrée.
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
