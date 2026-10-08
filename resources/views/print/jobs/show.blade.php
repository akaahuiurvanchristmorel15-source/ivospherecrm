<x-layouts.app :title="'Travail ' . $job->reference . ' — PRINT'">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('print.jobs.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux travaux d'impression</span>
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                        Travail {{ $job->reference }}
                    </h1>
                    @php
                        $statusBadge = match($job->status) {
                            'livré', 'terminé' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'en_production' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                            default => 'bg-amber-50 text-amber-700 border-amber-200'
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold border {{ $statusBadge }}">
                        {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                    </span>
                </div>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Créé le {{ $job->created_at->format('d/m/Y à H:i') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a 
                    href="{{ route('print.jobs.edit', $job) }}" 
                    class="px-4 py-2 rounded-xl bg-[#0B0F14] text-white hover:bg-[#0066FF] text-xs font-semibold transition-colors"
                >
                    Modifier le travail
                </a>
                <form action="{{ route('print.jobs.destroy', $job) }}" method="POST" onsubmit="return confirm('Confirmer la suppression de ce travail ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 transition-colors">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-5 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Client Associé</span>
                <span class="text-base font-bold text-[#0B0F14] mt-1 block">
                    {{ $job->customer->name ?? 'Client comptoir' }}
                </span>
                @if($job->customer?->company)
                    <span class="text-xs text-[#64748B] block">{{ $job->customer->company }}</span>
                @endif
                @if($job->customer?->phone)
                    <span class="text-xs text-[#0066FF] font-medium block mt-1">{{ $job->customer->phone }}</span>
                @endif
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Quantité & Prix</span>
                <span class="text-base font-bold text-[#0B0F14] mt-1 block">
                    {{ number_format($job->quantity, 0, ',', ' ') }} ex.
                </span>
                <span class="text-xs text-[#64748B] block">
                    {{ number_format($job->unit_price, 0, ',', ' ') }} FCFA / ex.
                </span>
                <span class="text-xs font-extrabold text-[#0066FF] block mt-1">
                    Total : {{ number_format($job->total, 0, ',', ' ') }} FCFA
                </span>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Échéance & Délais</span>
                <span class="text-base font-bold text-[#0B0F14] mt-1 block">
                    {{ $job->deadline ? $job->deadline->format('d/m/Y') : 'Non spécifiée' }}
                </span>
                <span class="text-xs text-[#64748B] block mt-1">
                    Type : <strong class="text-[#0B0F14] uppercase">{{ $job->type }}</strong>
                </span>
            </div>
        </div>

        <!-- Fiche Technique -->
        <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4 text-xs">
            <h3 class="text-sm font-bold text-[#0B0F14] pb-2 border-b border-[#E2E8F0]">Caractéristiques Techniques</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0]">
                    <span class="text-[10px] text-[#64748B] uppercase font-bold block">Format</span>
                    <span class="text-sm font-bold text-[#0B0F14] mt-0.5 block">
                        {{ $job->format->name ?? '-' }}
                    </span>
                    @if($job->format)
                        <span class="text-xs text-[#64748B]">{{ $job->format->width_mm }} x {{ $job->format->height_mm }} mm</span>
                    @endif
                </div>

                <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0]">
                    <span class="text-[10px] text-[#64748B] uppercase font-bold block">Support / Matière</span>
                    <span class="text-sm font-bold text-[#0B0F14] mt-0.5 block">
                        {{ $job->support->name ?? 'Standard' }}
                    </span>
                    @if($job->support?->grammage)
                        <span class="text-xs text-[#64748B]">{{ $job->support->grammage }}g</span>
                    @endif
                </div>

                <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0]">
                    <span class="text-[10px] text-[#64748B] uppercase font-bold block">Finition / Façonnage</span>
                    <span class="text-sm font-bold text-[#0B0F14] mt-0.5 block">
                        {{ $job->finishing->name ?? 'Aucune' }}
                    </span>
                    @if($job->finishing?->type)
                        <span class="text-xs text-[#64748B]">{{ $job->finishing->type }}</span>
                    @endif
                </div>
            </div>

            @if($job->specifications)
                <div class="pt-3">
                    <span class="text-[10px] text-[#64748B] uppercase font-bold block mb-1">Spécifications Particulières</span>
                    <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] whitespace-pre-line">
                        {{ $job->specifications }}
                    </div>
                </div>
            @endif

            @if($job->notes)
                <div class="pt-2">
                    <span class="text-[10px] text-[#64748B] uppercase font-bold block mb-1">Notes de Fabrication</span>
                    <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] whitespace-pre-line">
                        {{ $job->notes }}
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
