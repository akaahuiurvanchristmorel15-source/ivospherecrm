@php
    $totalEmployees = (int) ($totalEmployees ?? 0);
    $presentToday = (int) ($presentToday ?? 0);
    $lateToday = (int) ($lateToday ?? 0);
    $pendingTaskValidations = (int) ($pendingTaskValidations ?? 0);
    $monthlyEvaluationAvg = (float) ($monthlyEvaluationAvg ?? 0);
    $presenceRate = $totalEmployees > 0 ? min(100, round($presentToday / $totalEmployees * 100)) : 0;

    $modules = [
        ['rh.attendance.index', 'Présences', 'Arrivées, départs et retards', 'bg-emerald-50 text-emerald-600', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['rh.schedules.index', 'Plannings', 'Semaines de 6 jours max.', 'bg-blue-50 text-[#0066FF]', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['rh.daily-tasks.index', 'Tâches du jour', 'Fiches et validation', 'bg-amber-50 text-amber-600', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
        ['rh.employees.index', 'Employés', 'Dossiers et badges', 'bg-slate-100 text-slate-700', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['rh.evaluations.index', 'Évaluations', 'Notes mensuelles', 'bg-purple-50 text-purple-600', 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
        ['rh.leaves.index', 'Congés', 'Demandes et soldes', 'bg-rose-50 text-rose-600', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['rh.sales-targets.index', 'Objectifs ventes', 'Paliers et commissions', 'bg-emerald-50 text-emerald-600', 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
        ['rh.settings.index', 'Paramètres', 'Horaires et barème', 'bg-slate-100 text-slate-700', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z'],
    ];
@endphp

<x-layouts.app>
    <x-slot:title>Tableau de bord RH — IVOSPHERE ERP</x-slot>

    <div class="space-y-5 sm:space-y-6 [font-variant-numeric:tabular-nums]">

        {{-- ───────── En-tête ───────── --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="space-y-1">
                <p class="text-xs font-medium capitalize text-[#64748B]">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
                <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">Ressources humaines</h1>
                <p class="text-sm text-[#64748B]">Équipe, présences, plannings, tâches et évaluations.</p>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:flex">
                <a href="{{ route('rh.attendance.terminal') }}" target="_blank" class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl border border-[#E2E8F0] bg-white px-4 text-sm font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF]">
                    <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Borne QR
                </a>
                <a href="{{ route('rh.employees.create') }}" class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052cc]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nouvel employé
                </a>
            </div>
        </header>

        {{-- ───────── Aujourd'hui ───────── --}}
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-12" aria-label="Indicateurs du jour">
            <article class="relative overflow-hidden rounded-3xl bg-[#0B0F14] p-5 text-white sm:p-6 lg:col-span-5">
                <div class="pointer-events-none absolute -right-16 -top-16 h-52 w-52 rounded-full bg-emerald-500/25 blur-3xl"></div>
                <div class="relative flex items-center gap-5">
                    <div class="relative h-24 w-24 shrink-0">
                        <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90">
                            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="3"/>
                            @if($presenceRate > 0)
                                <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-dasharray="{{ $presenceRate }} 100"/>
                            @endif
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-xl font-semibold">{{ $presenceRate }}%</span>
                    </div>
                    <div class="min-w-0 space-y-1">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Présents aujourd'hui</p>
                        <p class="text-3xl font-semibold tracking-tight">{{ $presentToday }} <span class="text-base font-normal text-slate-400">/ {{ $totalEmployees }}</span></p>
                        <p class="text-xs {{ $lateToday > 0 ? 'text-amber-300' : 'text-emerald-300' }}">
                            {{ $lateToday > 0 ? $lateToday.' retard(s) aujourd\'hui' : 'Tout le monde à l\'heure' }}
                        </p>
                    </div>
                </div>
            </article>

            <div class="grid grid-cols-3 gap-3 sm:gap-4 lg:col-span-7">
                <a href="{{ route('rh.employees.index') }}" class="flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-white p-4 transition hover:border-slate-300">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <div class="mt-3">
                        <p class="text-2xl font-semibold text-[#0B0F14]">{{ $totalEmployees }}</p>
                        <p class="text-[11px] leading-tight text-[#64748B] sm:text-xs">Employés actifs</p>
                    </div>
                </a>
                <a href="{{ route('rh.daily-tasks.index') }}" class="flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-white p-4 transition hover:border-amber-300">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </span>
                    <div class="mt-3">
                        <p class="text-2xl font-semibold {{ $pendingTaskValidations > 0 ? 'text-amber-600' : 'text-[#0B0F14]' }}">{{ $pendingTaskValidations }}</p>
                        <p class="text-[11px] leading-tight text-[#64748B] sm:text-xs">Tâches à valider</p>
                    </div>
                </a>
                <a href="{{ route('rh.evaluations.index') }}" class="flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-white p-4 transition hover:border-purple-300">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </span>
                    <div class="mt-3">
                        <p class="text-2xl font-semibold text-[#0B0F14]">{{ $monthlyEvaluationAvg > 0 ? number_format($monthlyEvaluationAvg, 1, ',', '') : '—' }}</p>
                        <p class="text-[11px] leading-tight text-[#64748B] sm:text-xs">Moyenne / 20</p>
                    </div>
                </a>
            </div>
        </section>

        {{-- ───────── Modules ───────── --}}
        <section aria-labelledby="modules-title">
            <h2 id="modules-title" class="mb-3 px-1 text-sm font-semibold text-[#0B0F14]">Modules</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach($modules as [$routeName, $title, $description, $tint, $icon])
                    <a href="{{ route($routeName) }}" class="group rounded-2xl border border-[#E2E8F0] bg-white p-4 transition hover:border-[#0066FF]/40 hover:shadow-[0_8px_24px_-12px_rgba(0,102,255,0.25)]">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $tint }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        </span>
                        <p class="mt-3 text-sm font-medium text-[#0B0F14] group-hover:text-[#0066FF]">{{ $title }}</p>
                        <p class="mt-0.5 text-[11px] text-[#64748B]">{{ $description }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- ───────── Activité ───────── --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <section class="rounded-2xl border border-[#E2E8F0] bg-white" aria-labelledby="latest-title">
                <div class="flex items-center justify-between gap-3 border-b border-[#E2E8F0] px-5 py-4">
                    <div>
                        <h2 id="latest-title" class="text-sm font-semibold text-[#0B0F14]">Pointages du jour</h2>
                        <p class="text-xs text-[#64748B]">Arrivées et départs en temps réel</p>
                    </div>
                    <a href="{{ route('rh.attendance.index') }}" class="shrink-0 text-xs font-medium text-[#0066FF] hover:underline">Tout voir</a>
                </div>
                <ul class="divide-y divide-[#E2E8F0] px-5">
                    @forelse($latestAttendances ?? [] as $att)
                        <li class="flex items-center justify-between gap-3 py-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#F5F7FA] text-[11px] font-semibold text-[#0B0F14]">
                                    {{ mb_substr($att->employee->first_name ?? 'E', 0, 1) . mb_substr($att->employee->last_name ?? '', 0, 1) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-[#0B0F14]">{{ $att->employee->full_name ?? 'Collaborateur' }}</p>
                                    <p class="truncate text-[11px] text-[#64748B]">{{ $att->employee->department ?? 'Général' }}</p>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2.5 text-right">
                                <div>
                                    <p class="text-xs font-semibold text-[#0B0F14]">
                                        {{ $att->check_in_time ? substr($att->check_in_time, 0, 5) : '--:--' }}
                                        <span class="text-slate-400">→</span>
                                        {{ $att->check_out_time ? substr($att->check_out_time, 0, 5) : 'en poste' }}
                                    </p>
                                    @if($att->check_out_type === 'automatic')
                                        <p class="text-[10px] text-amber-600">Sortie auto</p>
                                    @endif
                                </div>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-medium {{ $att->is_late ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                                    {{ $att->is_late ? 'Retard' : 'À l\'heure' }}
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="py-10 text-center text-xs text-[#64748B]">Aucun pointage aujourd'hui.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl border border-[#E2E8F0] bg-white" aria-labelledby="activity-title">
                <div class="border-b border-[#E2E8F0] px-5 py-4">
                    <h2 id="activity-title" class="text-sm font-semibold text-[#0B0F14]">Activité RH</h2>
                    <p class="text-xs text-[#64748B]">Dernières actions et validations</p>
                </div>
                <ul class="divide-y divide-[#E2E8F0] px-5">
                    @forelse($recentActivities ?? [] as $log)
                        <li class="flex gap-3 py-3">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-[#0066FF]/60"></span>
                            <div class="min-w-0">
                                <p class="text-sm text-[#0B0F14]">{{ $log->description ?? $log->action }}</p>
                                <p class="text-[11px] text-[#64748B]">{{ $log->user?->name ?? 'Système' }} · {{ $log->created_at->diffForHumans() }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="py-10 text-center text-xs text-[#64748B]">Aucune activité récente.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-layouts.app>
