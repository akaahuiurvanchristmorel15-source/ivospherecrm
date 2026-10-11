@php
    $currentDate = \Carbon\Carbon::parse($date);
    $dayQuery = fn (\Carbon\Carbon $day): array => array_merge(request()->query(), ['date' => $day->toDateString(), 'page' => null]);
    $totalActiveEmployees = (int) $totalActiveEmployees;
    $presenceRate = $totalActiveEmployees > 0 ? min(100, round($presentCount / $totalActiveEmployees * 100)) : 0;

    $statusStyles = [
        'present' => ['Présent', 'bg-emerald-50 text-emerald-700'],
        'absent' => ['Absent', 'bg-rose-50 text-rose-700'],
        'retard' => ['En retard', 'bg-amber-50 text-amber-700'],
        'congé' => ['En congé', 'bg-blue-50 text-[#0066FF]'],
    ];
    $qrLabels = [
        'premises_monthly_poster' => 'Affiche du mois',
        'premises_dynamic' => 'Borne QR',
        'personal_badge' => 'Badge perso',
    ];

    $input = 'w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15';
@endphp

<x-layouts.app>
    <x-slot:title>Présences — IVOSPHERE RH</x-slot>

    <div class="space-y-5 sm:space-y-6 [font-variant-numeric:tabular-nums]" x-data="{ showModal: false }">

        {{-- ───────── En-tête ───────── --}}
        <header class="space-y-4">
            <x-breadcrumb :items="[['label' => 'Ressources Humaines', 'url' => route('rh.index')], ['label' => 'Présences']]" />
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">Présences</h1>
                    <p class="text-sm text-[#64748B]">Arrivées, départs et ponctualité de l'équipe.</p>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                    <a href="{{ route('rh.attendance.scanner') }}" class="col-span-2 inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052cc] sm:order-last">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Pointer
                    </a>
                    <button type="button" @click="showModal = true" class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl border border-[#E2E8F0] bg-white px-4 text-sm font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF]">
                        <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Rectifier
                    </button>
                    <a href="{{ route('rh.attendance.poster') }}" target="_blank" class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl border border-[#E2E8F0] bg-white px-4 text-sm font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF]">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Affiche
                    </a>
                    <a href="{{ route('rh.attendance.terminal') }}" target="_blank" class="hidden h-10 items-center justify-center gap-2 rounded-xl border border-[#E2E8F0] bg-white px-4 text-sm font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF] sm:inline-flex">
                        <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Borne
                    </a>
                </div>
            </div>
        </header>

        {{-- ───────── Synthèse du jour ───────── --}}
        <section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4" aria-label="Synthèse">
            <article class="col-span-2 flex items-center gap-4 rounded-2xl bg-[#0B0F14] p-4 text-white lg:col-span-1">
                <div class="relative h-16 w-16 shrink-0">
                    <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90">
                        <circle cx="18" cy="18" r="15.9155" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="3.5"/>
                        @if($presenceRate > 0)
                            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#10b981" stroke-width="3.5" stroke-linecap="round" stroke-dasharray="{{ $presenceRate }} 100"/>
                        @endif
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center text-xs font-semibold">{{ $presenceRate }}%</span>
                </div>
                <div>
                    <p class="text-2xl font-semibold">{{ $presentCount }} <span class="text-sm font-normal text-slate-400">/ {{ $totalActiveEmployees }}</span></p>
                    <p class="text-xs text-slate-400">Présents</p>
                </div>
            </article>
            @foreach([
                [$lateCount, 'Retards', 'Tolérance '.$settings->punctuality_grace_minutes.' min', 'text-amber-600', 'bg-amber-50 text-amber-600', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                [$autoCheckoutCount, 'Clôtures auto', 'Départ non scanné', 'text-[#0B0F14]', 'bg-slate-100 text-slate-600', 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1'],
                [$absentCount, 'Non pointés', 'À régulariser', 'text-rose-600', 'bg-rose-50 text-rose-600', 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
            ] as [$value, $label, $hint, $valueColor, $iconTint, $icon])
                <article class="rounded-2xl border border-[#E2E8F0] bg-white p-4 {{ $loop->last ? 'col-span-2 sm:col-span-1' : '' }}">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-[#64748B]">{{ $label }}</p>
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $iconTint }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        </span>
                    </div>
                    <p class="mt-1 text-2xl font-semibold {{ $value > 0 ? $valueColor : 'text-[#0B0F14]' }}">{{ $value }}</p>
                    <p class="text-[11px] text-[#64748B]">{{ $hint }}</p>
                </article>
            @endforeach
        </section>

        {{-- ───────── Filtres ───────── --}}
        <form method="GET" action="{{ route('rh.attendance.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="inline-flex h-10 items-center rounded-xl border border-[#E2E8F0] bg-white p-1">
                <a href="{{ route('rh.attendance.index', $dayQuery($currentDate->copy()->subDay())) }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] hover:bg-slate-100 hover:text-[#0B0F14]" aria-label="Jour précédent">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <label for="attDate" class="sr-only">Date</label>
                <input id="attDate" type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="h-8 flex-1 border-0 bg-transparent px-2 text-center text-sm font-medium text-[#0B0F14] focus:outline-none">
                <a href="{{ route('rh.attendance.index', $dayQuery($currentDate->copy()->addDay())) }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] hover:bg-slate-100 hover:text-[#0B0F14]" aria-label="Jour suivant">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            @unless($currentDate->isToday())
                <a href="{{ route('rh.attendance.index', $dayQuery(today())) }}" class="inline-flex h-10 items-center justify-center rounded-xl px-3 text-xs font-medium text-[#0066FF] hover:bg-blue-50">Aujourd'hui</a>
            @endunless
            <div class="grid flex-1 grid-cols-2 gap-2 sm:flex sm:justify-end">
                <label for="attDept" class="sr-only">Département</label>
                <select id="attDept" name="department" onchange="this.form.submit()" class="h-10 rounded-xl border border-[#E2E8F0] bg-white px-3 text-xs font-medium text-[#0B0F14] focus:border-[#0066FF] focus:outline-none sm:w-48">
                    <option value="">Tous les départements</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" @selected(request('department') == $dept)>{{ $dept }}</option>
                    @endforeach
                </select>
                <label for="attOut" class="sr-only">Mode de sortie</label>
                <select id="attOut" name="check_out_type" onchange="this.form.submit()" class="h-10 rounded-xl border border-[#E2E8F0] bg-white px-3 text-xs font-medium text-[#0B0F14] focus:border-[#0066FF] focus:outline-none sm:w-44">
                    <option value="">Tous les départs</option>
                    <option value="manual" @selected(request('check_out_type') == 'manual')>Départ scanné</option>
                    <option value="automatic" @selected(request('check_out_type') == 'automatic')>Clôture automatique</option>
                </select>
            </div>
            <noscript><button type="submit" class="h-10 rounded-xl bg-[#0B0F14] px-4 text-xs font-semibold text-white">Filtrer</button></noscript>
        </form>

        {{-- ───────── Registre ───────── --}}
        @if($attendances->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#E2E8F0] bg-white px-6 py-14 text-center">
                <p class="text-sm font-medium text-[#0B0F14]">Aucun pointage le {{ $currentDate->locale('fr')->isoFormat('D MMMM YYYY') }}</p>
                <p class="mt-1 text-xs text-[#64748B]">Les pointages apparaissent ici dès que les collaborateurs scannent le QR code.</p>
                <button type="button" @click="showModal = true" class="mt-4 inline-flex h-9 items-center rounded-xl bg-[#0066FF] px-4 text-xs font-semibold text-white hover:bg-[#0052cc]">Saisir un pointage</button>
            </div>
        @else
            {{-- Tableau desktop --}}
            <div class="hidden overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white md:block">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-[#E2E8F0] text-[11px] text-[#64748B]">
                        <tr>
                            <th class="px-5 py-3 font-medium">Collaborateur</th>
                            <th class="px-3 py-3 font-medium">Arrivée</th>
                            <th class="px-3 py-3 font-medium">Départ</th>
                            <th class="px-3 py-3 font-medium">Statut</th>
                            <th class="px-5 py-3 font-medium">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @foreach($attendances as $attendance)
                            @php [$statusLabel, $statusClass] = $statusStyles[$attendance->status] ?? [ucfirst($attendance->status), 'bg-slate-100 text-slate-600']; @endphp
                            <tr class="transition hover:bg-[#F5F7FA]/70">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#F5F7FA] text-[11px] font-semibold text-[#0B0F14]">
                                            {{ mb_substr($attendance->employee->first_name ?? 'E', 0, 1) . mb_substr($attendance->employee->last_name ?? '', 0, 1) }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-[#0B0F14]">{{ $attendance->employee->full_name }}</p>
                                            <p class="truncate text-[11px] text-[#64748B]">{{ $attendance->employee->department ?? 'Général' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5">
                                    @if($attendance->check_in)
                                        <p class="text-sm font-semibold text-[#0B0F14]">{{ $attendance->check_in_at ? $attendance->check_in_at->format('H:i') : substr($attendance->check_in, 0, 5) }}</p>
                                        <p class="text-[11px] {{ $attendance->delay_minutes > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                            {{ $attendance->delay_minutes > 0 ? "+{$attendance->delay_minutes} min" : 'À l\'heure' }} · {{ $attendance->punctuality_score }} pt
                                        </p>
                                        @if($attendance->qr_type)
                                            <p class="text-[10px] text-slate-400">{{ $qrLabels[$attendance->qr_type] ?? 'QR code' }}</p>
                                        @endif
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    @if($attendance->check_out)
                                        <p class="text-sm font-semibold text-[#0B0F14]">{{ $attendance->check_out_at ? $attendance->check_out_at->format('H:i') : substr($attendance->check_out, 0, 5) }}</p>
                                        <p class="text-[11px] {{ $attendance->isAutomaticCheckout() ? 'text-amber-600' : 'text-[#64748B]' }}">{{ $attendance->isAutomaticCheckout() ? 'Clôture auto' : 'Scanné' }}</p>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700"><span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>En poste</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5"><span class="rounded-full px-2.5 py-1 text-[11px] font-medium {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                <td class="max-w-56 px-5 py-3.5 text-[11px] text-[#64748B]">{{ $attendance->notes ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Cartes mobile --}}
            <ul class="space-y-2.5 md:hidden">
                @foreach($attendances as $attendance)
                    @php [$statusLabel, $statusClass] = $statusStyles[$attendance->status] ?? [ucfirst($attendance->status), 'bg-slate-100 text-slate-600']; @endphp
                    <li class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F5F7FA] text-xs font-semibold text-[#0B0F14]">
                                {{ mb_substr($attendance->employee->first_name ?? 'E', 0, 1) . mb_substr($attendance->employee->last_name ?? '', 0, 1) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-[#0B0F14]">{{ $attendance->employee->full_name }}</p>
                                <p class="truncate text-[11px] text-[#64748B]">{{ $attendance->employee->department ?? 'Général' }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                        <div class="mt-3 flex items-stretch gap-2">
                            <div class="flex-1 rounded-xl bg-[#F5F7FA] p-3">
                                <p class="text-[10px] font-medium uppercase tracking-wider text-[#64748B]">Arrivée</p>
                                <p class="mt-0.5 text-lg font-semibold text-[#0B0F14]">{{ $attendance->check_in_at ? $attendance->check_in_at->format('H:i') : ($attendance->check_in ? substr($attendance->check_in, 0, 5) : '—') }}</p>
                                @if($attendance->check_in)
                                    <p class="text-[11px] {{ $attendance->delay_minutes > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $attendance->delay_minutes > 0 ? "+{$attendance->delay_minutes} min" : 'À l\'heure' }}</p>
                                @endif
                            </div>
                            <div class="flex-1 rounded-xl bg-[#F5F7FA] p-3">
                                <p class="text-[10px] font-medium uppercase tracking-wider text-[#64748B]">Départ</p>
                                <p class="mt-0.5 text-lg font-semibold text-[#0B0F14]">{{ $attendance->check_out_at ? $attendance->check_out_at->format('H:i') : ($attendance->check_out ? substr($attendance->check_out, 0, 5) : '—') }}</p>
                                <p class="text-[11px] {{ $attendance->check_out && $attendance->isAutomaticCheckout() ? 'text-amber-600' : 'text-[#64748B]' }}">
                                    {{ $attendance->check_out ? ($attendance->isAutomaticCheckout() ? 'Clôture auto' : 'Scanné') : 'En poste' }}
                                </p>
                            </div>
                        </div>
                        @if($attendance->notes)
                            <p class="mt-2.5 text-[11px] text-[#64748B]">{{ $attendance->notes }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>

            @if($attendances->hasPages())
                <div>{{ $attendances->links() }}</div>
            @endif
        @endif

        {{-- ───────── Fenêtre de rectification ───────── --}}
        <div x-show="showModal" x-cloak @keydown.escape.window="showModal = false"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 flex items-end justify-center bg-[#0B0F14]/50 backdrop-blur-sm sm:items-center sm:p-4"
             role="dialog" aria-modal="true" aria-labelledby="rectifyTitle">
            <div @click.outside="showModal = false" class="max-h-[92dvh] w-full overflow-y-auto rounded-t-3xl bg-white p-5 pb-[max(1.25rem,env(safe-area-inset-bottom))] shadow-xl sm:max-w-lg sm:rounded-2xl sm:p-6">
                <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-slate-200 sm:hidden"></div>
                <div class="mb-5 flex items-start justify-between gap-3">
                    <div>
                        <h3 id="rectifyTitle" class="text-base font-semibold text-[#0B0F14]">Saisir ou rectifier un pointage</h3>
                        <p class="text-xs text-[#64748B]">Remplace le pointage existant de ce jour pour l'employé.</p>
                    </div>
                    <button type="button" @click="showModal = false" class="-mr-1 rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Fermer">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('rh.attendance.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="rectEmployee" class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Employé</label>
                            <select id="rectEmployee" name="employee_id" required class="{{ $input }}">
                                <option value="">Sélectionner un employé</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="rectDate" class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Date</label>
                            <input id="rectDate" type="date" name="date" value="{{ $date }}" required class="{{ $input }}">
                        </div>
                        <div>
                            <label for="rectStatus" class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Statut</label>
                            <select id="rectStatus" name="status" required class="{{ $input }}">
                                <option value="present">Présent</option>
                                <option value="absent">Absent</option>
                                <option value="retard">En retard</option>
                                <option value="congé">En congé</option>
                            </select>
                        </div>
                        <div>
                            <label for="rectIn" class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Arrivée</label>
                            <input id="rectIn" type="time" name="check_in" class="{{ $input }}">
                        </div>
                        <div>
                            <label for="rectOut" class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Départ</label>
                            <input id="rectOut" type="time" name="check_out" class="{{ $input }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="rectType" class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Type de départ</label>
                            <select id="rectType" name="check_out_type" class="{{ $input }}">
                                <option value="manual">Départ scanné</option>
                                <option value="automatic">Clôture automatique</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="rectNotes" class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Note</label>
                            <input id="rectNotes" type="text" name="notes" placeholder="Justification éventuelle…" class="{{ $input }}">
                        </div>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="button" @click="showModal = false" class="h-11 flex-1 rounded-xl border border-[#E2E8F0] text-sm font-medium text-[#0B0F14] hover:bg-slate-50 sm:flex-none sm:px-5">Annuler</button>
                        <button type="submit" class="h-11 flex-1 rounded-xl bg-[#0066FF] text-sm font-semibold text-white hover:bg-[#0052cc]">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
