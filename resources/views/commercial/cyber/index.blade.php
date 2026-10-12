@php
    $paymentMethods = [
        'especes' => 'Espèces',
        'mobile_money' => 'Mobile Money',
        'carte' => 'Carte',
        'credit' => 'Crédit',
    ];
    $categories = \App\Models\CyberService::CATEGORIES;
    $activeServices = $services->where('is_active', true)->values();
    $usedCategories = collect($categories)->only($activeServices->pluck('category')->unique()->all());

    $in = 'w-full rounded-lg border border-[#E2E8F0] bg-white px-3 py-2 text-base text-[#0B0F14] placeholder:text-[#94A3B8] transition focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15 sm:text-sm';
    $label = 'mb-1.5 block text-xs font-medium text-[#64748B]';
@endphp

<x-layouts.app>
    <x-slot:title>Cyber</x-slot>

    <div x-data="cyberDesk({ services: {{ Js::from($activeServices->map->only(['id', 'name', 'unit', 'price', 'category'])) }} })"
         @keydown.escape.window="sheetOpen = false; catalogOpen = false"
         class="pb-28 md:pb-0">

        <x-page-header title="Cyber" description="Prestations au comptoir avec facture automatique.">
            <x-slot:actions>
                @if($canManage)
                    <x-button type="button" variant="secondary" size="md" @click="catalogOpen = true">Tarifs</x-button>
                @endif
                <x-button :href="route('commercial.invoices.index')" variant="secondary" size="md">Factures</x-button>
            </x-slot:actions>
        </x-page-header>

        @if ($errors->any())
            <div class="mb-4">
                <x-alert type="danger" title="Impossible d'enregistrer">
                    <ul class="mt-1 list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </x-alert>
            </div>
        @endif

        <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-[minmax(0,1fr)_340px] xl:grid-cols-[minmax(0,1fr)_380px]">

            {{-- ═══ Catalogue ═══ --}}
            <section class="min-w-0 space-y-4">
                {{-- Recherche --}}
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#94A3B8]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                    <input type="search" x-model="search" placeholder="Rechercher une prestation" aria-label="Rechercher une prestation"
                           class="{{ $in }} h-11 pl-9">
                </div>

                {{-- Catégories --}}
                <div class="-mx-4 flex gap-1.5 overflow-x-auto px-4 pb-0.5 sm:mx-0 sm:flex-wrap sm:px-0" role="tablist" aria-label="Catégories">
                    <button type="button" @click="category = ''" :aria-selected="category === ''"
                            :class="category === '' ? 'border-[#0B0F14] bg-[#0B0F14] text-white' : 'border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#0B0F14]'"
                            class="shrink-0 rounded-full border px-3.5 py-1.5 text-xs font-medium transition">Tout</button>
                    @foreach($usedCategories as $key => $categoryLabel)
                        <button type="button" @click="category = '{{ $key }}'" :aria-selected="category === '{{ $key }}'"
                                :class="category === '{{ $key }}' ? 'border-[#0B0F14] bg-[#0B0F14] text-white' : 'border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#0B0F14]'"
                                class="shrink-0 rounded-full border px-3.5 py-1.5 text-xs font-medium transition">{{ $categoryLabel }}</button>
                    @endforeach
                </div>

                {{-- Prestations --}}
                <div class="grid grid-cols-2 gap-2 sm:gap-3 lg:grid-cols-3">
                    <template x-for="service in filtered" :key="service.id">
                        <button type="button" @click="add(service.id)"
                                :class="qtyOf(service.id) ? 'border-[#0066FF] ring-1 ring-[#0066FF]' : 'border-[#E2E8F0] hover:border-[#CBD5E1]'"
                                class="relative flex min-h-[96px] flex-col justify-between rounded-xl border bg-white p-3 text-left transition active:scale-[0.98] sm:p-3.5">
                            <span class="pr-6 text-[13px] font-medium leading-snug text-[#0B0F14] sm:text-sm" x-text="service.name"></span>
                            <span class="mt-3 flex items-baseline gap-1">
                                <span class="font-mono text-sm font-semibold text-[#0B0F14]" x-text="money(service.price)"></span>
                                <span class="text-[11px] text-[#94A3B8]" x-text="'F / ' + service.unit"></span>
                            </span>
                            <span x-show="qtyOf(service.id)" x-cloak x-text="qtyOf(service.id)"
                                  class="absolute right-2.5 top-2.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#0066FF] px-1.5 text-[11px] font-semibold text-white"></span>
                        </button>
                    </template>
                </div>
                <p x-show="!filtered.length" x-cloak class="rounded-xl border border-dashed border-[#E2E8F0] py-10 text-center text-sm text-[#64748B]">Aucune prestation trouvée.</p>

                {{-- Saisie libre --}}
                <div class="rounded-xl border border-[#E2E8F0] bg-white p-4">
                    <div class="mb-3">
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Autre prestation</h2>
                        <p class="text-xs text-[#64748B]">Travail hors catalogue : décrivez-le et indiquez son prix.</p>
                    </div>
                    <div class="grid grid-cols-[72px_minmax(0,1fr)] gap-2 sm:grid-cols-[minmax(0,1fr)_72px_120px_auto]">
                        <input type="text" x-model="custom.description" @keydown.enter.prevent="addCustom()" placeholder="Ex : mise en page d'un CV"
                               class="{{ $in }} col-span-2 sm:col-span-1" aria-label="Description">
                        <input type="number" x-model.number="custom.quantity" min="1" placeholder="Qté" class="{{ $in }} font-mono" aria-label="Quantité">
                        <input type="number" x-model.number="custom.unit_price" min="0" placeholder="Prix unitaire" @keydown.enter.prevent="addCustom()"
                               class="{{ $in }} font-mono" aria-label="Prix unitaire en FCFA">
                        <button type="button" @click="addCustom()" :disabled="!custom.description.trim() || !(custom.unit_price > 0)"
                                class="col-span-2 rounded-lg border border-[#0B0F14] px-4 py-2 text-sm font-medium text-[#0B0F14] transition hover:bg-[#0B0F14] hover:text-white disabled:cursor-not-allowed disabled:border-[#E2E8F0] disabled:text-[#94A3B8] disabled:hover:bg-transparent sm:col-span-1">Ajouter</button>
                    </div>
                </div>

                {{-- Activité du jour --}}
                <div class="rounded-xl border border-[#E2E8F0] bg-white">
                    <div class="flex items-center justify-between border-b border-[#E2E8F0] px-4 py-3">
                        <div>
                            <h2 class="text-sm font-semibold text-[#0B0F14]">Aujourd'hui</h2>
                            <p class="text-xs text-[#64748B]">{{ $canManage ? 'Toutes les ventes cyber' : 'Vos ventes' }} · {{ $todayInvoices->count() }} facture(s)</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[11px] text-[#64748B]">Encaissé</p>
                            <p class="font-mono text-base font-semibold text-[#0B0F14]">{{ number_format($todayInvoices->sum('paid_amount'), 0, ',', ' ') }} F</p>
                        </div>
                    </div>
                    @if($todayInvoices->isEmpty())
                        <p class="px-4 py-6 text-center text-sm text-[#94A3B8]">Aucune vente pour le moment.</p>
                    @else
                        <ul class="divide-y divide-[#E2E8F0]">
                            @foreach($todayInvoices->take(8) as $invoice)
                                <li class="flex items-center gap-3 px-4 py-2.5">
                                    <span class="w-11 shrink-0 font-mono text-xs text-[#94A3B8]">{{ $invoice->created_at->format('H:i') }}</span>
                                    <span class="min-w-0 flex-1 truncate font-mono text-xs text-[#0B0F14]">{{ $invoice->reference }}</span>
                                    @if($invoice->status !== 'payee')
                                        <span class="shrink-0 rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-medium text-amber-700">{{ $invoice->status === 'partielle' ? 'Partiel' : 'Impayé' }}</span>
                                    @endif
                                    <span class="shrink-0 font-mono text-sm font-medium text-[#0B0F14]">{{ number_format($invoice->total, 0, ',', ' ') }}</span>
                                    <a href="{{ route('commercial.invoices.print', $invoice) }}" class="shrink-0 rounded-md p-1 text-[#64748B] hover:bg-[#F5F7FA] hover:text-[#0066FF]" aria-label="Imprimer {{ $invoice->reference }}">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5z"/></svg>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            {{-- ═══ Ticket : colonne fixe sur ordinateur, panneau sur mobile ═══ --}}
            <div x-show="sheetOpen" x-cloak x-transition.opacity @click="sheetOpen = false"
                 class="fixed inset-0 z-40 bg-[#0B0F14]/40 md:hidden"></div>

            <aside :class="sheetOpen ? 'translate-y-0' : 'translate-y-full'"
                   class="fixed inset-x-0 bottom-0 z-50 max-h-[90dvh] translate-y-full overflow-y-auto rounded-t-2xl bg-white shadow-2xl transition-transform duration-200
                          md:sticky md:top-6 md:z-auto md:max-h-none md:translate-y-0 md:overflow-visible md:rounded-xl md:border md:border-[#E2E8F0] md:shadow-none md:transition-none">
                <form method="POST" action="{{ route('commercial.cyber.checkout') }}" @submit="submitting = true" class="flex flex-col">
                    @csrf

                    <div class="flex items-center justify-between border-b border-[#E2E8F0] px-4 py-3">
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Ticket <span class="font-normal text-[#94A3B8]" x-text="lines.length ? '· ' + count + ' article(s)' : ''"></span></h2>
                        <div class="flex items-center gap-1">
                            <button type="button" x-show="lines.length" x-cloak @click="lines = []" class="rounded-md px-2 py-1 text-xs font-medium text-[#64748B] hover:bg-[#F5F7FA] hover:text-rose-600">Vider</button>
                            <button type="button" @click="sheetOpen = false" class="rounded-md p-1 text-[#64748B] hover:bg-[#F5F7FA] md:hidden" aria-label="Fermer le ticket">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Client --}}
                    <div class="border-b border-[#E2E8F0] px-4 py-3">
                        <label for="customer_id" class="{{ $label }}">Client</label>
                        <select id="customer_id" name="customer_id" class="{{ $in }}">
                            <option value="">Client de passage</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }} · {{ $customer->code }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Lignes --}}
                    <div class="border-b border-[#E2E8F0]">
                        <p x-show="!lines.length" class="px-4 py-8 text-center text-sm text-[#94A3B8]">Sélectionnez une prestation.</p>
                        <ul class="divide-y divide-[#E2E8F0]">
                            <template x-for="(line, index) in lines" :key="index">
                                <li class="px-4 py-3">
                                    <input type="hidden" :name="`items[${index}][cyber_service_id]`" :value="line.cyber_service_id ?? ''">
                                    <template x-if="!line.cyber_service_id">
                                        <span>
                                            <input type="hidden" :name="`items[${index}][description]`" :value="line.description">
                                            <input type="hidden" :name="`items[${index}][unit_price]`" :value="line.unit_price">
                                        </span>
                                    </template>
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium leading-snug text-[#0B0F14]" x-text="line.description"></p>
                                            <p class="text-xs text-[#94A3B8]" x-text="money(line.unit_price) + ' F / ' + line.unit"></p>
                                        </div>
                                        <p class="shrink-0 font-mono text-sm font-semibold text-[#0B0F14]" x-text="money(line.quantity * line.unit_price)"></p>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between">
                                        <div class="inline-flex items-center rounded-lg border border-[#E2E8F0]">
                                            <button type="button" @click="line.quantity > 1 ? line.quantity-- : lines.splice(index, 1)" class="h-8 w-8 text-[#64748B] hover:text-[#0B0F14]" aria-label="Diminuer">−</button>
                                            <input type="number" min="1" :name="`items[${index}][quantity]`" x-model.number="line.quantity"
                                                   class="h-8 w-12 border-0 border-x border-[#E2E8F0] p-0 text-center font-mono text-sm focus:ring-0" aria-label="Quantité">
                                            <button type="button" @click="line.quantity++" class="h-8 w-8 text-[#64748B] hover:text-[#0B0F14]" aria-label="Augmenter">+</button>
                                        </div>
                                        <button type="button" @click="lines.splice(index, 1)" class="text-xs text-[#94A3B8] hover:text-rose-600">Retirer</button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>

                    {{-- Règlement --}}
                    <div class="space-y-4 px-4 py-4">
                        <div class="flex items-baseline justify-between">
                            <span class="text-sm text-[#64748B]">Total</span>
                            <span class="font-mono text-2xl font-semibold tracking-tight text-[#0B0F14]"><span x-text="money(total)"></span> <span class="text-sm font-normal text-[#94A3B8]">FCFA</span></span>
                        </div>

                        <div>
                            <span class="{{ $label }}">Règlement</span>
                            <div class="grid grid-cols-4 gap-1 rounded-lg bg-[#F5F7FA] p-1">
                                @foreach($paymentMethods as $value => $methodLabel)
                                    <label class="cursor-pointer select-none">
                                        <input type="radio" name="payment_method" value="{{ $value }}" x-model="paymentMethod" class="peer sr-only">
                                        <span class="block truncate rounded-md px-1 py-1.5 text-center text-[11px] font-medium text-[#64748B] transition peer-checked:bg-white peer-checked:text-[#0B0F14] peer-checked:shadow-sm peer-focus-visible:ring-2 peer-focus-visible:ring-[#0066FF] sm:text-xs">{{ $methodLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div x-show="paymentMethod === 'mobile_money'" x-cloak class="grid grid-cols-2 gap-2">
                            <input type="hidden" name="mobile_money_provider" :value="paymentMethod === 'mobile_money' ? provider : ''">
                            @foreach(['wave' => 'Wave', 'orange_money' => 'Orange Money'] as $providerKey => $providerLabel)
                                <button type="button" @click="provider = '{{ $providerKey }}'"
                                        :class="provider === '{{ $providerKey }}' ? 'border-[#0B0F14] text-[#0B0F14]' : 'border-[#E2E8F0] text-[#64748B]'"
                                        class="rounded-lg border px-2 py-2 text-xs font-medium transition">{{ $providerLabel }}</button>
                            @endforeach
                        </div>

                        <div x-show="paymentMethod !== 'credit'">
                            <label for="amount_paid" class="{{ $label }}">Montant reçu</label>
                            <input id="amount_paid" type="number" min="0" inputmode="numeric" x-model.number="amountPaid" @focus="amountTouched = true" class="{{ $in }} font-mono">
                            <p x-show="paymentMethod === 'especes' && amountPaid > total" x-cloak class="mt-1.5 flex justify-between text-xs">
                                <span class="text-[#64748B]">Monnaie à rendre</span>
                                <span class="font-mono font-semibold text-[#0B0F14]" x-text="money(amountPaid - total) + ' F'"></span>
                            </p>
                            <p x-show="amountTouched && amountPaid < total" x-cloak class="mt-1.5 text-xs text-amber-700">Paiement partiel : le reste sera dû sur la facture.</p>
                        </div>
                        <p x-show="paymentMethod === 'credit'" x-cloak class="rounded-lg bg-[#F5F7FA] px-3 py-2 text-xs text-[#64748B]">La facture sera enregistrée comme impayée.</p>
                        <input type="hidden" name="amount_paid" :value="paymentMethod === 'credit' ? 0 : Math.min(amountTouched ? amountPaid : total, total)">

                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Note (facultatif)" class="{{ $in }}" aria-label="Note">

                        <button type="submit" :disabled="!lines.length || total <= 0 || submitting"
                                class="h-11 w-full rounded-lg bg-[#0066FF] text-sm font-semibold text-white transition hover:bg-[#0052CC] disabled:cursor-not-allowed disabled:bg-[#E2E8F0] disabled:text-[#94A3B8]">
                            <span x-text="submitting ? 'Enregistrement…' : 'Encaisser et générer la facture'"></span>
                        </button>
                    </div>
                </form>
            </aside>
        </div>

        {{-- ═══ Barre du bas (mobile) ═══ --}}
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-[#E2E8F0] bg-white/95 px-4 py-3 backdrop-blur md:hidden"
             style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))">
            <button type="button" @click="sheetOpen = true" :disabled="!lines.length"
                    class="flex h-12 w-full items-center justify-between rounded-lg bg-[#0B0F14] px-4 text-white transition disabled:bg-[#E2E8F0] disabled:text-[#94A3B8]">
                <span class="text-sm font-medium" x-text="lines.length ? 'Voir le ticket · ' + count + ' article(s)' : 'Aucune prestation'"></span>
                <span class="font-mono text-sm font-semibold" x-text="money(total) + ' F'"></span>
            </button>
        </div>

        {{-- ═══ Tarifs (responsables) ═══ --}}
        @if($canManage)
            <div x-show="catalogOpen" x-cloak x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="catalog-title"
                 class="fixed inset-0 z-50 flex items-end justify-center bg-[#0B0F14]/40 sm:items-center sm:p-4">
                <div @click.outside="catalogOpen = false" class="flex max-h-[92dvh] w-full max-w-3xl flex-col rounded-t-2xl bg-white shadow-xl sm:rounded-2xl">
                    <div class="flex items-center justify-between border-b border-[#E2E8F0] px-5 py-4">
                        <div>
                            <h2 id="catalog-title" class="text-base font-semibold text-[#0B0F14]">Tarifs du cyber</h2>
                            <p class="text-xs text-[#64748B]">Chaque ligne s'enregistre séparément.</p>
                        </div>
                        <button type="button" @click="catalogOpen = false" class="rounded-md p-1 text-[#64748B] hover:bg-[#F5F7FA]" aria-label="Fermer">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="flex-1 space-y-2 overflow-y-auto p-4 sm:p-5">
                        @foreach($services as $service)
                            <form method="POST" action="{{ route('commercial.cyber.services.update', $service) }}"
                                  class="grid grid-cols-2 items-end gap-2 rounded-lg border border-[#E2E8F0] p-3 sm:grid-cols-[minmax(0,1fr)_130px_90px_100px_auto] {{ $service->is_active ? '' : 'bg-[#F5F7FA]' }}">
                                @csrf @method('PUT')
                                <label class="col-span-2 sm:col-span-1"><span class="sr-only">Nom</span>
                                    <input type="text" name="name" value="{{ $service->name }}" required class="{{ $in }}">
                                </label>
                                <label><span class="sr-only">Catégorie</span>
                                    <select name="category" class="{{ $in }}">
                                        @foreach($categories as $key => $categoryLabel)
                                            <option value="{{ $key }}" @selected($service->category === $key)>{{ $categoryLabel }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label><span class="sr-only">Unité</span>
                                    <input type="text" name="unit" value="{{ $service->unit }}" required class="{{ $in }}">
                                </label>
                                <label><span class="sr-only">Prix en FCFA</span>
                                    <input type="number" name="price" value="{{ (float) $service->price }}" min="0" required class="{{ $in }} font-mono">
                                </label>
                                <div class="flex items-center justify-end gap-2 sm:h-[38px]">
                                    <label class="mr-auto flex items-center gap-1.5 text-xs text-[#64748B] sm:mr-0">
                                        <input type="checkbox" name="is_active" value="1" @checked($service->is_active) class="h-4 w-4 rounded border-[#CBD5E1] text-[#0066FF] focus:ring-[#0066FF]"> Actif
                                    </label>
                                    <button type="submit" class="rounded-md bg-[#0B0F14] px-2.5 py-1.5 text-xs font-medium text-white hover:bg-slate-800">OK</button>
                                    <button type="submit" form="delete-service-{{ $service->id }}" class="rounded-md p-1.5 text-[#94A3B8] hover:bg-rose-50 hover:text-rose-600" aria-label="Supprimer {{ $service->name }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </div>
                            </form>
                            <form id="delete-service-{{ $service->id }}" method="POST" action="{{ route('commercial.cyber.services.destroy', $service) }}"
                                  onsubmit="return confirm('Supprimer cette prestation du catalogue ?')" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('commercial.cyber.services.store') }}"
                          class="grid grid-cols-2 items-end gap-2 border-t border-[#E2E8F0] bg-[#F5F7FA] p-4 sm:grid-cols-[minmax(0,1fr)_130px_90px_100px_auto] sm:rounded-b-2xl sm:px-5">
                        @csrf
                        <input type="hidden" name="is_active" value="1">
                        <label class="col-span-2 sm:col-span-1"><span class="{{ $label }}">Nouvelle prestation</span>
                            <input type="text" name="name" required placeholder="Ex : Impression A5" class="{{ $in }}">
                        </label>
                        <label><span class="{{ $label }}">Catégorie</span>
                            <select name="category" class="{{ $in }}">
                                @foreach($categories as $key => $categoryLabel)
                                    <option value="{{ $key }}">{{ $categoryLabel }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label><span class="{{ $label }}">Unité</span>
                            <input type="text" name="unit" value="page" required class="{{ $in }}">
                        </label>
                        <label><span class="{{ $label }}">Prix (F)</span>
                            <input type="number" name="price" min="0" required class="{{ $in }} font-mono">
                        </label>
                        <button type="submit" class="col-span-2 h-[38px] rounded-lg bg-[#0066FF] px-4 text-sm font-medium text-white hover:bg-[#0052CC] sm:col-span-1">Ajouter</button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <script>
        function cyberDesk(config) {
            return {
                services: config.services,
                lines: [],
                custom: { description: '', quantity: 1, unit_price: null },
                search: '',
                category: '',
                paymentMethod: 'especes',
                provider: 'wave',
                amountPaid: 0,
                amountTouched: false,
                submitting: false,
                sheetOpen: false,
                catalogOpen: {{ $errors->has('name') || $errors->has('price') ? 'true' : 'false' }},

                get filtered() {
                    const term = this.search.trim().toLowerCase();
                    return this.services.filter(s =>
                        (!this.category || s.category === this.category) &&
                        (!term || s.name.toLowerCase().includes(term))
                    );
                },

                get total() {
                    return this.lines.reduce((sum, line) => sum + (Number(line.quantity) || 0) * line.unit_price, 0);
                },

                get count() {
                    return this.lines.reduce((sum, line) => sum + (Number(line.quantity) || 0), 0);
                },

                init() {
                    this.$watch('total', (value) => { if (!this.amountTouched) this.amountPaid = value; });
                    this.$watch('lines', (lines) => { if (!lines.length) { this.sheetOpen = false; this.amountTouched = false; } });
                },

                qtyOf(id) {
                    const line = this.lines.find(l => l.cyber_service_id === id);
                    return line ? line.quantity : 0;
                },

                add(id) {
                    const existing = this.lines.find(l => l.cyber_service_id === id);
                    if (existing) { existing.quantity++; return; }
                    const service = this.services.find(s => s.id === id);
                    this.lines.push({ cyber_service_id: id, description: service.name, unit: service.unit, unit_price: Number(service.price), quantity: 1 });
                },

                addCustom() {
                    const description = this.custom.description.trim();
                    const price = Number(this.custom.unit_price);
                    if (!description || !(price > 0)) return;
                    this.lines.push({ cyber_service_id: null, description, unit: 'unité', unit_price: price, quantity: Math.max(1, Number(this.custom.quantity) || 1) });
                    this.custom = { description: '', quantity: 1, unit_price: null };
                },

                money(value) {
                    return Math.round(value || 0).toLocaleString('fr-FR');
                },
            };
        }
    </script>
</x-layouts.app>
