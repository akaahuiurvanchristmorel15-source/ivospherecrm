@php
    $paymentMethods = [
        'especes' => 'Espèces',
        'mobile_money' => 'Mobile Money',
        'carte' => 'Carte',
        'credit' => 'À crédit',
    ];
    $categoryColors = [
        'impression' => 'bg-blue-50 text-[#0066FF]',
        'photocopie' => 'bg-sky-50 text-sky-700',
        'photo' => 'bg-amber-50 text-amber-700',
        'bureautique' => 'bg-violet-50 text-violet-700',
        'finition' => 'bg-emerald-50 text-emerald-700',
        'internet' => 'bg-teal-50 text-teal-700',
        'autre' => 'bg-slate-100 text-slate-600',
    ];
    $activeServices = $services->where('is_active', true);
    $in = 'w-full rounded-xl border border-[#E2E8F0] bg-white px-3 py-2 text-base text-[#0B0F14] placeholder:text-slate-400 transition focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/20 sm:text-sm';
@endphp

<x-layouts.app>
    <x-slot:title>Cyber</x-slot>

    <div x-data="cyberDesk({ services: {{ Js::from($activeServices->map->only(['id', 'name', 'unit', 'price'])->values()) }} })"
         class="space-y-4 pb-24 md:pb-0">
        <x-page-header title="Cyber : prestations & facturation"
                       description="Choisissez les prestations réalisées ou saisissez-les librement. La facture est générée automatiquement à l'enregistrement.">
            <x-slot:actions>
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

        <div class="grid grid-cols-1 items-start gap-4 md:grid-cols-[minmax(0,1fr)_360px] lg:gap-6 xl:grid-cols-[minmax(0,1fr)_400px]">

            {{-- ═══ Catalogue des prestations ═══ --}}
            <section class="min-w-0 space-y-5">
                @forelse($activeServices->groupBy('category') as $category => $categoryServices)
                    <div>
                        <h2 class="mb-2 text-xs font-semibold uppercase tracking-wide text-[#64748B]">{{ \App\Models\CyberService::CATEGORIES[$category] ?? 'Autre' }}</h2>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-4">
                            @foreach($categoryServices as $service)
                                <button type="button" @click="add({{ $service->id }})"
                                        class="group relative flex min-h-[92px] flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-white p-3 text-left transition hover:border-[#0066FF] hover:shadow-sm active:scale-[0.98]">
                                    <span class="text-sm font-semibold leading-snug text-[#0B0F14]">{{ $service->name }}</span>
                                    <span class="mt-2 flex items-end justify-between gap-1">
                                        <span>
                                            <span class="block font-mono text-sm font-bold text-[#0B0F14]">{{ number_format($service->price, 0, ',', ' ') }} F</span>
                                            <span class="text-[11px] text-[#94A3B8]">/ {{ $service->unit }}</span>
                                        </span>
                                        <span class="rounded-lg px-2 py-0.5 text-[11px] font-semibold {{ $categoryColors[$category] ?? $categoryColors['autre'] }}"
                                              x-show="qtyOf({{ $service->id }}) > 0" x-cloak x-text="'× ' + qtyOf({{ $service->id }})"></span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <x-empty-state title="Aucune prestation active" description="Ajoutez des prestations au catalogue, ou utilisez la saisie libre." />
                @endforelse

                {{-- Saisie libre --}}
                <div class="rounded-2xl border border-dashed border-[#CBD5E1] bg-[#F5F7FA]/70 p-4">
                    <h2 class="text-sm font-semibold text-[#0B0F14]">Autre prestation (saisie libre)</h2>
                    <p class="mb-3 text-xs text-[#64748B]">Pour un travail hors catalogue : décrivez ce qui a été fait et indiquez le prix.</p>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_90px_130px_auto]">
                        <input type="text" x-model="custom.description" @keydown.enter.prevent="addCustom()" placeholder="Ex : Mise en page d'un CV" class="{{ $in }}" aria-label="Description">
                        <input type="number" x-model.number="custom.quantity" min="1" placeholder="Qté" class="{{ $in }} font-mono" aria-label="Quantité">
                        <input type="number" x-model.number="custom.unit_price" min="0" placeholder="Prix unitaire" @keydown.enter.prevent="addCustom()" class="{{ $in }} font-mono" aria-label="Prix unitaire (FCFA)">
                        <button type="button" @click="addCustom()" :disabled="!custom.description.trim() || !(custom.unit_price > 0)"
                                class="rounded-xl bg-[#0B0F14] px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40">Ajouter</button>
                    </div>
                </div>

                {{-- Factures du jour --}}
                <x-card title="Factures cyber du jour" :subtitle="$canManage ? 'Toutes les ventes cyber d\'aujourd\'hui' : 'Vos ventes d\'aujourd\'hui'">
                    @if($todayInvoices->isEmpty())
                        <p class="py-4 text-center text-sm text-[#64748B]">Aucune facture aujourd'hui.</p>
                    @else
                        <div class="mb-3 flex items-baseline justify-between rounded-xl bg-[#F5F7FA] px-4 py-3">
                            <span class="text-xs text-[#64748B]">{{ $todayInvoices->count() }} facture(s) · encaissé</span>
                            <span class="font-mono text-lg font-bold text-[#0B0F14]">{{ number_format($todayInvoices->sum('paid_amount'), 0, ',', ' ') }} FCFA</span>
                        </div>
                        <ul class="divide-y divide-[#E2E8F0]">
                            @foreach($todayInvoices as $invoice)
                                <li class="flex items-center justify-between gap-3 py-2.5 text-sm">
                                    <span class="min-w-0">
                                        <span class="block truncate font-mono text-xs font-semibold text-[#0B0F14]">{{ $invoice->reference }}</span>
                                        <span class="text-[11px] text-[#94A3B8]">{{ $invoice->created_at->format('H:i') }}</span>
                                    </span>
                                    <span class="flex items-center gap-3">
                                        <span class="font-mono text-sm font-semibold">{{ number_format($invoice->total, 0, ',', ' ') }} F</span>
                                        <a href="{{ route('commercial.invoices.print', $invoice) }}" class="text-xs font-semibold text-[#0066FF] hover:underline">Imprimer</a>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            </section>

            {{-- ═══ Ticket en cours ═══ --}}
            <aside class="md:sticky md:top-6">
                <form method="POST" action="{{ route('commercial.cyber.checkout') }}" @submit="submitting = true"
                      class="divide-y divide-[#E2E8F0] overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white">
                    @csrf

                    <div class="p-4">
                        <label for="customer_id" class="mb-1.5 block text-xs font-semibold text-[#0B0F14]">Client</label>
                        <select id="customer_id" name="customer_id" class="{{ $in }}">
                            <option value="">Client de passage</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }} ({{ $customer->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Lignes --}}
                    <div class="p-4">
                        <div class="mb-2 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-[#0B0F14]">Prestations réalisées</h2>
                            <button type="button" x-show="lines.length" x-cloak @click="lines = []" class="text-xs font-semibold text-rose-600 hover:underline">Vider</button>
                        </div>
                        <p x-show="!lines.length" class="rounded-xl bg-[#F5F7FA] py-6 text-center text-xs text-[#64748B]">Touchez une prestation pour l'ajouter.</p>
                        <ul class="space-y-2">
                            <template x-for="(line, index) in lines" :key="index">
                                <li class="rounded-xl border border-[#E2E8F0] p-2.5">
                                    <input type="hidden" :name="`items[${index}][cyber_service_id]`" :value="line.cyber_service_id ?? ''">
                                    <template x-if="!line.cyber_service_id">
                                        <span>
                                            <input type="hidden" :name="`items[${index}][description]`" :value="line.description">
                                            <input type="hidden" :name="`items[${index}][unit_price]`" :value="line.unit_price">
                                        </span>
                                    </template>
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="min-w-0 text-sm font-medium leading-snug text-[#0B0F14]" x-text="line.description"></span>
                                        <button type="button" @click="lines.splice(index, 1)" class="shrink-0 rounded-md p-0.5 text-slate-400 hover:text-rose-600" aria-label="Retirer">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between gap-2">
                                        <div class="flex items-center rounded-lg border border-[#E2E8F0]">
                                            <button type="button" @click="line.quantity > 1 ? line.quantity-- : lines.splice(index, 1)" class="px-2.5 py-1 text-slate-600 hover:bg-[#F5F7FA]" aria-label="Moins">−</button>
                                            <input type="number" min="1" :name="`items[${index}][quantity]`" x-model.number="line.quantity"
                                                   class="w-14 border-0 p-1 text-center font-mono text-sm focus:ring-0" aria-label="Quantité">
                                            <button type="button" @click="line.quantity++" class="px-2.5 py-1 text-slate-600 hover:bg-[#F5F7FA]" aria-label="Plus">+</button>
                                        </div>
                                        <span class="text-right">
                                            <span class="block font-mono text-sm font-bold" x-text="money(line.quantity * line.unit_price) + ' F'"></span>
                                            <span class="text-[11px] text-[#94A3B8]" x-text="money(line.unit_price) + ' F / ' + line.unit"></span>
                                        </span>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>

                    {{-- Règlement --}}
                    <div class="space-y-3 p-4">
                        <div class="flex items-baseline justify-between">
                            <span class="text-sm font-semibold text-[#0B0F14]">Total</span>
                            <span class="font-mono text-2xl font-bold text-[#0B0F14]" x-text="money(total) + ' FCFA'"></span>
                        </div>

                        <div class="grid grid-cols-2 gap-1.5">
                            @foreach($paymentMethods as $value => $label)
                                <label class="cursor-pointer select-none">
                                    <input type="radio" name="payment_method" value="{{ $value }}" x-model="paymentMethod" class="peer sr-only">
                                    <span class="block rounded-xl border border-[#E2E8F0] px-2 py-2 text-center text-xs font-semibold text-slate-600 transition peer-checked:border-[#0066FF] peer-checked:bg-[#0066FF]/5 peer-checked:text-[#0066FF]">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div x-show="paymentMethod === 'mobile_money'" x-cloak class="grid grid-cols-2 gap-1.5">
                            <input type="hidden" name="mobile_money_provider" :value="paymentMethod === 'mobile_money' ? provider : ''">
                            <button type="button" @click="provider = 'wave'" :class="provider === 'wave' ? 'border-[#1DC4FF] bg-sky-50' : 'border-[#E2E8F0]'" class="rounded-xl border px-2 py-2 text-xs font-semibold">Wave</button>
                            <button type="button" @click="provider = 'orange_money'" :class="provider === 'orange_money' ? 'border-[#FF7900] bg-orange-50' : 'border-[#E2E8F0]'" class="rounded-xl border px-2 py-2 text-xs font-semibold">Orange Money</button>
                        </div>

                        <div x-show="paymentMethod !== 'credit'">
                            <label for="amount_paid" class="mb-1 block text-xs font-semibold text-[#0B0F14]">Montant reçu</label>
                            <input id="amount_paid" type="number" min="0" x-model.number="amountPaid" @focus="amountTouched = true" class="{{ $in }} font-mono">
                            <p x-show="paymentMethod === 'especes' && amountPaid > total" x-cloak class="mt-1 text-xs font-semibold text-emerald-700"
                               x-text="'Monnaie à rendre : ' + money(amountPaid - total) + ' FCFA'"></p>
                        </div>
                        <input type="hidden" name="amount_paid" :value="paymentMethod === 'credit' ? 0 : Math.min(amountTouched ? amountPaid : total, total)">

                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Note (facultatif)" class="{{ $in }}">

                        <button type="submit" :disabled="!lines.length || total <= 0 || submitting"
                                class="w-full rounded-xl bg-[#0066FF] py-3 text-sm font-semibold text-white transition hover:bg-[#0052CC] disabled:cursor-not-allowed disabled:opacity-40">
                            <span x-text="submitting ? 'Enregistrement…' : 'Valider et générer la facture'"></span>
                        </button>
                    </div>
                </form>
            </aside>
        </div>

        {{-- ═══ Gestion du catalogue (responsables) ═══ --}}
        @if($canManage)
            <x-card title="Catalogue et tarifs du cyber" subtitle="Modifiez les prix, désactivez ou ajoutez des prestations.">
                <div class="space-y-2">
                    @foreach($services as $service)
                        <form method="POST" action="{{ route('commercial.cyber.services.update', $service) }}"
                              class="grid grid-cols-2 items-center gap-2 rounded-xl border border-[#E2E8F0] p-2.5 sm:grid-cols-[minmax(0,1fr)_150px_100px_110px_auto_auto] {{ $service->is_active ? '' : 'opacity-60' }}">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $service->name }}" required class="{{ $in }} col-span-2 sm:col-span-1" aria-label="Nom">
                            <select name="category" class="{{ $in }}" aria-label="Catégorie">
                                @foreach(\App\Models\CyberService::CATEGORIES as $key => $label)
                                    <option value="{{ $key }}" @selected($service->category === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="unit" value="{{ $service->unit }}" required class="{{ $in }}" aria-label="Unité">
                            <input type="number" name="price" value="{{ (float) $service->price }}" min="0" required class="{{ $in }} font-mono" aria-label="Prix (FCFA)">
                            <label class="flex items-center gap-1.5 text-xs text-slate-700">
                                <input type="checkbox" name="is_active" value="1" @checked($service->is_active) class="h-4 w-4 rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]"> Actif
                            </label>
                            <span class="col-span-2 flex justify-end gap-1.5 sm:col-span-1">
                                <button type="submit" class="rounded-lg bg-[#0B0F14] px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800">Enregistrer</button>
                                <button type="submit" form="delete-service-{{ $service->id }}" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Supprimer</button>
                            </span>
                        </form>
                        <form id="delete-service-{{ $service->id }}" method="POST" action="{{ route('commercial.cyber.services.destroy', $service) }}"
                              onsubmit="return confirm('Supprimer cette prestation du catalogue ?')" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    @endforeach

                    <form method="POST" action="{{ route('commercial.cyber.services.store') }}"
                          class="grid grid-cols-2 items-center gap-2 rounded-xl border border-dashed border-[#0066FF]/40 bg-[#0066FF]/[0.03] p-2.5 sm:grid-cols-[minmax(0,1fr)_150px_100px_110px_auto_auto]">
                        @csrf
                        <input type="text" name="name" required placeholder="Nouvelle prestation" class="{{ $in }} col-span-2 sm:col-span-1" aria-label="Nom">
                        <select name="category" class="{{ $in }}" aria-label="Catégorie">
                            @foreach(\App\Models\CyberService::CATEGORIES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="unit" value="page" required class="{{ $in }}" aria-label="Unité">
                        <input type="number" name="price" min="0" required placeholder="Prix" class="{{ $in }} font-mono" aria-label="Prix (FCFA)">
                        <input type="hidden" name="is_active" value="1">
                        <span class="hidden sm:block"></span>
                        <span class="col-span-2 flex justify-end sm:col-span-1">
                            <button type="submit" class="rounded-lg bg-[#0066FF] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#0052CC]">+ Ajouter</button>
                        </span>
                    </form>
                </div>
            </x-card>
        @endif
    </div>

    <script>
        function cyberDesk(config) {
            return {
                services: config.services,
                lines: [],
                custom: { description: '', quantity: 1, unit_price: null },
                paymentMethod: 'especes',
                provider: 'wave',
                amountPaid: 0,
                amountTouched: false,
                submitting: false,

                get total() {
                    return this.lines.reduce((sum, line) => sum + (Number(line.quantity) || 0) * line.unit_price, 0);
                },

                init() {
                    this.$watch('total', (value) => { if (!this.amountTouched) this.amountPaid = value; });
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
