<x-layouts.app>
    <x-slot:title>Fournisseurs — IVOSPHERE ERP</x-slot>

    <div class="space-y-6">
        <x-page-header 
            title="Fournisseurs & Partenaires" 
            description="Répertoire des fournisseurs, prestataires externes et gestion des approvisionnements"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                    ['label' => 'Fournisseurs']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button variant="primary" href="{{ route('commercial.suppliers.create') }}" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Nouveau Fournisseur</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <x-card :noPadding="true">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Code</th>
                            <th class="py-3 px-4">Raison Sociale / Nom</th>
                            <th class="py-3 px-4">Email de contact</th>
                            <th class="py-3 px-4">Statut</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($suppliers as $s)
                            <tr class="hover:bg-[#F5F7FA]/70 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-[#0066FF]">
                                    {{ $s->code }}
                                </td>
                                <td class="py-3.5 px-4 font-medium text-[#0B0F14]">
                                    {{ $s->name }}
                                </td>
                                <td class="py-3.5 px-4 text-[#64748B] font-mono text-[11px]">
                                    {{ $s->email ?? '—' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $s->status === 'actif' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-[#64748B] border-[#E2E8F0]' }}">
                                        {{ ucfirst($s->status ?? 'actif') }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('commercial.suppliers.edit', $s) }}" class="p-1.5 rounded-lg text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] transition border border-transparent hover:border-[#E2E8F0]" title="Modifier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12">
                                    <x-empty-state 
                                        title="Aucun fournisseur enregistré"
                                        description="Ajoutez vos partenaires et fournisseurs réguliers pour gérer vos achats et stocks."
                                    >
                                        <x-slot:action>
                                            <x-button variant="primary" href="{{ route('commercial.suppliers.create') }}">
                                                Ajouter un fournisseur
                                            </x-button>
                                        </x-slot:action>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Amplified Cards View (< md) -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($suppliers as $s)
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono font-bold text-xs text-[#0066FF] px-2 py-0.5 rounded bg-blue-50 border border-blue-200">
                                {{ $s->code }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $s->status === 'actif' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-[#64748B] border-[#E2E8F0]' }}">
                                {{ ucfirst($s->status ?? 'actif') }}
                            </span>
                        </div>

                        <div>
                            <p class="text-sm font-bold text-[#0B0F14] leading-snug">{{ $s->name }}</p>
                            @if($s->email)
                                <p class="text-xs text-slate-500 font-mono mt-0.5 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span>{{ $s->email }}</span>
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <a href="{{ route('commercial.suppliers.edit', $s) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Modifier</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-8">
                        <x-empty-state 
                            title="Aucun fournisseur enregistré"
                            description="Ajoutez vos partenaires et fournisseurs réguliers pour gérer vos achats et stocks."
                        />
                    </div>
                @endforelse
            </div>

            @if($suppliers->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
