<x-layouts.app>
    <x-slot:title>Tableau de bord Commercial — IVOSPHERE ERP</x-slot>

    @php
        $richText = fn (string $text) => preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', e($text));
        $moodLabels = [
            'happy' => 'Tout est sous contrôle',
            'neutral' => "Quelques points d'attention",
            'focused' => 'Plusieurs actions prioritaires',
        ];
        $toneTiles = [
            'good' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
            'warn' => 'bg-amber-50 text-amber-600 ring-amber-100',
            'alert' => 'bg-rose-50 text-rose-600 ring-rose-100',
            'info' => 'bg-[#EEF4FF] text-[#0066FF] ring-[#DCE8FF]',
        ];
        $briefingIcons = [
            'cash' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'trend' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
            'star' => 'M11.48 3.5a.56.56 0 011.04 0l2.13 5.1 5.5.44c.5.04.7.66.32.99l-4.2 3.6 1.28 5.38a.56.56 0 01-.84.61L12 16.73l-4.72 2.89a.56.56 0 01-.84-.61l1.28-5.38-4.2-3.6a.56.56 0 01.32-.99l5.5-.44 2.13-5.1z',
            'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            'alert' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            'doc' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'box' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            'stock' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
            'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'phone' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
        ];
        $quickQuestions = [
            "Quel est mon chiffre d'affaires ce mois-ci ?",
            'Quels sont les produits les plus vendus ce mois-ci ?',
            'Qui sont mes meilleurs clients cette année ?',
            'Quelles factures sont impayées ?',
            'Quels produits sont en rupture de stock ?',
            'Quel est le panier moyen ce mois-ci ?',
            'Quels clients sont inactifs ?',
            'Fais-moi un bilan du mois',
        ];
        $weekMax = max(1, collect($agent['week'])->max('amount'));
    @endphp

    <style>
        .ivo-orb-ring { background: conic-gradient(from 0deg, #0066FF, #22D3EE, #7C3AED, #0066FF); animation: ivo-spin 6s linear infinite; }
        .ivo-orb-core { background: radial-gradient(circle at 30% 25%, #FFFFFF 0%, #DCEBFF 22%, #3B82F6 58%, #1E3A8A 100%); animation: ivo-breathe 4s ease-in-out infinite; }
        .ivo-orb-glow { background: radial-gradient(circle, rgba(0,102,255,.35) 0%, transparent 70%); animation: ivo-breathe 4s ease-in-out infinite; }
        .is-thinking .ivo-orb-ring { animation-duration: 1.4s; }
        .ivo-wave span { animation: ivo-wave 1s ease-in-out infinite; transform-origin: center; }
        .ivo-wave span:nth-child(2) { animation-delay: .15s; }
        .ivo-wave span:nth-child(3) { animation-delay: .3s; }
        .ivo-wave span:nth-child(4) { animation-delay: .45s; }
        .ivo-row { animation: ivo-in .5s cubic-bezier(.2,.8,.2,1) both; }
        .ivo-dot { animation: ivo-dots 1.2s infinite ease-in-out; }
        .ivo-dot:nth-child(2) { animation-delay: .15s; }
        .ivo-dot:nth-child(3) { animation-delay: .3s; }
        .ivo-bar { animation: ivo-grow .8s cubic-bezier(.2,.8,.2,1) both; transform-origin: bottom; }
        .ivo-caret::after { content: ''; display: inline-block; width: 2px; height: 1em; margin-left: 2px; vertical-align: -2px; background: #0066FF; animation: ivo-caret 1s steps(1) infinite; }
        @keyframes ivo-spin { to { transform: rotate(360deg) } }
        @keyframes ivo-breathe { 0%, 100% { transform: scale(1) } 50% { transform: scale(1.06) } }
        @keyframes ivo-wave { 0%, 100% { transform: scaleY(.35) } 50% { transform: scaleY(1) } }
        @keyframes ivo-in { from { opacity: 0; transform: translateY(6px) } to { opacity: 1; transform: none } }
        @keyframes ivo-dots { 0%, 80%, 100% { opacity: .3 } 40% { opacity: 1 } }
        @keyframes ivo-grow { from { transform: scaleY(0) } to { transform: scaleY(1) } }
        @keyframes ivo-caret { 50% { opacity: 0 } }
        @media (prefers-reduced-motion: reduce) {
            .ivo-orb-ring, .ivo-orb-core, .ivo-orb-glow, .ivo-wave span, .ivo-row, .ivo-bar { animation: none !important; }
        }
        .ivo-answer strong { font-weight: 600; color: #0B0F14; }
        .ivo-answer table { width: 100%; font-size: 11px; border-collapse: collapse; margin-top: .6rem; }
        .ivo-answer th { text-align: left; font-weight: 500; color: #64748B; padding: .4rem .5rem; border-bottom: 1px solid #E2E8F0; white-space: nowrap; }
        .ivo-answer td { padding: .4rem .5rem; border-bottom: 1px solid #F1F5F9; color: #0B0F14; }
        .ivo-answer .ivo-metric { display: inline-flex; gap: .3rem; border: 1px solid #E2E8F0; border-radius: .5rem; padding: .25rem .55rem; font-size: 11px; background: #FFFFFF; }
    </style>

    {{-- En-tête compact --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Gestion commerciale</p>
            <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-[#0B0F14]">Tableau de bord Commercial</h1>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-button :href="route('commercial.products.index')" variant="secondary" size="md">
                <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Catalogue</span>
            </x-button>
            <x-button :href="route('commercial.pos.index')" variant="secondary" size="md">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Vente POS</span>
            </x-button>
            <x-button :href="route('commercial.quotations.create')" variant="primary" size="md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nouveau devis</span>
            </x-button>
        </div>
    </div>

    {{-- Agent commercial Ivo --}}
    <section
        id="commercial-agent"
        x-data="commercialAgent(@js([
            'greeting' => $agent['greeting'],
            'count' => count($agent['messages']),
            'queryUrl' => route('ai.query'),
        ]))"
        x-init="start()"
        class="bg-white border border-[#E2E8F0] rounded-2xl mb-6 overflow-hidden"
    >
        {{-- Identité de l'agent --}}
        <div class="flex items-center gap-4 px-4 sm:px-6 py-4 border-b border-[#E2E8F0]">
            <div class="relative w-12 h-12 shrink-0" :class="thinking && 'is-thinking'" aria-hidden="true">
                <div class="ivo-orb-glow absolute -inset-2 rounded-full blur-md"></div>
                <div class="ivo-orb-ring absolute inset-0 rounded-full"></div>
                <div class="absolute inset-[2px] rounded-full bg-[#FFFFFF]"></div>
                <div class="ivo-orb-core absolute inset-[5px] rounded-full flex items-center justify-center">
                    <div x-show="talking || thinking" class="ivo-wave flex items-center gap-[2px] h-4">
                        <span class="w-[2px] h-full rounded-full bg-[#FFFFFF]"></span>
                        <span class="w-[2px] h-full rounded-full bg-[#FFFFFF]"></span>
                        <span class="w-[2px] h-full rounded-full bg-[#FFFFFF]"></span>
                        <span class="w-[2px] h-full rounded-full bg-[#FFFFFF]"></span>
                    </div>
                </div>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <h2 class="text-sm font-semibold text-[#0B0F14]">Ivo</h2>
                    <span class="rounded-md bg-[#EEF4FF] px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-[#0066FF]">Agent IA</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] text-[#64748B]">
                        <span class="w-1.5 h-1.5 rounded-full" :class="thinking ? 'bg-amber-400' : 'bg-emerald-500'"></span>
                        <span x-text="thinking ? 'Analyse en cours…' : (talking ? 'Briefing en cours…' : @js($moodLabels[$agent['mood']]))">{{ $moodLabels[$agent['mood']] }}</span>
                    </span>
                </div>
                <p class="mt-0.5 text-xs sm:text-sm text-[#64748B] leading-relaxed" :class="greetingDone || 'ivo-caret'" x-text="typedGreeting">{{ $agent['greeting'] }}</p>
            </div>
            <button type="button" @click="replay()" title="Rejouer le briefing" aria-label="Rejouer le briefing"
                class="hidden sm:inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:border-slate-300 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5">
            {{-- Briefing du jour --}}
            @php $canAskIvo = auth()->user()->hasPermission('ai.assistant'); @endphp
            <div class="{{ $canAskIvo ? 'lg:col-span-3 lg:border-r border-b lg:border-b-0 border-[#E2E8F0]' : 'lg:col-span-5' }}">
                <div class="flex items-center justify-between px-4 sm:px-6 pt-4 pb-2">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Briefing du jour</p>
                    <p class="text-[11px] text-[#94A3B8]">{{ ucfirst(now()->locale('fr')->translatedFormat('l j F')) }}</p>
                </div>
                <ul class="px-2 sm:px-3 pb-3">
                    @foreach($agent['messages'] as $message)
                        <li x-show="revealed > {{ $loop->index }}" class="ivo-row">
                            <div class="group flex items-start gap-3 rounded-xl px-2 sm:px-3 py-2.5 hover:bg-[#F8FAFC] transition">
                                <span class="mt-0.5 w-8 h-8 shrink-0 rounded-lg ring-1 flex items-center justify-center {{ $toneTiles[$message['tone']] ?? $toneTiles['info'] }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $briefingIcons[$message['icon']] ?? $briefingIcons['doc'] }}"/></svg>
                                </span>
                                <p class="min-w-0 flex-1 pt-1 text-[13px] leading-relaxed text-[#475569] [&_strong]:font-semibold [&_strong]:text-[#0B0F14]">{!! $richText($message['text']) !!}</p>
                                @if($message['action'])
                                    <a href="{{ $message['action']['url'] }}" class="mt-1 inline-flex shrink-0 items-center gap-1 rounded-lg px-2 py-1 text-[11px] font-medium text-[#0066FF] hover:bg-[#EEF4FF] transition">
                                        <span class="hidden sm:inline">{{ $message['action']['label'] }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                    <li x-show="pending" class="flex items-center gap-1 px-3 py-3">
                        <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-[#94A3B8]"></span>
                        <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-[#94A3B8]"></span>
                        <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-[#94A3B8]"></span>
                    </li>
                </ul>
            </div>

            {{-- Conversation --}}
            @if($canAskIvo)
            <div class="lg:col-span-2 flex flex-col bg-[#F8FAFC] min-h-[320px]">
                <div class="flex items-center justify-between px-4 sm:px-5 pt-4 pb-2">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Demander à Ivo</p>
                    <button type="button" x-show="chat.length" @click="chat = []" class="text-[11px] text-[#94A3B8] hover:text-[#0B0F14] transition">Effacer</button>
                </div>

                <div x-ref="feed" class="flex-1 overflow-y-auto px-4 sm:px-5 space-y-3 max-h-[360px]">
                    <div x-show="! chat.length && ! thinking" class="flex flex-wrap gap-1.5 pb-2">
                        @foreach($quickQuestions as $question)
                            <button type="button" @click="ask(@js($question))"
                                class="{{ $loop->index >= 4 ? 'hidden sm:block' : '' }} rounded-lg border border-[#E2E8F0] bg-[#FFFFFF] px-2.5 py-1.5 text-left text-[11px] text-[#475569] hover:border-[#0066FF]/40 hover:text-[#0066FF] transition">
                                {{ $question }}
                            </button>
                        @endforeach
                    </div>

                    <template x-for="(entry, index) in chat" :key="index">
                        <div class="ivo-row" :class="entry.from === 'user' && 'flex justify-end'">
                            <div x-show="entry.from === 'user'" class="max-w-[85%] rounded-2xl rounded-br-md bg-[#0B0F14] px-3.5 py-2 text-[13px] text-[#FFFFFF]" x-text="entry.text"></div>
                            <div x-show="entry.from === 'agent'" class="ivo-answer rounded-2xl rounded-bl-md border border-[#E2E8F0] bg-[#FFFFFF] px-3.5 py-2.5 text-[13px] leading-relaxed text-[#475569] overflow-x-auto" x-html="entry.html"></div>
                        </div>
                    </template>

                    <div x-show="thinking" class="flex items-center gap-1 rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] px-3.5 py-3 w-fit">
                        <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-[#0066FF]"></span>
                        <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-[#0066FF]"></span>
                        <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-[#0066FF]"></span>
                    </div>
                </div>

                <form @submit.prevent="ask(question)" class="p-3 sm:p-4">
                    <div class="flex items-center gap-2 rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] pl-3.5 pr-1.5 py-1.5 focus-within:border-[#0066FF] focus-within:ring-2 focus-within:ring-[#0066FF]/10 transition">
                        <input x-model="question" type="text" maxlength="500" placeholder="Ventes, clients, stock, factures…"
                            class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-[#0B0F14] placeholder:text-[#94A3B8] focus:outline-none focus:ring-0">
                        <button type="submit" :disabled="thinking || ! question.trim()" aria-label="Envoyer"
                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#0066FF] text-[#FFFFFF] hover:bg-[#0052CC] transition disabled:opacity-40">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </section>

    {{-- Filtre de périodes --}}
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-2.5 sm:p-3 rounded-2xl border border-[#E2E8F0] mb-4">
        <div class="flex flex-wrap items-center gap-2.5 min-w-0">
            <span class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Période</span>
            <div class="flex flex-wrap items-center rounded-xl bg-[#F5F7FA] p-1 text-xs font-medium gap-1">
                @php
                    $commPeriods = [
                        'today' => "Aujourd'hui",
                        'this_week' => 'Cette semaine',
                        'this_month' => 'Ce mois',
                        'this_quarter' => 'Ce trimestre',
                        'this_year' => 'Cette année',
                    ];
                @endphp
                @foreach($commPeriods as $pk => $plabel)
                    <a href="{{ route('commercial.index', ['period' => $pk]) }}"
                       class="rounded-lg px-3 py-1.5 transition {{ $period === $pk ? 'bg-white text-[#0B0F14] shadow-xs border border-[#E2E8F0]' : 'text-[#64748B] hover:text-[#0B0F14]' }}">
                        {{ $plabel }}
                    </a>
                @endforeach
            </div>
        </div>
        <div class="text-xs text-[#64748B] hidden sm:block">
            Du <span class="font-medium text-[#0B0F14]">{{ $startDate->format('d/m/Y') }}</span> au <span class="font-medium text-[#0B0F14]">{{ $endDate->format('d/m/Y') }}</span>
        </div>
    </div>

    {{-- KPI --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card
            title="CA encaissé ({{ $periodLabel }})"
            :value="number_format($caPeriod, 0, ',', ' ') . ' FCFA'"
            :change="'Jour : ' . number_format($caToday, 0, ',', ' ') . ' F · Sem. : ' . number_format($caWeek, 0, ',', ' ') . ' F'"
            changeType="up"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card title="Devis en cours" :value="$devisEnCours" change="À valider / relancer" changeType="neutral" timeframe="">
            <x-slot:icon>
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card title="Commandes actives" :value="$commandesActives" change="En préparation / en cours" changeType="neutral" timeframe="">
            <x-slot:icon>
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card title="Créances impayées" :value="number_format($impayes, 0, ',', ' ') . ' FCFA'" change="À recouvrer" changeType="down" timeframe="">
            <x-slot:icon>
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Encaissements des 7 derniers jours --}}
        <x-card title="7 derniers jours" subtitle="Encaissements quotidiens" class="lg:col-span-2">
            <div class="flex items-end gap-2 sm:gap-4 h-44 pt-4">
                @foreach($agent['week'] as $day)
                    @php $height = max(3, round($day['amount'] / $weekMax * 100)); @endphp
                    <div class="flex-1 flex flex-col items-center justify-end gap-2 h-full min-w-0">
                        <span class="text-[10px] tabular-nums text-[#64748B] truncate max-w-full">{{ $day['amount'] > 0 ? number_format($day['amount'] / 1000, $day['amount'] < 10000 ? 1 : 0, ',', ' ').'k' : '—' }}</span>
                        <div class="ivo-bar w-full max-w-12 rounded-t-lg {{ $day['is_today'] ? 'bg-[#0066FF]' : 'bg-[#0066FF]/25' }}" style="height: {{ $height }}%; animation-delay: {{ $loop->index * 70 }}ms"></div>
                        <span class="text-[11px] {{ $day['is_today'] ? 'font-semibold text-[#0B0F14]' : 'text-[#64748B]' }}">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>

        {{-- Top produits du mois --}}
        <x-card title="Top produits" subtitle="Les plus vendus ce mois-ci">
            <div class="space-y-3">
                @forelse($agent['top_products'] as $product)
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 shrink-0 rounded-lg flex items-center justify-center text-xs font-semibold {{ $loop->first ? 'bg-amber-100 text-amber-700' : 'bg-[#F5F7FA] text-[#64748B]' }}">{{ $loop->iteration }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-[#0B0F14] truncate">{{ $product['name'] }}</p>
                            <p class="text-[11px] text-[#64748B]">{{ number_format($product['quantity'], 0, ',', ' ') }} vendu(s)</p>
                        </div>
                        <span class="text-xs font-semibold tabular-nums text-[#0B0F14] whitespace-nowrap">{{ number_format($product['revenue'], 0, ',', ' ') }} F</span>
                    </div>
                @empty
                    <p class="text-xs text-[#64748B] py-8 text-center">Aucune vente ce mois-ci.</p>
                @endforelse
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pipeline prospects --}}
        <x-card title="Pipeline des prospects" subtitle="Opportunités par étape">
            <x-slot:actions>
                <a href="{{ route('commercial.prospects.index') }}" class="inline-flex items-center gap-1 text-xs text-[#0066FF] hover:underline font-medium">
                    <span>Vue Kanban</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </x-slot:actions>

            @php $pipelineMax = max(1, $pipeline->max('count')); @endphp
            <div class="space-y-3">
                @foreach($pipeline as $stage)
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="font-medium text-[#0B0F14]">{{ $stage['label'] }} <span class="text-[#64748B] font-normal">· {{ $stage['probability'] }} %</span></span>
                            <span class="tabular-nums text-[#64748B]">{{ $stage['count'] }} prospect(s)</span>
                        </div>
                        <div class="h-2 rounded-full bg-[#F5F7FA] overflow-hidden">
                            <div class="h-full rounded-full bg-[#0066FF]" style="width: {{ $stage['count'] > 0 ? max(4, round($stage['count'] / $pipelineMax * 100)) : 0 }}%; opacity: {{ 0.35 + $stage['probability'] / 160 }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        {{-- Derniers devis --}}
        <x-card title="Derniers devis émis" subtitle="Propositions récentes">
            <x-slot:actions>
                <a href="{{ route('commercial.quotations.index') }}" class="inline-flex items-center gap-1 text-xs text-[#0066FF] hover:underline font-medium">
                    <span>Tous les devis</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
                </a>
            </x-slot:actions>

            <div class="divide-y divide-slate-100">
                @forelse($latestQuotations as $q)
                    <div class="py-3 flex items-center justify-between gap-3 text-xs">
                        <div class="min-w-0">
                            <a href="{{ route('commercial.quotations.show', $q) }}" class="font-medium text-[#0B0F14] hover:text-[#0066FF] transition-colors">{{ $q->reference }}</a>
                            <p class="text-[#64748B] mt-0.5 truncate">{{ $q->customer->name ?? 'Client particulier' }} <span class="mx-1 text-slate-300">·</span> {{ $q->date?->format('d/m/Y') }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="font-semibold text-[#0B0F14] block tabular-nums">{{ number_format($q->total, 0, ',', ' ') }} FCFA</span>
                            <x-badge variant="neutral" size="sm">{{ ucfirst($q->status) }}</x-badge>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-[#64748B] py-8 text-center">Aucun devis récent enregistré.</p>
                @endforelse
            </div>
        </x-card>
    </div>

    @push('scripts')
        <script>
            function commercialAgent(config) {
                const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
                const rich = (value) => escapeHtml(value).replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
                const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
                const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                return {
                    typedGreeting: config.greeting,
                    greetingDone: true,
                    revealed: config.count,
                    chat: [],
                    talking: false,
                    thinking: false,
                    pending: false,
                    question: '',
                    run: 0,

                    scroll() {
                        this.$nextTick(() => { this.$refs.feed.scrollTop = this.$refs.feed.scrollHeight; });
                    },
                    async start() {
                        if (reducedMotion) { return; }

                        const run = ++this.run;
                        this.typedGreeting = '';
                        this.greetingDone = false;
                        this.revealed = 0;
                        this.talking = true;

                        for (const char of config.greeting) {
                            if (run !== this.run) { return; }
                            this.typedGreeting += char;
                            await wait(16);
                        }
                        this.greetingDone = true;

                        while (this.revealed < config.count) {
                            this.pending = true;
                            await wait(420);
                            if (run !== this.run) { return; }
                            this.pending = false;
                            this.revealed++;
                            await wait(160);
                        }
                        this.talking = false;
                    },
                    replay() {
                        this.pending = false;
                        this.start();
                    },
                    async ask(text) {
                        const query = String(text ?? '').trim();
                        if (! query || this.thinking) { return; }

                        this.question = '';
                        this.chat.push({ from: 'user', text: query });
                        this.thinking = true;
                        this.scroll();

                        try {
                            const response = await fetch(config.queryUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({ query }),
                            });
                            if (! response.ok) { throw new Error(response.status); }
                            this.chat.push({ from: 'agent', html: this.render(await response.json()) });
                        } catch (error) {
                            this.chat.push({ from: 'agent', html: "Désolé, je n'ai pas pu analyser cette question pour le moment. Réessayez dans un instant." });
                        }

                        this.thinking = false;
                        this.talking = true;
                        this.scroll();
                        setTimeout(() => { this.talking = false; }, 1200);
                    },
                    render(result) {
                        let html = `<p>${rich(result.answer)}</p>`;

                        if (Array.isArray(result.metrics) && result.metrics.length) {
                            html += '<div style="display:flex;flex-wrap:wrap;gap:.35rem;margin-top:.5rem">'
                                + result.metrics.map((metric) => `<span class="ivo-metric"><span style="color:#64748B">${escapeHtml(metric.label)}</span><strong>${escapeHtml(metric.value)}</strong></span>`).join('')
                                + '</div>';
                        }

                        if (result.table && Array.isArray(result.table.rows) && result.table.rows.length) {
                            html += '<table><thead><tr>' + result.table.columns.map((column) => `<th>${escapeHtml(column)}</th>`).join('') + '</tr></thead><tbody>'
                                + result.table.rows.slice(0, 10).map((row) => '<tr>' + row.map((cell) => `<td>${escapeHtml(cell)}</td>`).join('') + '</tr>').join('')
                                + '</tbody></table>';
                        }

                        return html;
                    },
                };
            }
        </script>
    @endpush
</x-layouts.app>
