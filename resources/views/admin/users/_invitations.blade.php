@php
    $hasInvitationErrors = collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'invite_'));
    $inputClasses = 'w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition';
@endphp

{{-- Lien qui vient d'être généré : affiché une seule fois --}}
@if(session('invitation_link'))
    <div x-data="{ copied: false, copy() { navigator.clipboard.writeText(this.$refs.link.value).then(() => { this.copied = true; setTimeout(() => this.copied = false, 2000); }); } }"
         class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
        <div class="flex items-start gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-[#0B0F14]">Lien d'inscription généré</p>
                <p class="mt-0.5 text-xs text-[#64748B]">Envoyez-le à la personne concernée (WhatsApp, e-mail…). Il ne fonctionne qu'une seule fois et expire le {{ session('invitation_expires') }}. Copiez-le maintenant : il ne sera plus affiché.</p>
                <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                    <input x-ref="link" type="text" readonly value="{{ session('invitation_link') }}" @focus="$el.select()" class="{{ $inputClasses }} font-mono">
                    <button type="button" @click="copy()" class="inline-flex h-9 shrink-0 items-center justify-center gap-1.5 rounded-lg bg-[#0B0F14] px-4 text-xs font-semibold text-white transition hover:bg-[#1E293B]">
                        <span x-text="copied ? 'Copié !' : 'Copier le lien'">Copier le lien</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Liens encore utilisables --}}
@if($invitations->isNotEmpty())
    <x-card>
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-[#0B0F14]">Liens d'inscription en attente</h2>
                <p class="text-xs text-[#64748B]">Chaque lien sert à une seule inscription. Révoquez-le s'il a été envoyé par erreur.</p>
            </div>
            <span class="rounded-full bg-[#F5F7FA] px-2.5 py-0.5 text-xs font-semibold text-[#0B0F14]">{{ $invitations->count() }}</span>
        </div>
        <ul class="mt-4 divide-y divide-[#E2E8F0] rounded-lg border border-[#E2E8F0]">
            @foreach($invitations as $invitation)
                <li class="flex flex-col gap-2 px-4 py-3 text-xs sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="font-semibold text-[#0B0F14]">
                            {{ $invitation->name ?: ($invitation->email ?: 'Lien ouvert') }}
                            @if($invitation->name && $invitation->email)<span class="font-normal text-[#64748B]">· {{ $invitation->email }}</span>@endif
                        </p>
                        <p class="mt-0.5 text-[#64748B]">
                            {{ $invitation->roles()->pluck('name')->join(', ') }} ·
                            expire le {{ $invitation->expires_at->format('d/m/Y à H:i') }}
                            @if($invitation->creator) · par {{ $invitation->creator->name }}@endif
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.users.invitations.destroy', $invitation) }}" onsubmit="return confirm('Révoquer ce lien ? Il ne pourra plus être utilisé.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-rose-200 px-3 py-1.5 font-medium text-rose-600 transition hover:bg-rose-50">Révoquer</button>
                    </form>
                </li>
            @endforeach
        </ul>
    </x-card>
@endif

{{-- Fenêtre de génération --}}
<div x-data="{ open: @js($hasInvitationErrors), allDomains: @js((bool) old('invite_all_domains')) }"
     @open-invitation-modal.window="open = true" @keydown.escape.window="open = false"
     x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="invitation-title">
    <div class="absolute inset-0 bg-black/50" @click="open = false"></div>

    <form method="POST" action="{{ route('admin.users.invitations.store') }}"
          class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-xl">
        @csrf
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="invitation-title" class="text-base font-semibold text-[#0B0F14]">Générer un lien d'inscription</h2>
                <p class="mt-1 text-xs text-[#64748B]">L'utilisateur crée lui-même son compte (nom, e-mail, mot de passe) avec le rôle et les accès choisis ici.</p>
            </div>
            <button type="button" @click="open = false" class="rounded-lg p-1 text-[#64748B] hover:bg-[#F5F7FA] hover:text-[#0B0F14]" aria-label="Fermer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="mt-5 space-y-4 text-xs">
            <div>
                <label for="invite_role" class="mb-1.5 block font-semibold text-[#0B0F14]">Rôle <span class="text-rose-500">*</span></label>
                <select id="invite_role" name="invite_role" required class="{{ $inputClasses }}">
                    <option value="">Choisir un rôle…</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected(old('invite_role') == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('invite_role')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <p class="mb-1.5 font-semibold text-[#0B0F14]">Accès aux domaines</p>
                <label class="mb-2 flex items-center gap-2 text-[#0B0F14]">
                    <input type="hidden" name="invite_all_domains" value="0">
                    <input type="checkbox" name="invite_all_domains" value="1" x-model="allDomains" class="h-4 w-4 rounded border-[#E2E8F0] text-[#0066FF]">
                    Tous les domaines
                </label>
                <div x-show="! allDomains" class="grid grid-cols-2 gap-2">
                    @foreach($domains->where('is_active', true) as $domain)
                        <label class="flex items-center gap-2 rounded-lg border border-[#E2E8F0] px-3 py-2 text-[#0B0F14]">
                            <input type="checkbox" name="invite_domains[]" value="{{ $domain->id }}" @checked(in_array($domain->id, old('invite_domains', []))) class="h-4 w-4 rounded border-[#E2E8F0] text-[#0066FF]">
                            <span class="truncate">{{ $domain->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="invite_name" class="mb-1.5 block font-semibold text-[#0B0F14]">Nom <span class="font-normal text-[#64748B]">(facultatif)</span></label>
                    <input id="invite_name" name="invite_name" type="text" value="{{ old('invite_name') }}" placeholder="Pré-remplit le formulaire" class="{{ $inputClasses }}">
                </div>
                <div>
                    <label for="invite_email" class="mb-1.5 block font-semibold text-[#0B0F14]">E-mail <span class="font-normal text-[#64748B]">(facultatif)</span></label>
                    <input id="invite_email" name="invite_email" type="email" value="{{ old('invite_email') }}" placeholder="Réserve le lien à cette adresse" class="{{ $inputClasses }}">
                    @error('invite_email')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="invite_validity" class="mb-1.5 block font-semibold text-[#0B0F14]">Validité du lien</label>
                <select id="invite_validity" name="invite_validity" class="{{ $inputClasses }}">
                    @foreach($validityDays as $days)
                        <option value="{{ $days }}" @selected((int) old('invite_validity', 7) === $days)>{{ $days }} {{ $days > 1 ? 'jours' : 'jour' }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <x-button variant="secondary" type="button" x-on:click="open = false">Annuler</x-button>
            <x-button variant="primary" type="submit">Générer le lien</x-button>
        </div>
    </form>
</div>
