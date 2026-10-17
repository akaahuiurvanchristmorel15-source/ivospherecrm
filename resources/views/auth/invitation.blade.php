@php
    $field = 'block w-full rounded-2xl border border-black/10 bg-white/75 px-4 py-3 text-base text-neutral-950 placeholder:text-black/30 transition focus:border-black/40 focus:outline-none focus:ring-4 focus:ring-black/5 sm:text-sm night:border-white/10 night:bg-white/[0.04] night:text-white night:placeholder:text-white/25 night:focus:border-white/40 night:focus:ring-white/10';
    $label = 'mb-1.5 block text-xs font-medium text-black/60 night:text-white/60';
    $error = 'mt-1.5 text-[11px] text-rose-600 night:text-rose-300';
@endphp

<x-layouts.guest>
    <x-slot:title>Inscription</x-slot>

    <div class="rounded-[32px] border border-black/[0.08] bg-gradient-to-b from-white to-white/40 p-2 shadow-xl shadow-black/5 night:border-white/10 night:from-white/[0.08] night:to-white/[0.02] night:shadow-black/50">
        <div class="rounded-[26px] border border-black/[0.06] bg-white/60 p-5 backdrop-blur-2xl sm:p-8 night:border-white/10 night:bg-white/[0.03]">
            @if(! $invitation)
                {{-- Lien inconnu, expiré, révoqué ou déjà utilisé --}}
                <div class="text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-500/10 text-amber-600 night:text-amber-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244M3 3l18 18"/></svg>
                    </span>
                    <h1 class="mt-5 font-display text-2xl font-extrabold sm:text-3xl">Lien d'inscription invalide</h1>
                    <p class="mt-2 text-sm leading-relaxed text-black/55 night:text-white/55">
                        Ce lien a déjà été utilisé, a expiré ou a été révoqué. Demandez un nouveau lien à votre administrateur.
                    </p>
                    <a href="{{ route('login') }}" class="mt-7 inline-flex h-12 items-center justify-center rounded-full bg-neutral-950 px-7 text-sm font-semibold text-white transition hover:bg-black night:bg-white night:text-black night:hover:bg-white/90">
                        Aller à la connexion
                    </a>
                </div>
            @else
                <header class="mb-6">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 night:text-white/45">Invitation · {{ $spaceName }}</p>
                    <h1 class="mt-2 font-display text-3xl font-extrabold leading-[1.05] sm:text-4xl">Créez votre compte</h1>
                    <p class="mt-2 text-sm leading-relaxed text-black/55 night:text-white/55">
                        Ce lien est personnel et ne fonctionne qu'une seule fois. Il expire le {{ $invitation->expires_at->format('d/m/Y à H:i') }}.
                    </p>
                    @if($roles->isNotEmpty())
                        <div class="mt-4 flex flex-wrap gap-1.5">
                            @foreach($roles as $role)
                                <span class="rounded-full border border-black/10 px-2.5 py-0.5 text-[11px] font-semibold night:border-white/15">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </header>

                @if(session('error'))
                    <div role="alert" class="mb-5 rounded-2xl border border-rose-400/40 bg-rose-500/10 p-3.5 text-sm text-rose-700 night:text-rose-200">{{ session('error') }}</div>
                @endif

                @if($googleIdentity)
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-emerald-500/30 bg-emerald-500/[0.07] p-3.5">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white ring-1 ring-black/5">
                                @include('subscriptions.offers._google-icon')
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700 night:text-emerald-300">E-mail vérifié par Google</p>
                                <p class="truncate text-sm font-semibold">{{ $googleIdentity['email'] }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('invitations.google.forget', $token) }}">
                            @csrf
                            <button type="submit" class="h-8 rounded-full border border-black/15 px-3 text-xs font-semibold transition hover:border-black/40 night:border-white/15 night:hover:border-white/40">Changer</button>
                        </form>
                    </div>
                @elseif($googleEnabled)
                    <div class="mb-5 space-y-3">
                        <a href="{{ route('invitations.google', $token) }}"
                           class="flex h-12 w-full items-center justify-center gap-3 rounded-full border border-black/15 bg-white text-sm font-semibold text-[#0B0F14] shadow-sm transition hover:border-black/40 night:border-white/20 night:hover:border-white/50">
                            @include('subscriptions.offers._google-icon')
                            Continuer avec Google
                        </a>
                        <p class="text-center text-[11px] text-black/45 night:text-white/45">Recommandé : Google confirme que l'adresse e-mail vous appartient.</p>
                        <div class="flex items-center gap-3 text-[11px] uppercase tracking-wider text-black/35 night:text-white/35">
                            <span class="h-px flex-1 bg-black/10 night:bg-white/10"></span>
                            ou remplissez le formulaire
                            <span class="h-px flex-1 bg-black/10 night:bg-white/10"></span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('invitations.register', $token) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="{{ $label }}">Nom complet</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $invitation->name ?: ($googleIdentity['name'] ?? null)) }}" required autofocus autocomplete="name" placeholder="Prénom et nom" class="{{ $field }}">
                        @error('name')<p class="{{ $error }}">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="{{ $label }}">Adresse e-mail</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $googleIdentity['email'] ?? $invitation->email) }}" required autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false"
                               placeholder="votre.adresse@gmail.com" @if($invitation->email || $googleIdentity) readonly @endif class="{{ $field }} {{ ($invitation->email || $googleIdentity) ? 'cursor-not-allowed opacity-80' : '' }}">
                        @error('email')
                            <p class="{{ $error }}">{{ $message }}</p>
                        @else
                            <p class="mt-1.5 text-[11px] text-black/45 night:text-white/45">Utilisez votre vraie adresse e-mail : elle sert à vous connecter et à recevoir les informations de votre compte.</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="{{ $label }}">Téléphone</label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="07 00 00 00 00" class="{{ $field }}">
                        @error('phone')<p class="{{ $error }}">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="password" class="{{ $label }}">Mot de passe</label>
                            <input type="password" name="password" id="password" required autocomplete="new-password" class="{{ $field }}">
                            @error('password')<p class="{{ $error }}">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="{{ $label }}">Confirmer</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" class="{{ $field }}">
                        </div>
                    </div>

                    <button type="submit" class="flex h-12 w-full items-center justify-center gap-2 rounded-full bg-neutral-950 px-7 text-sm font-semibold text-white transition hover:bg-black focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#0066FF]/40 night:bg-white night:text-black night:hover:bg-white/90">
                        Valider mon inscription
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.guest>
