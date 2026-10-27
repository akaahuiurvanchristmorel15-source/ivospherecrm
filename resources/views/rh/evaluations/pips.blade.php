@php
    $statuses = [
        'en_cours' => ['En cours', 'bg-amber-50 text-amber-700'],
        'satisfaisant' => ['Réussi', 'bg-emerald-50 text-emerald-700'],
        'non_satisfaisant' => ['Non réussi', 'bg-rose-50 text-rose-700'],
        'annule' => ['Annulé', 'bg-slate-100 text-slate-600'],
    ];
    $input = 'w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] placeholder:text-[#94A3B8] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10';
    $label = 'mb-1.5 block text-xs font-medium text-[#0B0F14]';
    $filterUrl = fn (array $changes): string => route('rh.pips.index', array_filter(array_merge(request()->only(['employee_id', 'status']), $changes), fn ($value) => $value !== null && $value !== ''));
@endphp

<x-layouts.app>
    <x-slot:title>Plans de progrès — IVOSPHERE RH</x-slot>

    <div x-data="{ createOpen: false, edit: null, updateUrl: @js(route('rh.pips.update', '__ID__')) }" class="mx-auto max-w-6xl space-y-6 [font-variant-numeric:tabular-nums]">
        <x-evaluation.header
            title="Plans de progrès"
            subtitle="Sous 10 / 20 : plan de 30 jours, avec un référent et une formation."
            :breadcrumbs="[
                ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                ['label' => 'Évaluations', 'url' => route('rh.evaluations.index')],
                ['label' => 'Plans de progrès'],
            ]"
            active="pips"
        >
            <x-slot:actions>
                <button type="button" @click="createOpen = true" class="inline-flex h-10 items-center gap-2 rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052cc]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Ouvrir un plan
                </button>
            </x-slot:actions>
        </x-evaluation.header>

        <dl class="grid grid-cols-3 gap-3">
            @foreach([['En cours', $activeCount], ['Réussis', $satisfactoryCount], ['Total', $totalCount]] as [$statLabel, $statValue])
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                    <dt class="text-xs text-[#64748B]">{{ $statLabel }}</dt>
                    <dd class="mt-1 text-2xl font-semibold text-[#0B0F14]">{{ $statValue }}</dd>
                </div>
            @endforeach
        </dl>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="no-scrollbar -mx-3.5 flex gap-1.5 overflow-x-auto px-3.5 sm:mx-0 sm:px-0">
                <a href="{{ $filterUrl(['status' => null]) }}" class="inline-flex h-9 shrink-0 items-center rounded-full px-3.5 text-xs font-medium transition {{ ! request('status') ? 'bg-[#0B0F14] text-white' : 'border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#0B0F14]' }}">Tous</a>
                @foreach($statuses as $statusKey => [$statusLabel])
                    <a href="{{ $filterUrl(['status' => $statusKey]) }}" class="inline-flex h-9 shrink-0 items-center rounded-full px-3.5 text-xs font-medium transition {{ request('status') === $statusKey ? 'bg-[#0B0F14] text-white' : 'border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#0B0F14]' }}">{{ $statusLabel }}</a>
                @endforeach
            </div>
            <form method="GET" action="{{ route('rh.pips.index') }}">
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <select name="employee_id" aria-label="Membre" onchange="this.form.submit()" class="h-9 w-full rounded-full border border-[#E2E8F0] bg-white px-3.5 text-xs font-medium text-[#0B0F14] focus:border-[#0066FF] focus:outline-none sm:w-60">
                    <option value="">Tous les membres</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(request('employee_id') == $emp->id)>{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @forelse($pips as $pip)
                @php
                    [$statusLabel, $statusClass] = $statuses[$pip->status] ?? [$pip->status, 'bg-slate-100 text-slate-600'];
                    $daysLeft = $pip->end_date ? (int) now()->startOfDay()->diffInDays($pip->end_date, false) : null;
                @endphp
                <article x-data="{ open: false }" class="flex flex-col rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-[#0B0F14]">{{ $pip->employee->full_name }}</p>
                            <p class="text-xs text-[#64748B]">{{ $pip->title }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[11px] font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-[#EEF2F6]"><div class="h-full rounded-full bg-[#0066FF]" style="width: {{ $pip->progress_pct }}%"></div></div>
                        <span class="text-xs font-medium text-[#0B0F14]">{{ $pip->progress_pct }} %</span>
                    </div>
                    <p class="mt-1.5 text-[11px] text-[#94A3B8]">
                        {{ $pip->start_date?->format('d/m/Y') }} → {{ $pip->end_date?->format('d/m/Y') }}
                        @if($pip->status === 'en_cours' && $daysLeft !== null)
                            · <span class="{{ $daysLeft < 0 ? 'text-rose-600' : '' }}">{{ $daysLeft < 0 ? 'échu' : $daysLeft.' j restants' }}</span>
                        @endif
                        · {{ $pip->supervisor?->name ?? 'Référent RH' }}
                    </p>

                    <div x-show="open" x-collapse x-cloak class="mt-4 space-y-3 border-t border-[#EEF2F6] pt-4 text-xs text-[#334155]">
                        <div><p class="font-medium text-[#94A3B8]">Difficulté</p><p class="mt-0.5">{{ $pip->problem_identified }}</p></div>
                        <div><p class="font-medium text-[#94A3B8]">Objectif</p><p class="mt-0.5">{{ $pip->target_objective }}</p></div>
                        <div><p class="font-medium text-[#94A3B8]">Actions</p><p class="mt-0.5">{{ $pip->corrective_actions }}</p></div>
                        @if($pip->final_assessment)
                            <div class="rounded-xl bg-[#F8FAFC] p-3"><p class="font-medium text-[#0B0F14]">Bilan</p><p class="mt-0.5">{{ $pip->final_assessment }}</p></div>
                        @endif
                    </div>

                    <div class="mt-auto flex items-center justify-between gap-2 pt-4">
                        <button type="button" @click="open = !open" class="text-xs font-medium text-[#64748B] hover:text-[#0066FF]" x-text="open ? 'Masquer le détail' : 'Voir le détail'"></button>
                        <button type="button" @click="edit = @js(['id' => $pip->id, 'title' => $pip->title, 'target_objective' => $pip->target_objective, 'corrective_actions' => $pip->corrective_actions, 'end_date' => $pip->end_date?->format('Y-m-d'), 'progress_pct' => $pip->progress_pct, 'status' => $pip->status, 'final_assessment' => $pip->final_assessment])"
                                class="inline-flex h-9 items-center rounded-xl border border-[#E2E8F0] px-3 text-xs font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF]">Suivre / clôturer</button>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-[#E2E8F0] bg-white px-6 py-12 text-center lg:col-span-2">
                    <p class="text-sm font-medium text-[#0B0F14]">Aucun plan de progrès</p>
                    <p class="mt-1 text-xs text-[#64748B]">Un plan s'ouvre pour tout membre noté sous 10 / 20.</p>
                </div>
            @endforelse
        </div>

        @if($pips->hasPages())
            <div>{{ $pips->links() }}</div>
        @endif

        {{-- Fenêtre : nouveau plan --}}
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4" role="dialog" aria-modal="true" @keydown.escape.window="createOpen = false">
            <div x-show="createOpen" x-transition.opacity class="absolute inset-0 bg-[#0B0F14]/40 backdrop-blur-xs" @click="createOpen = false"></div>
            <div x-show="createOpen" x-transition class="relative max-h-[90vh] w-full overflow-y-auto rounded-t-3xl bg-white p-5 shadow-xl sm:max-w-lg sm:rounded-3xl sm:p-6">
                <h3 class="mb-4 text-base font-semibold text-[#0B0F14]">Ouvrir un plan de progrès</h3>
                <form action="{{ route('rh.pips.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="{{ $label }}">Membre</label>
                            <select name="employee_id" required class="{{ $input }}">
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $label }}">Référent</label>
                            <select name="supervisor_id" class="{{ $input }}">
                                <option value="">—</option>
                                @foreach($supervisors as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="{{ $label }}">Intitulé</label>
                        <input type="text" name="title" required placeholder="Ex. retrouver une ponctualité sans retard" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Difficulté constatée</label>
                        <textarea name="problem_identified" rows="2" required placeholder="Faits datés" class="{{ $input }}"></textarea>
                    </div>
                    <div>
                        <label class="{{ $label }}">Objectif mesurable</label>
                        <textarea name="target_objective" rows="2" required class="{{ $input }}"></textarea>
                    </div>
                    <div>
                        <label class="{{ $label }}">Actions et formation</label>
                        <textarea name="corrective_actions" rows="2" required class="{{ $input }}"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}">Début</label>
                            <input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}" required class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">Fin (30 jours)</label>
                            <input type="date" name="end_date" value="{{ now()->addDays(30)->format('Y-m-d') }}" required class="{{ $input }}">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="createOpen = false" class="inline-flex h-10 items-center rounded-xl border border-[#E2E8F0] px-4 text-sm font-medium text-[#0B0F14]">Annuler</button>
                        <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white hover:bg-[#0052cc]">Ouvrir le plan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Fenêtre : suivi / clôture --}}
        <div x-show="edit" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4" role="dialog" aria-modal="true" @keydown.escape.window="edit = null">
            <div x-show="edit" x-transition.opacity class="absolute inset-0 bg-[#0B0F14]/40 backdrop-blur-xs" @click="edit = null"></div>
            <template x-if="edit">
                <div class="relative max-h-[90vh] w-full overflow-y-auto rounded-t-3xl bg-white p-5 shadow-xl sm:max-w-lg sm:rounded-3xl sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-[#0B0F14]">Suivre le plan</h3>
                    <form :action="updateUrl.replace('__ID__', edit.id)" method="POST" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="{{ $label }}">Intitulé</label>
                            <input type="text" name="title" x-model="edit.title" required class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">Avancement : <span x-text="edit.progress_pct + ' %'"></span></label>
                            <input type="range" name="progress_pct" min="0" max="100" step="5" x-model="edit.progress_pct" class="w-full accent-[#0066FF]">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="{{ $label }}">Statut</label>
                                <select name="status" x-model="edit.status" required class="{{ $input }}">
                                    @foreach($statuses as $statusKey => [$statusLabel])
                                        <option value="{{ $statusKey }}">{{ $statusLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="{{ $label }}">Fin</label>
                                <input type="date" name="end_date" x-model="edit.end_date" required class="{{ $input }}">
                            </div>
                        </div>
                        <div>
                            <label class="{{ $label }}">Objectif</label>
                            <textarea name="target_objective" rows="2" x-model="edit.target_objective" required class="{{ $input }}"></textarea>
                        </div>
                        <div>
                            <label class="{{ $label }}">Actions</label>
                            <textarea name="corrective_actions" rows="2" x-model="edit.corrective_actions" required class="{{ $input }}"></textarea>
                        </div>
                        <div>
                            <label class="{{ $label }}">Bilan</label>
                            <textarea name="final_assessment" rows="2" x-model="edit.final_assessment" class="{{ $input }}"></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="edit = null" class="inline-flex h-10 items-center rounded-xl border border-[#E2E8F0] px-4 text-sm font-medium text-[#0B0F14]">Annuler</button>
                            <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white hover:bg-[#0052cc]">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </div>
</x-layouts.app>
