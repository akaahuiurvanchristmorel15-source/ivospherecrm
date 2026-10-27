@php
    $monthNames = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
    $period = \Carbon\Carbon::create($year, $month, 1);
    $periodQuery = fn (\Carbon\Carbon $date): array => array_filter(['year' => $date->year, 'month' => $date->month, 'department' => $department]);
    $isTrial = \App\Models\MonthlyEvaluation::isTrialMonth($year, $month);
    $memberOfTheMonth = $ranking['memberOfTheMonth'];
    $blocks = array_keys(\App\Models\EvaluationCriterion::BLOCKS);
    $initials = fn ($employee): string => strtoupper(mb_substr($employee->first_name ?? '', 0, 1).mb_substr($employee->last_name ?? '', 0, 1));
@endphp

<x-layouts.app>
    <x-slot:title>Classement et récompenses — {{ $monthNames[$month] }} {{ $year }}</x-slot>

    <div class="mx-auto max-w-6xl space-y-6 [font-variant-numeric:tabular-nums]">
        <x-evaluation.header
            title="Classement et récompenses"
            subtitle="Seul le membre du mois est annoncé en réunion ; les notes détaillées restent confidentielles."
            :breadcrumbs="[
                ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                ['label' => 'Évaluations', 'url' => route('rh.evaluations.index', ['year' => $year, 'month' => $month])],
                ['label' => 'Classement'],
            ]"
            active="ranking" :year="$year" :month="$month"
        >
            <x-slot:actions>
                <div class="inline-flex h-10 items-center rounded-xl border border-[#E2E8F0] bg-white p-1">
                    <a href="{{ route('rh.evaluations.ranking', $periodQuery($period->copy()->subMonth())) }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] hover:bg-[#F1F5F9]" aria-label="Mois précédent">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <span class="min-w-28 px-2 text-center text-sm font-semibold text-[#0B0F14]">{{ $monthNames[$month] }} {{ $year }}</span>
                    <a href="{{ route('rh.evaluations.ranking', $periodQuery($period->copy()->addMonth())) }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] hover:bg-[#F1F5F9]" aria-label="Mois suivant">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <form method="GET" action="{{ route('rh.evaluations.ranking') }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <select name="department" aria-label="Service" onchange="this.form.submit()" class="h-10 rounded-xl border border-[#E2E8F0] bg-white px-3 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none">
                        <option value="">Tous les services</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" @selected($department == $dept)>{{ $dept }}</option>
                        @endforeach
                    </select>
                </form>
            </x-slot:actions>
        </x-evaluation.header>

        {{-- ───────── Récompenses ───────── --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- Membre du mois --}}
            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 lg:col-span-1">
                <p class="text-xs font-medium text-[#64748B]">Membre du mois</p>
                @if($memberOfTheMonth)
                    <div class="mt-4 flex items-center gap-4">
                        <x-evaluation.score-ring :score="$memberOfTheMonth->final_score_20" />
                        <div class="min-w-0">
                            <p class="truncate text-base font-semibold text-[#0B0F14]">{{ $memberOfTheMonth->employee->full_name }}</p>
                            <p class="truncate text-xs text-[#64748B]">{{ $memberOfTheMonth->employee->position ?? 'Membre' }}</p>
                            <span class="mt-1.5 inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $memberOfTheMonth->appreciation_badge_class }}">{{ $memberOfTheMonth->appreciation }}</span>
                        </div>
                    </div>
                    <p class="mt-4 text-[11px] leading-relaxed text-[#64748B]">Félicitations en réunion, affichage dans les magasins, encouragement.</p>
                @else
                    <p class="mt-4 text-sm text-[#0B0F14]">{{ $isTrial ? 'Mois d\'essai' : 'Pas de membre du mois' }}</p>
                    <p class="mt-1 text-xs text-[#64748B]">{{ $isTrial ? 'Les notes sont données sans conséquence.' : 'Il faut une note d\'au moins 14 / 20.' }}</p>
                @endif
            </section>

            {{-- Récompense du trimestre --}}
            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
                <p class="text-xs font-medium text-[#64748B]">Récompense du trimestre</p>
                <p class="text-[11px] text-[#94A3B8]">{{ $ranking['quarter']['label'] }} · moyenne ≥ 12 / 20</p>
                <ol class="mt-4 space-y-2.5">
                    @forelse($ranking['quarter']['rewarded'] as $index => $row)
                        <li class="flex items-center gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold {{ $index === 0 ? 'bg-[#0B0F14] text-white' : 'bg-[#F1F5F9] text-[#334155]' }}">{{ $index + 1 }}</span>
                            <span class="min-w-0 flex-1 truncate text-sm text-[#0B0F14]">{{ $row['employee']->full_name }}</span>
                            <span class="shrink-0 text-sm font-semibold text-[#0B0F14]">{{ number_format($row['average'], 2, ',', '') }}</span>
                        </li>
                    @empty
                        <li class="text-xs text-[#64748B]">Aucune moyenne d'au moins 12 / 20 sur le trimestre.</li>
                    @endforelse
                </ol>
            </section>

            {{-- Progressions --}}
            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
                <p class="text-xs font-medium text-[#64748B]">Plus fortes progressions</p>
                <p class="text-[11px] text-[#94A3B8]">Par rapport au mois précédent</p>
                <ol class="mt-4 space-y-2.5">
                    @forelse($ranking['topProgression'] as $evaluation)
                        <li class="flex items-center gap-3">
                            <span class="min-w-0 flex-1 truncate text-sm text-[#0B0F14]">{{ $evaluation->employee->full_name }}</span>
                            <span class="shrink-0 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">+{{ number_format($evaluation->score_progress, 2, ',', '') }}</span>
                        </li>
                    @empty
                        <li class="text-xs text-[#64748B]">Aucune progression ce mois.</li>
                    @endforelse
                </ol>
            </section>
        </div>

        {{-- ───────── Classement du mois ───────── --}}
        <x-evaluation.section title="Classement du mois" subtitle="Les 10 meilleures notes sur 20" :padding="false">
            @if($ranking['topOverall']->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-[#64748B]">Aucune note consolidée pour ce mois.</p>
            @else
                {{-- Desktop --}}
                <table class="hidden w-full text-left text-sm md:table">
                    <thead class="border-b border-[#EEF2F6] text-[11px] font-medium text-[#94A3B8]">
                        <tr>
                            <th class="w-14 px-5 py-2.5 font-medium">Rang</th>
                            <th class="px-3 py-2.5 font-medium">Membre</th>
                            @foreach($blocks as $block)
                                <th class="w-14 px-2 py-2.5 text-center font-medium">{{ $block }}</th>
                            @endforeach
                            <th class="w-24 px-3 py-2.5 text-right font-medium">Note</th>
                            <th class="w-28 px-3 py-2.5 font-medium">Appréciation</th>
                            <th class="w-24 px-5 py-2.5 text-right font-medium">Évolution</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EEF2F6]">
                        @foreach($ranking['topOverall'] as $index => $evaluation)
                            <tr class="group cursor-pointer transition hover:bg-[#F8FAFC]" onclick="window.location='{{ route('rh.evaluations.show', $evaluation) }}'">
                                <td class="px-5 py-3 font-semibold {{ $index < 3 ? 'text-[#0B0F14]' : 'text-[#94A3B8]' }}">{{ $index + 1 }}</td>
                                <td class="px-3 py-3">
                                    <a href="{{ route('rh.evaluations.show', $evaluation) }}" class="font-medium text-[#0B0F14] group-hover:text-[#0066FF]">{{ $evaluation->employee->full_name }}</a>
                                    <p class="text-xs text-[#94A3B8]">{{ $evaluation->employee->department ?? 'Général' }}</p>
                                </td>
                                @foreach($blocks as $block)
                                    @php $blockScore = $evaluation->{'block_'.strtolower($block).'_score'}; @endphp
                                    <td class="px-2 py-3 text-center text-xs text-[#64748B]">{{ $blockScore === null ? '—' : number_format($blockScore, 1, ',', '') }}</td>
                                @endforeach
                                <td class="px-3 py-3 text-right font-semibold text-[#0B0F14]">{{ number_format($evaluation->final_score_20, 2, ',', '') }}</td>
                                <td class="px-3 py-3"><span class="rounded-full border px-2 py-0.5 text-[11px] font-medium {{ $evaluation->appreciation_badge_class }}">{{ $evaluation->appreciation }}</span></td>
                                <td class="px-5 py-3 text-right text-xs font-medium {{ $evaluation->score_progress > 0 ? 'text-emerald-700' : ($evaluation->score_progress < 0 ? 'text-rose-700' : 'text-[#94A3B8]') }}">
                                    {{ (float) $evaluation->score_progress === 0.0 ? '—' : ($evaluation->score_progress > 0 ? '+' : '').number_format($evaluation->score_progress, 2, ',', '') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Mobile --}}
                <ol class="divide-y divide-[#EEF2F6] md:hidden">
                    @foreach($ranking['topOverall'] as $index => $evaluation)
                        <li>
                            <a href="{{ route('rh.evaluations.show', $evaluation) }}" class="flex items-center gap-3 px-4 py-3 active:bg-[#F8FAFC]">
                                <span class="w-5 shrink-0 text-sm font-semibold {{ $index < 3 ? 'text-[#0B0F14]' : 'text-[#94A3B8]' }}">{{ $index + 1 }}</span>
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#F1F5F9] text-[11px] font-semibold text-[#334155]">{{ $initials($evaluation->employee) }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium text-[#0B0F14]">{{ $evaluation->employee->full_name }}</span>
                                    <span class="block text-[11px] text-[#64748B]">{{ $evaluation->appreciation }}</span>
                                </span>
                                <span class="shrink-0 text-base font-semibold text-[#0B0F14]">{{ number_format($evaluation->final_score_20, 1, ',', '') }}</span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            @endif
        </x-evaluation.section>
    </div>
</x-layouts.app>
