@php
    $printServices = $activeServices->where('category', 'impression')->values()->map->only(['id', 'name', 'price']);
@endphp

{{-- Documents déposés par les clients via le QR code (rafraîchi toutes les 15 secondes) --}}
<section x-data="cyberDeposits({ url: @js(route('commercial.cyber.deposits.index')), printServices: @js($printServices) })"
         class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white">
    <header class="flex flex-wrap items-center gap-3 p-4">
        <button type="button" @click="open = ! open" class="flex min-w-0 flex-1 items-center gap-3 text-left" :aria-expanded="open">
            <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-[#0066FF]">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                <span x-show="waitingCount > 0" x-cloak class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white" x-text="waitingCount"></span>
            </span>
            <span class="min-w-0">
                <span class="block text-sm font-semibold text-[#0B0F14]">Documents reçus</span>
                <span class="block truncate text-xs text-[#64748B]" x-text="waitingCount > 0 ? waitingCount + ' dépôt(s) à imprimer' : 'Aucun document en attente · les clients envoient via le QR code'"></span>
            </span>
            <svg class="ml-auto h-4 w-4 shrink-0 text-[#64748B] transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <a href="{{ route('commercial.cyber.qr') }}" class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-[#E2E8F0] px-3 text-xs font-semibold text-[#0B0F14] transition hover:bg-[#F5F7FA]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h2v2h-2zM18 18h2v2h-2zM14 18h2M18 14h2"/></svg>
            QR code
        </a>
    </header>

    <div x-show="open" x-collapse class="border-t border-[#E2E8F0]">
        <div class="flex items-center justify-between gap-3 px-4 py-2 text-[11px] text-[#64748B]">
            <label class="flex cursor-pointer items-center gap-2">
                <input type="checkbox" x-model="showDone" class="h-3.5 w-3.5 rounded border-[#E2E8F0] text-[#0066FF]">
                Afficher les dépôts traités du jour
            </label>
            <span x-text="lastRefresh ? 'Mis à jour à ' + lastRefresh : 'Chargement…'"></span>
        </div>

        <p x-show="visible.length === 0" class="px-4 pb-6 pt-2 text-center text-xs text-[#64748B]">Aucun dépôt à afficher pour le moment.</p>

        <ul class="divide-y divide-[#E2E8F0]">
            <template x-for="deposit in visible" :key="deposit.id">
                <li class="p-4" :class="newIds.includes(deposit.id) && 'bg-blue-50'">
                    <div class="flex flex-wrap items-start gap-3">
                        <span class="rounded-full bg-[#0B0F14] px-3 py-1.5 font-mono text-base font-extrabold tracking-[0.2em] text-white" x-text="deposit.code"></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-[#0B0F14]" x-text="deposit.customer_name"></p>
                            <p class="text-[11px] text-[#64748B]">
                                <span x-text="'Reçu ' + deposit.received_ago"></span>
                                <template x-if="deposit.customer_phone"><span x-text="' · ' + deposit.customer_phone"></span></template>
                            </p>
                            <div class="mt-1.5 flex flex-wrap gap-1.5 text-[10px] font-semibold">
                                <span class="rounded-full px-2 py-0.5" :class="deposit.color ? 'bg-violet-50 text-violet-700' : 'bg-slate-100 text-slate-700'" x-text="deposit.color ? 'Couleur' : 'Noir & blanc'"></span>
                                <span x-show="deposit.duplex" class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">Recto-verso</span>
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-700" x-text="deposit.copies + ' ex.'"></span>
                                <span class="rounded-full px-2 py-0.5" :class="statusClass(deposit.status)" x-text="deposit.status_label"></span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-[#0B0F14]" x-text="deposit.total_pages ?? '?'"></p>
                            <p class="text-[10px] text-[#64748B]" x-text="deposit.total_pages === null ? 'pages à compter' : 'pages à imprimer'"></p>
                        </div>
                    </div>

                    <p x-show="deposit.notes" class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800" x-text="deposit.notes"></p>

                    <ul class="mt-3 space-y-1.5">
                        <template x-for="file in deposit.files" :key="file.id">
                            <li class="flex items-center gap-2 rounded-lg border border-[#E2E8F0] px-3 py-2 text-xs">
                                <span class="shrink-0 rounded bg-[#F5F7FA] px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase text-[#64748B]" x-text="file.extension"></span>
                                <a :href="file.download_url" target="_blank" class="min-w-0 flex-1 truncate font-medium text-[#0B0F14] hover:text-[#0066FF] hover:underline" x-text="file.name"></a>
                                <span class="shrink-0 font-semibold" :class="file.pages === null ? 'text-amber-700' : 'text-[#0B0F14]'"
                                      x-text="file.pages === null ? 'pages ?' : (file.estimated ? '≈ ' : '') + file.pages + (file.pages > 1 ? ' pages' : ' page')"
                                      :title="file.estimated ? 'Estimation à partir des lignes du tableur' : ''"></span>
                            </li>
                        </template>
                    </ul>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <template x-if="printServices.length && deposit.status !== 'annule'">
                            <div class="flex w-full min-w-0 items-center gap-2 sm:w-auto sm:flex-1">
                                <select x-model.number="deposit.service_id" class="h-9 min-w-0 flex-1 rounded-full border border-[#E2E8F0] bg-white px-3 text-xs text-[#0B0F14]" aria-label="Prestation d'impression">
                                    <template x-for="service in printServices" :key="service.id">
                                        <option :value="service.id" x-text="service.name + ' — ' + Math.round(service.price) + ' F'" :selected="service.id === deposit.service_id"></option>
                                    </template>
                                </select>
                                <button type="button" @click="addDeposit(deposit, deposit.service_id); cartOpen = true"
                                        class="h-9 shrink-0 rounded-full bg-[#0B0F14] px-3 text-xs font-semibold text-white transition hover:bg-black">Ajouter au panier</button>
                            </div>
                        </template>
                        <button type="button" x-show="deposit.status === 'recu'" @click="setStatus(deposit, 'imprime')" class="h-9 rounded-full border border-[#E2E8F0] px-3 text-xs font-semibold text-[#0B0F14] transition hover:bg-[#F5F7FA]">Imprimé</button>
                        <button type="button" x-show="deposit.status === 'recu' || deposit.status === 'imprime'" @click="setStatus(deposit, 'retire')" class="h-9 rounded-full border border-emerald-200 px-3 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">Remis</button>
                        <button type="button" x-show="deposit.status === 'recu' || deposit.status === 'imprime'" @click="if (confirm('Annuler le dépôt ' + deposit.code + ' ?')) setStatus(deposit, 'annule')" class="h-9 rounded-full px-3 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">Annuler</button>
                    </div>
                </li>
            </template>
        </ul>
    </div>
</section>
