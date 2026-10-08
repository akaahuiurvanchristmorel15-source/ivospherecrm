<x-layouts.app title="Attribution des Tâches du Jour — RH">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête de page -->
        <x-page-header 
            title="Définir les Tâches du Jour" 
            description="Assignation quotidienne des objectifs, génération de la fiche PDF et notification automatique par Email & WhatsApp."
            :breadcrumbs="[
                ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                ['label' => 'Tâches du Jour', 'url' => route('rh.daily-tasks.index')],
                ['label' => 'Nouvelle Attribution']
            ]"
        >
            <x-slot:actions>
                <x-button :href="route('rh.daily-tasks.index')" variant="secondary" size="md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Historique</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <!-- Formulaire Interactif Alpine.js -->
        <form 
            action="{{ route('rh.daily-tasks.store') }}" 
            method="POST" 
            x-data="dailyTaskForm(@js($employees->keyBy('id')))"
            class="space-y-6"
        >
            @csrf

            <!-- 1. Carte Collaborateur & Fonction Automatique -->
            <x-card title="1. Collaborateur & Affectation" subtitle="Sélectionnez l'employé pour charger automatiquement sa fonction et ses coordonnées">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Sélecteur Employé -->
                    <div>
                        <label for="employee_id" class="block text-xs font-bold text-[#0B0F14] mb-1.5">
                            Employé(e) destinataire <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            id="employee_id" 
                            name="employee_id" 
                            x-model="selectedId" 
                            @change="onEmployeeSelect()" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-xl text-xs sm:text-sm text-[#0B0F14] focus:ring-2 focus:ring-[#0066FF] focus:border-[#0066FF] transition-all touch-target"
                        >
                            <option value="">-- Choisir un collaborateur --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->full_name }} {{ $emp->employee_code ? '('.$emp->employee_code.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fonction / Poste (Automatiquement renseigné) -->
                    <div>
                        <label for="position" class="block text-xs font-bold text-[#0B0F14] mb-1.5 flex items-center justify-between">
                            <span>Fonction / Poste de travail</span>
                            <span class="text-[10px] text-blue-600 font-semibold bg-blue-50 px-2 py-0.5 rounded-full">Automatique</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="position" 
                                name="position" 
                                x-model="position" 
                                placeholder="La fonction s'affiche automatiquement..." 
                                class="w-full px-3.5 py-2.5 bg-[#F5F7FA] border border-[#E2E8F0] rounded-xl text-xs sm:text-sm font-bold text-[#0066FF] placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-[#0066FF] transition-all touch-target"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Date de la mission -->
                    <div>
                        <label for="date" class="block text-xs font-bold text-[#0B0F14] mb-1.5">
                            Date d'exécution <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="date" 
                            name="date" 
                            value="{{ old('date', date('Y-m-d')) }}" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-xl text-xs sm:text-sm text-[#0B0F14] focus:ring-2 focus:ring-[#0066FF] transition-all touch-target"
                        />
                    </div>

                    <!-- Canaux d'expédition automatique -->
                    <div>
                        <span class="block text-xs font-bold text-[#0B0F14] mb-1.5">
                            Canaux de notification automatique
                        </span>
                        <div class="flex items-center gap-3 pt-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.584 1.961.949 3.013.953h.005c3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm0 13.067h-.004c-1.127 0-2.228-.316-3.187-.912l-.229-.144-1.58.414.421-1.54-.15-.238c-.655-1.042-1.001-2.247-1.001-3.483 0-3.621 2.947-6.568 6.569-6.568 3.621 0 6.567 2.947 6.567 6.568 0 3.622-2.946 6.568-6.568 6.568z"/></svg>
                                <span>WhatsApp Direct</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 text-xs font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>Email PDF</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Aperçu dynamique du contact sélectionné -->
                <div x-show="selectedEmployee" x-cloak class="mt-5 p-3.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-[#0066FF] text-white font-extrabold flex items-center justify-center shrink-0">
                            <span x-text="getInitials()"></span>
                        </div>
                        <div>
                            <span class="font-extrabold text-[#0B0F14]" x-text="selectedEmployee?.first_name + ' ' + selectedEmployee?.last_name"></span>
                            <span class="text-slate-400 block text-[11px]" x-text="'Département : ' + (selectedEmployee?.department || 'Général')"></span>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <template x-if="selectedEmployee?.phone">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-mono text-[11px] font-semibold border border-emerald-200">
                                💬 <span x-text="selectedEmployee.phone"></span>
                            </span>
                        </template>
                        <template x-if="selectedEmployee?.email">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 font-mono text-[11px] font-semibold border border-blue-200">
                                ✉️ <span x-text="selectedEmployee.email"></span>
                            </span>
                        </template>
                    </div>
                </div>
            </x-card>

            <!-- 2. Définition des Tâches avec Ajout Dynamique au Côté -->
            <x-card title="2. Tâches du Jour à Accomplir" subtitle="Renseignez les tâches. Cliquez sur le bouton '+' à côté pour ajouter d'autres tâches.">
                <div class="space-y-3">
                    <template x-for="(task, index) in tasks" :key="index">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <!-- Numéro de la tâche -->
                            <div class="w-7 h-7 rounded-lg bg-[#0066FF]/10 text-[#0066FF] font-extrabold text-xs flex items-center justify-center shrink-0">
                                <span x-text="index + 1"></span>
                            </div>

                            <!-- Champ texte de la tâche -->
                            <div class="flex-1 min-w-0">
                                <input 
                                    type="text" 
                                    name="tasks[]" 
                                    x-model="tasks[index]" 
                                    :placeholder="'Tâche #' + (index + 1) + ' : ex. Traiter les 15 commandes du pôle PRINT...'" 
                                    required 
                                    class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-xl text-xs sm:text-sm text-[#0B0F14] placeholder-slate-400 focus:ring-2 focus:ring-[#0066FF] transition-all touch-target"
                                    @keydown.enter.prevent="addTask()"
                                />
                            </div>

                            <!-- Bouton Ajouter au Côté (+) -->
                            <button 
                                type="button" 
                                @click="addTask()" 
                                class="w-10 h-10 rounded-xl bg-blue-50 hover:bg-[#0066FF] text-[#0066FF] hover:text-white border border-blue-200 transition-colors flex items-center justify-center shrink-0 touch-target font-bold"
                                title="Ajouter une autre tâche au côté"
                                aria-label="Ajouter une tâche"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </button>

                            <!-- Bouton Supprimer si plus d'une tâche -->
                            <button 
                                type="button" 
                                x-show="tasks.length > 1" 
                                @click="removeTask(index)" 
                                class="w-10 h-10 rounded-xl bg-slate-50 hover:bg-rose-50 text-slate-400 hover:text-rose-600 border border-slate-200 hover:border-rose-200 transition-colors flex items-center justify-center shrink-0 touch-target"
                                title="Supprimer cette tâche"
                                aria-label="Supprimer la tâche"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Bouton Large Complémentaire pour ajouter des tâches -->
                <div class="mt-4 pt-3 border-t border-[#E2E8F0]/70 flex items-center justify-between">
                    <button 
                        type="button" 
                        @click="addTask()" 
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#F5F7FA] hover:bg-slate-200 border border-[#E2E8F0] text-slate-700 text-xs font-bold transition-all touch-target"
                    >
                        <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Ajouter une ligne de tâche</span>
                    </button>

                    <span class="text-xs text-slate-400 font-semibold" x-text="tasks.length + ' tâche(s) définie(s)'"></span>
                </div>
            </x-card>

            <!-- 3. Consignes & Instructions Particulières (Optionnel) -->
            <x-card title="3. Consignes & Notes Particulières (Optionnel)" subtitle="Ajoutez des directives d'urgence, priorités ou points de vigilance">
                <textarea 
                    name="notes" 
                    rows="2" 
                    placeholder="Instructions particulières ou priorités pour cette journée..." 
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-xl text-xs sm:text-sm text-[#0B0F14] placeholder-slate-400 focus:ring-2 focus:ring-[#0066FF] transition-all"
                >{{ old('notes') }}</textarea>
            </x-card>

            <!-- 4. Barre de Validation & Soumission -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
                <a 
                    href="{{ route('rh.daily-tasks.index') }}" 
                    class="px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold text-center transition-colors touch-target"
                >
                    Annuler
                </a>

                <button 
                    type="submit" 
                    class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-[#0066FF] hover:bg-blue-600 text-white font-bold text-xs sm:text-sm shadow-md shadow-[#0066FF]/25 transition-all touch-target"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Enregistrer la Fiche & Notifier (PDF + WhatsApp + Email)</span>
                </button>
            </div>
        </form>

    </div>

    <!-- Script Alpine.js pour la réactivité du formulaire -->
    <script>
        function dailyTaskForm(employeesMap) {
            return {
                employees: employeesMap,
                selectedId: '{{ old('employee_id', '') }}',
                selectedEmployee: null,
                position: '{{ old('position', '') }}',
                tasks: @js(old('tasks', [''])),

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
