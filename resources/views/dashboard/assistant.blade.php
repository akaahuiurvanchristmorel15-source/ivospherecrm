<x-layouts.app title="Accueil — IVOSPHERE ERP">

    <style>
        main:has(#home-assistant) { background: #F5F7FA; transition: background-color .3s; }
        [data-offer-theme=night] main:has(#home-assistant) { background: #000000; }
        [data-offer-theme=night] body:has(#home-assistant) { background: #000000; }
        .ivo-orb-ring { background: conic-gradient(from 0deg, #0066FF, #22D3EE, #7C3AED, #0066FF); animation: ivo-spin 6s linear infinite; }
        .ivo-orb-core { background: radial-gradient(circle at 30% 25%, #FFFFFF 0%, #DCEBFF 22%, #3B82F6 58%, #1E3A8A 100%); animation: ivo-breathe 4s ease-in-out infinite; }
        .is-thinking .ivo-orb-ring { animation-duration: 1.2s; }
        .ivo-in { animation: ivo-in .5s cubic-bezier(.2,.8,.2,1) both; animation-delay: var(--d, 0ms); }
        .ivo-dot { animation: ivo-dots 1.2s infinite ease-in-out; }
        .ivo-dot:nth-child(2) { animation-delay: .15s; }
        .ivo-dot:nth-child(3) { animation-delay: .3s; }
        @keyframes ivo-spin { to { transform: rotate(360deg) } }
        @keyframes ivo-breathe { 0%, 100% { transform: scale(1) } 50% { transform: scale(1.07) } }
        @keyframes ivo-in { from { opacity: 0; transform: translateY(14px) } to { opacity: 1; transform: none } }
        @keyframes ivo-dots { 0%, 80%, 100% { opacity: .25 } 40% { opacity: 1 } }
        [x-cloak] { display: none !important; }
        #home-assistant textarea:focus-visible { outline: none !important; }
        @media (prefers-reduced-motion: reduce) { .ivo-orb-ring, .ivo-orb-core, .ivo-in { animation: none !important; } }
        .ivo-answer strong { font-weight: 600; color: #0B0F14; }
        .ivo-answer table { width: 100%; font-size: 12px; border-collapse: collapse; margin-top: .75rem; }
        .ivo-answer th { text-align: left; font-weight: 500; color: rgba(0,0,0,.45); padding: .5rem .6rem; border-bottom: 1px solid rgba(0,0,0,.08); white-space: nowrap; }
        .ivo-answer td { padding: .5rem .6rem; border-bottom: 1px solid rgba(0,0,0,.05); color: #0B0F14; }
        .ivo-answer .ivo-metric { display: inline-flex; gap: .35rem; border: 1px solid rgba(0,0,0,.08); border-radius: 999px; padding: .3rem .75rem; font-size: 12px; background: rgba(255,255,255,.6); }
        .ivo-answer .ivo-metric span { color: rgba(0,0,0,.45); }
        [data-offer-theme=night] .ivo-answer strong, [data-offer-theme=night] .ivo-answer td { color: #FFFFFF; }
        [data-offer-theme=night] .ivo-answer th { color: rgba(255,255,255,.45); border-color: rgba(255,255,255,.12); }
        [data-offer-theme=night] .ivo-answer td { border-color: rgba(255,255,255,.06); }
        [data-offer-theme=night] .ivo-answer .ivo-metric { border-color: rgba(255,255,255,.12); background: rgba(255,255,255,.05); }
        [data-offer-theme=night] .ivo-answer .ivo-metric span { color: rgba(255,255,255,.45); }
    </style>

    <div
        id="home-assistant"
        x-data="homeAssistant(@js(['queryUrl' => route('ai.query')]))"
        class="relative flex flex-col min-h-[calc(100dvh-11rem)] md:min-h-[calc(100dvh-9rem)] text-[#0B0F14] night:text-white overflow-x-clip"
    >
        {{-- Halo discret --}}
        <div class="pointer-events-none absolute -top-48 left-1/2 -translate-x-1/2 w-[1000px] max-w-[200vw] h-[600px] rounded-full bg-[#0066FF]/[0.07] blur-3xl night:bg-white/[0.06]"></div>

        {{-- ─────────── Accueil : titre géant + zone de saisie en verre ─────────── --}}
        <div x-show="! chat.length" class="relative flex-1 flex flex-col items-center pt-8 sm:pt-12">
            <span class="ivo-in inline-flex items-center gap-2 rounded-full border border-black/10 bg-white/60 px-3 py-1 text-[11px] font-medium text-black/60 backdrop-blur-xl night:border-white/15 night:bg-white/5 night:text-white/70">
                <span class="relative w-3.5 h-3.5" aria-hidden="true">
                    <span class="ivo-orb-ring absolute inset-0 rounded-full"></span>
                    <span class="ivo-orb-core absolute inset-[2px] rounded-full"></span>
                </span>
                Ivo · Assistant IA IVOSPHERE
            </span>

            <h1 class="ivo-in select-none mt-6 text-center font-extrabold tracking-tighter leading-none text-[#0B0F14] night:text-white text-[19vw] sm:text-[15vw] lg:text-[170px]" style="--d:80ms">
                {{ $greeting }}
            </h1>

            @if($canUseAssistant)
                <div class="ivo-in relative z-10 w-full max-w-2xl mt-6 sm:mt-8" style="--d:160ms">
                    @include('dashboard._composer')
                </div>

                <p class="ivo-in mt-4 text-center text-sm text-black/50 night:text-white/50" style="--d:220ms">
                    {{ $firstName }}, que voulez-vous savoir aujourd'hui ?
                </p>

                <div class="ivo-in mt-5 flex max-w-3xl flex-wrap justify-center gap-2" style="--d:280ms">
                    @foreach($suggestions as $suggestion)
                        <button type="button" @click="ask(@js($suggestion))"
                            class="rounded-full border border-black/10 bg-white/55 px-3.5 py-1.5 text-xs text-black/60 backdrop-blur-xl hover:border-black/25 hover:text-black night:border-white/15 night:bg-white/[0.04] night:text-white/65 night:hover:border-white/30 night:hover:text-white transition">
                            {{ $suggestion }}
                        </button>
                    @endforeach
                </div>
            @else
                <p class="ivo-in mt-6 max-w-md text-center text-sm text-black/50 night:text-white/50" style="--d:160ms">
                    L'assistant IA n'est pas activé pour votre rôle. Accédez directement à vos modules :
                </p>
                <div class="ivo-in mt-5 flex max-w-3xl flex-wrap justify-center gap-2" style="--d:220ms">
                    @foreach($modules as $moduleLabel => $pages)
                        @continue($moduleLabel === 'Général')
                        <a href="{{ $pages->first()['url'] }}" class="rounded-full border border-black/10 bg-white/55 px-3.5 py-1.5 text-xs text-black/60 backdrop-blur-xl hover:border-black/25 hover:text-black night:border-white/15 night:bg-white/[0.04] night:text-white/65 night:hover:border-white/30 night:hover:text-white transition">{{ $moduleLabel }}</a>
                    @endforeach
                </div>
            @endif

            <div class="ivo-in mt-10 pb-6 flex justify-center gap-6 text-xs text-black/45 night:text-white/45" style="--d:340ms">
                <a href="{{ route('my-day') }}" class="hover:text-black night:hover:text-white transition">Ma journée</a>
                <a href="{{ route('dashboard.indicators') }}" class="hover:text-black night:hover:text-white transition">Voir les indicateurs →</a>
            </div>
        </div>

        {{-- ─────────── Conversation ─────────── --}}
        <div x-show="chat.length" x-cloak class="relative flex-1 w-full max-w-3xl mx-auto pt-12 pb-6 space-y-7">
            <template x-for="(entry, index) in chat" :key="index">
                <div class="ivo-in">
                    <div x-show="entry.from === 'user'" class="flex justify-end">
                        <div class="max-w-[85%] rounded-3xl rounded-br-lg bg-[#0B0F14] px-4 py-2.5 text-sm text-white" x-text="entry.text"></div>
                    </div>

                    <div x-show="entry.from === 'agent'" class="flex items-start gap-3">
                        <div class="relative mt-0.5 w-7 h-7 shrink-0" aria-hidden="true">
                            <div class="ivo-orb-ring absolute inset-0 rounded-full"></div>
                            <div class="ivo-orb-core absolute inset-[3px] rounded-full"></div>
                        </div>
                        <div class="min-w-0 flex-1 rounded-3xl rounded-tl-lg border border-black/[0.08] bg-white/55 px-5 py-4 backdrop-blur-2xl night:border-white/10 night:bg-white/[0.04]">
                            <div class="ivo-answer text-[15px] leading-relaxed text-black/70 night:text-white/75 overflow-x-auto" x-html="entry.html"></div>

                            <template x-if="entry.links && entry.links.length">
                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <template x-for="link in entry.links" :key="link.url">
                                        <a :href="link.url" class="group flex items-center justify-between gap-3 rounded-2xl border border-black/[0.08] bg-white/60 px-4 py-3 hover:border-black/25 night:border-white/10 night:bg-white/[0.04] night:hover:border-white/30 transition">
                                            <span class="min-w-0">
                                                <span class="block text-sm font-medium truncate" x-text="link.label"></span>
                                                <span class="block text-[11px] text-black/45 night:text-white/45 truncate" x-text="link.module"></span>
                                            </span>
                                            <svg class="w-4 h-4 shrink-0 text-black/35 group-hover:text-black night:text-white/40 night:group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <template x-if="entry.suggestions && entry.suggestions.length">
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <template x-for="suggestion in entry.suggestions" :key="suggestion">
                                        <button type="button" @click="ask(suggestion)" class="rounded-full border border-black/10 bg-white/55 px-3 py-1 text-xs text-black/60 hover:text-black night:border-white/15 night:bg-white/[0.04] night:text-white/65 night:hover:text-white transition" x-text="suggestion"></button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="thinking" class="flex items-center gap-3">
                <div class="relative w-7 h-7 shrink-0 is-thinking" aria-hidden="true">
                    <div class="ivo-orb-ring absolute inset-0 rounded-full"></div>
                    <div class="ivo-orb-core absolute inset-[3px] rounded-full"></div>
                </div>
                <div class="flex items-center gap-1">
                    <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-black/40 night:bg-white/50"></span>
                    <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-black/40 night:bg-white/50"></span>
                    <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-black/40 night:bg-white/50"></span>
                </div>
            </div>
            <div x-ref="end"></div>
        </div>

        <div x-show="chat.length" x-cloak class="sticky bottom-0 z-10 -mx-3.5 sm:mx-0 px-3.5 sm:px-0 pt-6 pb-1 bg-gradient-to-t from-[#F5F7FA] via-[#F5F7FA] to-transparent night:from-black night:via-black">
            <div class="max-w-3xl mx-auto">
                @include('dashboard._composer', ['compact' => true])
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function homeAssistant(config) {
                const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
                const rich = (value) => escapeHtml(value).replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');

                return {
                    chat: [],
                    question: '',
                    thinking: false,
                    menuOpen: false,
                    resize(element) {
                        element.style.height = 'auto';
                        element.style.height = Math.min(element.scrollHeight, 180) + 'px';
                    },
                    scroll() {
                        this.$nextTick(() => this.$refs.end?.scrollIntoView({ behavior: 'smooth', block: 'end' }));
                    },
                    reset() {
                        this.chat = [];
                        this.question = '';
                    },
                    async ask(text) {
                        const query = String(text ?? '').trim();
                        if (! query || this.thinking) { return; }

                        this.menuOpen = false;
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
                            const result = await response.json();
                            this.chat.push({ from: 'agent', html: this.render(result), links: result.links ?? [], suggestions: result.intent === 'help' || result.intent === 'introduction' ? (result.suggestions ?? []) : [] });
                        } catch (error) {
                            this.chat.push({ from: 'agent', html: "Désolé, je n'ai pas pu répondre pour le moment. Réessayez dans un instant.", links: [], suggestions: [] });
                        }

                        this.thinking = false;
                        this.scroll();
                    },
                    render(result) {
                        let html = `<p>${rich(result.answer)}</p>`;

                        if (Array.isArray(result.metrics) && result.metrics.length) {
                            html += '<div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.75rem">'
                                + result.metrics.map((metric) => `<span class="ivo-metric"><span>${escapeHtml(metric.label)}</span><strong>${escapeHtml(metric.value)}</strong></span>`).join('')
                                + '</div>';
                        }

                        if (result.table && Array.isArray(result.table.rows) && result.table.rows.length) {
                            html += '<table><thead><tr>' + result.table.columns.map((column) => `<th>${escapeHtml(column)}</th>`).join('') + '</tr></thead><tbody>'
                                + result.table.rows.slice(0, 12).map((row) => '<tr>' + row.map((cell) => `<td>${escapeHtml(cell)}</td>`).join('') + '</tr>').join('')
                                + '</tbody></table>';
                        }

                        return html;
                    },
                };
            }
        </script>
    @endpush
</x-layouts.app>
