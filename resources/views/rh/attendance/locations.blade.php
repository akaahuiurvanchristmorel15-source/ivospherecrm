@php
    $input = 'w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15';
    $activeCount = $locations->where('is_active', true)->count();
    $emptyForm = ['id' => null, 'name' => '', 'latitude' => '', 'longitude' => '', 'radius_meters' => '', 'is_active' => true];
    $oldForm = $errors->any() ? [
        'id' => old('location_id') ? (int) old('location_id') : null,
        'name' => old('name', ''),
        'latitude' => old('latitude', ''),
        'longitude' => old('longitude', ''),
        'radius_meters' => old('radius_meters', ''),
        'is_active' => (bool) old('is_active', false),
    ] : null;
@endphp

<x-layouts.app>
    <x-slot:title>Lieux de pointage — IVOSPHERE RH</x-slot>

    <div class="space-y-5 sm:space-y-6 [font-variant-numeric:tabular-nums]"
         x-data="{
            showModal: @js($oldForm !== null),
            form: @js($oldForm ?? $emptyForm),
            storeUrl: @js(route('rh.attendance.locations.store')),
            updateUrl: @js(route('rh.attendance.locations.update', ['location' => '__ID__'])),
            locating: false,
            locateMessage: '',
            locateError: false,

            open(location = null) {
                this.form = location ? { ...location } : { ...@js($emptyForm) };
                this.locateMessage = '';
                this.showModal = true;
            },

            useCurrentPosition() {
                this.locateError = false;
                if (!window.isSecureContext || !navigator.geolocation) {
                    this.locateError = true;
                    this.locateMessage = 'La localisation nécessite une connexion sécurisée (HTTPS) et un navigateur compatible.';
                    return;
                }
                this.locating = true;
                this.locateMessage = 'Localisation en cours…';
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.locating = false;
                        this.form.latitude = pos.coords.latitude.toFixed(7);
                        this.form.longitude = pos.coords.longitude.toFixed(7);
                        this.locateMessage = 'Position récupérée (précision ± ' + Math.round(pos.coords.accuracy) + ' m).';
                    },
                    (err) => {
                        this.locating = false;
                        this.locateError = true;
                        this.locateMessage = err.code === err.PERMISSION_DENIED
                            ? 'Localisation refusée : autorisez l\'accès à votre position dans le navigateur.'
                            : 'Position introuvable. Activez le GPS puis réessayez.';
                    },
                    { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
                );
            }
         }">

        {{-- ───────── En-tête ───────── --}}
        <header class="space-y-4">
            <x-breadcrumb :items="[['label' => 'Ressources Humaines', 'url' => route('rh.index')], ['label' => 'Présences', 'url' => route('rh.attendance.index')], ['label' => 'Lieux de pointage']]" />
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">Lieux de pointage</h1>
                    <p class="text-sm text-[#64748B]">Lieux associés au QR code de pointage (affiche du mois, borne et badge). L'arrivée et le départ ne sont acceptés que dans le rayon de l'un d'eux.</p>
                </div>
                <button type="button" @click="open()" class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052cc]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Ajouter un lieu
                </button>
            </div>
        </header>

        @if($activeCount === 0)
            <x-alert type="warning">
                Aucun lieu actif : la restriction géographique est désactivée et le pointage est accepté depuis n'importe où. Ajoutez au moins un lieu pour l'activer.
            </x-alert>
        @else
            <div class="flex items-start gap-2.5 rounded-2xl border border-blue-100 bg-blue-50/70 p-3.5 text-xs text-blue-900">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-[#0066FF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p>Restriction active sur {{ $activeCount }} lieu(x). Rayon par défaut : {{ $defaultRadius }} m. Une position GPS moins précise que ± {{ $maxAccuracy }} m est refusée.</p>
            </div>
        @endif

        {{-- ───────── Liste des lieux ───────── --}}
        @if($locations->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#CBD5E1] bg-white p-8 text-center">
                <p class="text-sm font-medium text-[#0B0F14]">Aucun lieu de pointage</p>
                <p class="mt-1 text-xs text-[#64748B]">Rendez-vous sur place et utilisez « Ma position actuelle » pour enregistrer les coordonnées exactes.</p>
            </div>
        @else
            <ul class="grid grid-cols-1 gap-3 md:grid-cols-2">
                @foreach($locations as $location)
                    <li class="flex flex-col gap-3 rounded-2xl border border-[#E2E8F0] bg-white p-4 {{ $location->is_active ? '' : 'opacity-70' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-[#0B0F14]">{{ $location->name }}</p>
                                <p class="mt-0.5 font-mono text-xs text-[#64748B]">{{ $location->latitude }}, {{ $location->longitude }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium {{ $location->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $location->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                        <p class="text-xs text-[#64748B]">
                            Rayon : <span class="font-medium text-[#0B0F14]">{{ $location->effectiveRadius() }} m</span>
                            @if(! $location->radius_meters)
                                <span>(par défaut)</span>
                            @endif
                        </p>
                        <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
                            <a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}" target="_blank" rel="noopener" class="inline-flex h-9 items-center rounded-xl border border-[#E2E8F0] px-3 text-xs font-medium text-[#0B0F14] hover:border-[#0066FF] hover:text-[#0066FF]">Voir sur la carte</a>
                            <button type="button" @click="open(@js(['id' => $location->id, 'name' => $location->name, 'latitude' => (string) $location->latitude, 'longitude' => (string) $location->longitude, 'radius_meters' => $location->radius_meters ?? '', 'is_active' => $location->is_active]))" class="inline-flex h-9 items-center rounded-xl border border-[#E2E8F0] px-3 text-xs font-medium text-[#0B0F14] hover:border-[#0066FF] hover:text-[#0066FF]">Modifier</button>
                            <form action="{{ route('rh.attendance.locations.destroy', $location) }}" method="POST" class="ml-auto" onsubmit="return confirm(@js('Supprimer le lieu « '.$location->name.' » ?'));">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex h-9 items-center rounded-xl px-3 text-xs font-medium text-rose-600 hover:bg-rose-50">Supprimer</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- ───────── Fenêtre d'ajout / modification ───────── --}}
        <div x-show="showModal" x-cloak @keydown.escape.window="showModal = false"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 flex items-end justify-center bg-[#0B0F14]/50 backdrop-blur-sm sm:items-center sm:p-4"
             role="dialog" aria-modal="true" aria-labelledby="locationTitle">
            <div @click.outside="showModal = false" class="max-h-[92dvh] w-full overflow-y-auto rounded-t-3xl bg-white p-5 pb-[max(1.25rem,env(safe-area-inset-bottom))] shadow-xl sm:max-w-lg sm:rounded-2xl sm:p-6">
                <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-slate-200 sm:hidden"></div>
                <div class="mb-5 flex items-start justify-between gap-3">
                    <h3 id="locationTitle" class="text-base font-semibold text-[#0B0F14]" x-text="form.id ? 'Modifier le lieu' : 'Ajouter un lieu'"></h3>
                    <button type="button" @click="showModal = false" class="-mr-1 rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Fermer">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                @if($errors->any())
                    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form :action="form.id ? updateUrl.replace('__ID__', form.id) : storeUrl" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="form.id">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <input type="hidden" name="location_id" :value="form.id ?? ''">

                    <div>
                        <label for="locName" class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Nom du lieu</label>
                        <input id="locName" type="text" name="name" x-model="form.name" required maxlength="120" placeholder="Ex. Siège Cocody, Agence Plateau…" class="{{ $input }}">
                    </div>

                    <div class="space-y-2">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="locLat" class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Latitude</label>
                                <input id="locLat" type="text" inputmode="decimal" name="latitude" x-model="form.latitude" required placeholder="5.3599520" class="{{ $input }} font-mono">
                            </div>
                            <div>
                                <label for="locLng" class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Longitude</label>
                                <input id="locLng" type="text" inputmode="decimal" name="longitude" x-model="form.longitude" required placeholder="-4.0082560" class="{{ $input }} font-mono">
                            </div>
                        </div>
                        <button type="button" @click="useCurrentPosition()" :disabled="locating" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-[#0066FF]/30 bg-blue-50/60 text-sm font-medium text-[#0066FF] hover:bg-blue-50 disabled:opacity-60">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span x-text="locating ? 'Localisation en cours…' : 'Utiliser ma position actuelle'"></span>
                        </button>
                        <p x-show="locateMessage" x-text="locateMessage" class="text-xs" :class="locateError ? 'text-rose-600' : 'text-[#64748B]'"></p>
                    </div>

                    <div>
                        <label for="locRadius" class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Rayon autorisé (mètres)</label>
                        <input id="locRadius" type="number" name="radius_meters" x-model="form.radius_meters" min="10" max="5000" step="1" placeholder="{{ $defaultRadius }} (par défaut)" class="{{ $input }}">
                        <p class="mt-1 text-xs text-[#64748B]">Laisser vide pour utiliser le rayon par défaut ({{ $defaultRadius }} m).</p>
                    </div>

                    <label class="flex items-center gap-2.5 text-sm text-[#0B0F14]">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="h-4 w-4 rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]/30">
                        Lieu actif
                    </label>

                    <div class="flex gap-2 pt-2">
                        <button type="button" @click="showModal = false" class="h-11 flex-1 rounded-xl border border-[#E2E8F0] text-sm font-medium text-[#0B0F14] hover:bg-slate-50 sm:flex-none sm:px-5">Annuler</button>
                        <button type="submit" class="h-11 flex-1 rounded-xl bg-[#0066FF] text-sm font-semibold text-white hover:bg-[#0052cc]">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
