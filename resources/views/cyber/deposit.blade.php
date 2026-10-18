@php
    $field = 'block w-full rounded-2xl border border-black/10 bg-white/75 px-4 py-3 text-base text-neutral-950 placeholder:text-black/30 transition focus:border-black/40 focus:outline-none focus:ring-4 focus:ring-black/5 sm:text-sm night:border-white/10 night:bg-white/[0.04] night:text-white night:placeholder:text-white/25 night:focus:border-white/40 night:focus:ring-white/10';
    $label = 'mb-1.5 block text-xs font-medium text-black/60 night:text-white/60';
    $error = 'mt-1.5 text-[11px] text-rose-600 night:text-rose-300';
    $deposit = session('deposit');
@endphp

<x-layouts.guest>
    <x-slot:title>Dépôt d'impression</x-slot>

    <div class="rounded-[32px] border border-black/[0.08] bg-gradient-to-b from-white to-white/40 p-2 shadow-xl shadow-black/5 night:border-white/10 night:from-white/[0.08] night:to-white/[0.02] night:shadow-black/50">
        <div class="rounded-[26px] border border-black/[0.06] bg-white/60 p-5 backdrop-blur-2xl sm:p-8 night:border-white/10 night:bg-white/[0.03]">
            @if($deposit)
                {{-- Confirmation : code de retrait --}}
                <div class="text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 night:text-emerald-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    </span>
                    <h1 class="mt-4 font-display text-2xl font-extrabold sm:text-3xl">Documents envoyés</h1>
                    <p class="mt-2 text-sm text-black/55 night:text-white/55">Présentez ce code au comptoir pour récupérer vos impressions.</p>
                    <p class="mx-auto mt-5 w-fit rounded-2xl border border-black/10 bg-white px-6 py-3 font-mono text-4xl font-extrabold tracking-[0.3em] text-neutral-950 night:border-white/15 night:bg-white/[0.06] night:text-white">{{ $deposit['code'] }}</p>
                </div>

                <ul class="mt-6 divide-y divide-black/[0.06] rounded-2xl border border-black/[0.08] text-sm night:divide-white/10 night:border-white/10">
                    @foreach($deposit['files'] as $file)
                        <li class="flex items-center justify-between gap-3 px-4 py-3">
                            <span class="min-w-0 truncate">{{ $file['name'] }}</span>
                            <span class="shrink-0 text-xs font-semibold text-black/60 night:text-white/60">
                                @if($file['pages'] === null)
                                    à compter au comptoir
                                @else
                                    {{ $file['estimated'] ? '≈ ' : '' }}{{ $file['pages'] }} {{ $file['pages'] > 1 ? 'pages' : 'page' }}
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
                @if($deposit['total_pages'] !== null)
                    <p class="mt-3 text-center text-xs text-black/55 night:text-white/55">
                        Total : <strong class="text-neutral-950 night:text-white">{{ $deposit['total_pages'] }} {{ $deposit['total_pages'] > 1 ? 'pages' : 'page' }}</strong>
                        @if($deposit['copies'] > 1) ({{ $deposit['copies'] }} exemplaires) @endif
                    </p>
                @endif

                <a href="{{ route('cyber.deposit.show', $token) }}" class="mt-7 flex h-12 w-full items-center justify-center rounded-full border border-black/15 text-sm font-semibold transition hover:border-black/40 night:border-white/15 night:hover:border-white/40">
                    Envoyer d'autres documents
                </a>
            @else
                <header class="mb-6">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 night:text-white/45">Cyber · {{ $spaceName }}</p>
                    <h1 class="mt-2 font-display text-3xl font-extrabold leading-[1.05] sm:text-4xl">Envoyez vos documents à imprimer</h1>
                    <p class="mt-2 text-sm leading-relaxed text-black/55 night:text-white/55">PDF, Word ou Excel. Le nombre de pages est compté automatiquement, puis présentez votre code au comptoir.</p>
                </header>

                <form method="POST" action="{{ route('cyber.deposit.store', $token) }}" enctype="multipart/form-data" class="space-y-5"
                      x-data="{
                          files: [],
                          color: {{ old('color') ? 'true' : 'false' }},
                          duplex: {{ old('duplex') ? 'true' : 'false' }},
                          copies: {{ (int) old('copies', 1) }},
                          sending: false,
                          pick(event) { this.files = Array.from(event.target.files).map(file => ({ name: file.name, size: file.size })); },
                          size(bytes) { return bytes > 1048576 ? (bytes / 1048576).toFixed(1) + ' Mo' : Math.max(1, Math.round(bytes / 1024)) + ' Ko'; }
                      }"
                      @submit="sending = true">
                    @csrf

                    {{-- Documents --}}
                    <div>
                        <label for="files" class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-black/15 px-4 py-7 text-center transition hover:border-black/35 night:border-white/15 night:hover:border-white/35">
                            <svg class="h-8 w-8 text-black/40 night:text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            <span class="text-sm font-semibold">Choisir les documents</span>
                            <span class="text-[11px] text-black/45 night:text-white/45">PDF, Word, Excel · {{ \App\Http\Controllers\CyberDepositController::MAX_FILES }} fichiers max · 20 Mo chacun</span>
                        </label>
                        <input id="files" type="file" name="files[]" multiple required class="sr-only" @change="pick($event)"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                        <ul x-show="files.length" x-cloak class="mt-3 space-y-1.5">
                            <template x-for="file in files" :key="file.name">
                                <li class="flex items-center justify-between gap-3 rounded-xl bg-black/[0.04] px-3 py-2 text-xs night:bg-white/[0.06]">
                                    <span class="min-w-0 truncate font-medium" x-text="file.name"></span>
                                    <span class="shrink-0 text-black/45 night:text-white/45" x-text="size(file.size)"></span>
                                </li>
                            </template>
                        </ul>
                        @error('files')<p class="{{ $error }}">{{ $message }}</p>@enderror
                        @foreach($errors->get('files.*') as $messages)
                            <p class="{{ $error }}">{{ $messages[0] }}</p>
                        @endforeach
                    </div>

                    {{-- Options d'impression --}}
                    <div class="grid grid-cols-2 gap-2">
                        <input type="hidden" name="color" :value="color ? 1 : 0">
                        <button type="button" @click="color = false" :class="! color ? 'border-neutral-950 bg-neutral-950 text-white night:border-white night:bg-white night:text-black' : 'border-black/10 night:border-white/15'"
                                class="h-11 rounded-2xl border text-sm font-semibold transition">Noir &amp; blanc</button>
                        <button type="button" @click="color = true" :class="color ? 'border-neutral-950 bg-neutral-950 text-white night:border-white night:bg-white night:text-black' : 'border-black/10 night:border-white/15'"
                                class="h-11 rounded-2xl border text-sm font-semibold transition">Couleur</button>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <label class="flex cursor-pointer items-center gap-2.5 text-sm">
                            <input type="hidden" name="duplex" :value="duplex ? 1 : 0">
                            <input type="checkbox" x-model="duplex" class="h-4 w-4 rounded border-black/20 text-neutral-950 night:border-white/20 night:bg-white/5">
                            Recto-verso
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-black/55 night:text-white/55">Exemplaires</span>
                            <div class="flex items-center rounded-full border border-black/10 night:border-white/15">
                                <button type="button" @click="copies = Math.max(1, copies - 1)" class="h-9 w-9 text-lg" aria-label="Moins">−</button>
                                <input type="number" name="copies" x-model.number="copies" min="1" max="100" class="w-10 border-0 bg-transparent p-0 text-center text-sm font-semibold focus:ring-0" aria-label="Nombre d'exemplaires">
                                <button type="button" @click="copies = Math.min(100, copies + 1)" class="h-9 w-9 text-lg" aria-label="Plus">+</button>
                            </div>
                        </div>
                    </div>

                    {{-- Coordonnées --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="customer_name" class="{{ $label }}">Votre nom</label>
                            <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" required autocomplete="name" placeholder="Prénom et nom" class="{{ $field }}">
                            @error('customer_name')<p class="{{ $error }}">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="customer_phone" class="{{ $label }}">Téléphone <span class="text-black/35 night:text-white/35">(facultatif)</span></label>
                            <input id="customer_phone" name="customer_phone" type="tel" value="{{ old('customer_phone') }}" autocomplete="tel" inputmode="tel" placeholder="07 00 00 00 00" class="{{ $field }}">
                            @error('customer_phone')<p class="{{ $error }}">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="{{ $label }}">Précisions <span class="text-black/35 night:text-white/35">(facultatif)</span></label>
                        <textarea id="notes" name="notes" rows="2" maxlength="500" placeholder="Ex : pages 2 à 5 seulement, reliure…" class="{{ $field }}">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" :disabled="sending" class="flex h-12 w-full items-center justify-center gap-2 rounded-full bg-neutral-950 px-7 text-sm font-semibold text-white transition hover:bg-black disabled:opacity-60 night:bg-white night:text-black night:hover:bg-white/90">
                        <svg x-show="sending" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span x-text="sending ? 'Envoi et comptage des pages…' : 'Envoyer mes documents'">Envoyer mes documents</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.guest>
