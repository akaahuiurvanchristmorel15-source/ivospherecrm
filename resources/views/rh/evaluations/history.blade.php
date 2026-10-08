<x-layouts.app>
    <x-slot:title>Historique des Performances — {{ $employee->full_name }} — IVOSPHERE RH</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-page-header 
            title="Historique des Performances" 
            :description="$employee->full_name . ' • ' . $employee->employee_code . ' • ' . ($employee->position ?? 'Collaborateur')"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Évaluations', 'url' => route('rh.evaluations.index')],
                    ['label' => 'Historique ' . $employee->full_name]
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button :href="route('rh.evaluations.index')" variant="secondary" size="md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour Campagnes</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <!-- KPI Historique -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-stat-card 
                title="Moyenne Générale" 
                :value="number_format($average, 2, ',', ' ') . ' / 20'" 
                change="Moyenne sur la période" 
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Meilleur Mois" 
                :value="number_format($highest, 2, ',', ' ') . ' / 20'" 
                change="Pic de performance" 
                changeType="up"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Plus Bas Score" 
                :value="number_format($lowest, 2, ',', ' ') . ' / 20'" 
                change="Point d'attention" 
                changeType="down"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Mois Évalués" 
                :value="$evaluations->count()" 
                change="Historique disponible" 
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </x-slot:icon>
            </x-stat-card>
        </div>

        <!-- GRAPHIQUE D'ÉVOLUTION DES SCORES (SVG / Alpine) -->
        <x-card title="Courbe d'Évolution des Moyennes (/20)" subtitle="Progression mensuelle du score officiel">
            @if($evaluations->count() > 0)
                <div class="h-64 flex items-end gap-2 sm:gap-4 pt-8 pb-4 px-2 border-b border-slate-200">
                    @foreach($evaluations->reverse() as $ev)
                        @php
                            $sc20 = (float) ($ev->final_score_20 ?: ($ev->final_score_30 / 1.5));
                            $heightPercent = min(100, max(5, ($sc20 / 20) * 100));
                            $barColor = match(true) {
                                $sc20 >= 16 => 'bg-emerald-500 hover:bg-emerald-600',
                                $sc20 >= 14 => 'bg-[#0066FF] hover:bg-blue-600',
                                $sc20 >= 12 => 'bg-amber-500 hover:bg-amber-600',
                                default => 'bg-rose-500 hover:bg-rose-600',
                            };
                        @endphp
                        <div class="flex-1 flex flex-col items-center h-full justify-end group relative">
                            <!-- Tooltip sur hover -->
                            <div class="absolute -top-8 bg-slate-900 text-white px-2 py-0.5 rounded text-[10px] font-bold opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none shadow-md z-10">
                                {{ number_format($sc20, 2) }} / 20 • {{ $ev->appreciation }}
                            </div>

                            <!-- Barre -->
                            <div class="w-full max-w-[40px] rounded-t-xl transition-all duration-300 {{ $barColor }}" style="height: {{ $heightPercent }}%;"></div>

                            <!-- Libellé du mois -->
                            <span class="text-[10px] font-bold text-slate-500 mt-2 truncate max-w-full">
                                {{ substr($ev->month_name, 0, 3) }}
                            </span>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400 mt-3 pt-2">
                    <span>Légende : <strong class="text-emerald-600">≥16 Très bien</strong> • <strong class="text-[#0066FF]">≥14 Bien</strong> • <strong class="text-amber-600">≥12 Assez bien</strong> • <strong class="text-rose-600">&lt;12 Vigilance</strong></span>
                    <span>Note maximale possible : <strong>20 / 20</strong></span>
                </div>
            @else
                <p class="py-8 text-center text-xs text-slate-400">Aucune donnée historique pour le moment.</p>
            @endif
        </x-card>

        <!-- Tableau historique récapitulatif -->
        <x-card :noPadding="true">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Période</th>
                            <th class="py-3 px-3 text-center">Jours</th>
                            <th class="py-3 px-3 text-center">Ponctualité (/20)</th>
                            <th class="py-3 px-4 text-center">Moyenne / 20</th>
                            <th class="py-3 px-3 text-center">Appréciation</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($evaluations as $ev)
                            @php $evScore20 = (float) ($ev->final_score_20 ?: ($ev->final_score_30 / 1.5)); @endphp
                            <tr class="hover:bg-[#F5F7FA]/70 transition">
                                <td class="py-3 px-4 font-bold text-[#0B0F14]">
                                    {{ $ev->month_name }} {{ $ev->year }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono">{{ $ev->total_working_days }} j</td>
                                <td class="py-3 px-3 text-center font-mono">{{ number_format($ev->punctuality_points, 1) }} / 20</td>
                                <td class="py-3 px-4 text-center font-extrabold text-sm text-[#0066FF]">
                                    {{ number_format($evScore20, 2, ',', ' ') }} / 20
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $ev->appreciation_badge_class }}">
                                        {{ $ev->appreciation }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('rh.evaluations.show', $ev) }}" class="text-xs font-bold text-[#0066FF] hover:underline">
                                        Consulter →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Aucun historique disponible.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
