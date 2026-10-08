<x-layouts.app>
    <x-slot:title>Paramètres RH — IVOSPHERE ERP</x-slot>

    <x-page-header 
        title="Paramètres Ressources Humaines" 
        description="Configuration des règles de pointage, géofencing, plannings et barèmes d'évaluation"
        :breadcrumbs="[['label' => 'RH', 'url' => route('rh.index')], ['label' => 'Paramètres']]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <x-button :href="route('rh.attendance.poster')" variant="secondary" size="md">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Affiche QR Mensuelle (A4)</span>
                </x-button>
                <x-button :href="route('rh.index')" variant="secondary" size="md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour RH</span>
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('rh.settings.update') }}" class="space-y-6 max-w-5xl" x-data="{
        punctuality: {{ (float) old('weight_punctuality', $settings->weight_punctuality) }},
        tasks: {{ (float) old('weight_tasks', $settings->weight_tasks) }},
        teamwork: {{ (float) old('weight_teamwork', $settings->weight_teamwork) }},
        geoLoading: false,
        geoMessage: '',
        getCurrentPosition() {
            if (!navigator.geolocation) {
                alert('La géolocalisation n\'est pas supportée par votre navigateur.');
                return;
            }
            this.geoLoading = true;
            this.geoMessage = 'Capture des coordonnées GPS en cours...';
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    document.getElementById('office_latitude').value = pos.coords.latitude.toFixed(7);
                    document.getElementById('office_longitude').value = pos.coords.longitude.toFixed(7);
                    this.geoLoading = false;
                    this.geoMessage = 'Coordonnées capturées avec succès (Précision : ±' + Math.round(pos.coords.accuracy) + 'm)';
                },
                (err) => {
                    this.geoLoading = false;
                    this.geoMessage = 'Erreur : ' + err.message;
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }
    }">
        @csrf
        @method('PUT')

        <!-- 1. Horaires & Clôture Automatique -->
        <x-card title="1. Horaires de Travail & Pointage" subtitle="Heures de référence pour l'entreprise et départ automatique">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-[#0B0F14] mb-1.5" for="work_start_time">
                        Heure début standard
                    </label>
                    <input 
                        type="time" 
                        id="work_start_time" 
                        name="work_start_time" 
                        value="{{ old('work_start_time', $settings->work_start_time) }}"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] outline-none"
                        required
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Heure prévue d'arrivée des collaborateurs</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#0B0F14] mb-1.5" for="work_end_time">
                        Heure fin standard
                    </label>
                    <input 
                        type="time" 
                        id="work_end_time" 
                        name="work_end_time" 
                        value="{{ old('work_end_time', $settings->work_end_time) }}"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] outline-none"
                        required
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Heure habituelle de fin de journée</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#0B0F14] mb-1.5" for="auto_checkout_time">
                        Heure de départ automatique
                    </label>
                    <input 
                        type="time" 
                        id="auto_checkout_time" 
                        name="auto_checkout_time" 
                        value="{{ old('auto_checkout_time', $settings->auto_checkout_time) }}"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] outline-none"
                        required
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Clôture automatique si non pointé manuellement</p>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 flex items-start gap-3">
                <input 
                    type="checkbox" 
                    id="auto_checkout_enabled" 
                    name="auto_checkout_enabled" 
                    value="1" 
                    {{ old('auto_checkout_enabled', $settings->auto_checkout_enabled) ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF] mt-0.5"
                >
                <label for="auto_checkout_enabled" class="cursor-pointer">
                    <span class="text-xs font-bold text-[#0B0F14] block">Activer le départ automatique à l'heure configurée</span>
                    <span class="text-[11px] text-slate-500">
                        Si un employé n'a pas scanné sa sortie avant cette heure, le système enregistre automatiquement son départ en marquant <code class="text-amber-700 font-mono bg-amber-50 px-1 py-0.5 rounded">check_out_type = automatic</code>.
                    </span>
                </label>
            </div>
        </x-card>

        <!-- 2. Restriction Géographique & Géofencing -->
        <x-card title="2. Restriction Géographique (Géofencing)" subtitle="Empêche les collaborateurs de pointer en dehors des locaux d'IVOSPHERE">
            <div class="flex items-start gap-3 pb-4 mb-4 border-b border-slate-100">
                <input 
                    type="checkbox" 
                    id="geofence_enabled" 
                    name="geofence_enabled" 
                    value="1" 
                    {{ old('geofence_enabled', $settings->geofence_enabled) ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF] mt-0.5"
                >
                <label for="geofence_enabled" class="cursor-pointer">
                    <span class="text-xs font-bold text-[#0B0F14] block">Activer la restriction géographique</span>
                    <span class="text-[11px] text-slate-500">
                        Vérifie la position GPS du smartphone lors du scan. Si l'employé est en dehors du rayon, le pointage est bloqué.
                    </span>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-[#0B0F14] mb-1.5" for="office_latitude">
                        Latitude du siège
                    </label>
                    <input 
                        type="number" 
                        step="0.0000001" 
                        id="office_latitude" 
                        name="office_latitude" 
                        value="{{ old('office_latitude', $settings->office_latitude) }}"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-mono text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] outline-none"
                        required
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Exemple : 5.3599520 (Abidjan)</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#0B0F14] mb-1.5" for="office_longitude">
                        Longitude du siège
                    </label>
                    <input 
                        type="number" 
                        step="0.0000001" 
                        id="office_longitude" 
                        name="office_longitude" 
                        value="{{ old('office_longitude', $settings->office_longitude) }}"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-mono text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] outline-none"
                        required
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Exemple : -4.0082560</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#0B0F14] mb-1.5" for="geofence_radius_meters">
                        Rayon autorisé (mètres)
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            id="geofence_radius_meters" 
                            name="geofence_radius_meters" 
                            value="{{ old('geofence_radius_meters', $settings->geofence_radius_meters) }}"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 pr-12 text-sm font-bold text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] outline-none"
                            min="10" 
                            max="5000"
                            required
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs text-slate-400 font-semibold pointer-events-none">mètres</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Recommandé : 100 mètres</p>
                </div>
            </div>

            <!-- Aide & Détection GPS en 1 clic -->
            <div class="mt-4 flex flex-wrap items-center gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200/60">
                <button 
                    type="button" 
                    @click="getCurrentPosition()" 
                    :disabled="geoLoading"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:border-[#0066FF] hover:text-[#0066FF] shadow-2xs transition disabled:opacity-50"
                >
                    <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span x-text="geoLoading ? 'Localisation...' : 'Détecter les coordonnées actuelles du bureau'"></span>
                </button>
                <span x-show="geoMessage" x-text="geoMessage" class="text-xs text-slate-500 font-medium"></span>
            </div>

            <!-- Affiche Murale Mensuelle A4 -->
            <div class="mt-4 p-4 rounded-xl bg-emerald-50/60 border border-emerald-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-emerald-950">Affiche Murale Mensuelle de Pointage (Format A4)</h4>
                        <p class="text-[11px] text-emerald-800/80 mt-0.5">
                            Le QR code officiel à imprimer pour les locaux se renouvelle automatiquement chaque 1er du mois. Une tolérance de transition de 2 jours est accordée pour le remplacement physique de l'affiche.
                        </p>
                    </div>
                </div>
                <x-button :href="route('rh.attendance.poster')" variant="secondary" size="sm" class="shrink-0 bg-white hover:bg-emerald-50 text-emerald-800 border-emerald-300">
                    <span>Ouvrir l'Affiche A4</span>
                    <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </x-button>
            </div>
        </x-card>

        <!-- 3. Règles de Planning -->
        <x-card title="3. Plannings & Jours de Travail" subtitle="Garde-fous légaux et organisation du travail">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-[#0B0F14] mb-1.5" for="max_work_days_per_week">
                        Nombre maximum de jours / semaine
                    </label>
                    <input 
                        type="number" 
                        id="max_work_days_per_week" 
                        name="max_work_days_per_week" 
                        value="{{ old('max_work_days_per_week', $settings->max_work_days_per_week) }}"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] outline-none"
                        min="1" 
                        max="6"
                        required
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Maximum légal obligatoire (1 à 6 jours). Le 7e jour est impérativement un jour de repos.</p>
                </div>

                <div class="flex items-center pt-4 sm:pt-6">
                    <div class="flex items-start gap-3">
                        <input 
                            type="checkbox" 
                            id="allow_custom_schedules" 
                            name="allow_custom_schedules" 
                            value="1" 
                            {{ old('allow_custom_schedules', $settings->allow_custom_schedules) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF] mt-0.5"
                        >
                        <label for="allow_custom_schedules" class="cursor-pointer">
                            <span class="text-xs font-bold text-[#0B0F14] block">Autoriser les horaires personnalisés par employé</span>
                            <span class="text-[11px] text-slate-500">Permet au responsable d'ajuster les heures de début/fin pour des postes spécifiques (ex: gardiennage, astreinte).</span>
                        </label>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- 4. Barème d'Évaluation Mensuelle & Formule Normalisée /30 -->
        <x-card title="4. Moteur d'Évaluation Mensuelle" subtitle="Coefficients journaliers et normalisation équitable sur 30 points">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div>
                    <label class="block text-xs font-bold text-[#0B0F14] mb-1.5" for="evaluation_day_of_month">
                        Jour d'évaluation dans le mois
                    </label>
                    <input 
                        type="number" 
                        id="evaluation_day_of_month" 
                        name="evaluation_day_of_month" 
                        value="{{ old('evaluation_day_of_month', $settings->evaluation_day_of_month) }}"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] outline-none"
                        min="1" 
                        max="28"
                        required
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Exemple : Le 5 de chaque mois</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#0B0F14] mb-1.5" for="max_evaluation_score">
                        Note finale maximale
                    </label>
                    <input 
                        type="number" 
                        step="0.5" 
                        id="max_evaluation_score" 
                        name="max_evaluation_score" 
                        value="{{ old('max_evaluation_score', $settings->max_evaluation_score) }}"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold text-[#0066FF] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] outline-none"
                        required
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Note de référence (30/30)</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#0B0F14] mb-1.5" for="punctuality_grace_minutes">
                        Tolérance ponctualité (minutes)
                    </label>
                    <input 
                        type="number" 
                        id="punctuality_grace_minutes" 
                        name="punctuality_grace_minutes" 
                        value="{{ old('punctuality_grace_minutes', $settings->punctuality_grace_minutes) }}"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] outline-none"
                        min="0" 
                        max="60"
                        required
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Arrivée considérée à l'heure (ex: 5 min)</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#0B0F14] mb-1.5" for="late_threshold_minutes">
                        Seuil retard important (minutes)
                    </label>
                    <input 
                        type="number" 
                        id="late_threshold_minutes" 
                        name="late_threshold_minutes" 
                        value="{{ old('late_threshold_minutes', $settings->late_threshold_minutes) }}"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] outline-none"
                        min="1" 
                        max="120"
                        required
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Note = 0 au-delà (ex: >15 min)</p>
                </div>
            </div>

            <!-- Les 3 Piliers de notation journalière -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <h4 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider mb-3">Coefficients des 3 Piliers Journaliers</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div class="bg-blue-50/60 p-3.5 rounded-xl border border-blue-100">
                        <label class="block text-xs font-bold text-blue-900 mb-1" for="weight_punctuality">
                            1. Ponctualité (pt/jour)
                        </label>
                        <input 
                            type="number" 
                            step="0.01" 
                            id="weight_punctuality" 
                            name="weight_punctuality" 
                            x-model.number="punctuality"
                            class="w-full rounded-lg border border-blue-200 bg-white px-3 py-1.5 text-sm font-bold text-[#0B0F14] focus:border-[#0066FF] outline-none"
                            required
                        >
                        <p class="text-[10px] text-blue-700/80 mt-1">Arrivée à l'heure ou dans la tolérance</p>
                    </div>

                    <div class="bg-emerald-50/60 p-3.5 rounded-xl border border-emerald-100">
                        <label class="block text-xs font-bold text-emerald-900 mb-1" for="weight_tasks">
                            2. Tâches Validées (pt/jour)
                        </label>
                        <input 
                            type="number" 
                            step="0.01" 
                            id="weight_tasks" 
                            name="weight_tasks" 
                            x-model.number="tasks"
                            class="w-full rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-sm font-bold text-[#0B0F14] focus:border-[#0066FF] outline-none"
                            required
                        >
                        <p class="text-[10px] text-emerald-700/80 mt-1">Pondéré par le taux de tâches validées</p>
                    </div>

                    <div class="bg-amber-50/60 p-3.5 rounded-xl border border-amber-100">
                        <label class="block text-xs font-bold text-amber-900 mb-1" for="weight_teamwork">
                            3. Esprit d'Équipe (pt/jour)
                        </label>
                        <input 
                            type="number" 
                            step="0.01" 
                            id="weight_teamwork" 
                            name="weight_teamwork" 
                            x-model.number="teamwork"
                            class="w-full rounded-lg border border-amber-200 bg-white px-3 py-1.5 text-sm font-bold text-[#0B0F14] focus:border-[#0066FF] outline-none"
                            required
                        >
                        <p class="text-[10px] text-amber-700/80 mt-1">Attribution managériale avec commentaire</p>
                    </div>
                </div>

                <!-- Récapitulatif dynamique de la formule -->
                <div class="mt-4 p-4 rounded-xl bg-slate-900 text-white flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Plafond Théorique Journalier</span>
                        <div class="text-sm font-bold flex items-center gap-1.5 mt-0.5">
                            <span x-text="punctuality.toFixed(2)"></span> (Ponctualité) +
                            <span x-text="tasks.toFixed(2)"></span> (Tâches) +
                            <span x-text="teamwork.toFixed(2)"></span> (Équipe) =
                            <span class="text-[#0066FF] text-base" x-text="(punctuality + tasks + teamwork).toFixed(2) + ' pt/jour'"></span>
                        </div>
                    </div>
                    <div class="text-right sm:border-l sm:border-slate-800 sm:pl-4">
                        <span class="text-[10px] uppercase font-bold text-emerald-400 tracking-wider block">Formule de Normalisation</span>
                        <p class="text-xs font-mono text-slate-300">Note / 30 = (Score obtenu / (Jours × Max/jour)) × 30</p>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Actions de soumission -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <x-button type="submit" variant="primary" size="lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Enregistrer les paramètres RH</span>
            </x-button>
        </div>
    </form>
</x-layouts.app>
