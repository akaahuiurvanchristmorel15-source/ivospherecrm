<x-layouts.app>
    <x-slot:title>Palmarès & Classement Interne des Performances — IVOSPHERE RH</x-slot>

    @php
        $monthNames = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];
    @endphp

    <div class="space-y-6">
        <x-page-header 
            title="Palmarès & Classement Interne" 
            :description="'Tableau d\'honneur des collaborateurs et performances d\'excellence pour ' . $monthNames[$month] . ' ' . $year"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Évaluations', 'url' => route('rh.evaluations.index')],
                    ['label' => 'Classement']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <x-button :href="route('rh.evaluations.index', ['year' => $year, 'month' => $month])" variant="secondary" size="md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Retour Campagne</span>
                    </x-button>
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- Filtres Période & Service -->
        <x-card>
            <form method="GET" action="{{ route('rh.evaluations.ranking') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Mois</label>
                    <select name="month" class="w-full px-3.5 py-2 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                        @foreach($monthNames as $mNum => $mLabel)
                            <option value="{{ $mNum }}" @selected($month == $mNum)>{{ $mLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Année</label>
                    <select name="year" class="w-full px-3.5 py-2 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Département</label>
                    <select name="department" class="w-full px-3.5 py-2 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                        <option value="">Tous les départements</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" @selected($department == $dept)>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2 flex items-end">
                    <button type="submit" class="w-full py-2 px-4 bg-[#0B0F14] hover:bg-[#1E293B] text-white rounded-xl text-xs font-bold transition">
                        Filtrer
                    </button>
                </div>
            </form>
        </x-card>

        <!-- TOP 3 PODIUM VISUEL -->
        @if(!empty($ranking['topOverall']) && $ranking['topOverall']->count() >= 3)
            @php
                $first = $ranking['topOverall'][0];
                $second = $ranking['topOverall'][1];
                $third = $ranking['topOverall'][2];
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4">
                <!-- 2ème Place -->
                <div class="rounded-3xl border border-slate-200 bg-white p-5 text-center flex flex-col justify-between order-2 sm:order-1 shadow-xs">
                    <div>
                        <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-700 text-lg mb-2">
                            🥈 2
                        </div>
                        <h4 class="font-extrabold text-[#0B0F14] text-sm">{{ $second->employee->full_name }}</h4>
                        <p class="text-[11px] text-[#64748B]">{{ $second->employee->department ?? 'Général' }} • {{ $second->employee->position ?? 'Collaborateur' }}</p>
                    </div>
                    <div class="pt-4 flex flex-col items-center">
                        <span class="inline-flex px-3 py-1 rounded-xl bg-blue-50 text-[#0066FF] font-mono font-black text-base border border-blue-200">
                            {{ number_format($second->final_score_20 ?? ($second->final_score_30 / 1.5), 2) }} / 20
                        </span>
                        @if($second->appreciation)
                            <span class="mt-1 inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $second->appreciation_badge_class }}">
                                {{ $second->appreciation }}
                            </span>
                        @endif
                        <span class="text-[10px] text-slate-400 font-mono mt-0.5">{{ number_format($second->final_score_30, 1) }} / 30</span>
                    </div>
                </div>

                <!-- 1ère Place (Vainqueur) -->
                <div class="rounded-3xl border-2 border-amber-300 bg-gradient-to-b from-amber-50/50 to-white p-6 text-center flex flex-col justify-between order-1 sm:order-2 shadow-md sm:-translate-y-2">
                    <div>
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-400 text-amber-950 flex items-center justify-center font-black text-xl mb-2 shadow-sm">
                            👑 1
                        </div>
                        <span class="text-[10px] font-mono uppercase font-black tracking-widest text-amber-600 block mb-0.5">Major de Promo RH</span>
                        <h3 class="font-black text-[#0B0F14] text-base">{{ $first->employee->full_name }}</h3>
                        <p class="text-xs text-[#64748B]">{{ $first->employee->department ?? 'Général' }} • {{ $first->employee->position ?? 'Collaborateur' }}</p>
                    </div>
                    <div class="pt-4 flex flex-col items-center">
                        <span class="inline-flex px-4 py-1.5 rounded-2xl bg-emerald-600 text-white font-mono font-black text-lg shadow-sm">
                            {{ number_format($first->final_score_20 ?? ($first->final_score_30 / 1.5), 2) }} / 20
                        </span>
                        @if($first->appreciation)
                            <span class="mt-1 inline-flex px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                {{ $first->appreciation }}
                            </span>
                        @endif
                        <span class="text-[10px] text-slate-400 font-mono mt-0.5">{{ number_format($first->final_score_30, 1) }} / 30</span>
                        @if($first->score_progress > 0)
                            <div class="text-[10px] text-emerald-600 font-bold mt-1">↗ +{{ number_format($first->score_progress, 1) }} pts</div>
                        @endif
                    </div>
                </div>

                <!-- 3ème Place -->
                <div class="rounded-3xl border border-slate-200 bg-white p-5 text-center flex flex-col justify-between order-3 sm:order-3 shadow-xs">
                    <div>
                        <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-100 border border-amber-200 flex items-center justify-center font-bold text-amber-800 text-lg mb-2">
                            🥉 3
                        </div>
                        <h4 class="font-extrabold text-[#0B0F14] text-sm">{{ $third->employee->full_name }}</h4>
                        <p class="text-[11px] text-[#64748B]">{{ $third->employee->department ?? 'Général' }} • {{ $third->employee->position ?? 'Collaborateur' }}</p>
                    </div>
                    <div class="pt-4 flex flex-col items-center">
                        <span class="inline-flex px-3 py-1 rounded-xl bg-blue-50 text-[#0066FF] font-mono font-black text-base border border-blue-200">
                            {{ number_format($third->final_score_20 ?? ($third->final_score_30 / 1.5), 2) }} / 20
                        </span>
                        @if($third->appreciation)
                            <span class="mt-1 inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $third->appreciation_badge_class }}">
                                {{ $third->appreciation }}
                            </span>
                        @endif
                        <span class="text-[10px] text-slate-400 font-mono mt-0.5">{{ number_format($third->final_score_30, 1) }} / 30</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- 3 PALMARÈS THÉMATIQUES (Progression, Ponctualité, Tâches) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Top Progression -->
            <div class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-xs">
                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-emerald-100">
                    <span class="text-emerald-600 font-bold">🚀</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-900">Plus Fortes Progressions</h3>
                </div>
                <div class="space-y-2.5">
                    @forelse($ranking['topProgression'] ?? [] as $idx => $ev)
                        <div class="flex items-center justify-between text-xs p-2 rounded-xl bg-emerald-50/50 border border-emerald-100">
                            <div class="flex items-center gap-2 truncate">
                                <span class="font-mono font-bold text-emerald-700 text-[11px]">#{{ $idx + 1 }}</span>
                                <span class="font-bold text-[#0B0F14] truncate">{{ $ev->employee->full_name }}</span>
                            </div>
                            <span class="font-mono font-bold text-emerald-700 bg-white px-2 py-0.5 rounded-lg border border-emerald-200 shrink-0">
                                +{{ number_format($ev->score_progress, 1) }} pts
                            </span>
                        </div>
                    @empty
                        <p class="text-[11px] text-[#64748B] py-3 text-center">Aucune progression enregistrée ce mois.</p>
                    @endforelse
                </div>
            </div>

            <!-- Top Ponctualité -->
            <div class="rounded-2xl border border-blue-200 bg-white p-5 shadow-xs">
                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-blue-100">
                    <span class="text-[#0066FF] font-bold">⏱️</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-blue-900">Major de Ponctualité</h3>
                </div>
                <div class="space-y-2.5">
                    @forelse($ranking['topPunctuality'] ?? [] as $idx => $ev)
                        <div class="flex items-center justify-between text-xs p-2 rounded-xl bg-blue-50/50 border border-blue-100">
                            <div class="flex items-center gap-2 truncate">
                                <span class="font-mono font-bold text-[#0066FF] text-[11px]">#{{ $idx + 1 }}</span>
                                <span class="font-bold text-[#0B0F14] truncate">{{ $ev->employee->full_name }}</span>
                            </div>
                            <span class="font-mono font-bold text-[#0066FF] bg-white px-2 py-0.5 rounded-lg border border-blue-200 shrink-0">
                                {{ number_format($ev->punctuality_points, 2) }} pts
                            </span>
                        </div>
                    @empty
                        <p class="text-[11px] text-[#64748B] py-3 text-center">Aucune donnée de ponctualité.</p>
                    @endforelse
                </div>
            </div>

            <!-- Top Tâches Validées -->
            <div class="rounded-2xl border border-purple-200 bg-white p-5 shadow-xs">
                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-purple-100">
                    <span class="text-purple-600 font-bold">📋</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-purple-900">Major des Tâches Validées</h3>
                </div>
                <div class="space-y-2.5">
                    @forelse($ranking['topTasks'] ?? [] as $idx => $ev)
                        <div class="flex items-center justify-between text-xs p-2 rounded-xl bg-purple-50/50 border border-purple-100">
                            <div class="flex items-center gap-2 truncate">
                                <span class="font-mono font-bold text-purple-700 text-[11px]">#{{ $idx + 1 }}</span>
                                <span class="font-bold text-[#0B0F14] truncate">{{ $ev->employee->full_name }}</span>
                            </div>
                            <span class="font-mono font-bold text-purple-700 bg-white px-2 py-0.5 rounded-lg border border-purple-200 shrink-0">
                                {{ number_format($ev->tasks_points, 2) }} pts
                            </span>
                        </div>
                    @empty
                        <p class="text-[11px] text-[#64748B] py-3 text-center">Aucune tâche validée ce mois.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- CLASSEMENT TOP 10 COMPLET -->
        <x-card title="Top 10 Global de l'Entreprise" subtitle="Classement d'excellence par moyenne générale sur 20" :noPadding="true">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-center">Rang</th>
                            <th class="py-3 px-4">Collaborateur</th>
                            <th class="py-3 px-3 text-center">Service</th>
                            <th class="py-3 px-3 text-center">Ponctualité</th>
                            <th class="py-3 px-3 text-center">Tâches</th>
                            <th class="py-3 px-3 text-center">Progression</th>
                            <th class="py-3 px-4 text-center">Moyenne / 20</th>
                            <th class="py-3 px-3 text-center">Appréciation</th>
                            <th class="py-3 px-4 text-right">Fiche</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($ranking['topOverall'] ?? [] as $idx => $eval)
                            <tr class="hover:bg-[#F8FAFC] transition {{ $idx < 3 ? 'bg-amber-50/20' : '' }}">
                                <td class="py-3 px-4 text-center font-bold font-mono">
                                    @if($idx === 0) 🥇 1
                                    @elseif($idx === 1) 🥈 2
                                    @elseif($idx === 2) 🥉 3
                                    @else #{{ $idx + 1 }}
                                    @endif
                                </td>

                                <td class="py-3 px-4">
                                    <div class="font-bold text-[#0B0F14]">{{ $eval->employee->full_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $eval->employee->employee_code }} • {{ $eval->employee->position ?? 'Collaborateur' }}</div>
                                </td>

                                <td class="py-3 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $eval->employee->department ?? 'Général' }}
                                    </span>
                                </td>

                                <td class="py-3 px-3 text-center font-mono font-bold text-slate-700">
                                    {{ number_format($eval->punctuality_points, 2) }}
                                </td>

                                <td class="py-3 px-3 text-center font-mono font-bold text-slate-700">
                                    {{ number_format($eval->tasks_points, 2) }}
                                </td>

                                <td class="py-3 px-3 text-center">
                                    @if($eval->score_progress > 0)
                                        <span class="text-emerald-600 font-bold font-mono">↗ +{{ number_format($eval->score_progress, 1) }}</span>
                                    @elseif($eval->score_progress < 0)
                                        <span class="text-rose-600 font-bold font-mono">↘ {{ number_format($eval->score_progress, 1) }}</span>
                                    @else
                                        <span class="text-slate-400 font-mono">—</span>
                                    @endif
                                </td>

                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-xl text-xs font-black bg-slate-900 text-white font-mono">
                                        {{ number_format($eval->final_score_20 ?? ($eval->final_score_30 / 1.5), 2) }} / 20
                                    </span>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">({{ number_format($eval->final_score_30, 1) }} / 30)</div>
                                </td>

                                <td class="py-3 px-3 text-center">
                                    @if($eval->appreciation)
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $eval->appreciation_badge_class }}">
                                            {{ $eval->appreciation }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-mono text-[10px]">—</span>
                                    @endif
                                </td>

                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('rh.evaluations.show', $eval) }}" class="text-[#0066FF] hover:underline font-bold text-xs">
                                        Voir la fiche →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-10 text-center text-xs text-[#64748B]">
                                    Aucune évaluation disponible pour établir le classement de cette période.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
