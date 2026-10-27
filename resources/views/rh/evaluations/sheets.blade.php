<x-layouts.app>
    <x-slot:title>Fiches d'évaluation — IVOSPHERE RH</x-slot>

    @php
        $monthNames = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">
        <x-page-header
            :title="'Fiches d\'évaluation — ' . $monthNames[$month] . ' ' . $year"
            description="Chaque évaluateur note seulement ce qu'il observe dans son domaine ; une note de 1, 2 ou 5 se justifie par une observation datée."
        />

        @include('rh.evaluations._nav', ['active' => 'sheets'])

        <form method="GET" class="flex flex-wrap items-center gap-2 text-xs">
            <select name="month" aria-label="Mois" onchange="this.form.submit()" class="h-9 rounded-full border border-[#E2E8F0] bg-white px-3.5 font-medium text-[#0B0F14] focus:border-[#0066FF] focus:outline-none">
                @foreach($monthNames as $monthNumber => $monthLabel)
                    <option value="{{ $monthNumber }}" @selected($monthNumber === $month)>{{ $monthLabel }}</option>
                @endforeach
            </select>
            <input type="number" name="year" value="{{ $year }}" aria-label="Année" onchange="this.form.submit()" class="h-9 w-24 rounded-full border border-[#E2E8F0] bg-white px-3.5 font-medium text-[#0B0F14] focus:border-[#0066FF] focus:outline-none">
            <span class="text-[11px] text-[#64748B]">Fiches à remettre l'avant-dernier jour ouvrable, rendues le dernier.</span>
        </form>

        <x-card title="Membres à évaluer" subtitle="Les fiches sont confidentielles ; celles des pairs ne sont jamais nominatives dans la consolidation">
            <div class="divide-y divide-[#F1F5F9]">
                @forelse($evaluations as $row)
                    @php $evaluation = $row['evaluation']; @endphp
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <div class="font-bold text-sm text-[#0B0F14]">{{ $evaluation->employee->full_name }}</div>
                            <div class="text-[11px] text-[#64748B]">
                                {{ $evaluation->employee->position ?? 'Membre' }}
                                @if($evaluation->is_locked) • <span class="text-rose-600 font-semibold">Note clôturée</span>@endif
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($row['groups'] as $group)
                                @php $done = $evaluation->sheets->firstWhere('evaluator_group', $group); @endphp
                                @if($evaluation->is_locked)
                                    <span class="px-2.5 py-1 rounded-xl text-[11px] font-semibold border border-[#E2E8F0] text-[#94A3B8]">
                                        {{ \App\Models\EvaluationSheet::GROUPS[$group]['label'] }}{{ $done ? ' ✓' : '' }}
                                    </span>
                                @else
                                    <a href="{{ route('rh.evaluation-sheets.edit', ['evaluation' => $evaluation, 'group' => $group]) }}"
                                       class="px-2.5 py-1 rounded-xl text-[11px] font-semibold border transition {{ $done ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-white border-[#E2E8F0] text-[#0066FF] hover:border-[#0066FF]' }}">
                                        {{ \App\Models\EvaluationSheet::GROUPS[$group]['label'] }}{{ $done ? ' ✓' : '' }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-[#64748B] py-4 text-center">Aucune fiche à remplir pour ce mois. La campagne d'évaluation doit d'abord être générée par les RH.</p>
                @endforelse
            </div>
        </x-card>
    </div>
</x-layouts.app>
