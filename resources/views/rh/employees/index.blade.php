@php
    $statusStyles = [
        'actif' => ['Actif', 'bg-emerald-50 text-emerald-700', 'bg-emerald-500'],
        'suspendu' => ['Suspendu', 'bg-amber-50 text-amber-700', 'bg-amber-500'],
        'inactif' => ['Inactif', 'bg-slate-100 text-slate-600', 'bg-slate-400'],
    ];
    $statusFilters = ['' => 'Tous', 'actif' => 'Actifs', 'suspendu' => 'Suspendus', 'inactif' => 'Inactifs'];
    $avatarTints = ['bg-blue-50 text-[#0066FF]', 'bg-emerald-50 text-emerald-700', 'bg-purple-50 text-purple-700', 'bg-amber-50 text-amber-700', 'bg-rose-50 text-rose-700'];
    $hasFilters = request()->hasAny(['search', 'domain_id', 'status']) && collect(request()->only(['search', 'domain_id', 'status']))->filter()->isNotEmpty();
@endphp

<x-layouts.app>
    <x-slot:title>Employés — IVOSPHERE RH</x-slot>

    <div class="space-y-5 sm:space-y-6">

        {{-- ───────── En-tête ───────── --}}
        <header class="space-y-4">
            <x-breadcrumb :items="[['label' => 'Ressources Humaines', 'url' => route('rh.index')], ['label' => 'Employés']]" />
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">Employés</h1>
                    <p class="text-sm text-[#64748B]">{{ $employees->total() }} dossier(s) du personnel{{ $hasFilters ? ' correspondant aux filtres' : '' }}.</p>
                </div>
                <a href="{{ route('rh.employees.create') }}" class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052cc]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nouvel employé
                </a>
            </div>
        </header>

        {{-- ───────── Recherche & filtres ───────── --}}
        <form method="GET" action="{{ route('rh.employees.index') }}" class="space-y-3">
            <div class="flex flex-col gap-2 sm:flex-row">
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <label for="empSearch" class="sr-only">Rechercher</label>
                    <input id="empSearch" type="search" name="search" value="{{ request('search') }}" placeholder="Nom, e-mail ou matricule…"
                           class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-white pl-10 pr-3.5 text-sm text-[#0B0F14] placeholder-slate-400 focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15">
                </div>
                <label for="empDomain" class="sr-only">Domaine</label>
                <select id="empDomain" name="domain_id" onchange="this.form.submit()" class="h-11 rounded-xl border border-[#E2E8F0] bg-white px-3.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none sm:w-52">
                    <option value="">Tous les domaines</option>
                    @foreach($domains as $domain)
                        <option value="{{ $domain->id }}" @selected(request('domain_id') == $domain->id)>{{ $domain->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="status" value="{{ request('status') }}">
                <button type="submit" class="h-11 rounded-xl bg-[#0B0F14] px-5 text-sm font-semibold text-white transition hover:bg-slate-800">Rechercher</button>
            </div>

            <div class="flex items-center gap-2">
                <div class="no-scrollbar -mx-3.5 flex gap-1.5 overflow-x-auto px-3.5 sm:mx-0 sm:px-0">
                    @foreach($statusFilters as $value => $label)
                        <a href="{{ route('rh.employees.index', array_merge(request()->except('page'), ['status' => $value ?: null])) }}"
                           class="shrink-0 rounded-full px-3.5 py-1.5 text-xs font-medium transition {{ (string) request('status', '') === (string) $value ? 'bg-[#0B0F14] text-white' : 'border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#0B0F14]' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                @if($hasFilters)
                    <a href="{{ route('rh.employees.index') }}" class="ml-auto shrink-0 text-xs font-medium text-[#0066FF] hover:underline">Réinitialiser</a>
                @endif
            </div>
        </form>

        {{-- ───────── Annuaire ───────── --}}
        @if($employees->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#E2E8F0] bg-white px-6 py-14 text-center">
                <p class="text-sm font-medium text-[#0B0F14]">Aucun employé trouvé</p>
                <p class="mt-1 text-xs text-[#64748B]">Modifiez la recherche ou ajoutez un nouveau dossier.</p>
                <a href="{{ route('rh.employees.create') }}" class="mt-4 inline-flex h-9 items-center rounded-xl bg-[#0066FF] px-4 text-xs font-semibold text-white hover:bg-[#0052cc]">Ajouter un employé</a>
            </div>
        @else
            <ul class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($employees as $employee)
                    @php
                        [$statusLabel, $statusClass, $statusDot] = $statusStyles[$employee->status] ?? [ucfirst($employee->status), 'bg-slate-100 text-slate-600', 'bg-slate-400'];
                        $tint = $avatarTints[$employee->id % count($avatarTints)];
                    @endphp
                    <li class="group flex flex-col rounded-2xl border border-[#E2E8F0] bg-white transition hover:border-[#0066FF]/40 hover:shadow-[0_8px_24px_-12px_rgba(0,102,255,0.25)]">
                        <a href="{{ route('rh.employees.show', $employee) }}" class="flex items-start gap-3.5 p-4">
                            <span class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-sm font-semibold {{ $tint }}">
                                {{ mb_substr($employee->first_name, 0, 1) }}{{ mb_substr($employee->last_name, 0, 1) }}
                                <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white {{ $statusDot }}" title="{{ $statusLabel }}"></span>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF]">{{ $employee->full_name }}</p>
                                <p class="truncate text-xs text-[#64748B]">{{ $employee->position ?? 'Poste non renseigné' }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                    <span class="rounded-full bg-[#F5F7FA] px-2 py-0.5 font-mono text-[10px] text-[#0B0F14]">{{ $employee->employee_code }}</span>
                                    @if($employee->department)
                                        <span class="rounded-full bg-[#F5F7FA] px-2 py-0.5 text-[10px] text-[#64748B]">{{ $employee->department }}</span>
                                    @endif
                                    @if($employee->user)
                                        <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-[#0066FF]" title="Compte ERP">{{ $employee->user->roles->first()?->name ?? 'Accès ERP' }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                        </a>

                        <div class="mt-auto flex items-center gap-1 border-t border-[#E2E8F0] px-2 py-1.5 text-[#64748B]">
                            <span class="min-w-0 flex-1 truncate px-2 text-[11px]">{{ $employee->domain->name ?? 'Transversal' }}</span>
                            @if($employee->phone)
                                <a href="tel:{{ $employee->phone }}" class="rounded-lg p-2 transition hover:bg-blue-50 hover:text-[#0066FF]" title="Appeler {{ $employee->phone }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </a>
                            @endif
                            <a href="mailto:{{ $employee->email }}" class="rounded-lg p-2 transition hover:bg-blue-50 hover:text-[#0066FF]" title="Écrire à {{ $employee->email }}">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </a>
                            <a href="{{ route('rh.employees.edit', $employee) }}" class="rounded-lg p-2 transition hover:bg-slate-100 hover:text-[#0B0F14]" title="Modifier">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form action="{{ route('rh.employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Désactiver {{ addslashes($employee->full_name) }} ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg p-2 transition hover:bg-rose-50 hover:text-rose-600" title="Désactiver">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>

            @if($employees->hasPages())
                <div>{{ $employees->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.app>
