<x-layouts.app>
    <x-slot:title>{{ isset($supplier) ? 'Modifier' : 'Nouveau' }} Fournisseur — IVOSPHERE ERP</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-page-header 
            title="{{ isset($supplier) ? 'Modifier le Fournisseur : ' . $supplier->name : 'Nouveau Fournisseur Partenaire' }}"
            description="Fiche d'identification et coordonnées du fournisseur d'approvisionnement"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                    ['label' => 'Fournisseurs', 'url' => route('commercial.suppliers.index')],
                    ['label' => isset($supplier) ? 'Modification' : 'Nouveau']
                ]" />
            </x-slot:breadcrumbs>
        </x-page-header>

        <form action="{{ isset($supplier) ? route('commercial.suppliers.update', $supplier) : route('commercial.suppliers.store') }}" method="POST" class="space-y-6 text-xs">
            @csrf
            @if(isset($supplier)) @method('PUT') @endif

            <x-card title="Fiche Fournisseur">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Code Fournisseur *</label>
                        <input type="text" name="code" value="{{ old('code', $supplier->code ?? 'FOU-' . strtoupper(Str::random(6))) }}" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] font-mono focus:outline-none focus:border-[#0066FF] text-xs transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Raison Sociale / Nom *</label>
                        <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}" required placeholder="Ex: Papeterie Centrale Abidjan" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Adresse Email</label>
                        <input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}" placeholder="commercial@fournisseur.ci" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Statut *</label>
                        <select name="status" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                            <option value="actif" {{ old('status', $supplier->status ?? '') == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ old('status', $supplier->status ?? '') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>
                </div>

                <div class="pt-6 border-t border-[#E2E8F0] mt-6 flex justify-end gap-3">
                    <x-button variant="secondary" href="{{ route('commercial.suppliers.index') }}">
                        Annuler
                    </x-button>
                    <x-button variant="primary" type="submit">
                        {{ isset($supplier) ? 'Mettre à jour le fournisseur' : 'Enregistrer le fournisseur' }}
                    </x-button>
                </div>
            </x-card>
        </form>
    </div>
</x-layouts.app>
