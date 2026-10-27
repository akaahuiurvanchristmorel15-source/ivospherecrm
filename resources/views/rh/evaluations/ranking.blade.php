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
            title="Classement & récompenses" 
            :description="'Membre du mois, récompense du trimestre et classement de ' . $monthNames[$month] . ' ' . $year"
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

        @include('rh.evaluations._nav', ['active' => 'ranking'])

        <form method="GET" action="{{ route('rh.evaluations.ranking') }}" class="flex flex-wrap items-center gap-2 text-xs">
            <select name="month" aria-label="Mois" onchange="this.form.submit()" class="h-9 rounded-full border border-[#E2E8F0] bg-white px-3.5 text-xs font-medium text-[#0B0F14] focus:border-[#0066FF] focus:outline-none">
                @foreach($monthNames as $mNum => $mLabel)
                    <option value="{{ $mNum }}" @selected($month == $mNum)>{{ $mLabel }}</option>
                @endforeach
            </select>
            <select name="year" aria-label="Année" onchange="this.form.submit()" class="h-9 rounded-full border border-[#E2E8F0] bg-white px-3.5 text-xs font-medium text-[#0B0F14] focus:border-[#0066FF] focus:outline-none">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                @endfor
            </select>
            <select name="department" aria-label="Service" onchange="this.form.submit()" class="h-9 rounded-full border border-[#E2E8F0] bg-white px-3.5 text-xs font-medium text-[#0B0F14] focus:border-[#0066FF] focus:outline-none">
                <option value="">Tous les services</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" @selected($department == $dept)>{{ $dept }}</option>
                @endforeach
            </select>
            <noscript><button type="submit" class="rounded-full bg-[#0B0F14] px-3.5 py-2 font-semibold text-white">Filtrer</button></noscript>
        </form>

        <!-- RÉCOMPENSES DU RÉFÉRENTIEL : membre du mois, trimestre, progressions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Membre du mois -->
            <div class="rounded-2xl border border-amber-200 bg-white p-5 shadow-xs">
                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-amber-100">
                    <span class="text-amber-600 font-bold">🏅</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-amber-900">Membre du mois</h3>
                </div>
                @if($ranking['memberOfTheMonth'])
                    <div class="text-xs p-3 rounded-xl bg-amber-50/50 border border-amber-100 space-y-1">
                        <div class="font-bold text-sm text-[#0B0F14]">{{ $ranking['memberOfTheMonth']->employee->full_name }}</div>
                        <div class="font-mono font-bold text-amber-700">{{ number_format($ranking['memberOfTheMonth']->final_score_20, 2, ',', ' ') }} / 20</div>
                        <div class="text-[10px] text-[#64748B]">Félicitations en réunion, affichage dans les magasins, encouragement.</div>
                    </div>
                @else
                    <p class="text-[11px] text-[#64748B] py-3 text-center">
                        {{ \App\Models\MonthlyEvaluation::isTrialMonth($year, $month) ? "Mois d'essai : pas de membre du mois." : "Aucun membre n'atteint 14/20 ce mois." }}
                    </p>
                @endif
            </div>

            <!-- Récompense du trimestre -->
            <div class="rounded-2xl border border-blue-200 bg-white p-5 shadow-xs">
                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-blue-100">
                    <span class="text-[#0066FF] font-bold">🏆</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-blue-900">Récompense du trimestre</h3>
                </div>
                <p class="text-[10px] text-[#64748B] mb-2">{{ $ranking['quarter']['label'] }} — 3 meilleures moyennes d'au moins 12/20</p>
                <div class="space-y-2.5">
                    @forelse($ranking['quarter']['rewarded'] as $idx => $row)
                        <div class="flex items-center justify-between text-xs p-2 rounded-xl bg-blue-50/50 border border-blue-100">
                            <div class="flex items-center gap-2 truncate">
                                <span class="font-mono font-bold text-[#0066FF] text-[11px]">#{{ $idx + 1 }}</span>
                                <span class="font-bold text-[#0B0F14] truncate">{{ $row['employee']->full_name }}</span>
                            </div>
                            <span class="font-mono font-bold text-[#0066FF] bg-white px-2 py-0.5 rounded-lg border border-blue-200 shrink-0">
                                {{ number_format($row['average'], 2, ',', ' ') }} / 20 ({{ $row['months'] }} mois)
                            </span>
                        </div>
                    @empty
                        <p class="text-[11px] text-[#64748B] py-3 text-center">Aucune moyenne d'au moins 12/20 sur le trimestre.</p>
                    @endforelse
                </div>
            </div>

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
        </div>

        <!-- CLASSEMENT DU MOIS -->
        <x-card title="Classement du mois" subtitle="Top 10 par note sur 20 — les notes détaillées restent confidentielles ; seul le membre du mois est annoncé en réunion" :noPadding="true">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-center">Rang</th>
                            <th class="py-3 px-4">Collaborateur</th>
                            <th class="py-3 px-3 text-center">Service</th>
                            @foreach(array_keys(\App\Models\EvaluationCriterion::BLOCKS) as $block)
                                <th class="py-3 px-3 text-center">Bloc {{ $block }}</th>
                            @endforeach
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

                                @foreach(array_keys(\App\Models\EvaluationCriterion::BLOCKS) as $block)
                                    @php $blockScore = $eval->{'block_'.strtolower($block).'_score'}; @endphp
                                    <td class="py-3 px-3 text-center font-mono font-bold text-slate-700">
                                        {{ $blockScore !== null ? number_format($blockScore, 2, ',', ' ') : 'n.c.' }}
                                    </td>
                                @endforeach

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
                                        {{ number_format($eval->final_score_20, 2) }} / 20
                                    </span>
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
                                <td colspan="11" class="py-10 text-center text-xs text-[#64748B]">
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
