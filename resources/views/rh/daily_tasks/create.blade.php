@php
    $input = 'w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] placeholder-slate-400 transition focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15';
    $whatsappIcon = 'M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.584 1.961.949 3.013.953h.005c3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm0 13.067h-.004c-1.127 0-2.228-.316-3.187-.912l-.229-.144-1.58.414.421-1.54-.15-.238c-.655-1.042-1.001-2.247-1.001-3.483 0-3.621 2.947-6.568 6.569-6.568 3.621 0 6.567 2.947 6.567 6.568 0 3.622-2.946 6.568-6.568 6.568z';
@endphp

<x-layouts.app title="Nouvelle fiche de tâches — RH">
    <div class="mx-auto max-w-5xl space-y-5 sm:space-y-6">

        {{-- ───────── En-tête ───────── --}}
        <header class="space-y-4">
            <x-breadcrumb :items="[
                ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                ['label' => 'Tâches du jour', 'url' => route('rh.daily-tasks.index')],
                ['label' => 'Nouvelle fiche'],
            ]" />
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">Nouvelle fiche de tâches</h1>
                <p class="text-sm text-[#64748B]">Attribuez les missions du jour : la fiche PDF est envoyée au collaborateur par WhatsApp et e-mail.</p>
            </div>
        </header>

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800">
                <p class="font-semibold">Veuillez corriger les champs suivants :</p>
                <ul class="mt-1.5 list-inside list-disc space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('rh.daily-tasks.store') }}" method="POST"
              x-data="dailyTaskForm(@js($employees->keyBy('id')))"
              class="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_20rem] lg:items-start">
            @csrf

            <div class="min-w-0 space-y-5">
                {{-- ───────── Collaborateur ───────── --}}
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#0B0F14] text-xs font-semibold text-white">1</span>
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Collaborateur et date</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="employee_id" class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Collaborateur <span class="text-rose-500">*</span></label>
                            <select id="employee_id" name="employee_id" x-model="selectedId" @change="onEmployeeSelect()" required class="{{ $input }}">
                                <option value="">Choisir un collaborateur</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}{{ $emp->employee_code ? ' ('.$emp->employee_code.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="position" class="mb-1.5 flex items-center justify-between text-xs font-medium text-[#0B0F14]">
                                Fonction
                                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-[#0066FF]">remplie automatiquement</span>
                            </label>
                            <input type="text" id="position" name="position" x-model="position" placeholder="Fonction du collaborateur" class="{{ $input }}">
                        </div>
                        <div>
                            <label for="date" class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Date <span class="text-rose-500">*</span></label>
                            <input type="date" id="date" name="date" x-model="date" required class="{{ $input }}">
                        </div>
                    </div>
                </section>

                {{-- ───────── Tâches ───────── --}}
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#0B0F14] text-xs font-semibold text-white">2</span>
                            <h2 class="text-sm font-semibold text-[#0B0F14]">Tâches à accomplir</h2>
                        </div>
                        <span class="rounded-full bg-[#F5F7FA] px-2.5 py-1 text-[11px] font-medium text-[#64748B]" x-text="filledTasksCount() + ' / ' + tasks.length"></span>
                    </div>

                    <ol class="space-y-2">
                        <template x-for="(task, index) in tasks" :key="index">
                            <li class="group flex items-center gap-2">
                                <span class="flex h-11 w-8 shrink-0 items-center justify-center text-xs font-semibold" :class="tasks[index].trim() ? 'text-[#0066FF]' : 'text-slate-400'" x-text="index + 1"></span>
                                <input type="text" name="tasks[]" x-model="tasks[index]" required
                                       :placeholder="index === 0 ? 'Ex. Traiter les 15 commandes du pôle PRINT' : 'Tâche suivante…'"
                                       @keydown.enter.prevent="addTask()"
                                       class="{{ $input }} h-11 min-w-0 flex-1">
                                <button type="button" x-show="tasks.length > 1" @click="removeTask(index)"
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                                        :aria-label="'Supprimer la tâche ' + (index + 1)">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </li>
                        </template>
                    </ol>

                    <button type="button" @click="addTask()" class="mt-3 flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-dashed border-[#CBD5E1] text-sm font-medium text-[#64748B] transition hover:border-[#0066FF] hover:bg-blue-50/40 hover:text-[#0066FF]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Ajouter une tâche
                    </button>
                    <p class="mt-2 text-center text-[11px] text-slate-400">Astuce : appuyez sur Entrée pour ajouter la tâche suivante.</p>
                </section>

                {{-- ───────── Consignes ───────── --}}
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#F5F7FA] text-xs font-semibold text-[#64748B]">3</span>
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Consignes <span class="font-normal text-[#64748B]">(facultatif)</span></h2>
                    </div>
                    <label for="notes" class="sr-only">Consignes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Priorités, urgences ou points de vigilance pour la journée…" class="{{ $input }}">{{ old('notes') }}</textarea>
                </section>
            </div>

            {{-- ───────── Récapitulatif ───────── --}}
            <aside class="space-y-4 lg:sticky lg:top-6">
                <div class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white">
                    <div class="bg-[#0B0F14] p-5 text-white">
                        <p class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Récapitulatif</p>
                        <template x-if="selectedEmployee">
                            <div class="mt-3 flex items-center gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#0066FF] text-sm font-semibold" x-text="getInitials()"></span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold" x-text="selectedEmployee.first_name + ' ' + selectedEmployee.last_name"></p>
                                    <p class="truncate text-xs text-slate-400" x-text="position || selectedEmployee.department || 'Collaborateur'"></p>
                                </div>
                            </div>
                        </template>
                        <p x-show="!selectedEmployee" class="mt-3 text-sm text-slate-400">Aucun collaborateur sélectionné</p>
                    </div>
                    <dl class="divide-y divide-[#E2E8F0] px-5 text-xs">
                        <div class="flex items-center justify-between py-3">
                            <dt class="text-[#64748B]">Date</dt>
                            <dd class="font-medium capitalize text-[#0B0F14]" x-text="formattedDate()"></dd>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <dt class="text-[#64748B]">Tâches</dt>
                            <dd class="font-medium text-[#0B0F14]" x-text="filledTasksCount()"></dd>
                        </div>
                        <div class="space-y-2 py-3">
                            <dt class="text-[#64748B]">Envoi automatique</dt>
                            <dd class="flex flex-col gap-1.5">
                                <span class="flex items-center gap-2" :class="selectedEmployee?.phone ? 'text-emerald-700' : 'text-slate-400'">
                                    <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $whatsappIcon }}"/></svg>
                                    <span class="truncate" x-text="selectedEmployee?.phone || 'WhatsApp — numéro manquant'"></span>
                                </span>
                                <span class="flex items-center gap-2" :class="selectedEmployee?.email ? 'text-[#0066FF]' : 'text-slate-400'">
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span class="truncate" x-text="selectedEmployee?.email || 'E-mail — adresse manquante'"></span>
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="flex flex-col gap-2">
                    <button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-[#0066FF] px-5 text-sm font-semibold text-white shadow-[0_8px_20px_-8px_rgba(0,102,255,0.6)] transition hover:bg-[#0052cc]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Enregistrer et envoyer
                    </button>
                    <a href="{{ route('rh.daily-tasks.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl text-sm font-medium text-[#64748B] transition hover:bg-slate-100 hover:text-[#0B0F14]">Annuler</a>
                </div>
            </aside>
        </form>
    </div>

    <script>
        function dailyTaskForm(employeesMap) {
            return {
                employees: employeesMap,
                selectedId: @js((string) old('employee_id', '')),
                selectedEmployee: null,
                position: @js(old('position', '')),
                date: @js(old('date', date('Y-m-d'))),
                tasks: @js(array_values(old('tasks', ['']))),

                init() {
                    if (this.selectedId && this.employees[this.selectedId]) {
                        this.selectedEmployee = this.employees[this.selectedId];
                        if (!this.position) {
                            this.position = this.selectedEmployee.position || 'Collaborateur';
                        }
                    }
                    if (this.tasks.length === 0) {
                        this.tasks = [''];
                    }
                },

                onEmployeeSelect() {
                    if (this.selectedId && this.employees[this.selectedId]) {
                        this.selectedEmployee = this.employees[this.selectedId];
                        this.position = this.selectedEmployee.position || 'Collaborateur';
                    } else {
                        this.selectedEmployee = null;
                        this.position = '';
                    }
                },

                getInitials() {
                    if (!this.selectedEmployee) return 'IV';
                    const fn = this.selectedEmployee.first_name || '';
                    const ln = this.selectedEmployee.last_name || '';
                    return ((fn[0] || '') + (ln[0] || '')).toUpperCase() || 'EM';
                },

                filledTasksCount() {
                    return this.tasks.filter(t => String(t).trim() !== '').length;
                },

                formattedDate() {
                    if (!this.date) return '—';
                    const d = new Date(this.date + 'T00:00:00');
                    return isNaN(d) ? this.date : d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
                },

                addTask() {
                    this.tasks.push('');
                    this.$nextTick(() => {
                        const inputs = document.querySelectorAll('input[name="tasks[]"]');
                        if (inputs.length > 0) {
                            inputs[inputs.length - 1].focus();
                        }
                    });
                },

                removeTask(index) {
                    if (this.tasks.length > 1) {
                        this.tasks.splice(index, 1);
                    }
                }
            };
        }
    </script>
</x-layouts.app>
