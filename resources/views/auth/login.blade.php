@php
    $field = 'block w-full rounded-xl border border-[#E2E8F0] bg-white py-3 pl-10 text-base text-[#0B0F14] placeholder:text-slate-400 transition focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/20 sm:py-2.5 sm:text-sm';
    $icon = 'pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400';
@endphp

<x-layouts.guest>
    <div x-data="{
            email: '{{ old('email') }}',
            password: '',
            showPassword: false,
            fillAccount(accEmail) { this.email = accEmail; this.$nextTick(() => this.$refs.password.focus()); }
         }"
         class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-xl shadow-slate-200/60 sm:p-8">

        {{-- Marque --}}
        <header class="mb-7 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="IVOSPHERE" class="mx-auto h-14 w-auto object-contain sm:h-16">
            <h1 class="mt-5 text-xl font-bold tracking-tight text-[#0B0F14]">Connexion</h1>
            <p class="mt-1 text-sm text-[#64748B]">Accédez à votre espace de gestion IVOSPHERE.</p>
        </header>

        @if ($errors->any())
            <div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-sm text-rose-800">
                <p class="font-semibold">Connexion impossible</p>
                <ul class="mt-1 space-y-0.5 text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-[#0B0F14]">Adresse email</label>
                <div class="relative">
                    <svg class="{{ $icon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <input type="email" name="email" id="email" x-model="email" required autofocus
                           autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false"
                           placeholder="nom@ivosphere.com" class="{{ $field }} pr-3.5">
                </div>
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-[#0B0F14]">Mot de passe</label>
                <div class="relative">
                    <svg class="{{ $icon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <input :type="showPassword ? 'text' : 'password'" name="password" id="password" x-ref="password" x-model="password" required
                           autocomplete="current-password" placeholder="Votre mot de passe" class="{{ $field }} pr-12">
                    <button type="button" @click="showPassword = !showPassword"
                            :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" :aria-pressed="showPassword"
                            class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-xl text-slate-400 hover:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#0066FF]">
                        <svg x-show="!showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="showPassword" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                    </button>
                </div>
            </div>

            <label class="flex cursor-pointer select-none items-center gap-2.5 py-1 text-sm text-[#64748B]">
                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-[#CBD5E1] text-[#0066FF] focus:ring-[#0066FF]">
                Rester connecté
            </label>

            <button type="submit"
                    class="flex w-full items-center justify-center rounded-xl bg-[#0066FF] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#0052CC] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-2 active:scale-[0.99]">
                Se connecter
            </button>
        </form>
    </div>
</x-layouts.guest>