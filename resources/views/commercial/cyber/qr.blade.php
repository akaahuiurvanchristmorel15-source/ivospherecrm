<x-layouts.app>
    <x-slot:title>QR code de dépôt — Cyber</x-slot>

    <style>
        @media print {
            body * { visibility: hidden !important; }
            #qr-poster, #qr-poster * { visibility: visible !important; }
            #qr-poster { position: absolute; inset: 0; margin: 0 auto; border: 0 !important; box-shadow: none !important; }
        }
    </style>

    <div class="mx-auto max-w-3xl space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('commercial.cyber.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-[#64748B] hover:text-[#0B0F14]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Retour au cyber
            </a>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="window.print()" class="inline-flex h-10 items-center gap-2 rounded-xl bg-[#0066FF] px-4 text-xs font-semibold text-white transition hover:bg-[#0052CC]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Imprimer l'affiche
                </button>
                @if($canRegenerate)
                    <form method="POST" action="{{ route('commercial.cyber.qr.regenerate') }}" onsubmit="return confirm('Générer un nouveau QR code ? L\'affiche actuelle ne fonctionnera plus.');">
                        @csrf
                        <button type="submit" class="inline-flex h-10 items-center rounded-xl border border-[#E2E8F0] bg-white px-4 text-xs font-semibold text-[#0B0F14] transition hover:bg-[#F5F7FA]">Régénérer le QR code</button>
                    </form>
                @endif
            </div>
        </div>

        <section id="qr-poster" class="rounded-3xl border border-[#E2E8F0] bg-[#FFFFFF] p-8 text-center text-[#0B0F14] shadow-sm sm:p-12">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#64748B]">{{ $companyName }} · Cyber</p>
            <h1 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Envoyez vos documents à imprimer</h1>
            <p class="mt-2 text-sm text-[#64748B]">Scannez le QR code avec l'appareil photo de votre téléphone.</p>

            <div class="mx-auto mt-8 w-64 rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 sm:w-72 [&_svg]:h-auto [&_svg]:w-full">
                {!! $qrSvg !!}
            </div>

            <ol class="mx-auto mt-8 grid max-w-xl grid-cols-1 gap-3 text-left text-sm sm:grid-cols-3">
                <li class="rounded-2xl bg-[#F5F7FA] p-4"><strong class="block text-[#0066FF]">1. Scannez</strong>le QR code</li>
                <li class="rounded-2xl bg-[#F5F7FA] p-4"><strong class="block text-[#0066FF]">2. Envoyez</strong>vos PDF, Word ou Excel</li>
                <li class="rounded-2xl bg-[#F5F7FA] p-4"><strong class="block text-[#0066FF]">3. Donnez</strong>votre code au comptoir</li>
            </ol>

            <p class="mt-8 break-all font-mono text-[11px] text-[#64748B]">{{ $url }}</p>
        </section>
    </div>
</x-layouts.app>
