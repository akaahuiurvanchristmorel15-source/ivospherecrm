@php
    $statuses = [
        'a_faire' => ['À faire', 'bg-slate-100 text-slate-700'],
        'en_cours' => ['En cours', 'bg-blue-50 text-[#0066FF]'],
        'atteint' => ['Atteint', 'bg-emerald-50 text-emerald-700'],
        'non_atteint' => ['Non atteint', 'bg-rose-50 text-rose-700'],
    ];
    $input = 'w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] placeholder:text-[#94A3B8] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10';
    $label = 'mb-1.5 block text-xs font-medium text-[#0B0F14]';
    $filterUrl = fn (array $changes): string => route('rh.goals.index', array_filter(array_merge(request()->only(['employee_id', 'status']), $changes), fn ($value) => $value !== null && $value !== ''));
@endphp

<x-layouts.app>
    <x-slot:title>Objectifs des membres — IVOSPHERE RH</x-slot>

    <div x-data="{ createOpen: false, edit: null, updateUrl: @js(route('rh.goals.update', '__ID__')) }" class="space-y-5 sm:space-y-6 [font-variant-numeric:tabular-nums]">
        <x-evaluation.header
            title="Objectifs"
            subtitle="1 à 3 objectifs ciblés par membre, fixés à l'entretien et suivis jusqu'à l'échéance."
            active="goals"
        >
            <x-slot:actions>
                <button type="button" @click="createOpen = true" class="inline-flex h-10 items-center gap-2 rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052cc]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nouvel objectif
                </button>
            </x-slot:actions>
        </x-evaluation.header>

        {{-- Indicateurs --}}
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-12" aria-label="Indicateurs">
            <x-evaluation.hero glow="bg-emerald-500/25" class="lg:col-span-5">
                <div class="flex items-center gap-5">
                    <div class="relative h-24 w-24 shrink-0">
                        <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90">
                            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="3"/>
                            @if($achievementRate > 0)
                                <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-dasharray="{{ $achievementRate }} 100"/>
                            @endif
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-xl font-semibold">{{ $achievementRate }}%</span>
                    </div>
                    <div class="min-w-0 space-y-1">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Taux de réussite</p>
                        <p class="text-3xl font-semibold tracking-tight">{{ $achievedGoals }} <span class="text-base font-normal text-slate-400">/ {{ $totalGoals }}</span></p>
                        <p class="text-xs text-emerald-300">objectifs atteints</p>
                    </div>
                </div>
            </x-evaluation.hero>
            <div class="grid grid-cols-3 gap-3 sm:gap-4 lg:col-span-7">
                <x-evaluation.stat label="Objectifs" :value="$totalGoals" icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                <x-evaluation.stat label="En cours" :value="$inProgressGoals" tint="bg-blue-50 text-[#0066FF]" icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                <x-evaluation.stat label="Atteints" :value="$achievedGoals" tint="bg-emerald-50 text-emerald-600" icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </div>
        </section>

        {{-- Filtres --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="no-scrollbar -mx-3.5 flex gap-1.5 overflow-x-auto px-3.5 sm:mx-0 sm:px-0">
                <a href="{{ $filterUrl(['status' => null]) }}" class="inline-flex h-9 shrink-0 items-center rounded-full px-3.5 text-xs font-medium transition {{ ! request('status') ? 'bg-[#0B0F14] text-white' : 'border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#0B0F14]' }}">Tous</a>
                @foreach($statuses as $statusKey => [$statusLabel])
                    <a href="{{ $filterUrl(['status' => $statusKey]) }}" class="inline-flex h-9 shrink-0 items-center rounded-full px-3.5 text-xs font-medium transition {{ request('status') === $statusKey ? 'bg-[#0B0F14] text-white' : 'border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#0B0F14]' }}">{{ $statusLabel }}</a>
                @endforeach
            </div>
            <form method="GET" action="{{ route('rh.goals.index') }}">
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <select name="employee_id" aria-label="Membre" onchange="this.form.submit()" class="h-9 w-full rounded-full border border-[#E2E8F0] bg-white px-3.5 text-xs font-medium text-[#0B0F14] focus:border-[#0066FF] focus:outline-none sm:w-60">
                    <option value="">Tous les membres</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(request('employee_id') == $emp->id)>{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        {{-- Liste --}}
        <ul class="divide-y divide-[#E2E8F0] overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white">
            @forelse($goals as $goal)
                @php
                    [$statusLabel, $statusClass] = $statuses[$goal->status] ?? [$goal->status, 'bg-slate-100 text-slate-700'];
                    $isOverdue = $goal->due_date && $goal->due_date->isPast() && $goal->status !== 'atteint';
                @endphp
                <li class="grid grid-cols-1 gap-3 p-4 sm:px-5 md:grid-cols-[minmax(0,1fr)_10rem_9rem_auto] md:items-center">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-[#0B0F14]">{{ $goal->title }}</p>
                        <p class="text-xs text-[#64748B]">{{ $goal->employee->full_name }} · {{ $goal->employee->department ?? 'Général' }}</p>
                        @if($goal->result_notes)
                            <p class="mt-1 text-xs text-emerald-700">Bilan : {{ $goal->result_notes }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-[#0066FF]" style="width: {{ $goal->progress_pct }}%"></div></div>
                        <span class="w-9 text-right text-xs font-medium text-[#0B0F14]">{{ $goal->progress_pct }} %</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs md:flex-col md:items-start md:gap-1">
                        <span class="rounded-full px-2.5 py-0.5 font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                        <span class="{{ $isOverdue ? 'font-medium text-rose-600' : 'text-[#94A3B8]' }}">{{ $isOverdue ? 'Échu le ' : 'Échéance ' }}{{ $goal->due_date?->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center gap-1 md:justify-end">
                        <button type="button" @click="edit = @js(['id' => $goal->id, 'title' => $goal->title, 'progress_pct' => $goal->progress_pct, 'status' => $goal->status, 'due_date' => $goal->due_date?->format('Y-m-d'), 'result_notes' => $goal->result_notes])"
                                class="inline-flex h-9 items-center rounded-xl border border-[#E2E8F0] px-3 text-xs font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF]">Mettre à jour</button>
                        <form action="{{ route('rh.goals.destroy', $goal) }}" method="POST" onsubmit="return confirm('Supprimer cet objectif ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl p-2 text-[#94A3B8] transition hover:bg-rose-50 hover:text-rose-600" aria-label="Supprimer">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-[#0B0F14]">Aucun objectif</p>
                    <p class="mt-1 text-xs text-[#64748B]">Fixez 1 à 3 objectifs ciblés à chaque membre lors de l'entretien.</p>
                </li>
            @endforelse
        </ul>

        @if($goals->hasPages())
            <div>{{ $goals->links() }}</div>
        @endif

        {{-- Fenêtre : nouvel objectif --}}
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4" role="dialog" aria-modal="true" @keydown.escape.window="createOpen = false">
            <div x-show="createOpen" x-transition.opacity class="absolute inset-0 bg-[#0B0F14]/40 backdrop-blur-xs" @click="createOpen = false"></div>
            <div x-show="createOpen" x-transition class="relative max-h-[90vh] w-full overflow-y-auto rounded-t-3xl bg-white p-5 shadow-xl sm:max-w-lg sm:rounded-3xl sm:p-6">
                <h3 class="mb-4 text-base font-semibold text-[#0B0F14]">Nouvel objectif</h3>
                <form action="{{ route('rh.goals.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="status" value="a_faire">
                    <input type="hidden" name="progress_pct" value="0">
                    <div>
                        <label class="{{ $label }}">Membre</label>
                        <select name="employee_id" required class="{{ $input }}">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}">Intitulé</label>
                        <input type="text" name="title" required placeholder="Ex. traiter les dossiers d'impression sous 24 h" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Critère de réussite</label>
                        <textarea name="description" rows="2" class="{{ $input }}"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}">Début</label>
                            <input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}" required class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">Échéance</label>
                            <input type="date" name="due_date" value="{{ now()->addMonth()->format('Y-m-d') }}" required class="{{ $input }}">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="createOpen = false" class="inline-flex h-10 items-center rounded-xl border border-[#E2E8F0] px-4 text-sm font-medium text-[#0B0F14]">Annuler</button>
                        <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white hover:bg-[#0052cc]">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Fenêtre : mise à jour --}}
        <div x-show="edit" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4" role="dialog" aria-modal="true" @keydown.escape.window="edit = null">
            <div x-show="edit" x-transition.opacity class="absolute inset-0 bg-[#0B0F14]/40 backdrop-blur-xs" @click="edit = null"></div>
            <template x-if="edit">
                <div x-transition class="relative max-h-[90vh] w-full overflow-y-auto rounded-t-3xl bg-white p-5 shadow-xl sm:max-w-lg sm:rounded-3xl sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-[#0B0F14]">Mettre à jour l'objectif</h3>
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
                                <label class="{{ $label }}">Échéance</label>
                                <input type="date" name="due_date" x-model="edit.due_date" required class="{{ $input }}">
                            </div>
                        </div>
                        <div>
                            <label class="{{ $label }}">Bilan</label>
                            <textarea name="result_notes" rows="2" x-model="edit.result_notes" class="{{ $input }}"></textarea>
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
