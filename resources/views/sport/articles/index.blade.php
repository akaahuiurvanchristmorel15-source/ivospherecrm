<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Articles & Maillots Personnalisés" 
            subtitle="Personnalisation textile, flocage numéros/noms, dotations clubs et commandes sportives">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'SPORT', 'url' => route('sport.index')],
                    ['label' => 'Articles']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('sport.articles.create') }}" variant="primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouvel Article Sport
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden">
        <!-- Vue Table Desktop (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Produit / Article</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Client / Équipe</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Taille & Couleur</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Flocage personnalisé</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Quantité</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Montant Total</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-center">Statut</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($articles as $article)
                        <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-sm text-[#0B0F14]">{{ $article->product->name ?? 'Article standard' }}</td>
                            <td class="py-3.5 px-4 text-sm font-medium text-[#0B0F14]">{{ $article->customer->name ?? $article->team ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-xs text-[#64748B]">{{ $article->size ?? '-' }} / {{ $article->color ?? '-' }}</td>
                            <td class="py-3.5 px-4 font-mono text-xs text-[#0B0F14] font-medium">
                                {{ $article->number ? '#'.$article->number : '' }} {{ $article->custom_name }}
                            </td>
                            <td class="py-3.5 px-4 text-sm font-medium text-[#0B0F14] text-right">{{ $article->quantity }}</td>
                            <td class="py-3.5 px-4 text-sm font-bold text-[#0B0F14] text-right">{{ number_format($article->total, 0, ',', ' ') }} FCFA</td>
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $statusBadge = $article->status === 'livré' 
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                                        : 'bg-amber-50 text-amber-700 border-amber-200';
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $statusBadge }}">
                                    {{ ucfirst($article->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('sport.articles.edit', $article) }}" class="text-xs font-semibold text-[#0066FF] hover:underline">
                                    Modifier
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state 
                                    title="Aucun article de sport" 
                                    description="Aucune commande d'équipement ou article personnalisé pour le moment."
                                    action-label="Nouvel article"
                                    :action-url="route('sport.articles.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vue Cartes Mobile (< md) -->
        <div class="block md:hidden p-3 sm:p-4 space-y-3">
            @forelse($articles as $article)
                @php
                    $statusBadge = $article->status === 'livré' 
                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                        : 'bg-amber-50 text-amber-700 border-amber-200';
                @endphp
                <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h4 class="font-bold text-[#0B0F14] text-sm">{{ $article->product->name ?? 'Article standard' }}</h4>
                            <span class="text-xs text-[#64748B] block mt-0.5">{{ $article->customer->name ?? $article->team ?? 'Client sport' }}</span>
                        </div>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-semibold border shrink-0 {{ $statusBadge }}">
                            {{ ucfirst($article->status) }}
                        </span>
                    </div>

                    @if($article->custom_name || $article->number)
                        <div class="bg-slate-50 p-2.5 rounded-lg border border-[#E2E8F0] flex items-center justify-between text-xs">
                            <span class="text-[#64748B]">Flocage :</span>
                            <span class="font-mono font-bold text-[#0066FF]">
                                {{ $article->number ? '#'.$article->number : '' }} {{ $article->custom_name }}
                            </span>
                        </div>
                    @endif

                    <!-- 2-col info grid -->
                    <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Taille / Couleur</span>
                            <span class="font-medium text-[#0B0F14]">{{ $article->size ?? '-' }} / {{ $article->color ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Quantité</span>
                            <span class="font-bold text-[#0B0F14]">{{ $article->quantity }} unité(s)</span>
                        </div>
                    </div>

                    <!-- Total & action -->
                    <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block">Total</span>
                            <span class="text-sm font-bold text-[#0B0F14]">{{ number_format($article->total, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <a href="{{ route('sport.articles.edit', $article) }}" class="py-1.5 px-3 rounded-lg border border-[#E2E8F0] text-xs font-semibold text-[#0066FF] hover:bg-[#F5F7FA] transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Modifier</span>
                        </a>
                    </div>
                </div>
            @empty
                <x-empty-state 
                    title="Aucun article de sport" 
                    description="Aucune commande d'équipement ou article personnalisé pour le moment."
                    action-label="Nouvel article"
                    :action-url="route('sport.articles.create')"
                />
            @endforelse
        </div>

        @if($articles->hasPages())
            <div class="p-4 border-t border-[#E2E8F0]">
                {{ $articles->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
