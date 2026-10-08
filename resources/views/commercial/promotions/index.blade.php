<x-layouts.app>
    <x-slot:title>Codes Promo & Remises — IVOSPHERE ERP</x-slot>

    <div class="space-y-6" x-data="{ showModal: false }">
        <x-page-header 
            title="Codes Promo & Remises" 
            description="Gestion des campagnes promotionnelles, remises en pourcentage ou montant fixe utilisables en caisse"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                    ['label' => 'Promotions']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button variant="primary" type="button" @click="showModal = true" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Créer un Code Promo</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <!-- Quick Stats (12 cols) -->
        @php
            $activeCount = \App\Models\Promotion::where('is_active', true)->count();
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-6">
                <x-stat-card 
                    title="Total Codes Promo" 
                    :value="$promotions->total()" 
                    change="Campagnes enregistrées" 
                    changeType="neutral"
                />
            </div>

            <div class="lg:col-span-6">
                <x-stat-card 
                    title="Codes Promo Actifs" 
                    :value="$activeCount" 
                    change="Utilisables en caisse POS et commandes" 
                    changeType="up"
                />
            </div>
        </div>

        <!-- Promotions Table -->
        <x-card :noPadding="true">
            <!-- Vue Table Desktop (>= md) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Code / Campagne</th>
                            <th class="py-3 px-4">Type de Remise</th>
                            <th class="py-3 px-4">Valeur</th>
                            <th class="py-3 px-4">Conditions</th>
                            <th class="py-3 px-4">Statut</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($promotions as $promo)
                            <tr class="hover:bg-[#F5F7FA]/70 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-bold text-[#0066FF] bg-[#0066FF]/5 border border-[#0066FF]/20 px-2 py-0.5 rounded-md inline-block text-xs">
                                        {{ $promo->code }}
                                    </div>
                                    <p class="font-medium text-[#0B0F14] mt-1">{{ $promo->name }}</p>
                                </td>
                                <td class="py-3.5 px-4 capitalize">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $promo->type === 'percent' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                        {{ $promo->type === 'percent' ? 'Pourcentage (%)' : 'Montant Fixe' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-emerald-700">
                                    @if($promo->type === 'percent')
                                        -{{ $promo->value }}%
                                    @else
                                        -{{ number_format($promo->value, 0, ',', ' ') }} FCFA
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-[11px] text-[#64748B] space-y-0.5">
                                    @if($promo->min_amount > 0)
                                        <div>Min d'achat : {{ number_format($promo->min_amount, 0, ',', ' ') }} FCFA</div>
                                    @endif
                                    @if($promo->end_date)
                                        <div class="text-amber-700">Expire le {{ $promo->end_date->format('d/m/Y') }}</div>
                                    @endif
                                    @if(!$promo->min_amount && !$promo->end_date)
                                        <span class="text-[#64748B]/70">Sans condition minimum</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <form action="{{ route('commercial.promotions.toggle', $promo) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-semibold transition border {{ $promo->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-[#64748B] border-[#E2E8F0] hover:bg-slate-200' }}">
                                            {{ $promo->is_active ? '● Actif' : '○ Inactif' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <form action="{{ route('commercial.promotions.destroy', $promo) }}" method="POST" class="inline-block" onsubmit="return confirm('Supprimer ce code promo ?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1 text-[#64748B] hover:text-rose-600 transition" title="Supprimer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12">
                                    <x-empty-state 
                                        title="Aucun code promo créé"
                                        description="Créez des remises promotionnelles pour fidéliser vos clients et animer vos ventes."
                                    >
                                        <x-slot:action>
                                            <x-button variant="primary" type="button" @click="showModal = true">
                                                Créer un code promo
                                            </x-button>
                                        </x-slot:action>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Vue Cartes Mobile (< md) -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($promotions as $promo)
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="font-mono font-bold text-[#0066FF] bg-[#0066FF]/5 border border-[#0066FF]/20 px-2.5 py-1 rounded-lg inline-block text-xs">
                                    {{ $promo->code }}
                                </span>
                                <h4 class="font-bold text-[#0B0F14] text-xs mt-1.5">{{ $promo->name }}</h4>
                            </div>
                            <form action="{{ route('commercial.promotions.toggle', $promo) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-semibold transition border {{ $promo->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-[#64748B] border-[#E2E8F0]' }}">
                                    {{ $promo->is_active ? '● Actif' : '○ Inactif' }}
                                </button>
                            </form>
                        </div>

                        <!-- 2-col type & reduction grid -->
                        <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Type</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold inline-block {{ $promo->type === 'percent' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                    {{ $promo->type === 'percent' ? 'Pourcentage (%)' : 'Montant Fixe' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Réduction</span>
                                <span class="font-bold text-emerald-700 text-sm block">
                                    @if($promo->type === 'percent')
                                        -{{ $promo->value }}%
                                    @else
                                        -{{ number_format($promo->value, 0, ',', ' ') }} FCFA
                                    @endif
                                </span>
                            </div>
                        </div>

                        @if($promo->min_amount > 0 || $promo->end_date)
                            <div class="text-[11px] text-[#64748B] space-y-0.5">
                                @if($promo->min_amount > 0)
                                    <div>Achat minimum : <strong class="text-[#0B0F14]">{{ number_format($promo->min_amount, 0, ',', ' ') }} FCFA</strong></div>
                                @endif
                                @if($promo->end_date)
                                    <div class="text-amber-700">Expire le {{ $promo->end_date->format('d/m/Y') }}</div>
                                @endif
                            </div>
                        @endif

                        <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-end">
                            <form action="{{ route('commercial.promotions.destroy', $promo) }}" method="POST" class="inline-block" onsubmit="return confirm('Supprimer ce code promo ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-rose-600 hover:bg-rose-50 transition" title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <x-empty-state 
                        title="Aucun code promo créé"
                        description="Créez des remises promotionnelles pour fidéliser vos clients et animer vos ventes."
                    >
                        <x-slot:action>
                            <x-button variant="primary" type="button" @click="showModal = true">
                                Créer un code promo
                            </x-button>
                        </x-slot:action>
                    </x-empty-state>
                @endforelse
            </div>

            @if($promotions->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">{{ $promotions->links() }}</div>
            @endif
        </x-card>

        <!-- Modal Création Code Promo -->
        <div 
            x-show="showModal" 
            x-transition 
            class="fixed inset-0 z-50 bg-[#0B0F14]/50 flex items-center justify-center p-4" 
            style="display: none;"
        >
            <div @click.outside="showModal = false" class="bg-white border border-[#E2E8F0] rounded-xl max-w-xl w-full p-6 shadow-xl space-y-4">
                <div class="flex justify-between items-center border-b border-[#E2E8F0] pb-3">
                    <h3 class="text-base font-bold text-[#0B0F14]">Créer un Nouveau Code Promo</h3>
                    <button type="button" @click="showModal = false" class="text-[#64748B] hover:text-[#0B0F14]">✕</button>
                </div>

                <form action="{{ route('commercial.promotions.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Code Promo *</label>
                            <input type="text" name="code" required placeholder="Ex: BIENVENUE10" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] uppercase font-mono focus:outline-none focus:border-[#0066FF] text-xs transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Nom de la Campagne *</label>
                            <input type="text" name="name" required placeholder="Ex: Remise de bienvenue" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Type de réduction *</label>
                            <select name="type" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                                <option value="percent">Pourcentage (%)</option>
                                <option value="fixed">Montant fixe (FCFA)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Valeur de la réduction *</label>
                            <input type="number" step="0.01" name="value" required placeholder="Ex: 10 pour 10% ou 5000 pour 5000 F" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] font-bold focus:outline-none focus:border-[#0066FF] text-xs transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Montant minimum d'achat (FCFA)</label>
                        <input type="number" name="min_amount" placeholder="Ex: 50000" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date d'activation</label>
                            <input type="date" name="start_date" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date d'expiration</label>
                            <input type="date" name="end_date" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-[#E2E8F0]">
                        <x-button variant="secondary" type="button" @click="showModal = false">Annuler</x-button>
                        <x-button variant="primary" type="submit">Créer le Code</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
