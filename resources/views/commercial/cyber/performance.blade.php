<x-layouts.app>
    <x-slot:title>Performance des agents cyber</x-slot>

    @php
        $money = fn (float $amount) => number_format($amount, 0, ',', ' ');
        $previousMonth = $month->copy()->subMonthNoOverflow();
        $nextMonth = $month->copy()->addMonthNoOverflow();
        $isCurrentMonth = $month->isSameMonth(now());
        $statusBadges = [
            'Performant' => 'success',
            'À améliorer' => 'warning',
            'Caisse' => 'primary',
            'Inactif' => 'neutral',
        ];
        $maxDaily = max(1, collect($report['daily']['counts'])->flatten()->max() ?? 1);
        $totals = $report['totals'];
    @endphp

    <x-page-header
        title="Performance des agents"
        description="Clients servis et montant généré par chaque agent du cyber, comparés à la moyenne des agents."
        :breadcrumbs="[['label' => 'Cyber', 'url' => route('commercial.cyber.index')], ['label' => 'Performance']]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-1 rounded-full border border-slate-200/70 bg-white/70 p-1 shadow-sm">
                <a href="{{ route('commercial.cyber.performance', ['mois' => $previousMonth->format('Y-m')]) }}"
                   class="flex h-8 w-8 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 hover:text-slate-900" aria-label="Mois précédent">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <span class="min-w-32 px-2 text-center text-sm font-semibold text-[#0B0F14]">{{ ucfirst($month->locale('fr')->translatedFormat('F Y')) }}</span>
                @if($isCurrentMonth)
                    <span class="flex h-8 w-8 items-center justify-center text-slate-300" aria-hidden="true">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @else
                    <a href="{{ route('commercial.cyber.performance', ['mois' => $nextMonth->format('Y-m')]) }}"
                       class="flex h-8 w-8 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 hover:text-slate-900" aria-label="Mois suivant">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endif
            </div>
            <x-button :href="route('commercial.cyber.index')" variant="secondary" size="md">Retour au cyber</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-stat-card title="CA cyber du mois" :value="$money($totals['revenue']).' F'" :change="$totals['days'].' jour(s) d\'activité'" changeType="neutral" timeframe="" />
        <x-stat-card title="Clients servis" :value="number_format($totals['clients'], 0, ',', ' ')" change="1 ticket = 1 client" changeType="neutral" timeframe="" />
        <x-stat-card title="Ticket moyen" :value="$money($totals['average_ticket']).' F'" change="par client" changeType="neutral" timeframe="" />
        <x-stat-card title="Seuil de performance" :value="$money($totals['threshold']).' F'" change="moyenne des agents actifs" changeType="neutral" timeframe="" />
    </div>

    @if($totals['unassigned_clients'] > 0)
        <x-alert type="warning">
            {{ $totals['unassigned_clients'] }} ticket(s) du mois ({{ $money($totals['unassigned_revenue']) }} F) n'ont pas d'agent « Réalisé par » : ils ne comptent pour aucun agent.
        </x-alert>
    @endif

    <x-card title="Classement du mois" subtitle="Montant généré = prestations réalisées par l'agent · Encaissé = tickets passés à sa caisse" :padding="false">
        @if(collect($report['agents'])->isEmpty())
            <p class="px-6 py-12 text-center text-sm text-slate-500">Aucun agent actif. Ajoutez vos agents dans Ressources Humaines › Employés.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3 sm:px-6">Rang</th>
                            <th class="px-3 py-3">Agent</th>
                            <th class="px-3 py-3 text-right">Clients</th>
                            <th class="px-3 py-3 text-right">Jours</th>
                            <th class="px-3 py-3 text-right">Clients / jour</th>
                            <th class="px-3 py-3 text-right">Montant généré</th>
                            <th class="px-3 py-3 text-right">F / jour</th>
                            <th class="px-3 py-3">Part du CA</th>
                            <th class="px-3 py-3 text-right">Encaissé</th>
                            <th class="px-5 py-3 sm:px-6">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($report['agents'] as $agent)
                            <tr class="{{ $agent['status'] === 'Inactif' ? 'opacity-50' : '' }}">
                                <td class="px-5 py-3.5 sm:px-6">
                                    @if($agent['rank'])
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold {{ $agent['rank'] === 1 ? 'bg-[#0B0F14] text-white' : 'bg-slate-100 text-slate-600' }}">{{ $agent['rank'] }}</span>
                                    @else
                                        <span class="pl-3 text-slate-300">–</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    <p class="font-semibold text-[#0B0F14] whitespace-nowrap">{{ $agent['name'] }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $agent['position'] ?: 'Agent' }}</p>
                                </td>
                                <td class="px-3 py-3.5 text-right tabular-nums text-[#0B0F14]">{{ $agent['clients'] }}</td>
                                <td class="px-3 py-3.5 text-right tabular-nums text-slate-600">{{ $agent['days'] }}</td>
                                <td class="px-3 py-3.5 text-right tabular-nums text-slate-600">{{ number_format($agent['clients_per_day'], 1, ',', ' ') }}</td>
                                <td class="px-3 py-3.5 text-right font-semibold tabular-nums text-[#0B0F14] whitespace-nowrap">{{ $money($agent['revenue']) }} F</td>
                                <td class="px-3 py-3.5 text-right tabular-nums text-slate-600 whitespace-nowrap">{{ $money($agent['revenue_per_day']) }} F</td>
                                <td class="px-3 py-3.5">
                                    <div class="flex min-w-28 items-center gap-2">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-[#0B0F14] dark:bg-white" style="width: {{ min(100, $agent['share']) }}%"></div>
                                        </div>
                                        <span class="w-10 text-right text-xs tabular-nums text-slate-500">{{ number_format($agent['share'], 1, ',', ' ') }} %</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-right tabular-nums text-slate-600 whitespace-nowrap">{{ $agent['collected'] > 0 ? $money($agent['collected']).' F' : '–' }}</td>
                                <td class="px-5 py-3.5 sm:px-6"><x-badge :variant="$statusBadges[$agent['status']] ?? 'neutral'" dot>{{ $agent['status'] }}</x-badge></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    <x-card title="Clients servis par jour" subtitle="Nombre de tickets réalisés par chaque agent, jour par jour" :padding="false">
        @if(empty($report['daily']['agents']))
            <p class="px-6 py-12 text-center text-sm text-slate-500">Aucune prestation attribuée à un agent ce mois-ci.</p>
        @else
            <div class="max-h-[560px] overflow-auto">
                <table class="w-full text-center text-xs">
                    <thead class="sticky top-0 z-10 bg-white text-[11px] font-semibold text-slate-500">
                        <tr class="border-b border-slate-100">
                            <th class="sticky left-0 bg-white px-4 py-3 text-left">Jour</th>
                            @foreach($report['daily']['agents'] as $agent)
                                <th class="px-2 py-3 font-medium whitespace-nowrap">{{ $agent['name'] }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-[#0B0F14]">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($report['daily']['days'] as $day)
                            @php $date = $month->copy()->day($day); @endphp
                            <tr class="{{ $date->isSunday() ? 'bg-[#F5F7FA]/60' : '' }}">
                                <td class="sticky left-0 bg-white px-4 py-2 text-left whitespace-nowrap">
                                    <span class="font-semibold text-[#0B0F14]">{{ $day }}</span>
                                    <span class="text-slate-400">{{ $date->locale('fr')->translatedFormat('D') }}</span>
                                </td>
                                @foreach($report['daily']['agents'] as $agent)
                                    @php $count = $report['daily']['counts'][$day][$agent['employee_id']] ?? 0; @endphp
                                    <td class="px-2 py-1.5">
                                        @if($count > 0)
                                            <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full px-1.5 font-semibold tabular-nums text-[#0B0F14]"
                                                  style="background-color: rgb(0 102 255 / {{ round(0.08 + 0.42 * $count / $maxDaily, 2) }})">{{ $count }}</span>
                                        @else
                                            <span class="text-slate-300">·</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-4 py-2 font-semibold tabular-nums text-[#0B0F14]">{{ $report['daily']['totals'][$day] ?: '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</x-layouts.app>
