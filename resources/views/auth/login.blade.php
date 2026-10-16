@php
    $field = 'block w-full rounded-2xl border border-black/10 bg-white/75 py-3 pl-11 text-base text-neutral-950 placeholder:text-black/30 transition focus:border-black/40 focus:outline-none focus:ring-4 focus:ring-black/5 sm:text-sm night:border-white/10 night:bg-white/[0.04] night:text-white night:placeholder:text-white/25 night:focus:border-white/40 night:focus:ring-white/10';
    $icon = 'pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-black/35 night:text-white/35';
    $label = 'mb-1.5 block text-xs font-medium text-black/60 night:text-white/60';
@endphp

<x-layouts.guest>
    <div x-data="{
            email: '{{ old('email') }}',
            password: '',
            showPassword: false,
            fillAccount(accEmail) { this.email = accEmail; this.$nextTick(() => this.$refs.password.focus()); }
         }"
         class="rounded-[32px] border border-black/[0.08] bg-gradient-to-b from-white to-white/40 p-2 shadow-xl shadow-black/5 night:border-white/10 night:from-white/[0.08] night:to-white/[0.02] night:shadow-black/50">

        <div class="rounded-[26px] border border-black/[0.06] bg-white/60 p-5 backdrop-blur-2xl sm:p-8 night:border-white/10 night:bg-white/[0.03]">
            {{-- Marque --}}
            <header class="mb-7">
                <span class="inline-flex items-center gap-2 rounded-full border border-black/10 bg-white/60 px-3 py-1 text-[11px] font-medium text-black/60 night:border-white/15 night:bg-white/5 night:text-white/70">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                    Espace employé
                </span>
                <h1 class="mt-5 font-display text-3xl font-extrabold leading-[1.05] sm:text-4xl">Connexion</h1>
                <p class="mt-2 text-sm leading-relaxed text-black/55 night:text-white/55">Accédez à votre espace de gestion IVOSPHERE.</p>
            </header>

            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-2xl border border-rose-400/40 bg-rose-500/10 p-3.5 text-sm text-rose-700 night:text-rose-200">
                    <p class="font-semibold">Connexion impossible</p>
                    <ul class="mt-1 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="{{ $label }}">Adresse email</label>
                    <div class="relative">
                        <svg class="{{ $icon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <input type="email" name="email" id="email" x-model="email" required autofocus
                               autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false"
                               placeholder="nom@ivosphere.com" class="{{ $field }} pr-4">
                    </div>
                </div>

                <div>
                    <label for="password" class="{{ $label }}">Mot de passe</label>
                    <div class="relative">
                        <svg class="{{ $icon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <input :type="showPassword ? 'text' : 'password'" type="password" name="password" id="password" x-ref="password" x-model="password" required
                               autocomplete="current-password" placeholder="Votre mot de passe" class="{{ $field }} pr-12">
                        <button type="button" @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" :aria-pressed="showPassword"
                                class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-2xl text-black/40 transition hover:text-black focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#0066FF] night:text-white/40 night:hover:text-white">
                            <svg x-show="!showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPassword" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <label class="flex cursor-pointer select-none items-center gap-2.5 py-1 text-sm text-black/55 night:text-white/55">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-black/20 text-neutral-950 focus:ring-black/20 night:border-white/20 night:bg-white/5 night:text-white night:focus:ring-white/20">
                    Rester connecté
                </label>

                <button type="submit"
                        class="flex h-12 w-full items-center justify-center gap-2 rounded-full bg-neutral-950 px-7 text-sm font-semibold text-white transition hover:bg-black focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#0066FF]/40 active:scale-[0.99] night:bg-white night:text-black night:hover:bg-white/90">
                    Se connecter
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </button>
            </form>
        </div>
    </div>
</x-layouts.guest>
