<x-layouts.app title="Accueil — IVOSPHERE ERP">
    <style>
        .ivo-orb-ring { background: conic-gradient(from 0deg, #0066FF, #22D3EE, #7C3AED, #0066FF); animation: ivo-spin 6s linear infinite; }
        .ivo-orb-core { background: radial-gradient(circle at 30% 25%, #FFFFFF 0%, #DCEBFF 22%, #3B82F6 58%, #1E3A8A 100%); animation: ivo-breathe 4s ease-in-out infinite; }
        .is-thinking .ivo-orb-ring { animation-duration: 1.2s; }
        .ivo-in { animation: ivo-in .45s cubic-bezier(.2,.8,.2,1) both; }
        .ivo-dot { animation: ivo-dots 1.2s infinite ease-in-out; }
        .ivo-dot:nth-child(2) { animation-delay: .15s; }
        .ivo-dot:nth-child(3) { animation-delay: .3s; }
        @keyframes ivo-spin { to { transform: rotate(360deg) } }
        @keyframes ivo-breathe { 0%, 100% { transform: scale(1) } 50% { transform: scale(1.07) } }
        @keyframes ivo-in { from { opacity: 0; transform: translateY(8px) } to { opacity: 1; transform: none } }
        @keyframes ivo-dots { 0%, 80%, 100% { opacity: .25 } 40% { opacity: 1 } }
        [x-cloak] { display: none !important; }
        #home-assistant textarea:focus-visible { outline: none !important; }
        @media (prefers-reduced-motion: reduce) { .ivo-orb-ring, .ivo-orb-core, .ivo-in { animation: none !important; } }
        .ivo-answer strong { font-weight: 600; color: #0B0F14; }
        .ivo-answer table { width: 100%; font-size: 12px; border-collapse: collapse; margin-top: .75rem; }
        .ivo-answer th { text-align: left; font-weight: 500; color: #64748B; padding: .45rem .6rem; border-bottom: 1px solid #E2E8F0; white-space: nowrap; }
        .ivo-answer td { padding: .45rem .6rem; border-bottom: 1px solid #F1F5F9; color: #0B0F14; }
    </style>

    <div
        id="home-assistant"
        x-data="homeAssistant(@js(['queryUrl' => route('ai.query')]))"
        class="relative flex flex-col min-h-[calc(100dvh-11rem)] md:min-h-[calc(100dvh-9rem)]"
    >
        {{-- Accueil : salutation + zone de saisie centrée --}}
        <div x-show="! chat.length" class="flex-1 flex flex-col items-center justify-center px-1 py-10">
            <div class="flex items-center gap-3 sm:gap-4 mb-7">
                <div class="relative w-9 h-9 sm:w-11 sm:h-11 shrink-0" aria-hidden="true">
                    <div class="ivo-orb-ring absolute inset-0 rounded-full"></div>
                    <div class="absolute inset-[2px] rounded-full bg-[#F5F7FA]"></div>
                    <div class="ivo-orb-core absolute inset-[5px] rounded-full"></div>
                </div>
                <h1 class="text-[28px] sm:text-[40px] leading-tight tracking-tight text-[#0B0F14]" style="font-family: Georgia, 'Times New Roman', serif;">
                    {{ $greeting }}, {{ $firstName }}
                </h1>
            </div>

            <div class="w-full max-w-2xl">
                @include('dashboard._composer')

                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    @foreach($suggestions as $suggestion)
                        <button type="button" @click="ask(@js($suggestion))"
                            class="rounded-full border border-[#E2E8F0] bg-[#FFFFFF] px-3.5 py-1.5 text-xs text-[#475569] hover:border-slate-300 hover:text-[#0B0F14] transition">
                            {{ $suggestion }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Conversation --}}
        <div x-show="chat.length" x-cloak class="flex-1 w-full max-w-2xl mx-auto pt-2 pb-6 space-y-6">
            <template x-for="(entry, index) in chat" :key="index">
                <div class="ivo-in">
                    <div x-show="entry.from === 'user'" class="flex justify-end">
                        <div class="max-w-[85%] rounded-2xl bg-[#E9EEF5] px-4 py-2.5 text-sm text-[#0B0F14]" x-text="entry.text"></div>
                    </div>

                    <div x-show="entry.from === 'agent'" class="flex items-start gap-3">
                        <div class="relative mt-0.5 w-7 h-7 shrink-0" aria-hidden="true">
                            <div class="ivo-orb-ring absolute inset-0 rounded-full"></div>
                            <div class="absolute inset-[1.5px] rounded-full bg-[#F5F7FA]"></div>
                            <div class="ivo-orb-core absolute inset-[4px] rounded-full"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="ivo-answer text-[15px] leading-relaxed text-[#334155] overflow-x-auto" x-html="entry.html"></div>

                            <template x-if="entry.links && entry.links.length">
                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <template x-for="link in entry.links" :key="link.url">
                                        <a :href="link.url" class="group flex items-center justify-between gap-3 rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] px-3.5 py-2.5 hover:border-[#0066FF]/40 transition">
                                            <span class="min-w-0">
                                                <span class="block text-sm font-medium text-[#0B0F14] truncate" x-text="link.label"></span>
                                                <span class="block text-[11px] text-[#94A3B8] truncate" x-text="link.module"></span>
                                            </span>
                                            <svg class="w-4 h-4 shrink-0 text-[#94A3B8] group-hover:text-[#0066FF] transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <template x-if="entry.suggestions && entry.suggestions.length">
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <template x-for="suggestion in entry.suggestions" :key="suggestion">
                                        <button type="button" @click="ask(suggestion)" class="rounded-full border border-[#E2E8F0] bg-[#FFFFFF] px-3 py-1 text-xs text-[#475569] hover:text-[#0B0F14] transition" x-text="suggestion"></button>
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
                    <div class="absolute inset-[1.5px] rounded-full bg-[#F5F7FA]"></div>
                    <div class="ivo-orb-core absolute inset-[4px] rounded-full"></div>
                </div>
                <div class="flex items-center gap-1">
                    <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-[#94A3B8]"></span>
                    <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-[#94A3B8]"></span>
                    <span class="ivo-dot w-1.5 h-1.5 rounded-full bg-[#94A3B8]"></span>
                </div>
            </div>
            <div x-ref="end"></div>
        </div>

        <div x-show="chat.length" x-cloak class="sticky bottom-0 -mx-3.5 sm:mx-0 px-3.5 sm:px-0 pt-3 pb-1 bg-gradient-to-t from-[#F5F7FA] via-[#F5F7FA] to-transparent">
            <div class="max-w-2xl mx-auto">
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
                                + result.metrics.map((metric) => `<span style="display:inline-flex;gap:.35rem;border:1px solid #E2E8F0;border-radius:.6rem;padding:.3rem .65rem;font-size:12px;background:#FFFFFF"><span style="color:#64748B">${escapeHtml(metric.label)}</span><strong>${escapeHtml(metric.value)}</strong></span>`).join('')
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
