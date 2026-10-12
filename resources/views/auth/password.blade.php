@php
    $field = 'h-11 w-full rounded-xl border bg-white pl-3 pr-11 text-base text-[#0B0F14] placeholder:text-slate-400 transition focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/20 sm:text-sm';
    $fields = [
        ['current_password', 'Mot de passe actuel', 'current-password'],
        ['password', 'Nouveau mot de passe', 'new-password'],
        ['password_confirmation', 'Confirmer le nouveau mot de passe', 'new-password'],
    ];
@endphp

<x-layouts.app>
    <x-slot:title>Mot de passe</x-slot>

    <div class="mx-auto max-w-xl space-y-5">
        <x-page-header title="Mot de passe" description="Modifiez le mot de passe de votre compte {{ auth()->user()->email }}." />

        <x-card>
            <form method="POST" action="{{ route('password.update') }}" x-data="{ show: false, password: '' }" class="space-y-5">
                @csrf
                @method('PUT')

                @foreach($fields as [$name, $label, $autocomplete])
                    <div>
                        <label for="{{ $name }}" class="mb-1.5 block text-xs font-semibold text-[#0B0F14] sm:text-sm">{{ $label }}</label>
                        <div class="relative">
                            <input id="{{ $name }}" name="{{ $name }}" :type="show ? 'text' : 'password'" required autocomplete="{{ $autocomplete }}"
                                   @if($name === 'password') x-model="password" @endif
                                   @if($loop->first) autofocus @endif
                                   class="{{ $field }} {{ $errors->has($name) ? 'border-rose-400' : 'border-[#E2E8F0]' }}">
                            @if($loop->first)
                                <button type="button" @click="show = !show" :aria-label="show ? 'Masquer les mots de passe' : 'Afficher les mots de passe'"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-400 hover:text-slate-700">
                                    <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                </button>
                            @endif
                        </div>
                        @error($name)
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror

                        @if($name === 'password')
                            <ul class="mt-2 grid grid-cols-1 gap-1 text-xs sm:grid-cols-3">
                                @foreach([['password.length >= 8', '8 caractères minimum'], ['/[A-Za-z]/.test(password)', 'Au moins une lettre'], ['/[0-9]/.test(password)', 'Au moins un chiffre']] as [$rule, $ruleLabel])
                                    <li class="flex items-center gap-1.5" :class="{{ $rule }} ? 'text-emerald-600' : 'text-slate-400'">
                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        {{ $ruleLabel }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach

                <div class="flex flex-col-reverse gap-2 border-t border-[#E2E8F0] pt-5 sm:flex-row sm:justify-end">
                    <a href="{{ route('dashboard') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-[#E2E8F0] px-4 text-sm font-semibold text-slate-700 hover:bg-[#F5F7FA]">Annuler</a>
                    <button type="submit" class="h-11 rounded-xl bg-[#0066FF] px-5 text-sm font-semibold text-white transition hover:bg-[#0052CC]">Enregistrer le mot de passe</button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
