<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Produits d'Assurance Partenaires" 
            subtitle="Catalogue des solutions d'assurance distribuées en intermédiation et courtage conseil">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'ASSURANCE', 'url' => route('assurance.index')],
                    ['label' => 'Produits']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('assurance.products.create') }}" variant="primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouveau Produit
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($products as $product)
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-6 shadow-xs hover:border-[#0066FF] transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#0066FF] bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">
                            {{ $product->partner }}
                        </span>
                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                            {{ ucfirst($product->status) }}
                        </span>
                    </div>

                    <h3 class="text-base font-bold text-[#0B0F14] mb-0.5">{{ $product->name }}</h3>
                    <p class="text-xs text-[#64748B] uppercase tracking-wider mb-3">Branche : {{ $product->type }}</p>
                    <p class="text-xs text-[#64748B] mb-4 leading-relaxed line-clamp-3">{{ $product->description ?? 'Aucune description disponible.' }}</p>

                    <dl class="space-y-1.5 border-t border-[#E2E8F0] pt-3 text-xs">
                        <div class="flex justify-between text-[#64748B]">
                            <span>Prime indicative :</span>
                            <span class="font-medium text-[#0B0F14]">{{ $product->premium_range ?? 'Sur devis' }}</span>
                        </div>
                        <div class="flex justify-between text-[#64748B]">
                            <span>Taux de commission courtier :</span>
                            <span class="font-bold text-[#0066FF]">{{ $product->commission_rate }}%</span>
                        </div>
                    </dl>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3 border-t border-[#E2E8F0] pt-4">
                    <a href="{{ route('assurance.products.edit', $product) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#0066FF] hover:underline">
                        <span>Modifier le produit</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-empty-state 
                    title="Aucun produit d'assurance" 
                    description="Ajoutez des offres partenaires de santé, auto, prévoyance ou multirisque."
                    action-label="Nouveau produit"
                    :action-url="route('assurance.products.create')"
                />
            </div>
        @endforelse
    </div>
</x-layouts.app>
