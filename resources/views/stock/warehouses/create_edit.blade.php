<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="{{ isset($warehouse) ? 'Modifier l\'Entrepôt : ' . $warehouse->name : 'Nouvel Entrepôt' }}" 
            subtitle="Configurez les paramètres, la localisation et le responsable de l'entrepôt">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Stocks', 'url' => route('stock.index')],
                    ['label' => 'Entrepôts', 'url' => route('stock.warehouses.index')],
                    ['label' => isset($warehouse) ? 'Modifier' : 'Créer']
                ]" />
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ isset($warehouse) ? route('stock.warehouses.update', $warehouse) : route('stock.warehouses.store') }}" class="space-y-6">
            @csrf
            @if(isset($warehouse))
                @method('PUT')
            @endif

            <div class="rounded-xl bg-white border border-[#E2E8F0] p-6 shadow-xs">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-[#0B0F14] border-b border-[#E2E8F0] pb-3 mb-5">
                    Identification de l'entrepôt
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Nom de l'entrepôt <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $warehouse->name ?? '') }}" 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                            placeholder="Ex: Entrepôt Central - Zone Industrielle">
                        @error('name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="code" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Code Référence <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="code" id="code" required value="{{ old('code', $warehouse->code ?? '') }}" 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                            placeholder="Ex: ENT-GEN, ENT-01">
                        @error('code') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="domain_id" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Domaine Associé
                        </label>
                        <select id="domain_id" name="domain_id" 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                            <option value="">(Général / Commun à tous)</option>
                            @foreach($domains as $domain)
                                <option value="{{ $domain->id }}" {{ old('domain_id', $warehouse->domain_id ?? '') == $domain->id ? 'selected' : '' }}>
                                    {{ $domain->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('domain_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="manager" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Responsable de l'entrepôt
                        </label>
                        <input type="text" name="manager" id="manager" value="{{ old('manager', $warehouse->manager ?? '') }}" 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                            placeholder="Ex: Jean Kouassi">
                        @error('manager') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="relative flex items-center gap-3 cursor-pointer">
                            <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $warehouse->is_active ?? true) ? 'checked' : '' }} 
                                class="h-4 w-4 rounded border-[#E2E8F0] text-[#0066FF] focus:ring-[#0066FF]">
                            <div>
                                <span class="block text-sm font-semibold text-[#0B0F14]">Entrepôt Actif</span>
                                <span class="block text-xs text-[#64748B]">Permet les mouvements d'entrée et de sortie dans cet entrepôt</span>
                            </div>
                        </label>
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Adresse Physique / Localisation
                        </label>
                        <textarea id="address" name="address" rows="2" 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                            placeholder="Ex: Rue des Brasseries, Zone 4C, Abidjan">{{ old('address', $warehouse->address ?? '') }}</textarea>
                        @error('address') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Description / Notes
                        </label>
                        <textarea id="description" name="description" rows="3" 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                            placeholder="Spécificités de stockage, accès sécurisé, horaires d'ouverture...">{{ old('description', $warehouse->description ?? '') }}</textarea>
                        @error('description') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button href="{{ route('stock.warehouses.index') }}" variant="secondary">
                    Annuler
                </x-button>
                <x-button type="submit" variant="primary">
                    {{ isset($warehouse) ? 'Mettre à jour l\'entrepôt' : 'Créer l\'entrepôt' }}
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
