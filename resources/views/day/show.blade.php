@php
    $money = fn ($value) => number_format((float) $value, 0, ',', ' ').' F';
    $isToday = $date->isToday();
    $attendance = $day['attendance'];
    $arrival = $attendance?->check_in_at?->format('H:i') ?? ($attendance?->check_in ? substr((string) $attendance->check_in, 0, 5) : null);
    $departure = $attendance?->check_out_at?->format('H:i') ?? ($attendance?->check_out ? substr((string) $attendance->check_out, 0, 5) : null);
    $tasks = $day['tasks'];
    $taskPercent = $tasks['total'] > 0 ? round($tasks['done'] / $tasks['total'] * 100) : 0;
    $methodLabels = ['especes' => 'Espèces', 'mobile_money' => 'Mobile Money', 'carte' => 'Carte', 'credit' => 'Crédit', 'virement' => 'Virement', 'cheque' => 'Chèque'];
    $stockLabels = ['entree' => 'Entrées', 'sortie' => 'Sorties', 'transfert' => 'Transferts', 'ajustement' => 'Ajustements', 'inventaire' => 'Inventaires'];
    $kpis = [
        ['Ventes facturées', $money($day['sales']['invoiced']), $day['sales']['invoices'].' facture(s)', 'text-[#0066FF]'],
        ['Encaissé', $money($day['payments']['total']), $day['payments']['count'].' paiement(s)', 'text-emerald-600'],
        ['Devis & commandes', $day['sales']['quotations'] + $day['sales']['orders'], $day['sales']['quotations'].' devis · '.$day['sales']['orders'].' commande(s)', 'text-[#0B0F14]'],
        ['Dépenses', $money($day['expenses']['total']), $day['expenses']['count'].' dépense(s)', 'text-rose-600'],
    ];
@endphp

