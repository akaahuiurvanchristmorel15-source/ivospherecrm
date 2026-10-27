<x-layouts.app>
    <x-slot:title>Fiche d'évaluation — {{ $evaluation->employee->full_name }} — IVOSPHERE RH</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-page-header
            :title="\App\Models\EvaluationSheet::GROUPS[$group]['label'] . ' — ' . $evaluation->employee->full_name"
            :description="$evaluation->month_name . ' ' . $evaluation->year . ($group === \App\Models\EvaluationSheet::GROUP_SELF ? ' • L\'auto-évaluation sert à l\'entretien, elle ne compte pas dans la note.' : '')"
        >
            <x-slot:actions>
                <x-button :href="route('rh.evaluation-sheets.index', ['year' => $evaluation->year, 'month' => $evaluation->month])" variant="secondary" size="md">Retour</x-button>
            </x-slot:actions>
        </x-page-header>

        <div class="p-4 rounded-2xl bg-white border border-[#E2E8F0] text-[11px] text-[#64748B] grid grid-cols-2 sm:grid-cols-6 gap-2">
            @foreach(\App\Models\EvaluationCriterion::SCALE as $note => $level)
                <div><strong class="text-[#0B0F14]">{{ $note }} — {{ $level['label'] }}</strong><br>{{ $level['meaning'] }}</div>
            @endforeach
            <div><strong class="text-[#0B0F14]">n.c. — Non concerné</strong><br>Critère non observé : la case n'est pas comptée</div>
        </div>

        @if($errors->any())
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800">
                Certaines notes doivent être corrigées ou justifiées (voir ci-dessous).
            </div>
        @endif

        <form action="{{ route('rh.evaluation-sheets.update', $evaluation) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="evaluator_group" value="{{ $group }}">

            @foreach($criteria as $block => $blockCriteria)
                <x-card :title="'Bloc ' . $block . ' — ' . \App\Models\EvaluationCriterion::BLOCKS[$block]['label']" :subtitle="(\App\Models\EvaluationCriterion::BLOCKS[$block]['weight'] * 100) . ' % de la note'">
                    <div class="space-y-4">
                        @foreach($blockCriteria as $criterion)
                            @php
                                $existing = $sheet->scores->firstWhere('evaluation_criterion_id', $criterion->id);
                                $currentScore = old('scores.'.$criterion->id.'.score', $existing?->score);
                            @endphp
                            <div class="p-4 rounded-2xl border border-[#E2E8F0] space-y-2">
                                <div class="flex flex-wrap items-baseline gap-2">
                                    <span class="font-mono font-bold text-xs text-[#64748B]">{{ $criterion->code }}</span>
                                    <span class="font-bold text-sm text-[#0B0F14]">{{ $criterion->name }}</span>
                                    @if($criterion->isAutomatic())
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-[#0066FF] border border-blue-200">Calculé des bilans quotidiens si non noté</span>
                                    @endif
                                </div>
                                <p class="text-xs text-[#64748B]">{{ $criterion->description }}</p>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[10px] text-[#64748B]">
                                    <div><strong class="text-rose-700">1 :</strong> {{ $criterion->level_1 }}</div>
                                    <div><strong class="text-[#0B0F14]">3 :</strong> {{ $criterion->level_3 }}</div>
                                    <div><strong class="text-emerald-700">5 :</strong> {{ $criterion->level_5 }}</div>
                                </div>

                                <div class="flex flex-wrap gap-2 pt-1">
                                    @foreach([1, 2, 3, 4, 5] as $note)
                                        <label class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-[#E2E8F0] text-xs font-mono font-bold cursor-pointer has-[:checked]:bg-[#0066FF] has-[:checked]:text-white has-[:checked]:border-[#0066FF]">
                                            <input type="radio" class="sr-only" name="scores[{{ $criterion->id }}][score]" value="{{ $note }}" @checked((string) $currentScore === (string) $note)>
                                            {{ $note }}
                                        </label>
                                    @endforeach
                                    <label class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-[#E2E8F0] text-xs font-bold cursor-pointer has-[:checked]:bg-slate-700 has-[:checked]:text-white">
                                        <input type="radio" class="sr-only" name="scores[{{ $criterion->id }}][score]" value="" @checked($currentScore === null || $currentScore === '')>
                                        n.c.
                                    </label>
                                </div>
                                @error('scores.'.$criterion->id.'.score')
                                    <p class="text-[11px] text-rose-600">{{ $message }}</p>
                                @enderror

                                <textarea name="scores[{{ $criterion->id }}][observation]" rows="2"
                                          placeholder="Observation datée et chiffrée (ex. « arrivé à 08h11 les 20, 21 et 22 octobre »)"
                                          class="w-full px-3 py-2 rounded-xl border text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF] @error('scores.'.$criterion->id.'.observation') border-rose-400 @else border-[#E2E8F0] @enderror">{{ old('scores.'.$criterion->id.'.observation', $existing?->observation) }}</textarea>
                                @error('scores.'.$criterion->id.'.observation')
                                    <p class="text-[11px] text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endforeach

            <div class="flex justify-end">
                <x-button variant="primary" type="submit" size="md">Enregistrer la fiche</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
