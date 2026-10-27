@php
    $scored = $evaluations->filter(fn ($evaluation) => $evaluation->isScored());
    $chronological = $scored->reverse()->values();
    $latest = $scored->first();
@endphp

<x-layouts.app>
    <x-slot:title>Historique — {{ $employee->full_name }}</x-slot>

    <div class="space-y-5 sm:space-y-6 [font-variant-numeric:tabular-nums]">
        <x-evaluation.header
            :title="$employee->full_name"
            :subtitle="'Historique des notes · ' . ($employee->position ?? 'Membre') . ' · ' . ($employee->department ?? 'Général')"
            :back-url="route('rh.evaluations.index')"
            back-label="Évaluations"
        />

        {{-- Synthèse --}}
        @php
            $fmt = fn ($value) => $value === null ? '—' : number_format($value, 2, ',', '');
            $average = $scored->isNotEmpty() ? $scored->avg(fn ($e) => (float) $e->final_score_20) : null;
            $best = $scored->isNotEmpty() ? $scored->max(fn ($e) => (float) $e->final_score_20) : null;
            $lowest = $scored->isNotEmpty() ? $scored->min(fn ($e) => (float) $e->final_score_20) : null;
        @endphp
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-12" aria-label="Synthèse">
            <x-evaluation.hero class="lg:col-span-5">
                <div class="flex items-center gap-5">
                    <x-evaluation.score-ring :score="$latest?->final_score_20" on-dark />
                    <div class="min-w-0 space-y-1">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Dernière note</p>
                        <p class="text-2xl font-semibold tracking-tight">{{ $latest ? $latest->month_name.' '.$latest->year : '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $latest?->appreciation ?? 'Aucune note consolidée' }}</p>
                    </div>
                </div>
            </x-evaluation.hero>
            <div class="grid grid-cols-3 gap-3 sm:gap-4 lg:col-span-7">
                <x-evaluation.stat label="Moyenne" :value="$fmt($average)" tint="bg-blue-50 text-[#0066FF]" icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                <x-evaluation.stat label="Meilleure note" :value="$fmt($best)" tint="bg-emerald-50 text-emerald-600" icon="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                <x-evaluation.stat label="Plus basse" :value="$fmt($lowest)" tint="bg-rose-50 text-rose-600" icon="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
            </div>
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
            <ul class="divide-y divide-[#E2E8F0]">
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
