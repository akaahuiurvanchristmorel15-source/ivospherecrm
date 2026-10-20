@php
    // Modes de règlement : valeur => [libellé, tracé de l'icône] (identiques à la caisse POS)
    $paymentMethods = [
        'especes' => ['Espèces', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'mobile_money' => ['Mobile Money', 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'],
        'carte' => ['Carte', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
        'credit' => ['Crédit', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
    $cashPresets = [500, 1000, 2000, 5000, 10000];

    // Visuel de chaque catégorie : [tracé de l'icône, couleur de fond, couleur de l'icône]
    $categoryVisuals = [
        'impression' => ['M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5z', 'bg-blue-50', 'text-[#0066FF]'],
        'photocopie' => ['M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75', 'bg-sky-50', 'text-sky-600'],
        'photo' => ['M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316zM16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z', 'bg-amber-50', 'text-amber-600'],
        'bureautique' => ['M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z', 'bg-violet-50', 'text-violet-600'],
        'finition' => ['M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42', 'bg-emerald-50', 'text-emerald-600'],
        'internet' => ['M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418', 'bg-teal-50', 'text-teal-600'],
        'autre' => ['M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'bg-slate-100', 'text-slate-500'],
    ];

    $categories = \App\Models\CyberService::CATEGORIES;
    $activeServices = $services->where('is_active', true)->values();
    $usedCategories = collect($categories)->only($activeServices->pluck('category')->unique()->all());

    $field = 'h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 transition focus:border-slate-400 dark:focus:border-white/30 focus:outline-none focus:ring-2 focus:ring-black/5 sm:h-11';
@endphp

<x-layouts.app>
    <x-slot:title>Cyber</x-slot>

    <div x-data="cyberDesk({
            services: {{ Js::from($activeServices->map->only(['id', 'name', 'unit', 'price', 'category'])) }},
            openCatalog: {{ $errors->hasAny(['name', 'category', 'unit', 'price']) ? 'true' : 'false' }}
         })"
         x-init="if (window.matchMedia('(pointer: fine)').matches) { $nextTick(() => $refs.searchInput?.focus()); }"
         @keydown.escape.window="cartOpen = false; customOpen = false; catalogOpen = false; cancelTarget = null"
         class="space-y-4 pb-24 md:pb-0">

        <x-page-header title="Cyber & prestations">
            <x-slot:actions>
                @if($canManage)
                    <x-button type="button" variant="secondary" size="md" @click="catalogOpen = true">Tarifs</x-button>
                @endif
                @if($canSeePerformance)
                    <x-button :href="route('commercial.cyber.performance')" variant="secondary" size="md">Performance</x-button>
                @endif
                <x-button :href="route('commercial.invoices.index')" variant="secondary" size="md">Factures</x-button>
            </x-slot:actions>
        </x-page-header>

        @if ($errors->any())
            <x-alert type="danger" title="Impossible d'enregistrer">
                <ul class="mt-1 list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </x-alert>
        @endif

        @include('commercial.cyber._deposits')

        <div class="grid grid-cols-1 items-start gap-4 md:grid-cols-[minmax(0,1fr)_340px] lg:grid-cols-[minmax(0,1fr)_380px] lg:gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">

            {{-- ===================== CATALOGUE ===================== --}}
            <section class="min-w-0 space-y-3">

                {{-- Recherche + prestation libre + panier (mobile) --}}
                <div class="flex items-center gap-2">
                    <div class="relative min-w-0 flex-1">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                        <input type="text" x-ref="searchInput" x-model="search" autocomplete="off" enterkeyhint="search"
                               placeholder="Rechercher une prestation"
                               class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-9 text-sm text-slate-900 placeholder-slate-400 transition focus:border-slate-400 dark:focus:border-white/30 focus:outline-none focus:ring-2 focus:ring-black/5">
                        <button type="button" x-show="search" x-cloak @click="search = ''; $refs.searchInput.focus()"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-400 hover:text-slate-700" aria-label="Effacer la recherche">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <button type="button" @click="openCustom()"
                            class="inline-flex h-11 shrink-0 items-center gap-2 rounded-full bg-[#0B0F14] px-3 text-sm font-semibold text-white transition-colors hover:bg-black focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] sm:px-4"
                            aria-label="Saisir une prestation libre">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span class="hidden sm:inline">Saisie libre</span>
                    </button>

                    <button type="button" @click="cartOpen = true"
                            class="relative flex h-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-slate-700 transition hover:bg-slate-50 md:hidden"
                            aria-label="Ouvrir le ticket">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span x-show="cartCount > 0" x-cloak
                              class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#0B0F14] px-1 text-[11px] font-bold text-white"
                              x-text="cartCount"></span>
                    </button>
                </div>

                {{-- Catégories --}}
                <div class="no-scrollbar -mx-4 flex items-center gap-1.5 overflow-x-auto px-4 py-0.5 sm:mx-0 sm:px-0">
                    <button type="button" @click="selectedCategory = ''"
                            :class="selectedCategory === '' ? 'bg-[#0B0F14] text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                            class="h-8 shrink-0 whitespace-nowrap rounded-full border px-3 text-xs font-medium transition">
                        Toutes <span class="ml-1 opacity-60" x-text="countFor('')"></span>
                    </button>
                    @foreach($usedCategories as $key => $categoryLabel)
                        <button type="button" @click="selectedCategory = '{{ $key }}'"
                                :class="selectedCategory === '{{ $key }}' ? 'bg-[#0B0F14] text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                                class="h-8 shrink-0 whitespace-nowrap rounded-full border px-3 text-xs font-medium transition">
                            {{ $categoryLabel }} <span class="ml-1 opacity-60" x-text="countFor('{{ $key }}')"></span>
                        </button>
                    @endforeach
                </div>

                {{-- Grille des prestations --}}
                <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 sm:gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($activeServices as $service)
                        @php [$iconPath, $iconBg, $iconColor] = $categoryVisuals[$service->category] ?? $categoryVisuals['autre']; @endphp
                        <button type="button" @click="addToCart({{ $service->id }})"
                                x-show="isVisible({{ $service->id }})"
                                class="group relative flex touch-manipulation flex-col overflow-hidden rounded-xl border border-slate-200 bg-white text-left transition hover:border-slate-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] active:scale-[0.98]">

                            <div class="relative flex aspect-[4/3] items-center justify-center {{ $iconBg }}">
                                <svg class="h-10 w-10 {{ $iconColor }} transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/></svg>

                                <div x-show="qtyOf({{ $service->id }}) > 0" x-cloak
                                     class="absolute right-2 top-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#0B0F14] px-1.5 text-[11px] font-bold text-white shadow-md">
                                    <span x-text="qtyOf({{ $service->id }})"></span>
                                </div>
                            </div>

                            <div class="flex flex-1 flex-col justify-between gap-1.5 p-2.5 sm:p-3">
                                <div>
                                    <h4 class="line-clamp-2 text-xs font-semibold leading-snug text-slate-900 sm:text-sm">{{ $service->name }}</h4>
                                    <p class="mt-0.5 truncate text-[10px] text-slate-400 sm:text-xs">{{ $categories[$service->category] ?? 'Autre' }}</p>
                                </div>
                                <div class="flex items-end justify-between gap-1 border-t border-slate-100 pt-1.5">
                                    <span class="truncate text-xs font-bold text-slate-900 sm:text-sm">{{ number_format($service->price, 0, ',', ' ') }} F</span>
                                    <span class="shrink-0 text-[10px] font-medium text-slate-500 sm:text-xs">/ {{ $service->unit }}</span>
                                </div>
                            </div>
                        </button>
                    @endforeach

                    {{-- Carte : prestation libre --}}
                    <button type="button" @click="openCustom()" x-show="!search && !selectedCategory"
                            class="flex min-h-[180px] touch-manipulation flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white p-3 text-center text-slate-500 transition hover:border-slate-400 hover:text-[#0066FF] active:scale-[0.98]">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </span>
                        <span class="text-xs font-semibold sm:text-sm">Autre prestation</span>
                        <span class="text-[10px] text-slate-400 sm:text-xs">Saisie libre avec prix</span>
                    </button>
                </div>

                <div x-show="visibleCount === 0" x-cloak class="py-16 text-center">
                    <p class="text-sm font-medium text-slate-900">Aucune prestation trouvée</p>
                    <p class="mt-1 text-sm text-slate-500">Modifiez la recherche ou utilisez la <button type="button" @click="openCustom()" class="font-semibold text-[#0066FF] hover:underline">saisie libre</button>.</p>
                </div>

                {{-- Factures du jour --}}
                <div class="rounded-2xl border border-slate-200 bg-white">
                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 sm:px-5">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 sm:text-base">Ventes du jour</h2>
                            <p class="text-xs text-slate-500">{{ $canManage ? 'Toutes les ventes cyber' : 'Vos ventes' }} · {{ $todayInvoices->count() }} facture(s)</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[11px] text-slate-500">Encaissé</p>
                            <p class="text-base font-bold tabular-nums text-slate-900 sm:text-lg">{{ number_format($todayInvoices->where('status', '!=', 'annulee')->sum('paid_amount'), 0, ',', ' ') }} F</p>
                        </div>
                    </div>
                    @if($todayInvoices->isEmpty())
                        <p class="px-4 py-6 text-center text-sm text-slate-500">Aucune vente pour le moment.</p>
                    @else
                        <ul class="divide-y divide-slate-100">
                            @foreach($todayInvoices->take(8) as $invoice)
                                <li class="flex items-center gap-3 px-4 py-2.5 sm:px-5">
                                    <span class="w-11 shrink-0 text-xs tabular-nums text-slate-400">{{ $invoice->created_at->format('H:i') }}</span>
                                    <span class="min-w-0 flex-1 truncate font-mono text-xs font-medium text-slate-900">{{ $invoice->reference }}</span>
                                    @if($invoice->status === 'annulee')
                                        <span class="shrink-0 rounded-md bg-rose-50 px-1.5 py-0.5 text-[10px] font-semibold text-rose-700">Annulée</span>
                                    @elseif($invoice->status !== 'payee')
                                        <span class="shrink-0 rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">{{ $invoice->status === 'partielle' ? 'Partiel' : 'Impayé' }}</span>
                                    @endif
                                    <span class="shrink-0 text-sm font-bold tabular-nums {{ $invoice->status === 'annulee' ? 'text-slate-400 line-through' : 'text-slate-900' }}">{{ number_format($invoice->total, 0, ',', ' ') }} F</span>
                                    <a href="{{ route('commercial.invoices.print', $invoice) }}" class="shrink-0 text-xs font-semibold text-[#0066FF] hover:underline">Imprimer</a>
                                    @if($canManage && $invoice->status !== 'annulee')
                                        <button type="button" @click="openCancel({{ Js::from(['url' => route('commercial.cyber.invoices.cancel', $invoice), 'reference' => $invoice->reference, 'total' => (float) $invoice->total]) }})"
                                                class="shrink-0 text-xs font-semibold text-rose-600 hover:underline">Annuler</button>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            {{-- ===================== TICKET ===================== --}}
            <div x-show="cartOpen" x-cloak x-transition.opacity @click="cartOpen = false"
                 class="fixed inset-0 z-40 bg-[#0B0F14]/60 md:hidden"></div>

            <aside
                :class="cartOpen ? 'translate-y-0' : 'translate-y-full md:translate-y-0'"
                class="fixed inset-x-0 bottom-0 z-50 flex h-[92dvh] translate-y-full flex-col overflow-hidden rounded-t-3xl border border-slate-200 bg-white shadow-2xl transition-transform duration-300 ease-out
                       md:sticky md:top-4 md:z-auto md:h-auto md:max-h-[calc(100dvh-5.5rem)] md:translate-y-0 md:rounded-2xl md:shadow-none"
                aria-label="Ticket">

                <div class="flex shrink-0 justify-center bg-white pb-1 pt-2.5 md:hidden" @click="cartOpen = false">
                    <div class="h-1.5 w-10 rounded-full bg-slate-300"></div>
                </div>

                {{-- En-tête --}}
                <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-4 py-3 sm:px-5">
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-bold text-slate-900 sm:text-base">Ticket</h2>
                        <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full px-2 text-xs font-bold transition-colors"
                              :class="cartCount > 0 ? 'bg-[#0B0F14] text-white' : 'bg-slate-100 text-slate-500'"
                              x-text="cartCount"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="clearCart()" x-show="cart.length > 0" x-cloak
                                class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-500 transition-colors hover:bg-rose-50 hover:text-rose-600">Vider</button>
                        <button type="button" @click="cartOpen = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 md:hidden" aria-label="Fermer le ticket">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <form action="{{ route('commercial.cyber.checkout') }}" method="POST" @submit="submitting = true" class="flex min-h-0 flex-1 flex-col">
                    @csrf

                    <div class="min-h-0 flex-1 divide-y divide-slate-100 overflow-y-auto overscroll-contain">

                        {{-- Client --}}
                        <div class="p-4 sm:p-5">
                            <div class="mb-1.5 flex items-center justify-between">
                                <label for="customer_id" class="text-xs font-semibold text-slate-700 sm:text-sm">Client</label>
                                <a href="{{ route('commercial.customers.create') }}" target="_blank" class="text-xs font-medium text-[#0066FF] hover:underline sm:text-sm">Nouveau client</a>
                            </div>
                            <select id="customer_id" name="customer_id"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-900 transition focus:border-slate-400 dark:focus:border-white/30 focus:outline-none focus:ring-2 focus:ring-black/5 sm:h-11 sm:text-sm">
                                <option value="">Client comptoir (par défaut)</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }} ({{ $customer->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Agent qui a réalisé la prestation (le caissier est l'utilisateur connecté) --}}
                        @if($agents->isNotEmpty())
                            <div class="p-4 sm:p-5">
                                <label for="performed_by_employee_id" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Réalisé par</label>
                                <select id="performed_by_employee_id" name="performed_by_employee_id"
                                        class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-900 transition focus:border-slate-400 dark:focus:border-white/30 focus:outline-none focus:ring-2 focus:ring-black/5 sm:h-11 sm:text-sm">
                                    <option value="">— Par défaut : la personne connectée —</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" @selected((int) old('performed_by_employee_id', $defaultAgentId) === $agent->id)>{{ $agent->full_name }}{{ $agent->position ? ' · '.$agent->position : '' }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-[11px] text-slate-400">Le caissier enregistré est {{ auth()->user()->name }}.</p>
                            </div>
                        @endif

                        {{-- Prestations --}}
                        <div class="p-4 sm:p-5">
                            <template x-if="cart.length === 0">
                                <div class="py-8 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-slate-900">Le ticket est vide</p>
                                    <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Touchez une prestation ou faites une saisie libre.</p>
                                </div>
                            </template>

                            <ul class="divide-y divide-slate-100" x-show="cart.length > 0">
                                <template x-for="(item, index) in cart" :key="item.key">
                                    <li class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-slate-200"
                                             :class="item.cyber_service_id ? 'bg-slate-50 text-slate-400' : 'bg-white text-slate-300'">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="item.cyber_service_id ? 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' : 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z'"/></svg>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-1.5">
                                                <p class="line-clamp-2 text-xs font-semibold leading-snug text-slate-900 sm:text-sm" x-text="item.name"></p>
                                                <button type="button" @click="cart.splice(index, 1)" class="-mr-1 shrink-0 rounded-md p-1 text-slate-400 transition-colors hover:text-rose-600" aria-label="Retirer la prestation">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <p class="text-[11px] text-slate-500" x-text="money(item.unit_price) + ' F / ' + item.unit"></p>

                                            <div class="mt-2 flex items-center justify-between gap-2">
                                                <div class="inline-flex items-center rounded-lg border border-slate-200 bg-white">
                                                    <button type="button" @click="updateQty(index, -1)" class="flex h-8 w-8 touch-manipulation items-center justify-center rounded-l-lg text-base font-bold text-slate-700 transition hover:bg-slate-100 active:scale-95" aria-label="Diminuer">−</button>
                                                    <input type="number" min="1" inputmode="numeric" x-model.number="item.quantity" @blur="if (!(item.quantity >= 1)) item.quantity = 1"
                                                           class="h-8 w-12 border-0 p-0 text-center text-xs font-bold tabular-nums text-slate-900 focus:ring-0 sm:text-sm" aria-label="Quantité">
                                                    <button type="button" @click="updateQty(index, 1)" class="flex h-8 w-8 touch-manipulation items-center justify-center rounded-r-lg text-base font-bold text-slate-700 transition hover:bg-slate-100 active:scale-95" aria-label="Augmenter">+</button>
                                                </div>
                                                <span class="shrink-0 text-xs font-bold tabular-nums text-slate-900 sm:text-sm" x-text="money(item.quantity * item.unit_price) + ' F'"></span>
                                            </div>
                                        </div>

                                        <input type="hidden" :name="'items[' + index + '][cyber_service_id]'" :value="item.cyber_service_id ?? ''">
                                        <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                                        <template x-if="!item.cyber_service_id">
                                            <span>
                                                <input type="hidden" :name="'items[' + index + '][description]'" :value="item.name">
                                                <input type="hidden" :name="'items[' + index + '][unit_price]'" :value="item.unit_price">
                                            </span>
                                        </template>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        {{-- Règlement --}}
                        <div class="space-y-3.5 p-4 sm:p-5">
                            <p class="text-xs font-semibold text-slate-700 sm:text-sm">Mode de règlement</p>

                            <div class="grid grid-cols-4 gap-1.5 sm:gap-2">
                                @foreach($paymentMethods as $value => [$methodLabel, $icon])
                                    <label class="relative cursor-pointer select-none">
                                        <input type="radio" name="payment_method" value="{{ $value }}" x-model="paymentMethod" class="peer sr-only">
                                        <span class="flex h-16 flex-col items-center justify-center gap-1 rounded-xl border border-slate-200 bg-white px-1 text-center text-[11px] font-semibold text-slate-600 transition hover:border-slate-300 peer-checked:border-[#0B0F14] dark:peer-checked:border-white peer-checked:bg-[#0B0F14]/5 peer-checked:text-[#0B0F14] dark:peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-[#0066FF] sm:text-xs">
                                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                                            <span class="block w-full truncate">{{ $methodLabel }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            {{-- Mobile Money --}}
                            <div x-show="paymentMethod === 'mobile_money'" x-cloak x-transition.opacity class="space-y-2">
                                <input type="hidden" name="mobile_money_provider" :value="paymentMethod === 'mobile_money' ? mobileMoneyProvider : ''">
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="mobileMoneyProvider = 'wave'"
                                            :class="mobileMoneyProvider === 'wave' ? 'border-[#1DC4FF] bg-sky-50 ring-2 ring-[#1DC4FF]/30' : 'border-slate-200 hover:border-slate-300'"
                                            class="flex items-center gap-2.5 rounded-xl border bg-white p-2.5 text-left transition">
                                        <img src="{{ asset('images/payments/wave.png') }}" alt="" class="h-8 w-8 shrink-0 rounded-lg object-contain">
                                        <span class="min-w-0">
                                            <span class="block text-xs font-bold leading-tight text-slate-900 sm:text-sm">Wave</span>
                                            <span class="block truncate text-[10px] text-slate-500 sm:text-xs">QR code</span>
                                        </span>
                                    </button>
                                    <button type="button" @click="mobileMoneyProvider = 'orange_money'"
                                            :class="mobileMoneyProvider === 'orange_money' ? 'border-[#FF7900] bg-orange-50 ring-2 ring-[#FF7900]/30' : 'border-slate-200 hover:border-slate-300'"
                                            class="flex items-center gap-2.5 rounded-xl border bg-white p-2.5 text-left transition">
                                        <img src="{{ asset('images/payments/orange-money.png') }}" alt="" class="h-8 w-8 shrink-0 rounded-lg object-contain">
                                        <span class="min-w-0">
                                            <span class="block text-xs font-bold leading-tight text-slate-900 sm:text-sm">Orange</span>
                                            <span class="block truncate text-[10px] text-slate-500 sm:text-xs">QR / #144#</span>
                                        </span>
                                    </button>
                                </div>
                            </div>

                            {{-- Espèces --}}
                            <template x-if="paymentMethod === 'especes'">
                                <div class="space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <label for="amount_paid" class="text-xs font-semibold text-slate-700 sm:text-sm">Montant reçu</label>
                                        <button type="button" @click="amountPaid = total" class="text-xs font-semibold text-[#0066FF] hover:underline sm:text-sm">Montant exact</button>
                                    </div>
                                    <div class="relative">
                                        <input id="amount_paid" type="number" inputmode="numeric" min="0" x-model.number="amountPaid"
                                               class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-3 pr-16 text-base font-bold tabular-nums text-slate-900 transition focus:border-slate-400 dark:focus:border-white/30 focus:outline-none focus:ring-2 focus:ring-black/5 sm:h-12 sm:text-lg">
                                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400 sm:text-sm">FCFA</span>
                                    </div>

                                    <div class="flex flex-wrap gap-1.5">
                                        <button type="button" @click="amountPaid = total"
                                                :class="amountPaid === total ? 'bg-[#0B0F14] text-white border-[#0B0F14]' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200'"
                                                class="rounded-lg border px-2.5 py-1 text-[11px] font-semibold transition active:scale-95">Exact</button>
                                        @foreach($cashPresets as $preset)
                                            <button type="button" @click="amountPaid = {{ $preset }}"
                                                    class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-50 active:scale-95">
                                                {{ number_format($preset, 0, ',', ' ') }} F
                                            </button>
                                        @endforeach
                                    </div>

                                    <div x-show="amountPaid > total" x-cloak class="flex items-center justify-between rounded-xl bg-emerald-50 px-3 py-2 text-xs text-emerald-900 sm:text-sm">
                                        <span class="font-medium">Monnaie à rendre</span>
                                        <span class="font-bold tabular-nums text-emerald-700" x-text="money(amountPaid - total) + ' FCFA'"></span>
                                    </div>
                                    <div x-show="amountPaid < total" x-cloak class="flex items-center justify-between rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-900 sm:text-sm">
                                        <span class="font-medium">Reste dû sur la facture</span>
                                        <span class="font-bold tabular-nums text-amber-700" x-text="money(total - amountPaid) + ' FCFA'"></span>
                                    </div>
                                </div>
                            </template>

                            <input type="hidden" name="amount_paid" :value="paymentMethod === 'credit' ? 0 : (paymentMethod === 'especes' ? Math.min(amountPaid || 0, total) : total)">

                            <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Note (facultatif)" aria-label="Note"
                                   class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-900 transition focus:border-slate-400 dark:focus:border-white/30 focus:outline-none focus:ring-2 focus:ring-black/5 sm:text-sm">
                        </div>
                    </div>

                    {{-- Pied : total + validation --}}
                    <div class="shrink-0 space-y-3 border-t border-slate-200 bg-white p-4 pb-[max(1rem,env(safe-area-inset-bottom))] sm:p-5 sm:pb-[max(1.25rem,env(safe-area-inset-bottom))]">
                        <dl class="space-y-1.5 text-xs sm:text-sm">
                            <div class="flex justify-between text-slate-500">
                                <dt>Prestations</dt>
                                <dd class="font-medium tabular-nums text-slate-700" x-text="cartCount + ' unité(s)'"></dd>
                            </div>
                            <div class="flex items-baseline justify-between border-t border-slate-100 pt-2">
                                <dt class="text-sm font-bold text-slate-900 sm:text-base">Total à payer</dt>
                                <dd class="text-lg font-bold tabular-nums text-slate-900 sm:text-xl" x-text="money(total) + ' FCFA'"></dd>
                            </div>
                        </dl>

                        <button type="submit" :disabled="cart.length === 0 || total <= 0 || submitting"
                                class="h-11 w-full rounded-full bg-[#0B0F14] text-sm font-bold text-white transition hover:bg-black focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400 sm:h-12">
                            <span x-text="submitting ? 'Enregistrement…' : 'Valider et générer la facture'"></span>
                        </button>
                    </div>
                </form>
            </aside>
        </div>

        {{-- Barre ticket fixe (mobile) --}}
        <div x-show="!cartOpen" x-cloak
             class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] backdrop-blur md:hidden">
            <button type="button" @click="cartOpen = true"
                    class="flex h-12 w-full items-center justify-between rounded-full bg-[#0B0F14] px-4 text-sm font-bold text-white transition hover:bg-black active:scale-[0.99]">
                <span class="flex items-center gap-2.5">
                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-white/25 px-1.5 text-xs font-bold" x-text="cartCount"></span>
                    <span>Voir le ticket</span>
                </span>
                <span class="tabular-nums" x-text="money(total) + ' FCFA'"></span>
            </button>
        </div>

        {{-- ===================== SAISIE LIBRE ===================== --}}
        <div x-show="customOpen" x-cloak x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="custom-title"
             class="fixed inset-0 z-[60] flex items-end justify-center bg-[#0B0F14]/60 sm:items-center sm:p-4">
            <form @submit.prevent="addCustom()" @click.outside="customOpen = false"
                  class="w-full max-w-md space-y-4 rounded-t-3xl bg-white p-5 pb-[max(1.25rem,env(safe-area-inset-bottom))] shadow-2xl sm:rounded-2xl sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 id="custom-title" class="text-base font-bold text-slate-900">Autre prestation</h3>
                        <p class="text-xs text-slate-500">Décrivez le travail réalisé et indiquez son prix.</p>
                    </div>
                    <button type="button" @click="customOpen = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Fermer">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Description</span>
                    <input type="text" x-ref="customDescription" x-model="custom.description" required placeholder="Ex : mise en page d'un CV" class="{{ $field }}">
                </label>
                <div class="grid grid-cols-[100px_minmax(0,1fr)] gap-3">
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Quantité</span>
                        <input type="number" min="1" inputmode="numeric" x-model.number="custom.quantity" required class="{{ $field }} tabular-nums">
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Prix unitaire</span>
                        <span class="relative block">
                            <input type="number" min="1" inputmode="numeric" x-model.number="custom.unit_price" required class="{{ $field }} pr-16 font-bold tabular-nums">
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400">FCFA</span>
                        </span>
                    </label>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2.5 text-sm">
                    <span class="text-slate-500">Montant</span>
                    <span class="font-bold tabular-nums text-slate-900" x-text="money((custom.quantity || 0) * (custom.unit_price || 0)) + ' FCFA'"></span>
                </div>

                <button type="submit" class="h-11 w-full rounded-full bg-[#0B0F14] text-sm font-bold text-white transition hover:bg-black sm:h-12">Ajouter au ticket</button>
            </form>
        </div>

        {{-- ===================== ANNULATION ===================== --}}
        @if($canManage)
            <div x-show="cancelTarget" x-cloak x-transition.opacity role="alertdialog" aria-modal="true" aria-labelledby="cancel-title"
                 class="fixed inset-0 z-[60] flex items-end justify-center bg-[#0B0F14]/60 sm:items-center sm:p-4">
                <form method="POST" :action="cancelTarget?.url" @click.outside="cancelTarget = null"
                      class="w-full max-w-md space-y-4 rounded-t-3xl bg-white p-5 pb-[max(1.25rem,env(safe-area-inset-bottom))] shadow-2xl sm:rounded-2xl sm:p-6">
                    @csrf
                    <div>
                        <h3 id="cancel-title" class="text-base font-bold text-slate-900">Annuler la facture</h3>
                        <p class="mt-1 text-sm text-slate-600">
                            <span class="font-mono font-semibold text-slate-900" x-text="cancelTarget?.reference"></span>
                            · <span class="font-semibold tabular-nums" x-text="money(cancelTarget?.total) + ' FCFA'"></span>
                        </p>
                    </div>
                    <p class="rounded-xl bg-amber-50 px-3 py-2.5 text-xs text-amber-900">
                        La facture et ses paiements seront marqués comme annulés. Action définitive, possible jusqu'à {{ \App\Models\Order::CANCELLATION_WINDOW_DAYS }} jours après la vente.
                    </p>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Motif d'annulation</span>
                        <textarea name="cancellation_reason" x-ref="cancelReason" rows="3" required minlength="5" maxlength="500"
                                  placeholder="Ex : erreur de saisie, client remboursé…"
                                  class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-slate-400 dark:focus:border-white/30 focus:outline-none focus:ring-2 focus:ring-black/5"></textarea>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="cancelTarget = null" class="h-11 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50">Retour</button>
                        <button type="submit" class="h-11 rounded-xl bg-rose-600 text-sm font-bold text-white hover:bg-rose-700">Annuler la facture</button>
                    </div>
                </form>
            </div>
        @endif

        {{-- ===================== TARIFS (responsables) ===================== --}}
        @if($canManage)
            <div x-show="catalogOpen" x-cloak x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="catalog-title"
                 class="fixed inset-0 z-[60] flex items-end justify-center bg-[#0B0F14]/60 sm:items-center sm:p-4">
                <div @click.outside="catalogOpen = false" class="flex max-h-[92dvh] w-full max-w-3xl flex-col overflow-hidden rounded-t-3xl bg-white shadow-2xl sm:rounded-2xl">
                    <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-5 py-4">
                        <div>
                            <h3 id="catalog-title" class="text-base font-bold text-slate-900">Tarifs du cyber</h3>
                            <p class="text-xs text-slate-500">Chaque ligne s'enregistre séparément.</p>
                        </div>
                        <button type="button" @click="catalogOpen = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Fermer">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="min-h-0 flex-1 space-y-2 overflow-y-auto p-4 sm:p-5">
                        @foreach($services as $service)
                            <form method="POST" action="{{ route('commercial.cyber.services.update', $service) }}"
                                  class="grid grid-cols-2 items-center gap-2 rounded-xl border border-slate-200 p-3 sm:grid-cols-[minmax(0,1fr)_130px_90px_110px_auto] {{ $service->is_active ? '' : 'bg-slate-50' }}">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $service->name }}" required aria-label="Nom" class="{{ $field }} col-span-2 sm:col-span-1">
                                <select name="category" aria-label="Catégorie" class="{{ $field }}">
                                    @foreach($categories as $key => $categoryLabel)
                                        <option value="{{ $key }}" @selected($service->category === $key)>{{ $categoryLabel }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="unit" value="{{ $service->unit }}" required aria-label="Unité" class="{{ $field }}">
                                <input type="number" name="price" value="{{ (float) $service->price }}" min="0" required aria-label="Prix en FCFA" class="{{ $field }} font-bold tabular-nums">
                                <div class="flex items-center justify-end gap-2">
                                    <label class="mr-auto flex items-center gap-1.5 text-xs font-medium text-slate-600 sm:mr-0">
                                        <input type="checkbox" name="is_active" value="1" @checked($service->is_active) class="h-4 w-4 rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]"> Actif
                                    </label>
                                    <button type="submit" class="rounded-full bg-[#0B0F14] px-3 py-2 text-xs font-semibold text-white hover:bg-black">Enregistrer</button>
                                    <button type="submit" form="delete-service-{{ $service->id }}" class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600" aria-label="Supprimer {{ $service->name }}">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
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
                          class="grid shrink-0 grid-cols-2 items-end gap-2 border-t border-slate-200 bg-slate-50 p-4 pb-[max(1rem,env(safe-area-inset-bottom))] sm:grid-cols-[minmax(0,1fr)_130px_90px_110px_auto] sm:px-5">
                        @csrf
                        <input type="hidden" name="is_active" value="1">
                        <label class="col-span-2 sm:col-span-1">
                            <span class="mb-1 block text-xs font-semibold text-slate-700">Nouvelle prestation</span>
                            <input type="text" name="name" required placeholder="Ex : Impression A5" class="{{ $field }}">
                        </label>
                        <label>
                            <span class="mb-1 block text-xs font-semibold text-slate-700">Catégorie</span>
                            <select name="category" class="{{ $field }}">
                                @foreach($categories as $key => $categoryLabel)
                                    <option value="{{ $key }}">{{ $categoryLabel }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span class="mb-1 block text-xs font-semibold text-slate-700">Unité</span>
                            <input type="text" name="unit" value="page" required class="{{ $field }}">
                        </label>
                        <label>
                            <span class="mb-1 block text-xs font-semibold text-slate-700">Prix (F)</span>
                            <input type="number" name="price" min="0" required class="{{ $field }} font-bold tabular-nums">
                        </label>
                        <button type="submit" class="col-span-2 h-10 rounded-full bg-[#0B0F14] px-4 text-sm font-bold text-white hover:bg-black sm:col-span-1 sm:h-11">Ajouter</button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <script>
        function cyberDesk(config) {
            let nextKey = 1;

            return {
                services: config.services,
                cart: [],
                search: '',
                selectedCategory: '',
                cartOpen: false,
                customOpen: false,
                catalogOpen: config.openCatalog,
                cancelTarget: null,
                custom: { description: '', quantity: 1, unit_price: null },
                paymentMethod: 'especes',
                mobileMoneyProvider: 'wave',
                amountPaid: 0,
                submitting: false,

                get total() {
                    return this.cart.reduce((sum, item) => sum + (Number(item.quantity) || 0) * item.unit_price, 0);
                },

                get cartCount() {
                    return this.cart.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0);
                },

                get visibleCount() {
                    return this.services.filter(s => this.isVisible(s.id)).length;
                },

                init() {
                    this.$watch('total', (value) => { this.amountPaid = value; });
                },

                isVisible(id) {
                    const service = this.services.find(s => s.id === id);
                    const term = this.search.trim().toLowerCase();
                    return (!this.selectedCategory || service.category === this.selectedCategory)
                        && (!term || service.name.toLowerCase().includes(term));
                },

                countFor(category) {
                    return this.services.filter(s => !category || s.category === category).length;
                },

                qtyOf(id) {
                    const item = this.cart.find(i => i.cyber_service_id === id);
                    return item ? item.quantity : 0;
                },

                addToCart(id) {
                    const existing = this.cart.find(i => i.cyber_service_id === id);
                    if (existing) { existing.quantity++; return; }
                    const service = this.services.find(s => s.id === id);
                    this.cart.push({ key: nextKey++, cyber_service_id: id, name: service.name, unit: service.unit, unit_price: Number(service.price), quantity: 1 });
                },

                updateQty(index, delta) {
                    const item = this.cart[index];
                    item.quantity = (Number(item.quantity) || 1) + delta;
                    if (item.quantity <= 0) this.cart.splice(index, 1);
                },

                clearCart() {
                    this.cart = [];
                    this.amountPaid = 0;
                },

                openCancel(target) {
                    this.cancelTarget = target;
                    this.$nextTick(() => this.$refs.cancelReason?.focus());
                },

                openCustom() {
                    this.customOpen = true;
                    this.$nextTick(() => this.$refs.customDescription?.focus());
                },

                addCustom() {
                    const name = this.custom.description.trim();
                    const price = Number(this.custom.unit_price);
                    if (!name || !(price > 0)) return;
                    this.cart.push({ key: nextKey++, cyber_service_id: null, name, unit: 'unité', unit_price: price, quantity: Math.max(1, Number(this.custom.quantity) || 1) });
                    this.custom = { description: '', quantity: 1, unit_price: null };
                    this.customOpen = false;
                },

                money(value) {
                    return Math.round(value || 0).toLocaleString('fr-FR');
                },

                // Dépôt reçu par QR code : une ligne d'impression, quantité = pages × exemplaires
                addDeposit(deposit, serviceId) {
                    const service = this.services.find(s => s.id === Number(serviceId));
                    if (!service) return;
                    this.cart.push({
                        key: nextKey++,
                        cyber_service_id: service.id,
                        name: service.name + ' · dépôt ' + deposit.code,
                        unit: service.unit,
                        unit_price: Number(service.price),
                        quantity: Math.max(1, Number(deposit.total_pages) || 1),
                    });
                },
            };
        }

        function cyberDeposits(config) {
            return {
                url: config.url,
                printServices: config.printServices,
                deposits: [],
                newIds: [],
                open: true,
                showDone: false,
                lastRefresh: null,
                timer: null,

                get visible() {
                    return this.deposits.filter(d => this.showDone || d.status === 'recu' || d.status === 'imprime');
                },

                get waitingCount() {
                    return this.deposits.filter(d => d.status === 'recu').length;
                },

                init() {
                    this.refresh();
                    this.timer = setInterval(() => this.refresh(), 15000);
                },

                destroy() {
                    clearInterval(this.timer);
                },

                defaultService(deposit) {
                    const pattern = deposit.color ? /couleur/i : /(noir|n\s*&\s*b|\bnb\b)/i;
                    return (this.printServices.find(s => pattern.test(s.name)) || this.printServices[0] || {}).id ?? null;
                },

                async refresh() {
                    try {
                        const response = await fetch(this.url, { headers: { Accept: 'application/json' } });
                        if (!response.ok) return;
                        const data = await response.json();
                        const known = this.deposits.map(d => d.id);
                        const previous = Object.fromEntries(this.deposits.map(d => [d.id, d.service_id]));
                        this.newIds = known.length ? data.deposits.filter(d => !known.includes(d.id)).map(d => d.id) : [];
                        this.deposits = data.deposits.map(d => ({ ...d, service_id: previous[d.id] ?? this.defaultService(d) }));
                        this.lastRefresh = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                    } catch (e) {}
                },

                async setStatus(deposit, status) {
                    const response = await fetch(deposit.status_url, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ status }),
                    });
                    if (response.ok) {
                        await this.refresh();
                    }
                },

                statusClass(status) {
                    return { recu: 'bg-blue-50 text-[#0066FF]', imprime: 'bg-amber-50 text-amber-700', retire: 'bg-emerald-50 text-emerald-700', annule: 'bg-slate-100 text-slate-600' }[status] || '';
                },
            };
        }
    </script>
</x-layouts.app>
