<x-layouts.app>
    <x-slot:title>Tableau de bord Commercial — IVOSPHERE ERP</x-slot>

    @php
        $richText = fn (string $text) => preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', e($text));
        $moodLabels = [
            'happy' => 'Tout est sous contrôle',
            'neutral' => "Quelques points d'attention",
            'focused' => 'Plusieurs actions prioritaires',
        ];
        $briefingMessages = collect($agent['messages'])->map(fn (array $message) => [
            'tone' => $message['tone'],
            'html' => $richText($message['text']),
            'action' => $message['action'],
        ])->values();
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
        .ivo-agent { animation: ivo-float 4s ease-in-out infinite; transform-origin: center; }
        .ivo-eye { animation: ivo-blink 5s infinite; transform-box: fill-box; transform-origin: center; }
        .ivo-antenna { animation: ivo-pulse 1.6s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        .ivo-arm { animation: ivo-wave 2.8s ease-in-out infinite; transform-box: fill-box; transform-origin: top center; }
        .ivo-halo { animation: ivo-halo 3s ease-in-out infinite; }
        .ivo-mouth { transform-box: fill-box; transform-origin: center; transition: transform .2s; }
        .is-talking .ivo-mouth { animation: ivo-talk .28s ease-in-out infinite alternate; }
        .is-talking .ivo-chest { animation: ivo-pulse .5s ease-in-out infinite; }
        .ivo-chest { transform-box: fill-box; transform-origin: center; }
        .ivo-scan { animation: ivo-scan 3.5s linear infinite; }
        .ivo-msg { animation: ivo-in .45s cubic-bezier(.2,.8,.2,1) both; }
        .ivo-dot { animation: ivo-dots 1.2s infinite ease-in-out; }
        .ivo-dot:nth-child(2) { animation-delay: .15s; }
        .ivo-dot:nth-child(3) { animation-delay: .3s; }
        .ivo-bar { animation: ivo-grow .8s cubic-bezier(.2,.8,.2,1) both; transform-origin: bottom; }
        .ivo-caret::after { content: '▍'; margin-left: 1px; animation: ivo-caret 1s steps(1) infinite; }
        @keyframes ivo-float { 0%, 100% { transform: translateY(0) } 50% { transform: translateY(-8px) } }
        @keyframes ivo-blink { 0%, 92%, 100% { transform: scaleY(1) } 95% { transform: scaleY(.1) } }
        @keyframes ivo-pulse { 0%, 100% { transform: scale(1); opacity: 1 } 50% { transform: scale(1.35); opacity: .6 } }
        @keyframes ivo-wave { 0%, 70%, 100% { transform: rotate(0) } 80% { transform: rotate(-22deg) } 90% { transform: rotate(8deg) } }
        @keyframes ivo-halo { 0%, 100% { opacity: .35; transform: scale(.95) } 50% { opacity: .7; transform: scale(1.05) } }
        @keyframes ivo-talk { from { transform: scaleY(.35) } to { transform: scaleY(1.4) } }
        @keyframes ivo-scan { 0% { transform: translateX(-30px) } 100% { transform: translateX(30px) } }
        @keyframes ivo-in { from { opacity: 0; transform: translateY(10px) scale(.98) } to { opacity: 1; transform: none } }
        @keyframes ivo-dots { 0%, 80%, 100% { transform: translateY(0); opacity: .4 } 40% { transform: translateY(-4px); opacity: 1 } }
        @keyframes ivo-grow { from { transform: scaleY(0) } to { transform: scaleY(1) } }
        @keyframes ivo-caret { 50% { opacity: 0 } }
        @media (prefers-reduced-motion: reduce) {
            .ivo-agent, .ivo-eye, .ivo-antenna, .ivo-arm, .ivo-halo, .ivo-scan, .is-talking .ivo-mouth, .ivo-msg, .ivo-bar { animation: none !important; }
        }
        .ivo-answer table { width: 100%; font-size: 11px; border-collapse: collapse; margin-top: .5rem; }
        .ivo-answer th { text-align: left; font-weight: 600; color: #94A3B8; padding: .35rem .5rem; border-bottom: 1px solid rgba(148,163,184,.2); }
        .ivo-answer td { padding: .35rem .5rem; border-bottom: 1px solid rgba(148,163,184,.1); color: #E2E8F0; }
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
            'messages' => $briefingMessages,
            'queryUrl' => route('ai.query'),
        ]))"
        x-init="start()"
        class="relative overflow-hidden rounded-3xl mb-6 text-white"
        style="background: radial-gradient(120% 140% at 0% 0%, #13306B 0%, #0B1A3A 45%, #070D1C 100%);"
    >
        <div class="pointer-events-none absolute inset-0 opacity-[0.15]" style="background-image: radial-gradient(rgba(255,255,255,.5) 1px, transparent 1px); background-size: 22px 22px;"></div>

        <div class="relative grid grid-cols-1 lg:grid-cols-[260px_minmax(0,1fr)]">
            {{-- Avatar animé --}}
            <div class="flex flex-row lg:flex-col items-center gap-4 p-5 sm:p-6 lg:border-r border-b lg:border-b-0 border-white/10">
                <div class="relative w-24 h-24 sm:w-32 sm:h-32 lg:w-44 lg:h-44 shrink-0" :class="talking && 'is-talking'">
                    <div class="ivo-halo absolute inset-2 rounded-full blur-2xl" style="background: radial-gradient(circle, #2F80FF 0%, transparent 70%);"></div>
                    <svg class="ivo-agent relative w-full h-full" viewBox="0 0 200 200" aria-label="Ivo, agent commercial">
                        <defs>
                            <linearGradient id="ivoHead" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#F8FAFC"/><stop offset="1" stop-color="#CBD5E1"/></linearGradient>
                            <linearGradient id="ivoVisor" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#0B1A3A"/><stop offset="1" stop-color="#14254D"/></linearGradient>
                            <clipPath id="ivoVisorClip"><rect x="58" y="66" width="84" height="46" rx="20"/></clipPath>
                        </defs>
                        <line x1="100" y1="34" x2="100" y2="50" stroke="#CBD5E1" stroke-width="4" stroke-linecap="round"/>
                        <circle class="ivo-antenna" cx="100" cy="30" r="7" fill="#22D3EE"/>
                        <g class="ivo-arm"><rect x="38" y="128" width="16" height="40" rx="8" fill="url(#ivoHead)"/></g>
                        <rect x="146" y="128" width="16" height="40" rx="8" fill="url(#ivoHead)"/>
                        <rect x="58" y="122" width="84" height="56" rx="22" fill="url(#ivoHead)"/>
                        <circle class="ivo-chest" cx="100" cy="148" r="8" fill="#0066FF"/>
                        <circle cx="100" cy="148" r="3.5" fill="#BAE6FD"/>
                        <rect x="44" y="48" width="112" height="80" rx="32" fill="url(#ivoHead)"/>
                        <rect x="36" y="78" width="10" height="22" rx="5" fill="#94A3B8"/>
                        <rect x="154" y="78" width="10" height="22" rx="5" fill="#94A3B8"/>
                        <rect x="58" y="66" width="84" height="46" rx="20" fill="url(#ivoVisor)"/>
                        <g clip-path="url(#ivoVisorClip)"><rect class="ivo-scan" x="90" y="66" width="20" height="46" fill="#22D3EE" opacity=".12"/></g>
                        <ellipse class="ivo-eye" cx="83" cy="86" rx="7" ry="8" fill="#22D3EE"/>
                        <ellipse class="ivo-eye" cx="117" cy="86" rx="7" ry="8" fill="#22D3EE"/>
                        <rect class="ivo-mouth" x="90" y="100" width="20" height="4" rx="2" fill="#22D3EE"/>
                    </svg>
                </div>
                <div class="min-w-0 lg:text-center">
                    <p class="text-base sm:text-lg font-semibold">Ivo</p>
                    <p class="text-xs text-sky-200/80">Agent commercial intelligent</p>
                    <span class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 text-[11px] text-sky-100">
                        <span class="relative flex w-2 h-2">
                            <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                            <span class="relative inline-flex w-2 h-2 rounded-full bg-emerald-400"></span>
                        </span>
                        <span x-text="talking ? 'Ivo parle…' : (thinking ? 'Ivo analyse…' : @js($moodLabels[$agent['mood']]))">{{ $moodLabels[$agent['mood']] }}</span>
                    </span>
                    <button type="button" @click="replay()" class="mt-3 hidden lg:inline-flex items-center gap-1.5 text-[11px] text-sky-200/80 hover:text-white transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Rejouer le briefing
                    </button>
                </div>
            </div>

            {{-- Conversation --}}
            <div class="flex flex-col min-w-0">
                <div x-ref="feed" class="flex-1 space-y-2.5 p-4 sm:p-6 overflow-y-auto max-h-[420px] lg:max-h-[460px]">
                    <div class="rounded-2xl rounded-tl-md bg-white/10 px-4 py-3 text-sm leading-relaxed" :class="greetingDone || 'ivo-caret'" x-text="typedGreeting">{{ $agent['greeting'] }}</div>

                    <template x-for="(message, index) in shown" :key="'b' + index">
                        <div class="ivo-msg flex items-start gap-3 rounded-2xl border bg-white/[0.06] px-4 py-3" :class="toneRing(message.tone)">
                            <span class="mt-1.5 w-2 h-2 shrink-0 rounded-full" :class="toneDot(message.tone)"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm leading-relaxed text-slate-100 [&_strong]:text-white [&_strong]:font-semibold" x-html="message.html"></p>
                                <template x-if="message.action">
                                    <a :href="message.action.url" class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-medium text-sky-300 hover:text-white">
                                        <span x-text="message.action.label"></span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-for="(entry, index) in chat" :key="'c' + index">
                        <div class="ivo-msg" :class="entry.from === 'user' ? 'flex justify-end' : ''">
                            <div x-show="entry.from === 'user'" class="max-w-[85%] rounded-2xl rounded-tr-md bg-[#0066FF] px-4 py-2.5 text-sm" x-text="entry.text"></div>
                            <div x-show="entry.from === 'agent'" class="ivo-answer rounded-2xl rounded-tl-md bg-white/10 px-4 py-3 text-sm leading-relaxed text-slate-100 [&_strong]:text-white overflow-x-auto" x-html="entry.html"></div>
                        </div>
                    </template>

                    <div x-show="thinking || pending" class="flex items-center gap-1.5 rounded-2xl bg-white/10 px-4 py-3 w-fit">
                        <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-sky-300"></span>
                        <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-sky-300"></span>
                        <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-sky-300"></span>
                    </div>

                    <noscript>
                        @foreach($briefingMessages as $message)
                            <p class="text-sm text-slate-100">{!! $message['html'] !!}</p>
                        @endforeach
                    </noscript>
                </div>

                <div class="border-t border-white/10 p-3 sm:p-4 space-y-3">
                    <div class="flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none]">
                        @foreach($quickQuestions as $question)
                            <button type="button" @click="ask(@js($question))" :disabled="thinking"
                                class="shrink-0 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-[11px] text-sky-100 hover:bg-white/15 hover:border-white/30 transition disabled:opacity-50">
                                {{ $question }}
                            </button>
                        @endforeach
                    </div>
                    <form @submit.prevent="ask(question)" class="flex items-center gap-2">
                        <input x-model="question" type="text" maxlength="500" placeholder="Posez une question à Ivo : ventes, clients, stock, factures…"
                            class="min-w-0 flex-1 rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-0">
                        <button type="submit" :disabled="thinking || ! question.trim()"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#0066FF] text-white hover:bg-[#0052CC] transition disabled:opacity-50" aria-label="Envoyer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </button>
                    </form>
                </div>
            </div>
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
                    typedGreeting: '',
                    greetingDone: false,
                    shown: [],
                    chat: [],
                    talking: false,
                    thinking: false,
                    pending: false,
                    question: '',
                    run: 0,

                    toneDot(tone) {
                        return { good: 'bg-emerald-400', warn: 'bg-amber-400', alert: 'bg-rose-400' }[tone] ?? 'bg-sky-400';
                    },
                    toneRing(tone) {
                        return { good: 'border-emerald-400/30', warn: 'border-amber-400/30', alert: 'border-rose-400/40' }[tone] ?? 'border-sky-400/25';
                    },
                    scroll() {
                        this.$nextTick(() => { this.$refs.feed.scrollTop = this.$refs.feed.scrollHeight; });
                    },
                    async start() {
                        const run = ++this.run;
                        this.typedGreeting = '';
                        this.greetingDone = false;
                        this.shown = [];

                        if (reducedMotion) {
                            this.typedGreeting = config.greeting;
                            this.greetingDone = true;
                            this.shown = [...config.messages];
                            return;
                        }

                        this.talking = true;
                        for (const char of config.greeting) {
                            if (run !== this.run) { return; }
                            this.typedGreeting += char;
                            await wait(18);
                        }
                        this.greetingDone = true;

                        for (const message of config.messages) {
                            if (run !== this.run) { return; }
                            this.talking = false;
                            this.pending = true;
                            this.scroll();
                            await wait(650);
                            if (run !== this.run) { return; }
                            this.pending = false;
                            this.talking = true;
                            this.shown.push(message);
                            this.scroll();
                            await wait(450);
                        }
                        this.talking = false;
                    },
                    replay() {
                        this.chat = [];
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
                        setTimeout(() => { this.talking = false; }, 1400);
                    },
                    render(result) {
                        let html = `<p>${rich(result.answer)}</p>`;

                        if (Array.isArray(result.metrics) && result.metrics.length) {
                            html += '<div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.5rem">'
                                + result.metrics.map((metric) => `<span style="border-radius:.6rem;background:rgba(255,255,255,.1);padding:.3rem .6rem;font-size:11px"><span style="color:#94A3B8">${escapeHtml(metric.label)}</span> <strong>${escapeHtml(metric.value)}</strong></span>`).join('')
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
