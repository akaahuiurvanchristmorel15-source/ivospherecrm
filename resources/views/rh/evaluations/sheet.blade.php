@php
    $groupLabel = \App\Models\EvaluationSheet::GROUPS[$group]['label'];
    $isSelf = $group === \App\Models\EvaluationSheet::GROUP_SELF;
    $total = $criteria->flatten()->count();
    $scale = \App\Models\EvaluationCriterion::SCALE;
    $mustJustify = \App\Models\EvaluationCriterion::SCORES_REQUIRING_OBSERVATION;
@endphp

<x-layouts.app>
    <x-slot:title>{{ $groupLabel }} — {{ $evaluation->employee->full_name }}</x-slot>

    <form action="{{ route('rh.evaluation-sheets.update', $evaluation) }}" method="POST"
          x-data="{ rated: 0, total: {{ $total }}, recount() { this.rated = [...this.$el.querySelectorAll('input[type=radio]:checked')].filter(input => input.value !== '').length } }"
          x-init="recount()" @change="recount()"
          class="mx-auto max-w-3xl space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="evaluator_group" value="{{ $group }}">

        <x-evaluation.header
            :title="$evaluation->employee->full_name"
            :subtitle="$groupLabel . ' · ' . $evaluation->month_name . ' ' . $evaluation->year"
            :breadcrumbs="[
                ['label' => 'Fiches d\'évaluation', 'url' => route('rh.evaluation-sheets.index', ['year' => $evaluation->year, 'month' => $evaluation->month])],
                ['label' => $evaluation->employee->full_name],
            ]"
        />

        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4 text-xs leading-relaxed text-[#334155] sm:p-5">
            @if($isSelf)
                <p><strong class="text-[#0B0F14]">Auto-évaluation :</strong> elle prépare l'entretien et ne compte pas dans la note.</p>
            @else
                <p><strong class="text-[#0B0F14]">Notez seulement ce que vous observez.</strong> Choisissez « n.c. » pour un critère que vous n'avez pas pu observer : il ne sera pas compté.</p>
            @endif
            <p class="mt-1.5">Une note de <strong>1, 2 ou 5</strong> se justifie par une observation datée : un fait, un chiffre, un exemple (« arrivé à 08h11 les 20, 21 et 22 octobre »).</p>
        </div>

        @if($errors->any())
            <p class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-800">Certaines notes doivent être justifiées ou corrigées ci-dessous.</p>
        @endif

        @foreach($criteria as $block => $blockCriteria)
            <section class="space-y-3" aria-labelledby="block-{{ $block }}">
                <div class="flex items-baseline justify-between gap-3 px-1">
                    <h2 id="block-{{ $block }}" class="text-sm font-semibold text-[#0B0F14]">Bloc {{ $block }} · {{ \App\Models\EvaluationCriterion::BLOCKS[$block]['label'] }}</h2>
                    <span class="text-[11px] text-[#94A3B8]">{{ \App\Models\EvaluationCriterion::BLOCKS[$block]['weight'] * 100 }} % de la note</span>
                </div>

                @foreach($blockCriteria as $criterion)
                    @php
                        $existing = $sheet->scores->firstWhere('evaluation_criterion_id', $criterion->id);
                        $current = (string) old('scores.'.$criterion->id.'.score', $existing?->score);
                        $levels = [1 => $criterion->level_1, 3 => $criterion->level_3, 5 => $criterion->level_5];
                        $observationError = $errors->first('scores.'.$criterion->id.'.observation');
                    @endphp
                    <article x-data="{ score: '{{ $current }}', levels: @js($levels), scale: @js(collect($scale)->map(fn ($level) => $level['label'])) }"
                             class="rounded-2xl border bg-white p-4 transition sm:p-5"
                             :class="score !== '' ? 'border-[#0066FF]/30' : 'border-[#E2E8F0]'">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="text-sm font-medium text-[#0B0F14]"><span class="mr-1 font-mono text-xs text-[#94A3B8]">{{ $criterion->code }}</span>{{ $criterion->name }}</h3>
                                <p class="mt-0.5 text-xs text-[#64748B]">{{ $criterion->description }}</p>
                            </div>
                            @if($criterion->isAutomatic())
                                <span class="shrink-0 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-[#0066FF]">Calculé des bilans si n.c.</span>
                            @endif
                        </div>

                        {{-- Sélecteur de note --}}
                        <fieldset class="mt-4">
                            <legend class="sr-only">Note de {{ $criterion->name }}</legend>
                            <div class="grid grid-cols-6 gap-1.5">
                                @foreach([1, 2, 3, 4, 5] as $note)
                                    <label class="cursor-pointer">
                                        <input type="radio" class="peer sr-only" name="scores[{{ $criterion->id }}][score]" value="{{ $note }}" x-model="score" @checked($current === (string) $note)>
                                        <span class="flex h-11 items-center justify-center rounded-xl border border-[#E2E8F0] text-sm font-semibold text-[#334155] transition peer-checked:border-[#0066FF] peer-checked:bg-[#0066FF] peer-checked:text-white peer-focus-visible:ring-4 peer-focus-visible:ring-[#0066FF]/20 hover:border-[#0066FF]">{{ $note }}</span>
                                    </label>
                                @endforeach
                                <label class="cursor-pointer">
                                    <input type="radio" class="peer sr-only" name="scores[{{ $criterion->id }}][score]" value="" x-model="score" @checked($current === '')>
                                    <span class="flex h-11 items-center justify-center rounded-xl border border-dashed border-[#CBD5E1] text-xs font-medium text-[#64748B] transition peer-checked:border-solid peer-checked:border-[#0B0F14] peer-checked:bg-[#0B0F14] peer-checked:text-white peer-focus-visible:ring-4 peer-focus-visible:ring-[#0066FF]/20">n.c.</span>
                                </label>
                            </div>
                        </fieldset>

                        {{-- Repère du niveau choisi --}}
                        <p class="mt-2.5 min-h-4 text-xs text-[#64748B]">
                            <template x-if="score === ''"><span>Non concerné : la case ne sera pas comptée.</span></template>
                            <template x-if="score !== ''">
                                <span><strong class="text-[#0B0F14]" x-text="score + ' — ' + scale[score]"></strong><span x-show="levels[score]"> · </span><span x-text="levels[score] ?? ''"></span></span>
                            </template>
                        </p>

                        {{-- Observation --}}
                        <div class="mt-3">
                            <label for="observation-{{ $criterion->id }}" class="mb-1 flex items-center gap-2 text-xs font-medium text-[#0B0F14]">
                                Observation
                                <span x-show="@js($mustJustify).includes(Number(score))" x-cloak class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-700">obligatoire</span>
                            </label>
                            <textarea id="observation-{{ $criterion->id }}" name="scores[{{ $criterion->id }}][observation]" rows="2"
                                      :required="@js($mustJustify).includes(Number(score))"
                                      placeholder="Fait daté et chiffré"
                                      class="w-full rounded-xl border px-3.5 py-2.5 text-sm text-[#0B0F14] placeholder:text-[#94A3B8] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10 {{ $observationError ? 'border-rose-400' : 'border-[#E2E8F0]' }}">{{ old('scores.'.$criterion->id.'.observation', $existing?->observation) }}</textarea>
                            @if($observationError)
                                <p class="mt-1 text-xs text-rose-600">{{ $observationError }}</p>
                            @endif
                            @error('scores.'.$criterion->id.'.score')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </article>
                @endforeach
            </section>
        @endforeach

        {{-- Barre d'enregistrement flottante (au-dessus de la barre de navigation mobile) --}}
        <div class="sticky bottom-24 z-30 rounded-2xl border border-[#E2E8F0] bg-white/95 shadow-[0_12px_32px_-16px_rgba(11,15,20,0.35)] backdrop-blur md:bottom-4">
            <div class="flex items-center gap-4 px-4 py-3">
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-[#64748B]"><span class="font-semibold text-[#0B0F14]" x-text="rated"></span> / {{ $total }} critères notés</p>
                    <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-[#EEF2F6]">
                        <div class="h-full rounded-full bg-[#0066FF] transition-all duration-300" :style="`width: ${total ? rated / total * 100 : 0}%`"></div>
                    </div>
                </div>
                <a href="{{ route('rh.evaluation-sheets.index', ['year' => $evaluation->year, 'month' => $evaluation->month]) }}" class="hidden h-11 items-center rounded-xl px-3 text-sm font-medium text-[#64748B] hover:text-[#0B0F14] sm:inline-flex">Annuler</a>
                <button type="submit" class="inline-flex h-11 shrink-0 items-center justify-center rounded-xl bg-[#0066FF] px-5 text-sm font-semibold text-white transition hover:bg-[#0052cc] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/20">Enregistrer la fiche</button>
            </div>
        </div>
    </form>
</x-layouts.app>