<x-layouts.app>
    <x-slot:title>{{ $isSelf ? 'Ma journée' : 'Journée de '.$user->name }}</x-slot>

    <div class="mx-auto max-w-6xl space-y-5">

        {{-- En-tête : utilisateur et navigation entre les jours --}}
        <section class="flex flex-col gap-4 rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-4">
                <x-user-avatar :user="$user" size="lg" />
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">{{ $isSelf ? 'Ma journée' : 'Journée de' }}</p>
                    <h1 class="truncate text-xl font-bold tracking-tight text-[#0B0F14]">{{ $isSelf ? $user->name : $user->name }}</h1>
                    <p class="truncate text-xs text-[#64748B]">{{ $user->roles->pluck('name')->join(', ') ?: 'Utilisateur' }}</p>
                </div>
            </div>

            <form method="GET" class="flex items-center gap-2">
                <a href="{{ $dayRoute($date->subDay()) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#E2E8F0] text-[#0B0F14] transition hover:bg-[#F5F7FA]" aria-label="Jour précédent">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <input type="date" name="date" value="{{ $date->toDateString() }}" max="{{ today()->toDateString() }}" onchange="this.form.submit()"
                       class="h-10 rounded-xl border border-[#E2E8F0] bg-white px-3 text-sm text-[#0B0F14]" aria-label="Choisir le jour">
                @if($isToday)
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#E2E8F0] text-slate-300" aria-hidden="true">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @else
                    <a href="{{ $dayRoute($date->addDay()) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#E2E8F0] text-[#0B0F14] transition hover:bg-[#F5F7FA]" aria-label="Jour suivant">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ $dayRoute(today()->toImmutable()) }}" class="h-10 rounded-xl bg-[#0B0F14] px-3 text-xs font-semibold leading-10 text-white">Aujourd'hui</a>
                @endif
            </form>
        </section>

        <p class="text-sm font-semibold capitalize text-[#0B0F14]">{{ $date->locale('fr')->isoFormat('dddd D MMMM YYYY') }}{{ $isToday ? ' · aujourd\'hui' : '' }}</p>

        {{-- Pointage et tâches --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">Pointage</p>
                @if($attendance)
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-[#F5F7FA] p-3">
                            <p class="text-[11px] text-[#64748B]">Arrivée</p>
                            <p class="text-xl font-bold text-[#0B0F14]">{{ $arrival ?? '—' }}</p>
                        </div>
                        <div class="rounded-xl bg-[#F5F7FA] p-3">
                            <p class="text-[11px] text-[#64748B]">Départ</p>
                            <p class="text-xl font-bold text-[#0B0F14]">{{ $departure ?? ($isToday ? 'En poste' : '—') }}</p>
                        </div>
                    </div>
                    @if($attendance->status)
                        <p class="mt-2 text-xs text-[#64748B]">Statut : <strong class="text-[#0B0F14]">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</strong></p>
                    @endif
                @else
                    <p class="mt-3 text-sm text-[#64748B]">{{ $user->employee ? 'Aucun pointage ce jour-là.' : 'Pas de fiche collaborateur : pointage non suivi.' }}</p>
                    @if($isSelf && $isToday && $user->employee)
                        <a href="{{ route('rh.attendance.scanner') }}" class="mt-3 inline-flex h-9 items-center rounded-xl bg-[#0066FF] px-3 text-xs font-semibold text-white">Pointer maintenant</a>
                    @endif
                @endif
            </section>

            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">Tâches du jour</p>
                    <p class="text-xs font-semibold text-[#0B0F14]">{{ $tasks['done'] }}/{{ $tasks['total'] }}</p>
                </div>
                @if($tasks['total'] > 0)
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-[#F5F7FA]"><div class="h-full rounded-full bg-emerald-500" style="width: {{ $taskPercent }}%"></div></div>
                    <ul class="mt-3 max-h-40 space-y-1.5 overflow-y-auto text-xs">
                        @foreach($tasks['items'] as $task)
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full {{ $task['done'] ? 'bg-emerald-500 text-white' : 'border border-[#E2E8F0]' }}">
                                    @if($task['done'])<svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                                </span>
                                <span class="{{ $task['done'] ? 'text-[#64748B] line-through' : 'text-[#0B0F14]' }}">{{ $task['title'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-3 text-sm text-[#64748B]">Aucune fiche de tâches ce jour-là.</p>
                @endif
                @if($isSelf && $isToday && $tasks['total'] > 0)
                    <a href="{{ route('rh.portal.index') }}" class="mt-3 inline-block text-xs font-semibold text-[#0066FF] hover:underline">Mettre à jour mes tâches →</a>
                @endif
            </section>
        </div>

        {{-- Indicateurs commerciaux --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach($kpis as [$kpiLabel, $kpiValue, $kpiHint, $kpiColor])
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">{{ $kpiLabel }}</p>
                    <p class="mt-1 text-xl font-bold {{ $kpiColor }}">{{ $kpiValue }}</p>
                    <p class="mt-0.5 text-[11px] text-[#64748B]">{{ $kpiHint }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- Encaissements, caisse, stock, cyber --}}
            <section class="space-y-4 rounded-2xl border border-[#E2E8F0] bg-white p-5">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">Encaissements par mode</p>
                    @forelse($day['payments']['by_method'] as $method => $amount)
                        <p class="mt-2 flex justify-between text-sm"><span class="text-[#64748B]">{{ $methodLabels[$method] ?? ucfirst((string) $method) }}</span><strong class="text-[#0B0F14]">{{ $money($amount) }}</strong></p>
                    @empty
                        <p class="mt-2 text-sm text-[#64748B]">Aucun encaissement.</p>
                    @endforelse
                </div>
                <div class="border-t border-[#E2E8F0] pt-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">Sessions de caisse</p>
                    @forelse($day['cash_sessions'] as $session)
                        <p class="mt-2 flex justify-between text-sm">
                            <span class="text-[#64748B]">{{ $session->opened_at?->format('H:i') }} → {{ $session->closed_at?->format('H:i') ?? 'ouverte' }}</span>
                            <strong class="{{ (float) $session->discrepancy !== 0.0 && $session->closed_at ? 'text-rose-600' : 'text-[#0B0F14]' }}">{{ $session->closed_at ? 'Écart '.$money($session->discrepancy) : '' }}</strong>
                        </p>
                    @empty
                        <p class="mt-2 text-sm text-[#64748B]">Aucune session.</p>
                    @endforelse
                </div>
                <div class="border-t border-[#E2E8F0] pt-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">Stock & cyber</p>
                    @foreach($day['stock'] as $type => $count)
                        <p class="mt-2 flex justify-between text-sm"><span class="text-[#64748B]">{{ $stockLabels[$type] ?? ucfirst((string) $type) }}</span><strong class="text-[#0B0F14]">{{ $count }}</strong></p>
                    @endforeach
                    <p class="mt-2 flex justify-between text-sm"><span class="text-[#64748B]">Dépôts cyber traités</span><strong class="text-[#0B0F14]">{{ $day['cyber_deposits'] }}</strong></p>
                </div>
            </section>

            {{-- Fil d'activité --}}
            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 lg:col-span-2">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">Activité de la journée</p>
                @if($day['timeline']->isEmpty())
                    <p class="mt-3 text-sm text-[#64748B]">Aucune action enregistrée ce jour-là.</p>
                @else
                    <ol class="mt-3 max-h-[28rem] space-y-0 overflow-y-auto">
                        @foreach($day['timeline'] as $entry)
                            <li class="relative flex gap-3 pb-4 pl-1 last:pb-0">
                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-[#0066FF]"></span>
                                <div class="min-w-0">
                                    <p class="text-sm text-[#0B0F14]">{{ $entry->description }}</p>
                                    <p class="text-[11px] text-[#64748B]">{{ $entry->created_at->format('H:i') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        </div>

        @unless($isSelf)
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#0066FF] hover:underline">← Retour aux utilisateurs</a>
        @endunless
    </div>
</x-layouts.app>
