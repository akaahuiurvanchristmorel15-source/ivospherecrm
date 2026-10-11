@php
    $n = fn ($value) => number_format((float) $value, 0, ',', ' ');
    $averageAchievementRate = (float) $averageAchievementRate;
    $rateRing = min(100, max(0, round($averageAchievementRate)));
    $ringColor = $averageAchievementRate >= 100 ? '#10b981' : ($averageAchievementRate >= 60 ? '#3B8BFF' : '#f59e0b');

    $statusFilters = ['' => 'Tous', 'en_cours' => 'En cours', 'atteint' => 'Atteints', 'partiel' => 'Partiels', 'non_atteint' => 'Non atteints'];
    $hasFilters = collect(request()->only(['employee_id', 'domain_id', 'status', 'period']))->filter()->isNotEmpty();
    $select = 'h-10 rounded-xl border border-[#E2E8F0] bg-white px-3 text-xs font-medium text-[#0B0F14] focus:border-[#0066FF] focus:outline-none';
@endphp

<x-layouts.app title="Objectifs des ventes — IVOSPHERE RH">
    <div class="space-y-5 sm:space-y-6 [font-variant-numeric:tabular-nums]">

        {{-- ───────── En-tête ───────── --}}
        <header class="space-y-4">
            <x-breadcrumb :items="[['label' => 'Ressources Humaines', 'url' => route('rh.index')], ['label' => 'Objectifs des ventes']]" />
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">Objectifs des ventes</h1>
                    <p class="text-sm text-[#64748B]">Quotas, chiffre réalisé et commissions par collaborateur.</p>
                </div>
                <a href="{{ route('rh.sales-targets.create') }}" class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052cc]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nouvel objectif
                </a>
            </div>
        </header>

        {{-- ───────── Synthèse ───────── --}}
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-12" aria-label="Synthèse">
            <article class="relative overflow-hidden rounded-3xl bg-[#0B0F14] p-5 text-white sm:p-6 lg:col-span-5">
                <div class="pointer-events-none absolute -right-16 -top-16 h-52 w-52 rounded-full bg-[#0066FF]/30 blur-3xl"></div>
                <div class="relative flex items-center gap-5">
                    <div class="relative h-24 w-24 shrink-0">
                        <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90">
                            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="3"/>
                            @if($rateRing > 0)
                                <circle cx="18" cy="18" r="15.9155" fill="none" stroke="{{ $ringColor }}" stroke-width="3" stroke-linecap="round" stroke-dasharray="{{ $rateRing }} 100"/>
                            @endif
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-xl font-semibold">{{ round($averageAchievementRate) }}%</span>
                    </div>
                    <div class="min-w-0 space-y-1">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Taux moyen d'atteinte</p>
                        <p class="text-2xl font-semibold tracking-tight">{{ $n($totalAchievedAmount) }} <span class="text-xs font-normal text-slate-400">FCFA réalisés</span></p>
                        <p class="text-xs text-slate-400">sur {{ $n($totalTargetAmount) }} FCFA d'objectifs</p>
                    </div>
                </div>
            </article>

            <div class="grid grid-cols-3 gap-3 sm:gap-4 lg:col-span-7">
                @foreach([
                    [$activeCount, 'Objectifs actifs', 'bg-blue-50 text-[#0066FF]', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    [$achievedCount, 'Objectifs atteints', 'bg-emerald-50 text-emerald-600', 'M5 13l4 4L19 7'],
                    [$n($totalCommissions), 'Commissions (FCFA)', 'bg-amber-50 text-amber-600', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ] as [$value, $label, $tint, $icon])
                    <article class="flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-white p-4">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $tint }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        </span>
                        <div class="mt-3 min-w-0">
                            <p class="truncate text-xl font-semibold text-[#0B0F14] sm:text-2xl">{{ $value }}</p>
                            <p class="text-[11px] leading-tight text-[#64748B] sm:text-xs">{{ $label }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- ───────── Filtres ───────── --}}
        <form method="GET" action="{{ route('rh.sales-targets.index') }}" class="space-y-3">
            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                <label for="stEmployee" class="sr-only">Collaborateur</label>
                <select id="stEmployee" name="employee_id" onchange="this.form.submit()" class="{{ $select }} col-span-2 sm:w-56">
                    <option value="">Tous les collaborateurs</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(request('employee_id') == $emp->id)>{{ $emp->full_name }}</option>
                    @endforeach
                </select>
                <label for="stDomain" class="sr-only">Pôle</label>
                <select id="stDomain" name="domain_id" onchange="this.form.submit()" class="{{ $select }} sm:w-48">
                    <option value="">Tous les pôles</option>
                    @foreach($domains as $dom)
                        <option value="{{ $dom->id }}" @selected(request('domain_id') == $dom->id)>{{ $dom->name }}</option>
                    @endforeach
                </select>
                <label for="stPeriod" class="sr-only">Périodicité</label>
                <select id="stPeriod" name="period" onchange="this.form.submit()" class="{{ $select }} sm:w-44">
                    <option value="">Toutes périodes</option>
                    @foreach(['mensuel' => 'Mensuel', 'trimestriel' => 'Trimestriel', 'semestriel' => 'Semestriel', 'annuel' => 'Annuel'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('period') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="status" value="{{ request('status') }}">
                <noscript><button type="submit" class="h-10 rounded-xl bg-[#0B0F14] px-4 text-xs font-semibold text-white">Filtrer</button></noscript>
            </div>
            <div class="flex items-center gap-2">
                <div class="no-scrollbar -mx-3.5 flex gap-1.5 overflow-x-auto px-3.5 sm:mx-0 sm:px-0">
                    @foreach($statusFilters as $value => $label)
                        <a href="{{ route('rh.sales-targets.index', array_merge(request()->except('page'), ['status' => $value ?: null])) }}"
                           class="shrink-0 rounded-full px-3.5 py-1.5 text-xs font-medium transition {{ (string) request('status', '') === (string) $value ? 'bg-[#0B0F14] text-white' : 'border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#0B0F14]' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                @if($hasFilters)
                    <a href="{{ route('rh.sales-targets.index') }}" class="ml-auto shrink-0 text-xs font-medium text-[#0066FF] hover:underline">Réinitialiser</a>
                @endif
            </div>
        </form>

        {{-- ───────── Objectifs ───────── --}}
        @if($targets->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#E2E8F0] bg-white px-6 py-14 text-center">
                <p class="text-sm font-medium text-[#0B0F14]">Aucun objectif commercial</p>
                <p class="mt-1 text-xs text-[#64748B]">Fixez des quotas pour suivre la performance et calculer les commissions.</p>
                <a href="{{ route('rh.sales-targets.create') }}" class="mt-4 inline-flex h-9 items-center rounded-xl bg-[#0066FF] px-4 text-xs font-semibold text-white hover:bg-[#0052cc]">Fixer un objectif</a>
            </div>
        @else
            <ul class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                @foreach($targets as $target)
                    @php
                        $badge = $target->status_badge;
                        $progress = (float) $target->progress_percentage;
                        $isReached = $progress >= 100;
                    @endphp
                    <li class="group flex flex-col rounded-2xl border border-[#E2E8F0] bg-white transition hover:border-[#0066FF]/40">
                        <a href="{{ route('rh.sales-targets.show', $target) }}" class="block p-4 sm:p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-xs font-semibold text-[#0066FF]">
                                        {{ mb_substr($target->employee->first_name ?? 'E', 0, 1) }}{{ mb_substr($target->employee->last_name ?? 'M', 0, 1) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-[#0B0F14] group-hover:text-[#0066FF]">{{ $target->title }}</p>
                                        <p class="truncate text-[11px] text-[#64748B]">{{ $target->employee->full_name ?? 'Employé' }} · {{ $target->domain->name ?? 'Tous les pôles' }}</p>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-full border px-2.5 py-0.5 text-[10px] font-medium {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                            </div>

                            <div class="mt-4 space-y-1.5">
                                <div class="flex items-baseline justify-between gap-2">
                                    <p class="text-lg font-semibold text-[#0B0F14]">{{ $n($target->achieved_amount) }} <span class="text-xs font-normal text-[#64748B]">/ {{ $n($target->target_amount) }} FCFA</span></p>
                                    <span class="text-sm font-semibold {{ $isReached ? 'text-emerald-600' : 'text-[#0066FF]' }}">{{ $target->progress_percentage }}%</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $isReached ? 'bg-emerald-500' : 'bg-[#0066FF]' }}" style="width: {{ min(100, $progress) }}%"></div>
                                </div>
                                <p class="text-[11px] {{ $target->remaining_amount > 0 ? 'text-[#64748B]' : 'font-medium text-emerald-600' }}">
                                    {{ $target->remaining_amount > 0 ? 'Reste '.$n($target->remaining_amount).' FCFA' : 'Dépassé de '.$n(abs($target->target_amount - $target->achieved_amount)).' FCFA' }}
                                </p>
                            </div>
                        </a>
                        <div class="mt-auto flex items-center gap-3 border-t border-[#E2E8F0] px-4 py-2.5 text-[11px] text-[#64748B] sm:px-5">
                            <span class="flex min-w-0 flex-1 flex-wrap items-center gap-x-3 gap-y-1">
                                <span><span class="capitalize">{{ $target->period }}</span> · {{ $target->start_date->format('d/m/y') }} → {{ $target->end_date->format('d/m/y') }}</span>
                                <span class="font-medium text-[#0B0F14]">{{ $n($target->estimated_commission) }} FCFA <span class="font-normal text-[#64748B]">({{ $target->commission_rate }}%{{ $target->bonus_amount > 0 ? ' + prime' : '' }})</span></span>
                            </span>
                            <a href="{{ route('rh.sales-targets.edit', $target) }}" class="shrink-0 rounded-lg px-2.5 py-1.5 font-medium text-[#0B0F14] transition hover:bg-slate-100">Modifier</a>
                        </div>
                    </li>
                @endforeach
            </ul>

            @if($targets->hasPages())
                <div>{{ $targets->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.app>
