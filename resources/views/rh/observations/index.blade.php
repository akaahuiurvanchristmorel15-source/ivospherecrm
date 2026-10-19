<x-layouts.app>
    <x-slot:title>Observations sur les collaborateurs — IVOSPHERE RH</x-slot>

    <div class="space-y-6" x-data="{ creating: {{ $errors->any() ? 'true' : 'false' }} }">
        <x-page-header
            title="Observations sur les collaborateurs"
            description="Constats des responsables sur le travail et le comportement des agents : points forts, axes d'amélioration et faits notables"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => auth()->user()->hasRole('administrateur', 'responsable', 'responsable_rh') ? route('rh.index') : route('rh.portal.index')],
                    ['label' => 'Observations']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <button type="button" @click="creating = !creating"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-xs font-bold transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nouvelle observation</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        @if($errors->any())
            <x-alert type="error">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <!-- Saisie -->
        <div x-show="creating" x-cloak style="display: none;">
            <x-card title="Nouvelle observation" subtitle="Seuls les collaborateurs que vous suivez sont proposés.">
                @if($employees->isEmpty())
                    <p class="text-xs text-[#64748B]">Aucun collaborateur actif dans vos domaines.</p>
                @else
                    @include('rh.observations._form', ['observation' => null, 'employee' => null, 'employees' => $employees])
                @endif
            </x-card>
        </div>

        <!-- Indicateurs -->
        <div class="grid grid-cols-3 gap-3 sm:gap-4">
            @foreach($natures as $key => $label)
                <x-stat-card :title="$label" :value="$natureCounts[$key] ?? 0" change="Observations" changeType="neutral" timeframe="" />
            @endforeach
        </div>

        <!-- Filtres -->
        <x-card>
            <form method="GET" action="{{ route('rh.observations.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
                <div class="sm:col-span-4">
                    <label for="filter-employee" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Collaborateur</label>
                    <select id="filter-employee" name="employee_id" class="w-full px-3.5 py-2 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                        <option value="">Tous les collaborateurs</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" @selected(request('employee_id') == $emp->id)>{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <label for="filter-nature" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Nature</label>
                    <select id="filter-nature" name="nature" class="w-full px-3.5 py-2 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                        <option value="">Toutes</option>
                        @foreach($natures as $key => $label)
                            <option value="{{ $key }}" @selected(request('nature') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <label for="filter-category" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Catégorie</label>
                    <select id="filter-category" name="category" class="w-full px-3.5 py-2 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                        <option value="">Toutes</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 flex items-end">
                    <button type="submit" class="w-full py-2 px-4 bg-[#0B0F14] hover:bg-[#1E293B] text-white rounded-xl text-xs font-bold transition">Filtrer</button>
                </div>
            </form>
        </x-card>

        <!-- Liste -->
        <div class="space-y-3">
            @forelse($observations as $observation)
                @include('rh.observations._card', ['observation' => $observation, 'showEmployee' => true])
            @empty
                <div class="p-12 text-center text-xs text-[#64748B] bg-white rounded-2xl border border-[#E2E8F0]">
                    Aucune observation pour cette sélection.
                </div>
            @endforelse
        </div>

        @if($observations->hasPages())
            <div>{{ $observations->links() }}</div>
        @endif
    </div>
</x-layouts.app>
