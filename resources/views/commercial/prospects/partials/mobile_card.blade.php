@props([
    'prospect',
    'col' => null,
    'colors' => null,
])

@php
    $stageKey = $prospect->stage ?? 'nouveau';
    $stageInfo = \App\Http\Controllers\Commercial\ProspectController::STAGES[$stageKey] ?? [
        'label' => ucfirst($stageKey),
        'default_prob' => 10,
    ];

    if (!$colors) {
        $stageColors = [
            'nouveau' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400'],
            'contacte' => ['bg' => 'bg-blue-50', 'text' => 'text-[#0066FF]', 'border' => 'border-blue-200', 'dot' => 'bg-[#0066FF]'],
            'interesse' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'dot' => 'bg-indigo-500'],
            'devis_envoye' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
            'negociation' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'dot' => 'bg-purple-500'],
            'gagne' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
            'perdu' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'dot' => 'bg-rose-500'],
        ];
        $colors = $stageColors[$stageKey] ?? $stageColors['nouveau'];
    }

    $prob = $prospect->probability ?? ($stageInfo['default_prob'] ?? 10);
    $estimatedVal = (float) ($prospect->estimated_value ?? 0);
    $weightedVal = $estimatedVal * ($prob / 100);
    $cleanPhone = preg_replace('/[^0-9]/', '', (string) $prospect->phone);
@endphp

<div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs hover:border-[#0066FF]/40 transition-all flex flex-col gap-3">
    
    <!-- Ligne 1 : Nom, Entreprise & Badge Étape -->
    <div class="flex items-start justify-between gap-2.5">
        <div class="min-w-0 flex-1">
            <a href="{{ route('commercial.prospects.edit', $prospect) }}" class="font-extrabold text-sm text-[#0B0F14] hover:text-[#0066FF] transition-colors leading-snug line-clamp-1">
                {{ $prospect->name }}
            </a>
            @if($prospect->company)
                <p class="text-[11px] font-medium text-slate-500 mt-0.5 truncate flex items-center gap-1">
                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>{{ $prospect->company }}</span>
                </p>
            @endif
        </div>

        <!-- Badge d'étape + Probabilité -->
        <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold {{ $colors['bg'] }} {{ $colors['text'] }} border {{ $colors['border'] }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $colors['dot'] }}"></span>
            <span>{{ $stageInfo['label'] }}</span>
            <span class="font-mono opacity-80">({{ $prob }}%)</span>
        </span>
    </div>

    <!-- Ligne 2 : Montant & Pondération (Design Minimaliste Épuré) -->
    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
        <div>
            <span class="text-[9px] uppercase font-extrabold text-slate-400 block tracking-wider">Valeur Estimée</span>
            <span class="text-sm font-black text-[#0B0F14]">
                {{ number_format($estimatedVal, 0, ',', ' ') }} <span class="text-[10px] font-semibold text-slate-400">FCFA</span>
            </span>
        </div>
        <div class="text-right">
            <span class="text-[9px] uppercase font-extrabold text-slate-400 block tracking-wider">Valeur Pondérée</span>
            <span class="text-sm font-black text-[#0066FF]">
                {{ number_format($weightedVal, 0, ',', ' ') }} <span class="text-[10px] font-semibold text-blue-300">FCFA</span>
            </span>
        </div>
    </div>

    <!-- Ligne 3 : Informations Clés (Commercial, Domaine & Relance) -->
    <div class="flex items-center justify-between text-[11px] text-slate-500 gap-2">
        <div class="flex items-center gap-1.5 min-w-0">
            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-[9px] shrink-0">
                {{ strtoupper(substr($prospect->commercial?->name ?? $prospect->assignedUser?->name ?? '?', 0, 1)) }}
            </span>
            <span class="truncate font-medium text-slate-700">
                {{ $prospect->commercial?->name ?? $prospect->assignedUser?->name ?? 'Non assigné' }}
            </span>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @if($prospect->domain)
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600">
                    {{ $prospect->domain->code ?? $prospect->domain->name }}
                </span>
            @endif

            @if($prospect->next_follow_up)
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#0066FF] bg-blue-50 px-2 py-0.5 rounded-full">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ $prospect->next_follow_up->format('d/m') }}</span>
                </span>
            @endif
        </div>
    </div>

    <!-- Ligne 4 : Barre d'Actions Mobiles Express (Contact direct, Changement d'étape, Conversion) -->
    <div class="pt-2 border-t border-slate-100 flex flex-col gap-2">
        
        <!-- Sous-ligne A : Contact Direct 1-Clic (Appel & WhatsApp) + Éditer -->
        <div class="flex items-center justify-between gap-1.5">
            <div class="flex items-center gap-1.5">
                @if($prospect->phone)
                    <a 
                        href="tel:{{ $prospect->phone }}" 
                        class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs transition flex items-center justify-center touch-target"
                        title="Appeler le prospect"
                    >
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </a>

                    @if($cleanPhone)
                        <a 
                            href="https://wa.me/{{ $cleanPhone }}" 
                            target="_blank" 
                            class="p-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs transition flex items-center justify-center touch-target"
                            title="Contacter sur WhatsApp"
                        >
                            <svg class="w-4 h-4 fill-emerald-600" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </a>
                    @endif
                @endif
            </div>

            <div class="flex items-center gap-1.5 flex-1 justify-end">
                <a 
                    href="{{ route('commercial.prospects.edit', $prospect) }}" 
                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition inline-flex items-center gap-1"
                >
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Modifier</span>
                </a>

                @if($stageKey !== 'gagne')
                    <form action="{{ route('commercial.prospects.convert', $prospect) }}" method="POST" class="inline-block">
                        @csrf
                        <button 
                            type="submit" 
                            class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold transition inline-flex items-center gap-1"
                        >
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Convertir</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Sous-ligne B : Sélecteur d'Étape Rapide en 1 Touche -->
        <div class="w-full">
            <form action="{{ route('commercial.prospects.stage', $prospect) }}" method="POST">
                @csrf @method('PATCH')
                <div class="relative">
                    <select 
                        name="stage" 
                        onchange="this.form.submit()" 
                        class="w-full pl-3 pr-8 py-1.5 bg-[#F8FAFC] border border-slate-200 text-[#0B0F14] text-xs font-semibold rounded-xl appearance-none focus:outline-none focus:border-[#0066FF] transition"
                    >
                        @foreach(\App\Http\Controllers\Commercial\ProspectController::STAGES as $stK => $stI)
                            <option value="{{ $stK }}" {{ $stageKey === $stK ? 'selected' : '' }}>
                                Déplacer vers : {{ $stI['label'] }} ({{ $stI['default_prob'] }}%)
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </form>
        </div>

    </div>

</div>
