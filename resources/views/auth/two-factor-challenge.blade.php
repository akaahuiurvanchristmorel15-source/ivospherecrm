@php
    $field = 'block w-full rounded-2xl border border-black/10 bg-white/75 py-3 px-4 text-base text-neutral-950 placeholder:text-black/30 transition focus:border-black/40 focus:outline-none focus:ring-4 focus:ring-black/5 sm:text-sm night:border-white/10 night:bg-white/[0.04] night:text-white night:placeholder:text-white/25 night:focus:border-white/40 night:focus:ring-white/10';
    $label = 'mb-1.5 block text-xs font-medium text-black/60 night:text-white/60';
    $useRecovery = $errors->has('recovery_code') || old('recovery_code');
@endphp

<x-layouts.guest>
    <x-slot:title>Vérification</x-slot>

    <div x-data="{ recovery: {{ $useRecovery ? 'true' : 'false' }} }"
         class="rounded-[32px] border border-black/[0.08] bg-gradient-to-b from-white to-white/40 p-2 shadow-xl shadow-black/5 night:border-white/10 night:from-white/[0.08] night:to-white/[0.02] night:shadow-black/50">

        <div class="rounded-[26px] border border-black/[0.06] bg-white/60 p-5 backdrop-blur-2xl sm:p-8 night:border-white/10 night:bg-white/[0.03]">
            <header class="mb-7">
                <h1 class="font-display text-3xl font-extrabold leading-[1.05] sm:text-4xl">Vérification</h1>
                <p class="mt-2 text-sm leading-relaxed text-black/55 night:text-white/55" x-show="! recovery">
                    Saisissez le code à 6 chiffres affiché par votre application d'authentification.
                </p>
                <p class="mt-2 text-sm leading-relaxed text-black/55 night:text-white/55" x-show="recovery" x-cloak>
                    Saisissez l'un de vos codes de récupération. Chaque code ne sert qu'une fois.
                </p>
            </header>

            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-2xl border border-rose-400/40 bg-rose-500/10 p-3.5 text-sm text-rose-700 night:text-rose-200">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('two-factor.challenge.store') }}" class="space-y-4">
                @csrf

                <div x-show="! recovery">
                    <label for="code" class="{{ $label }}">Code de vérification</label>
                    <input type="text" name="code" id="code" inputmode="numeric" pattern="[0-9 ]*" maxlength="7"
                           autocomplete="one-time-code" placeholder="123456" autofocus
                           :disabled="recovery" x-init="$nextTick(() => ! recovery && $el.focus())"
                           class="{{ $field }} text-center font-mono text-xl tracking-[0.5em] sm:text-xl">
                </div>

                <div x-show="recovery" x-cloak>
                    <label for="recovery_code" class="{{ $label }}">Code de récupération</label>
                    <input type="text" name="recovery_code" id="recovery_code" value="{{ old('recovery_code') }}" maxlength="20"
                           autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="XXXXX-XXXXX"
                           :disabled="! recovery" x-effect="recovery && $nextTick(() => $el.focus())"
                           class="{{ $field }} text-center font-mono uppercase tracking-widest">
                </div>

                <button type="submit"
                        class="flex h-12 w-full items-center justify-center gap-2 rounded-full bg-neutral-950 px-7 text-sm font-semibold text-white transition hover:bg-black focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#0066FF]/40 active:scale-[0.99] night:bg-white night:text-black night:hover:bg-white/90">
                    Vérifier et se connecter
                </button>
            </form>

            <div class="mt-5 flex flex-col items-center gap-2 text-sm">
                <button type="button" @click="recovery = ! recovery" class="font-medium text-black/60 underline-offset-4 hover:text-black hover:underline night:text-white/60 night:hover:text-white">
                    <span x-show="! recovery">Utiliser un code de récupération</span>
                    <span x-show="recovery" x-cloak>Utiliser le code de l'application</span>
                </button>
                <a href="{{ route('login') }}" class="text-black/45 hover:text-black night:text-white/45 night:hover:text-white">Retour à la connexion</a>
            </div>
        </div>
    </div>
</x-layouts.guest>
