<x-layouts.app>
    <x-slot:title>Offres & tarifs</x-slot>

    <div class="space-y-6">
        <x-page-header title="Offres & tarifs" description="Prix, nombre d'utilisateurs et disponibilité des offres d'abonnement"
            :breadcrumbs="[['label' => 'Abonnements', 'url' => route('admin.subscriptions.index')], ['label' => 'Offres & tarifs']]" />

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            @foreach($plans as $plan)
                <x-card>
                    <form method="POST" action="{{ route('admin.subscriptions.plans.update', $plan) }}" class="p-5 space-y-4 text-xs">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-mono text-[11px] text-[#64748B]">{{ $plan->code }}</span>
                            @unless($plan->canBeSubscribed())
                                <x-badge variant="warning">Non souscriptible</x-badge>
                            @endunless
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold mb-1">Nom</label>
                                <input name="name" value="{{ $plan->name }}" required class="w-full h-10 px-3 rounded-xl border border-[#E2E8F0]">
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Utilisateurs max.</label>
                                <input type="number" min="1" name="max_users" value="{{ $plan->max_users }}" placeholder="Illimité" class="w-full h-10 px-3 rounded-xl border border-[#E2E8F0]">
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Prix mensuel (FCFA)</label>
                                <input type="number" min="0" name="price_monthly" value="{{ $plan->price_monthly }}" required class="w-full h-10 px-3 rounded-xl border border-[#E2E8F0]">
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Prix annuel (FCFA)</label>
                                <input type="number" min="0" name="price_yearly" value="{{ $plan->price_yearly }}" required class="w-full h-10 px-3 rounded-xl border border-[#E2E8F0]">
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Accroche</label>
                            <input name="tagline" value="{{ $plan->tagline }}" class="w-full h-10 px-3 rounded-xl border border-[#E2E8F0]">
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($plan->moduleDetails() as $module)
                                <span class="px-2 py-1 rounded-lg border text-[11px] {{ $module['ready'] ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-slate-50 border-[#E2E8F0] text-[#64748B]' }}">
                                    {{ $module['label'] }}{{ $module['ready'] ? '' : ' (en préparation)' }}
                                </span>
                            @endforeach
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($plan->is_active) class="rounded"> Affichée</label>
                                <label class="flex items-center gap-2"><input type="hidden" name="is_available" value="0"><input type="checkbox" name="is_available" value="1" @checked($plan->is_available) class="rounded"> En vente</label>
                            </div>
                            <x-button type="submit" variant="primary" size="sm">Enregistrer</x-button>
                        </div>
                    </form>
                </x-card>
            @endforeach
        </div>
    </div>
</x-layouts.app>
