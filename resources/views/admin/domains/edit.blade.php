<x-layouts.app>
    <x-slot:title>Modifier le Domaine {{ $domain->name }}</x-slot>

    <x-slot name="header">
        <x-page-header 
            title="Modifier le Domaine : {{ $domain->name }}" 
            subtitle="Code : {{ $domain->code }} &bull; {{ $domain->users_count }} collaborateur(s) affecté(s)">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Administration', 'url' => route('admin.users.index')],
                    ['label' => 'Domaines Métiers', 'url' => route('admin.domains.index')],
                    ['label' => 'Modifier ' . $domain->name]
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('admin.domains.index') }}" variant="secondary" size="sm" class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux domaines</span>
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Formulaire de modification -->
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-xs">
            <h2 class="text-base font-bold text-[#0B0F14] border-b border-[#E2E8F0] pb-3 mb-4">
                Paramètres du Domaine Métier
            </h2>

            <form action="{{ route('admin.domains.update', $domain) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700">Nom du Domaine <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $domain->name) }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs focus:border-[#0066FF] focus:outline-none focus:ring-1 focus:ring-[#0066FF]">
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700">Code identifiant unique <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $domain->code) }}" required maxlength="50"
                           oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9_-]/g, '')"
                           class="mt-1 w-full font-mono uppercase font-bold text-[#0066FF] rounded-xl border border-slate-200 px-3.5 py-2 text-xs focus:border-[#0066FF] focus:outline-none focus:ring-1 focus:ring-[#0066FF]">
                    @error('code')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700">Description Métier</label>
                    <textarea name="description" rows="3"
                              class="mt-1 w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs focus:border-[#0066FF] focus:outline-none focus:ring-1 focus:ring-[#0066FF]">{{ old('description', $domain->description) }}</textarea>
                    @error('description')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Couleur Thématique</label>
                        <select name="color"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs focus:border-[#0066FF] focus:outline-none bg-white">
                            @foreach(['indigo' => 'Indigo (Bleu)', 'emerald' => 'Émeraude (Vert)', 'sky' => 'Ciel (Bleu clair)', 'purple' => 'Violet', 'amber' => 'Ambre (Orange)', 'rose' => 'Rose (Rouge)', 'teal' => 'Sarcelle (Turquoise)', 'slate' => 'Ardoise (Gris)'] as $val => $label)
                                <option value="{{ $val }}" {{ old('color', $domain->color) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Icône</label>
                        <select name="icon"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs focus:border-[#0066FF] focus:outline-none bg-white">
                            @foreach(['briefcase' => 'Mallette', 'truck' => 'Camion (Logistique)', 'building-office' => 'Bâtiment (Immobilier)', 'academic-cap' => 'Formation', 'shield-check' => 'Sécurité', 'shopping-bag' => 'Commerce', 'globe-alt' => 'International', 'chart-bar' => 'Conseil', 'printer' => 'Print', 'cpu-chip' => 'Tech', 'trophy' => 'Sport', 'camera' => 'Média'] as $val => $label)
                                <option value="{{ $val }}" {{ old('icon', $domain->icon) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="inline-flex items-center gap-2 cursor-pointer mt-2">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $domain->is_active) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                        <span class="text-xs font-semibold text-slate-700">Domaine actif (disponible dans l'ensemble des modules CRM)</span>
                    </label>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.domains.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Annuler
                    </a>

                    <button type="submit" class="rounded-xl bg-[#0066FF] hover:bg-[#0052CC] px-5 py-2 text-xs font-bold text-white transition shadow-xs">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>

        <!-- Zone Danger : Suppression -->
        <div class="rounded-2xl border border-rose-200 bg-rose-50/40 p-6 shadow-xs">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-sm font-bold text-rose-900">Zone Dangereuse : Supprimer ce domaine</h3>
                    <p class="text-xs text-rose-700 mt-1">
                        La suppression de ce domaine détachera automatiquement les collaborateurs associés ({{ $domain->users_count }} utilisateur(s)) et réassignera les données associées en gestion centrale.
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-rose-200 flex justify-end">
                <form action="{{ route('admin.domains.destroy', $domain) }}" method="POST" onsubmit="return confirm('Êtes-vous certain de vouloir supprimer définitivement le domaine {{ $domain->name }} ? Cette action est irréversible.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl bg-rose-600 hover:bg-rose-700 px-4 py-2 text-xs font-bold text-white transition shadow-xs inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Supprimer définitivement ce domaine</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
