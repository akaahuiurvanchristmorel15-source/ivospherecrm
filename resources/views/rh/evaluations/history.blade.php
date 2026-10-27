@php
    $scored = $evaluations->filter(fn ($evaluation) => $evaluation->isScored());
    $chronological = $scored->reverse()->values();
    $latest = $scored->first();
@endphp

<x-layouts.app>
    <x-slot:title>Historique — {{ $employee->full_name }}</x-slot>

    <div class="mx-auto max-w-5xl space-y-6 [font-variant-numeric:tabular-nums]">
        <x-evaluation.header
            :title="$employee->full_name"
            :subtitle="'Historique des notes · ' . ($employee->position ?? 'Membre') . ' · ' . ($employee->department ?? 'Général')"
            :breadcrumbs="[
                ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                ['label' => 'Évaluations', 'url' => route('rh.evaluations.index')],
                ['label' => 'Historique'],
            ]"
        />

        {{-- Synthèse --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-[auto_1fr]">
            <div class="flex items-center gap-4 rounded-2xl border border-[#E2E8F0] bg-white p-5">
                <x-evaluation.score-ring :score="$latest?->final_score_20" />
                <div>
                    <p class="text-xs text-[#64748B]">Dernière note</p>
                    <p class="text-sm font-medium text-[#0B0F14]">{{ $latest ? $latest->month_name.' '.$latest->year : '—' }}</p>
                    @if($latest)
                        <span class="mt-1.5 inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $latest->appreciation_badge_class }}">{{ $latest->appreciation }}</span>
                    @endif
                </div>
            </div>
            <dl class="grid grid-cols-3 divide-x divide-[#EEF2F6] rounded-2xl border border-[#E2E8F0] bg-white">
                @foreach([
                    ['Moyenne', $scored->isNotEmpty() ? $scored->avg(fn ($e) => (float) $e->final_score_20) : null],
                    ['Meilleure', $scored->isNotEmpty() ? $scored->max(fn ($e) => (float) $e->final_score_20) : null],
                    ['Plus basse', $scored->isNotEmpty() ? $scored->min(fn ($e) => (float) $e->final_score_20) : null],
                ] as [$statLabel, $statValue])
                    <div class="flex flex-col justify-center p-4 sm:p-5">
                        <dt class="text-xs text-[#64748B]">{{ $statLabel }}</dt>
                        <dd class="mt-1 text-xl font-semibold text-[#0B0F14] sm:text-2xl">{{ $statValue === null ? '—' : number_format($statValue, 2, ',', '') }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        {{-- Évolution --}}
        <x-evaluation.section title="Évolution" subtitle="Note du mois sur 20 · repères à 10 (moyenne) et 14 (membre du mois)">
            @if($chronological->isEmpty())
                <p class="py-8 text-center text-sm text-[#64748B]">Aucune note consolidée pour le moment.</p>
            @else
                <div class="-mx-1 overflow-x-auto px-1">
                    <div class="relative flex h-48 min-w-[22rem] items-end gap-3 border-b border-[#E2E8F0]">
                        <div class="pointer-events-none absolute inset-x-0 border-t border-dashed border-amber-400/70" style="bottom: 50%"></div>
                        <div class="pointer-events-none absolute inset-x-0 border-t border-dashed border-emerald-500/70" style="bottom: 70%"></div>
                        @foreach($chronological as $evaluation)
                            @php $height = max(4, round((float) $evaluation->final_score_20 / 20 * 100)); @endphp
                            <a href="{{ route('rh.evaluations.show', $evaluation) }}" class="group relative flex h-full flex-1 items-end" title="{{ $evaluation->month_name }} {{ $evaluation->year }}">
                                <span class="absolute inset-x-0 text-center text-[11px] font-semibold text-[#0B0F14]" style="bottom: calc({{ $height }}% + 4px)">{{ number_format($evaluation->final_score_20, 1, ',', '') }}</span>
                                <span class="w-full rounded-t-lg transition {{ $loop->last ? 'bg-[#0066FF]' : 'bg-blue-100 group-hover:bg-blue-200' }} {{ $evaluation->is_trial ? 'opacity-50' : '' }}" style="height: {{ $height }}%"></span>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-2 flex min-w-[22rem] gap-3">
                        @foreach($chronological as $evaluation)
                            <span class="flex-1 text-center text-[10px] text-[#64748B]">{{ mb_substr($evaluation->month_name, 0, 3) }} {{ substr((string) $evaluation->year, 2) }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-evaluation.section>

        {{-- Mois par mois --}}
        <x-evaluation.section title="Mois par mois" :padding="false">
            <ul class="divide-y divide-[#EEF2F6]">
                @forelse($evaluations as $evaluation)
                    <li>
                        <a href="{{ route('rh.evaluations.show', $evaluation) }}" class="grid grid-cols-[1fr_auto] items-center gap-4 px-4 py-3.5 transition hover:bg-[#F8FAFC] sm:grid-cols-[10rem_1fr_auto] sm:px-5">
                            <div>
                                <p class="text-sm font-medium text-[#0B0F14]">{{ $evaluation->month_name }} {{ $evaluation->year }}</p>
                                <p class="text-[11px] text-[#94A3B8]">{{ $evaluation->is_trial ? 'Mois d\'essai' : ($evaluation->is_locked ? 'Clôturée' : 'En cours') }}</p>
                            </div>
                            <x-evaluation.block-bars :evaluation="$evaluation" compact class="hidden sm:block" />
                            <div class="text-right">
                                <p class="text-base font-semibold text-[#0B0F14]">{{ $evaluation->isScored() ? number_format($evaluation->final_score_20, 2, ',', '') : '—' }}</p>
                                <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $evaluation->appreciation_badge_class }}">{{ $evaluation->appreciation }}</span>
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center text-sm text-[#64748B]">Aucune évaluation pour ce membre.</li>
                @endforelse
            </ul>
        </x-evaluation.section>
    </div>
</x-layouts.app>
