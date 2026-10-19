@php
    $user = auth()->user();
    $field = 'h-11 w-full rounded-xl border border-[#E2E8F0] bg-white px-3 text-base text-[#0B0F14] placeholder:text-slate-400 transition focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/20 sm:text-sm';
    $recoveryCodes = session('two_factor_recovery_codes');
    $twoFactorErrors = $errors->getBag('twoFactor');
@endphp

<x-card id="double-authentification" class="scroll-mt-24">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-[#0B0F14]">Double authentification</p>
            <p class="mt-0.5 text-xs text-[#64748B]">À chaque connexion, un code à 6 chiffres de votre application d'authentification (Google Authenticator, Microsoft Authenticator, Authy…) est demandé après le mot de passe.</p>
        </div>
        @if($user->hasTwoFactorEnabled())
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Activée
            </span>
        @elseif($user->requiresTwoFactor())
            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Obligatoire pour votre rôle</span>
        @else
            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Désactivée</span>
        @endif
    </div>

    @if($twoFactorErrors->any())
        <div role="alert" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-medium text-rose-700">
            @foreach($twoFactorErrors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if($recoveryCodes)
        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4" x-data="{ copied: false }">
            <p class="text-sm font-semibold text-amber-900">Vos codes de récupération</p>
            <p class="mt-0.5 text-xs text-amber-800">Notez-les ou imprimez-les maintenant : ils ne seront plus affichés. Chacun permet une connexion si vous n'avez plus votre téléphone.</p>
            <ul class="mt-3 grid grid-cols-2 gap-2 font-mono text-sm text-[#0B0F14]">
                @foreach($recoveryCodes as $recoveryCode)
                    <li class="rounded-lg bg-white px-3 py-1.5 text-center">{{ $recoveryCode }}</li>
                @endforeach
            </ul>
            <button type="button" class="mt-3 text-xs font-semibold text-amber-900 hover:underline"
                    @click="navigator.clipboard.writeText(@js(implode("\n", $recoveryCodes))); copied = true; setTimeout(() => copied = false, 2000)">
                <span x-show="! copied">Copier les codes</span>
                <span x-show="copied" x-cloak>Codes copiés</span>
            </button>
        </div>
    @endif

    @if($user->hasTwoFactorEnabled())
        <p class="mt-4 text-xs text-[#64748B]">
            Activée le {{ $user->two_factor_confirmed_at->format('d/m/Y à H:i') }} · {{ count($user->two_factor_recovery_codes ?? []) }} code(s) de récupération restant(s).
        </p>

        <div class="mt-5 space-y-4 border-t border-[#E2E8F0] pt-5" x-data="{ action: null }">
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="action = action === 'codes' ? null : 'codes'" class="inline-flex h-10 items-center rounded-xl border border-[#E2E8F0] px-4 text-xs font-semibold text-[#0B0F14] transition hover:bg-[#F5F7FA]">Régénérer les codes de récupération</button>
                @unless($user->requiresTwoFactor())
                    <button type="button" @click="action = action === 'disable' ? null : 'disable'" class="inline-flex h-10 items-center rounded-xl border border-rose-200 px-4 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">Désactiver</button>
                @endunless
            </div>

            <form method="POST" action="{{ route('two-factor.recovery-codes') }}" x-show="action === 'codes'" x-cloak class="flex flex-col gap-2 sm:flex-row">
                @csrf
                <input type="password" name="current_password" required autocomplete="current-password" placeholder="Votre mot de passe" aria-label="Mot de passe" class="{{ $field }}">
                <button type="submit" class="h-11 shrink-0 rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052CC]">Générer</button>
            </form>

            @unless($user->requiresTwoFactor())
                <form method="POST" action="{{ route('two-factor.disable') }}" x-show="action === 'disable'" x-cloak class="flex flex-col gap-2 sm:flex-row">
                    @csrf
                    @method('DELETE')
                    <input type="password" name="current_password" required autocomplete="current-password" placeholder="Votre mot de passe" aria-label="Mot de passe" class="{{ $field }}">
                    <button type="submit" class="h-11 shrink-0 rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white transition hover:bg-rose-700">Désactiver la double authentification</button>
                </form>
            @endunless
        </div>
    @elseif($twoFactorSecret)
        <ol class="mt-5 space-y-5 border-t border-[#E2E8F0] pt-5">
            <li>
                <p class="text-xs font-semibold text-[#0B0F14] sm:text-sm">1. Scannez ce QR code avec votre application d'authentification</p>
                <div class="mt-3 flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                    <div class="h-44 w-44 shrink-0 rounded-xl border border-[#E2E8F0] bg-white p-2 [&>svg]:h-full [&>svg]:w-full">{!! $twoFactorQrCode !!}</div>
                    <div class="min-w-0 text-center text-xs text-[#64748B] sm:text-left">
                        <p>Impossible de scanner ? Saisissez cette clé dans l'application :</p>
                        <p class="mt-2 break-all rounded-lg bg-[#F5F7FA] px-3 py-2 font-mono text-sm tracking-wider text-[#0B0F14]">{{ trim(chunk_split($twoFactorSecret, 4, ' ')) }}</p>
                    </div>
                </div>
            </li>
            <li>
                <p class="text-xs font-semibold text-[#0B0F14] sm:text-sm">2. Saisissez le code à 6 chiffres affiché</p>
                <form method="POST" action="{{ route('two-factor.confirm') }}" class="mt-3 flex flex-col gap-2 sm:flex-row">
                    @csrf
                    <input type="text" name="code" required inputmode="numeric" pattern="[0-9 ]*" maxlength="7" autocomplete="one-time-code" placeholder="123456" aria-label="Code de vérification" autofocus
                           class="{{ $field }} text-center font-mono tracking-[0.4em] sm:max-w-48">
                    <button type="submit" class="h-11 shrink-0 rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052CC]">Confirmer l'activation</button>
                </form>
            </li>
        </ol>
        <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-4">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs font-medium text-[#64748B] hover:underline">Annuler l'activation</button>
        </form>
    @else
        <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-5 flex flex-col gap-2 border-t border-[#E2E8F0] pt-5 sm:flex-row">
            @csrf
            <input type="password" name="current_password" required autocomplete="current-password" placeholder="Confirmez avec votre mot de passe" aria-label="Mot de passe" class="{{ $field }}">
            <button type="submit" class="h-11 shrink-0 rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052CC]">Activer</button>
        </form>
    @endif
</x-card>
