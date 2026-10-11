@php
    $statusStyles = [
        'assigne' => ['Assignée', 'bg-blue-50 text-[#0066FF]'],
        'en_cours' => ['En cours', 'bg-amber-50 text-amber-700'],
        'termine' => ['Terminée', 'bg-emerald-50 text-emerald-700'],
    ];
    $statusFilters = ['' => 'Toutes', 'assigne' => 'Assignées', 'en_cours' => 'En cours', 'termine' => 'Terminées'];
    $hasFilters = collect(request()->only(['employee_id', 'date', 'status']))->filter()->isNotEmpty();
    $whatsappIcon = 'M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.584 1.961.949 3.013.953h.005c3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm0 13.067h-.004c-1.127 0-2.228-.316-3.187-.912l-.229-.144-1.58.414.421-1.54-.15-.238c-.655-1.042-1.001-2.247-1.001-3.483 0-3.621 2.947-6.568 6.569-6.568 3.621 0 6.567 2.947 6.567 6.568 0 3.622-2.946 6.568-6.568 6.568z';
@endphp

<x-layouts.app title="Tâches du jour — Ressources Humaines">
    <div class="space-y-5 sm:space-y-6 [font-variant-numeric:tabular-nums]">

        {{-- ───────── En-tête ───────── --}}
        <header class="space-y-4">
            <x-breadcrumb :items="[['label' => 'Ressources Humaines', 'url' => route('rh.index')], ['label' => 'Tâches du jour']]" />
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">Tâches du jour</h1>
                    <p class="text-sm text-[#64748B]">Fiches de missions quotidiennes, envoyées par WhatsApp et e-mail.</p>
                </div>
                <a href="{{ route('rh.daily-tasks.create') }}" class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052cc]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nouvelle fiche
                </a>
            </div>
        </header>

        {{-- ───────── Synthèse ───────── --}}
        <section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4" aria-label="Synthèse">
            <article class="col-span-2 flex items-center justify-between rounded-2xl bg-[#0B0F14] p-5 text-white lg:col-span-1">
                <div>
                    <p class="text-xs text-slate-400">Fiches aujourd'hui</p>
                    <p class="mt-1 text-3xl font-semibold">{{ $todaySheetsCount ?? 0 }}</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </span>
            </article>
            @foreach([
                [$totalAssignedCount ?? 0, 'Fiches au total', 'bg-slate-100 text-slate-600', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', false],
                [$whatsappDispatchedCount ?? 0, 'Envoyées WhatsApp', 'bg-emerald-50 text-emerald-600', $whatsappIcon, true],
                [$emailDispatchedCount ?? 0, 'Envoyées par e-mail', 'bg-blue-50 text-[#0066FF]', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', false],
            ] as [$value, $label, $tint, $icon, $filled])
                <article class="rounded-2xl border border-[#E2E8F0] bg-white p-4 {{ $loop->last ? 'col-span-2 sm:col-span-1' : '' }}">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-[#64748B]">{{ $label }}</p>
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $tint }}">
                            @if($filled)
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $icon }}"/></svg>
                            @else
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            @endif
                        </span>
                    </div>
                    <p class="mt-1 text-2xl font-semibold text-[#0B0F14]">{{ $value }}</p>
                </article>
            @endforeach
        </section>

        {{-- ───────── Filtres ───────── --}}
        <form method="GET" action="{{ route('rh.daily-tasks.index') }}" class="space-y-3">
            <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
                <label for="taskEmployee" class="sr-only">Collaborateur</label>
                <select id="taskEmployee" name="employee_id" onchange="this.form.submit()" class="col-span-2 h-10 rounded-xl border border-[#E2E8F0] bg-white px-3.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none sm:w-60">
                    <option value="">Tous les collaborateurs</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}" @selected(request('employee_id') == $e->id)>{{ $e->full_name }}</option>
                    @endforeach
                </select>
                <label for="taskDate" class="sr-only">Date</label>
                <input id="taskDate" type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()" class="h-10 rounded-xl border border-[#E2E8F0] bg-white px-3.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none">
                <a href="{{ route('rh.daily-tasks.index', array_merge(request()->except('page'), ['date' => today()->toDateString()])) }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-[#E2E8F0] bg-white px-3.5 text-xs font-medium text-[#0B0F14] hover:border-[#0066FF] hover:text-[#0066FF] {{ request('date') === today()->toDateString() ? 'border-[#0066FF] text-[#0066FF]' : '' }}">Aujourd'hui</a>
                <input type="hidden" name="status" value="{{ request('status') }}">
                <noscript><button type="submit" class="h-10 rounded-xl bg-[#0B0F14] px-4 text-xs font-semibold text-white">Filtrer</button></noscript>
            </div>
            <div class="flex items-center gap-2">
                <div class="no-scrollbar -mx-3.5 flex gap-1.5 overflow-x-auto px-3.5 sm:mx-0 sm:px-0">
                    @foreach($statusFilters as $value => $label)
                        <a href="{{ route('rh.daily-tasks.index', array_merge(request()->except('page'), ['status' => $value ?: null])) }}"
                           class="shrink-0 rounded-full px-3.5 py-1.5 text-xs font-medium transition {{ (string) request('status', '') === (string) $value ? 'bg-[#0B0F14] text-white' : 'border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#0B0F14]' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                @if($hasFilters)
                    <a href="{{ route('rh.daily-tasks.index') }}" class="ml-auto shrink-0 text-xs font-medium text-[#0066FF] hover:underline">Réinitialiser</a>
                @endif
            </div>
        </form>

        {{-- ───────── Fiches ───────── --}}
        @if($sheets->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#E2E8F0] bg-white px-6 py-14 text-center">
                <p class="text-sm font-medium text-[#0B0F14]">Aucune fiche de tâches</p>
                <p class="mt-1 text-xs text-[#64748B]">Créez une fiche pour attribuer les missions du jour à un collaborateur.</p>
                <a href="{{ route('rh.daily-tasks.create') }}" class="mt-4 inline-flex h-9 items-center rounded-xl bg-[#0066FF] px-4 text-xs font-semibold text-white hover:bg-[#0052cc]">Nouvelle fiche</a>
            </div>
        @else
            <ul class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                @foreach($sheets as $s)
                    @php
                        $tasksCount = is_array($s->tasks) ? count($s->tasks) : 0;
                        $completedCount = is_array($s->tasks) ? collect($s->tasks)->filter(fn ($t) => is_array($t) && ($t['status'] ?? '') === 'termine')->count() : 0;
                        $progress = $tasksCount > 0 ? round($completedCount / $tasksCount * 100) : 0;
                        [$statusLabel, $statusClass] = $statusStyles[$s->status] ?? [ucfirst(str_replace('_', ' ', $s->status)), 'bg-slate-100 text-slate-600'];
                    @endphp
                    <li class="group flex flex-col rounded-2xl border border-[#E2E8F0] bg-white transition hover:border-[#0066FF]/40">
                        <a href="{{ route('rh.daily-tasks.show', $s) }}" class="block p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-xl bg-[#F5F7FA] leading-none">
                                        <span class="text-sm font-semibold text-[#0B0F14]">{{ $s->date->format('d') }}</span>
                                        <span class="text-[9px] uppercase text-[#64748B]">{{ $s->date->locale('fr')->isoFormat('MMM') }}</span>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-[#0B0F14] group-hover:text-[#0066FF]">{{ $s->employee?->full_name }}</p>
                                        <p class="truncate text-[11px] text-[#64748B]">{{ $s->position ?: 'Poste non précisé' }} · <span class="font-mono">{{ $s->reference }}</span></p>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <div class="mt-4 flex items-center gap-3">
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $progress === 100 ? 'bg-emerald-500' : 'bg-[#0066FF]' }}" style="width: {{ $progress }}%"></div>
                                </div>
                                <span class="shrink-0 text-xs font-medium text-[#0B0F14]">{{ $completedCount }}/{{ $tasksCount }} <span class="font-normal text-[#64748B]">tâches</span></span>
                            </div>
                        </a>
                        <div class="mt-auto flex items-center gap-1 border-t border-[#E2E8F0] px-2 py-1.5">
                            <span class="flex flex-1 items-center gap-2 px-2 text-[11px]">
                                <span class="inline-flex items-center gap-1 {{ $s->whatsapp_sent_at ? 'text-emerald-600' : 'text-slate-400' }}" title="WhatsApp : {{ $s->whatsapp_sent_at ? 'envoyé' : 'non envoyé' }}">
                                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $whatsappIcon }}"/></svg>
                                    {{ $s->whatsapp_sent_at ? 'Envoyé' : '—' }}
                                </span>
                                <span class="inline-flex items-center gap-1 {{ $s->email_sent_at ? 'text-[#0066FF]' : 'text-slate-400' }}" title="E-mail : {{ $s->email_sent_at ? 'envoyé' : 'non envoyé' }}">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    {{ $s->email_sent_at ? 'Envoyé' : '—' }}
                                </span>
                            </span>
                            @if($s->whats_app_url)
                                <a href="{{ $s->whats_app_url }}" target="_blank" class="rounded-lg p-2 text-[#64748B] transition hover:bg-emerald-50 hover:text-emerald-600" title="Envoyer sur WhatsApp">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $whatsappIcon }}"/></svg>
                                </a>
                            @endif
                            <a href="{{ route('rh.daily-tasks.print', $s) }}" target="_blank" class="rounded-lg p-2 text-[#64748B] transition hover:bg-slate-100 hover:text-[#0B0F14]" title="Imprimer la fiche (PDF A4)">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>

            @if($sheets->hasPages())
                <div>{{ $sheets->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.app>
