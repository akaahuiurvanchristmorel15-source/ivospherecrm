<x-layouts.app>
    <x-slot:title>Tableau de Bord des Évaluations RH (/30) — IVOSPHERE</x-slot>

    <div class="space-y-6">
        <!-- En-tête Principal & Navigation Modules Évaluations -->
        <x-page-header 
            title="Évaluations des Collaborateurs" 
            description="Tableau de bord de pilotage des performances RH normalisées sur 30 points, workflow de validation et suivi des progressions"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Évaluations & Performances']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('rh.evaluations.criteria.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-[#E2E8F0] hover:border-[#0066FF] hover:text-[#0066FF] text-xs font-semibold text-[#0B0F14] transition shadow-xs">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
                        <span>Critères RH</span>
                    </a>

                    <a href="{{ route('rh.goals.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-[#E2E8F0] hover:border-[#0066FF] hover:text-[#0066FF] text-xs font-semibold text-[#0B0F14] transition shadow-xs">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        <span>Objectifs</span>
                    </a>

                    <a href="{{ route('rh.pips.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-[#E2E8F0] hover:border-[#0066FF] hover:text-[#0066FF] text-xs font-semibold text-[#0B0F14] transition shadow-xs">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Plans d'Amélioration (PIP)</span>
                    </a>

                    <a href="{{ route('rh.evaluations.ranking', ['year' => $year, 'month' => $month]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-[#E2E8F0] hover:border-[#0066FF] hover:text-[#0066FF] text-xs font-semibold text-[#0B0F14] transition shadow-xs">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        <span>Palmarès & Classement</span>
                    </a>

                    <form action="{{ route('rh.evaluations.generate') }}" method="POST" onsubmit="return confirm('Synchroniser et préremplir les évaluations pour {{ $monthNames[$month] }} {{ $year }} ?');">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <x-button variant="primary" type="submit" size="md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Générer / Recalculer Campagne</span>
                        </x-button>
                    </form>
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- Point 1 : KPI Tableau de Bord RH -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card 
                title="Moyenne Entreprise /20" 
                :value="number_format($stats['companyAverage'] ?? 0, 2, ',', ' ') . ' / 20'" 
                :change="(($stats['companyAverage'] ?? 0) >= 14 ? 'Niveau satisfaisant' : 'Vigilance requise')"
                :changeType="(($stats['companyAverage'] ?? 0) >= 14 ? 'up' : 'down')"
                timeframe="Système sur 20 (Moyenne Entreprise /30 : {{ number_format(($stats['companyAverage'] ?? 0) * 1.5, 1) }} / 30)"
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Couverture Campagne" 
                :value="($stats['evaluatedCount'] ?? 0) . ' / ' . ($stats['totalActiveEmployees'] ?? 0)" 
                change="{{ ($stats['pendingCount'] ?? 0) . ' en attente de validation' }}" 
                changeType="neutral"
                timeframe="Collaborateurs actifs"
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="En Progression (+)" 
                :value="($stats['progressingCount'] ?? 0) . ' collaborateurs'" 
                change="Hausse vs mois précédent" 
                changeType="up"
                timeframe="Dynamique positive"
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Nécessitant Suivi" 
                :value="($stats['followUpCount'] ?? 0) . ' collaborateurs'" 
                change="Moyenne < 12/20 ou baisse" 
                changeType="down"
                timeframe="Candidats au PIP"
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </x-slot:icon>
            </x-stat-card>
        </div>

        <!-- Synthèse des Services & Tendance 6 Mois -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Moyennes par Service -->
            <div class="lg:col-span-2 rounded-2xl bg-white border border-[#E2E8F0] p-5 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-[#0B0F14]">Performances Moyennes par Service</h3>
                        <p class="text-xs text-[#64748B]">Moyenne des 10 matières sur 20 pour {{ $monthNames[$month] }} {{ $year }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-blue-50 text-[#0066FF]">
                        {{ count($stats['departmentAverages'] ?? []) }} Services actifs
                    </span>
                </div>

                @if(count($stats['departmentAverages'] ?? []) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($stats['departmentAverages'] as $deptName => $deptData)
                            @php
                                $dScore = (float) $deptData['avg_score'];
                                $pctBar = min(100, round(($dScore / 20) * 100));
                                $deptColor = match(true) {
                                    $dScore >= 16 => 'text-emerald-700 bg-emerald-500',
                                    $dScore >= 14 => 'text-[#0066FF] bg-[#0066FF]',
                                    $dScore >= 12 => 'text-amber-700 bg-amber-500',
                                    default => 'text-rose-700 bg-rose-500',
                                };
                            @endphp
                            <div class="p-3.5 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC]/50 hover:bg-white hover:border-[#CBD5E1] transition">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-bold text-[#0B0F14] truncate">{{ $deptName }}</span>
                                    <span class="font-mono font-bold text-[#0B0F14]">{{ number_format($dScore, 2, ',', ' ') }} <span class="text-[10px] text-[#64748B]">/20</span></span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden my-2">
                                    <div class="h-full rounded-full transition-all {{ explode(' ', $deptColor)[1] }}" style="width: {{ $pctBar }}%;"></div>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-[#64748B]">
                                    <span>{{ $deptData['count'] }} évalué(s)</span>
                                    <span>{{ $pctBar }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-[#64748B] py-6 text-center">Aucune note départementale calculée pour cette période.</p>
                @endif
            </div>

            <!-- Tendance 6 Mois Entreprise -->
            <div class="rounded-2xl bg-white border border-[#E2E8F0] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-bold text-[#0B0F14]">Tendance Globale</h3>
                        <span class="text-[10px] font-mono text-[#64748B]">6 derniers mois</span>
                    </div>
                    <p class="text-xs text-[#64748B] mb-4">Évolution de la moyenne d'entreprise / 20</p>
                </div>

                <div class="h-36 flex items-end gap-3 pt-4 px-2 border-b border-[#E2E8F0]">
                    @foreach($stats['historicalTrend'] ?? [] as $trend)
                        @php
                            $tScore = (float) ($trend['average'] ?? $trend['avg'] ?? 0);
                            $hPct = min(100, max(10, round(($tScore / 20) * 100)));
                        @endphp
                        <div class="flex-1 flex flex-col items-center h-full justify-end group relative">
                            <div class="absolute -top-7 bg-slate-900 text-white px-2 py-0.5 rounded text-[10px] font-bold opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none z-10">
                                {{ number_format($tScore, 2) }} / 20
                            </div>
                            <div class="w-full rounded-t-lg bg-[#0066FF] hover:bg-blue-600 transition" style="height: {{ $hPct }}%;"></div>
                            <span class="text-[10px] font-bold text-[#64748B] mt-2">{{ $trend['label'] }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="pt-3 text-[11px] text-[#64748B] flex items-center justify-between">
                    <span>Objectif standard : <strong>15,0 / 20</strong></span>
                    <span class="font-mono text-[#0066FF]">Période : {{ $monthNames[$month] }} {{ $year }}</span>
                </div>
            </div>
        </div>
        </div>

        <!-- Filtres & Sélecteur de Période -->
        <x-card>
            <form method="GET" action="{{ route('rh.evaluations.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
                <input type="hidden" name="tab" value="{{ $tab }}">

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Mois d'évaluation</label>
                    <select name="month" class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        @foreach($monthNames as $mNum => $mLabel)
                            <option value="{{ $mNum }}" @selected($month == $mNum)>{{ $mLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Année</label>
                    <select name="year" class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Département</label>
                    <select name="department" class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        <option value="">Tous les départements</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" @selected(request('department') == $dept)>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-3 flex items-end">
                    <button type="submit" class="w-full py-2 px-4 bg-[#0B0F14] hover:bg-[#1E293B] text-white rounded-xl text-xs font-bold transition shadow-xs">
                        Filtrer les fiches
                    </button>
                </div>
            </form>
        </x-card>

        <!-- Onglets de Tri Rapide (Toutes, Progression, Suivi, Verrouillées) -->
        <div class="flex items-center gap-2 border-b border-[#E2E8F0] pb-2 text-xs">
            <a href="{{ route('rh.evaluations.index', array_merge(request()->query(), ['tab' => 'all'])) }}"
               class="px-3.5 py-1.5 rounded-lg font-bold transition {{ $tab === 'all' ? 'bg-[#0B0F14] text-white' : 'text-[#64748B] hover:text-[#0B0F14] hover:bg-slate-100' }}">
                Toutes les fiches ({{ $evaluations->total() }})
            </a>
            <a href="{{ route('rh.evaluations.index', array_merge(request()->query(), ['tab' => 'progressing'])) }}"
               class="px-3.5 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 {{ $tab === 'progressing' ? 'bg-emerald-600 text-white' : 'text-emerald-700 hover:bg-emerald-50' }}">
                <span>En progression ↗</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'progressing' ? 'bg-white/20' : 'bg-emerald-100 text-emerald-800' }}">{{ $stats['progressingCount'] ?? 0 }}</span>
            </a>
            <a href="{{ route('rh.evaluations.index', array_merge(request()->query(), ['tab' => 'follow_up'])) }}"
               class="px-3.5 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 {{ $tab === 'follow_up' ? 'bg-rose-600 text-white' : 'text-rose-700 hover:bg-rose-50' }}">
                <span>Nécessitant un suivi ⚠️</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'follow_up' ? 'bg-white/20' : 'bg-rose-100 text-rose-800' }}">{{ $stats['followUpCount'] ?? 0 }}</span>
            </a>
            <a href="{{ route('rh.evaluations.index', array_merge(request()->query(), ['tab' => 'locked'])) }}"
               class="px-3.5 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 {{ $tab === 'locked' ? 'bg-slate-800 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <span>Verrouillées 🔒</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'locked' ? 'bg-white/20' : 'bg-slate-200 text-slate-800' }}">{{ $stats['lockedCount'] ?? 0 }}</span>
            </a>
        </div>

        <!-- Table des Évaluations Mensuelles Normalisées /20 -->
        <x-card :noPadding="true">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Collaborateur</th>
                            <th class="py-3 px-3 text-center">Jours Ouvrés</th>
                            <th class="py-3 px-3 text-center">Ponctualité (/20)</th>
                            <th class="py-3 px-4 text-center">Moyenne Mensuelle / 20</th>
                            <th class="py-3 px-3 text-center">Appréciation</th>
                            <th class="py-3 px-3 text-center">Progression</th>
                            <th class="py-3 px-3 text-center">Workflow</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($evaluations as $eval)
                            @php
                                $score20 = (float) ($eval->final_score_20 ?: ($eval->final_score_30 / 1.5));
                                $scoreColor = match(true) {
                                    $score20 >= 16 => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                                    $score20 >= 14 => 'text-[#0066FF] bg-blue-50 border-blue-200',
                                    $score20 >= 12 => 'text-amber-700 bg-amber-50 border-amber-200',
                                    default => 'text-rose-700 bg-rose-50 border-rose-200',
                                };
                                $prog = $eval->score_progress;
                            @endphp
                            <tr class="hover:bg-[#F8FAFC] transition">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-[#0066FF] shrink-0">
                                            {{ strtoupper(substr($eval->employee->first_name ?? 'E', 0, 1) . substr($eval->employee->last_name ?? '', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-[#0B0F14] flex items-center gap-1.5">
                                                <span>{{ $eval->employee->full_name }}</span>
                                                @if($eval->is_locked)
                                                    <span title="Dossier formellement verrouillé" class="text-slate-400">🔒</span>
                                                @endif
                                            </div>
                                            <div class="text-[10px] font-mono text-[#64748B]">
                                                {{ $eval->employee->employee_code }} • {{ $eval->employee->department ?? 'Général' }} • {{ $eval->employee->position ?? 'Collaborateur' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3.5 px-3 text-center font-bold text-slate-700">
                                    {{ $eval->total_working_days }} j
                                </td>

                                <td class="py-3.5 px-3 text-center">
                                    <span class="font-mono font-bold text-slate-800">{{ number_format($eval->punctuality_points, 1) }} / 20</span>
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-sm font-extrabold border {{ $scoreColor }}">
                                        {{ number_format($score20, 2, ',', ' ') }} / 20
                                    </span>
                                </td>

                                <td class="py-3.5 px-3 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $eval->appreciation_badge_class }}">
                                        {{ $eval->appreciation }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-3 text-center">
                                    @if(is_null($prog))
                                        <span class="text-slate-400 text-[11px]">—</span>
                                    @elseif($prog > 0)
                                        <span class="inline-flex items-center gap-0.5 text-xs font-bold text-emerald-600">
                                            ↗ +{{ number_format($prog, 2) }}
                                        </span>
                                    @elseif($prog < 0)
                                        <span class="inline-flex items-center gap-0.5 text-xs font-bold text-rose-600">
                                            ↘ {{ number_format($prog, 2) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs font-bold font-mono">0.00</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-3 text-center">
                                    @php
                                        $wfBadge = match($eval->workflow_step) {
                                            'verrouille' => 'bg-slate-100 text-slate-800 border-slate-300',
                                            'valide_rh' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                            'validation_manager' => 'bg-purple-50 text-purple-800 border-purple-200',
                                            'en_evaluation' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                                            default => 'bg-amber-50 text-amber-800 border-amber-200',
                                        };
                                        $wfLabel = match($eval->workflow_step) {
                                            'verrouille' => 'Verrouillé',
                                            'valide_rh' => 'Validé RH',
                                            'validation_manager' => 'Valid. Manager',
                                            'en_evaluation' => 'En évaluation',
                                            default => 'Brouillon',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $wfBadge }}">
                                        {{ $wfLabel }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('rh.evaluations.show', $eval) }}" 
                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-white border border-[#E2E8F0] hover:border-[#0066FF] hover:text-[#0066FF] text-xs font-semibold text-[#0B0F14] transition shadow-2xs">
                                            <span>Fiche</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>

                                        <a href="{{ route('rh.evaluations.print', $eval) }}" target="_blank"
                                            class="p-1 rounded-lg text-[#64748B] hover:text-[#0066FF] hover:bg-blue-50 transition" 
                                            title="Imprimer rapport officiel A4">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>

                                        <a href="{{ route('rh.evaluations.history', $eval->employee) }}" 
                                            class="p-1 rounded-lg text-[#64748B] hover:text-[#0066FF] hover:bg-blue-50 transition" 
                                            title="Historique plurimensuel">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center">
                                    <x-empty-state 
                                        title="Aucune évaluation trouvée"
                                        description="Cliquez sur « Générer / Recalculer Campagne » pour générer automatiquement la campagne pour ce mois."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($evaluations->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $evaluations->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
