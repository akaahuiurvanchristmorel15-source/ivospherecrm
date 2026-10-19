@php
    $documentTypes = [
        'commercial_pitch' => ['Proposition commerciale', 'Offre structurée pour un prospect'],
        'invoice_followup_email' => ['Relance de facture', 'E-mail courtois et ferme'],
        'product_description' => ['Fiche produit', 'Texte catalogue ou site web'],
        'social_post' => ['Post réseaux sociaux', 'LinkedIn, Facebook'],
        'slogans' => ['Slogans', '5 accroches de campagne'],
        'video_script' => ['Script vidéo 30 s', 'Plans et voix off'],
        'executive_report' => ['Synthèse exécutive', 'Avec les chiffres du mois'],
    ];
    $field = 'h-10 w-full rounded-xl border border-[#E2E8F0] bg-white px-3 text-sm text-[#0B0F14] placeholder:text-slate-400 transition focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15';
@endphp

<x-layouts.app title="Assistant IVOSPHERE">
    <div x-data="aiWorkspace({
            queryUrl: '{{ route('ai.query') }}',
            initialTab: '{{ auth()->user()->hasPermission('ai.assistant') ? 'assistant' : 'studio' }}',
            generateUrl: '{{ route('ai.generate') }}',
            examples: {{ Js::from($defaultSuggestions) }},
         })"
         class="mx-auto max-w-5xl space-y-5">

        {{-- En-tête --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#0B0F14] text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/></svg>
                </span>
                <div>
                    <h1 class="text-xl font-semibold tracking-tight text-[#0B0F14] sm:text-2xl">Assistant IVOSPHERE</h1>
                    <p class="text-sm text-[#64748B]">Posez vos questions sur l'entreprise, en français, comme à un collègue.</p>
                </div>
            </div>

            @php
                $canAsk = auth()->user()->hasPermission('ai.assistant');
                $canGenerate = auth()->user()->hasPermission('ai.studio');
            @endphp
            @if($canAsk && $canGenerate)
            <div class="inline-flex rounded-xl bg-[#F1F5F9] p-1 text-sm" role="tablist">
                <button type="button" @click="tab = 'assistant'" :aria-selected="tab === 'assistant'"
                        :class="tab === 'assistant' ? 'bg-white text-[#0B0F14] shadow-sm' : 'text-[#64748B] hover:text-[#0B0F14]'"
                        class="rounded-lg px-4 py-1.5 font-medium transition">Questions</button>
                <button type="button" @click="tab = 'studio'" :aria-selected="tab === 'studio'"
                        :class="tab === 'studio' ? 'bg-white text-[#0B0F14] shadow-sm' : 'text-[#64748B] hover:text-[#0B0F14]'"
                        class="rounded-lg px-4 py-1.5 font-medium transition">Rédaction</button>
            </div>
            @endif
        </header>

        {{-- ═══════════════ QUESTIONS ═══════════════ --}}
        @if($canAsk)
        <section x-show="tab === 'assistant'" class="flex min-h-[60vh] flex-col overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white">

            {{-- Conversation --}}
            <div x-ref="thread" class="flex-1 space-y-6 overflow-y-auto p-4 sm:p-6" style="max-height: calc(100dvh - 18rem)">

                {{-- Accueil --}}
                <template x-if="messages.length === 0">
                    <div class="mx-auto max-w-2xl py-6 text-center sm:py-10">
                        <p class="text-base font-semibold text-[#0B0F14]">Que voulez-vous savoir ?</p>
                        <p class="mt-1 text-sm text-[#64748B]">Ventes, produits, stock, clients, factures, trésorerie, dépenses, projets. Précisez un pôle et une période si besoin.</p>
                        <div class="mt-6 grid grid-cols-1 gap-2 text-left sm:grid-cols-2">
                            <template x-for="example in examples" :key="example">
                                <button type="button" @click="ask(example)"
                                        class="group flex items-center justify-between gap-3 rounded-xl border border-[#E2E8F0] px-3.5 py-3 text-sm text-[#0B0F14] transition hover:border-[#0066FF] hover:bg-[#0066FF]/[0.03]">
                                    <span x-text="example"></span>
                                    <svg class="h-4 w-4 shrink-0 text-[#94A3B8] transition group-hover:translate-x-0.5 group-hover:text-[#0066FF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Messages --}}
                <template x-for="(message, index) in messages" :key="index">
                    <div>
                        {{-- Question --}}
                        <template x-if="message.role === 'user'">
                            <div class="flex justify-end">
                                <p class="max-w-[85%] rounded-2xl rounded-br-md bg-[#0B0F14] px-4 py-2.5 text-sm text-white" x-text="message.text"></p>
                            </div>
                        </template>

                        {{-- Réponse --}}
                        <template x-if="message.role === 'assistant'">
                            <div class="flex gap-3">
                                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#0066FF] text-[11px] font-bold text-white">IA</span>
                                <div class="min-w-0 flex-1 space-y-3">
                                    <template x-if="message.understood?.length">
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="chip in message.understood" :key="chip">
                                                <span class="rounded-md bg-[#F1F5F9] px-2 py-0.5 text-[11px] font-medium text-[#64748B]" x-text="chip"></span>
                                            </template>
                                        </div>
                                    </template>

                                    <div class="text-sm leading-relaxed text-[#0B0F14] [&_strong]:font-semibold [&_em]:text-[#64748B]" x-html="format(message.answer)"></div>

                                    {{-- Chiffres clés --}}
                                    <template x-if="message.metrics?.length">
                                        <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
                                            <template x-for="metric in message.metrics" :key="metric.label">
                                                <div class="rounded-xl border border-[#E2E8F0] p-3">
                                                    <p class="truncate text-[11px] text-[#64748B]" x-text="metric.label"></p>
                                                    <p class="mt-0.5 truncate text-base font-semibold tabular-nums text-[#0B0F14]" x-text="metric.value"></p>
                                                    <template x-if="metric.trend !== undefined && metric.trend !== null">
                                                        <p class="text-[11px] font-medium tabular-nums"
                                                           :class="(metric.inverse ? -metric.trend : metric.trend) >= 0 ? 'text-emerald-600' : 'text-rose-600'"
                                                           x-text="(metric.trend > 0 ? '▲ +' : (metric.trend < 0 ? '▼ ' : '')) + metric.trend + ' %'"></p>
                                                    </template>
                                                    <template x-if="metric.hint">
                                                        <p class="text-[11px] text-[#94A3B8]" x-text="metric.hint"></p>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Tableau --}}
                                    <template x-if="message.table?.rows?.length">
                                        <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
                                            <table class="w-full text-left text-sm">
                                                <thead class="bg-[#F8FAFC] text-[11px] uppercase tracking-wide text-[#64748B]">
                                                    <tr>
                                                        <template x-for="column in message.table.columns" :key="column">
                                                            <th class="whitespace-nowrap px-3 py-2 font-medium" x-text="column"></th>
                                                        </template>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-[#E2E8F0]">
                                                    <template x-for="(row, rowIndex) in message.table.rows" :key="rowIndex">
                                                        <tr>
                                                            <template x-for="(cell, cellIndex) in row" :key="cellIndex">
                                                                <td class="whitespace-nowrap px-3 py-2 tabular-nums" :class="cellIndex === 0 ? 'font-medium text-[#0B0F14]' : 'text-[#334155]'" x-text="cell"></td>
                                                            </template>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </template>

                                    {{-- Actions & relances --}}
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <template x-for="suggestion in message.suggestions || []" :key="suggestion">
                                            <button type="button" @click="ask(suggestion)" :disabled="loading"
                                                    class="rounded-full border border-[#E2E8F0] px-3 py-1 text-xs text-[#334155] transition hover:border-[#0066FF] hover:text-[#0066FF] disabled:opacity-50"
                                                    x-text="suggestion"></button>
                                        </template>
                                        <button type="button" @click="copy(message)" class="ml-auto rounded-md px-2 py-1 text-xs text-[#94A3B8] hover:bg-[#F1F5F9] hover:text-[#0B0F14]"
                                                x-text="copiedIndex === index ? 'Copié' : 'Copier'"></button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Chargement --}}
                <div x-show="loading" x-cloak class="flex items-center gap-3">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#0066FF] text-[11px] font-bold text-white">IA</span>
                    <span class="flex gap-1">
                        <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-[#94A3B8]"></span>
                        <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-[#94A3B8] [animation-delay:120ms]"></span>
                        <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-[#94A3B8] [animation-delay:240ms]"></span>
                    </span>
                </div>
            </div>

            {{-- Saisie --}}
            <form @submit.prevent="ask(input)" class="border-t border-[#E2E8F0] p-3 sm:p-4">
                <div class="flex items-end gap-2 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] p-1.5 transition focus-within:border-[#0066FF] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#0066FF]/15">
                    <label for="ai-question" class="sr-only">Votre question</label>
                    <textarea id="ai-question" x-ref="input" x-model="input" rows="1" maxlength="500"
                              @keydown.enter.prevent="if (!$event.shiftKey) ask(input)"
                              @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 140) + 'px'"
                              placeholder="Ex : chiffre d'affaires de PRINT le mois dernier"
                              class="max-h-36 min-h-10 flex-1 resize-none border-0 bg-transparent px-2.5 py-2 text-base text-[#0B0F14] placeholder:text-[#94A3B8] focus:ring-0 sm:text-sm"></textarea>
                    <button type="submit" :disabled="loading || !input.trim()" aria-label="Envoyer"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#0066FF] text-white transition hover:bg-[#0052CC] disabled:bg-[#CBD5E1]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                    </button>
                </div>
                <div class="mt-2 flex items-center justify-between px-1 text-[11px] text-[#94A3B8]">
                    <span>Entrée pour envoyer · Maj + Entrée pour aller à la ligne</span>
                    <button type="button" x-show="messages.length" @click="messages = []" class="hover:text-[#0B0F14]">Nouvelle conversation</button>
                </div>
            </form>
        </section>
        @endif

        {{-- ═══════════════ RÉDACTION ═══════════════ --}}
        @if($canGenerate)
        <section x-show="tab === 'studio'" x-cloak class="grid grid-cols-1 gap-5 lg:grid-cols-[360px_minmax(0,1fr)]">
            <form @submit.prevent="generate()" class="space-y-4 rounded-2xl border border-[#E2E8F0] bg-white p-5">
                <fieldset>
                    <legend class="mb-2 text-xs font-semibold text-[#0B0F14]">Type de document</legend>
                    <div class="grid grid-cols-1 gap-1.5">
                        @foreach($documentTypes as $value => [$label, $hint])
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="{{ $value }}" x-model="studio.type" class="peer sr-only">
                                <span class="flex items-center justify-between gap-2 rounded-xl border border-[#E2E8F0] px-3 py-2 text-sm transition peer-checked:border-[#0066FF] peer-checked:bg-[#0066FF]/[0.04] peer-focus-visible:ring-2 peer-focus-visible:ring-[#0066FF]">
                                    <span class="font-medium text-[#0B0F14]">{{ $label }}</span>
                                    <span class="truncate text-[11px] text-[#94A3B8]">{{ $hint }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-[#0B0F14]">Sujet</span>
                    <input type="text" x-model="studio.topic" required maxlength="255" placeholder="Ex : impression de 5 000 catalogues" class="{{ $field }}">
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-[#0B0F14]">Pôle</span>
                    <select x-model="studio.domain" class="{{ $field }}">
                        <option value="IVOSPHERE">IVOSPHERE (groupe)</option>
                        @foreach($domains as $domain)
                            <option value="{{ $domain->name }}">{{ $domain->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-[#0B0F14]">Cible <span class="font-normal text-[#94A3B8]">(facultatif)</span></span>
                    <input type="text" x-model="studio.target" placeholder="Ex : PME, écoles, directions financières" class="{{ $field }}">
                </label>

                <button type="submit" :disabled="generating || !studio.topic.trim()"
                        class="h-11 w-full rounded-xl bg-[#0066FF] text-sm font-semibold text-white transition hover:bg-[#0052CC] disabled:bg-[#CBD5E1]"
                        x-text="generating ? 'Rédaction…' : 'Rédiger le document'"></button>
            </form>

            <div class="flex min-h-[420px] flex-col rounded-2xl border border-[#E2E8F0] bg-white">
                <template x-if="!document">
                    <div class="m-auto max-w-xs p-6 text-center">
                        <p class="text-sm font-medium text-[#0B0F14]">Votre document apparaîtra ici</p>
                        <p class="mt-1 text-xs text-[#64748B]">Choisissez un type, indiquez le sujet, puis modifiez librement le texte avant de le copier.</p>
                    </div>
                </template>
                <template x-if="document">
                    <div class="flex flex-1 flex-col">
                        <div class="flex items-start justify-between gap-3 border-b border-[#E2E8F0] px-5 py-4">
                            <div class="min-w-0">
                                <p class="text-[11px] font-medium uppercase tracking-wide text-[#0066FF]" x-text="document.format"></p>
                                <h2 class="truncate text-base font-semibold text-[#0B0F14]" x-text="document.title"></h2>
                            </div>
                            <button type="button" @click="copyDocument()" class="shrink-0 rounded-lg bg-[#0B0F14] px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800"
                                    x-text="documentCopied ? 'Copié' : 'Copier'"></button>
                        </div>
                        <textarea x-model="document.content" aria-label="Contenu du document"
                                  class="min-h-[340px] flex-1 resize-none border-0 p-5 text-sm leading-relaxed text-[#0B0F14] focus:ring-0"></textarea>
                    </div>
                </template>
            </div>
        </section>
        @endif
    </div>

    <script>
        function aiWorkspace(config) {
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            };

            return {
                tab: config.initialTab ?? 'assistant',
                examples: config.examples,
                messages: [],
                input: '',
                loading: false,
                copiedIndex: null,
                studio: { type: 'commercial_pitch', topic: '', domain: 'IVOSPHERE', target: '' },
                generating: false,
                document: null,
                documentCopied: false,

                async ask(question) {
                    question = (question || '').trim();
                    if (!question || this.loading) return;

                    this.messages.push({ role: 'user', text: question });
                    this.input = '';
                    this.$refs.input.style.height = 'auto';
                    this.loading = true;
                    this.scrollDown();

                    try {
                        const response = await fetch(config.queryUrl, { method: 'POST', headers, body: JSON.stringify({ query: question }) });
                        if (!response.ok) throw new Error();
                        this.messages.push({ role: 'assistant', ...(await response.json()) });
                    } catch (error) {
                        this.messages.push({ role: 'assistant', answer: "Désolé, je n'ai pas pu interroger les données. Réessayez dans un instant.", suggestions: [question] });
                    } finally {
                        this.loading = false;
                        this.scrollDown();
                        this.$nextTick(() => this.$refs.input.focus());
                    }
                },

                async generate() {
                    this.generating = true;
                    try {
                        const response = await fetch(config.generateUrl, { method: 'POST', headers, body: JSON.stringify(this.studio) });
                        if (!response.ok) throw new Error();
                        this.document = await response.json();
                        this.documentCopied = false;
                    } catch (error) {
                        alert('Impossible de générer le document. Vérifiez le sujet et réessayez.');
                    } finally {
                        this.generating = false;
                    }
                },

                scrollDown() {
                    this.$nextTick(() => { this.$refs.thread.scrollTop = this.$refs.thread.scrollHeight; });
                },

                // Texte échappé, puis **gras**, *italique* et retours à la ligne
                format(text) {
                    const escaped = String(text || '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
                    return escaped
                        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                        .replace(/(^|[^*])\*(?!\s)(.+?)\*/g, '$1<em>$2</em>')
                        .replace(/\n/g, '<br>');
                },

                copy(message) {
                    const lines = [message.answer.replace(/\*/g, '')];
                    (message.metrics || []).forEach(metric => lines.push(`${metric.label} : ${metric.value}`));
                    if (message.table?.rows?.length) {
                        lines.push('', message.table.columns.join('\t'), ...message.table.rows.map(row => row.join('\t')));
                    }
                    navigator.clipboard.writeText(lines.join('\n'));
                    this.copiedIndex = this.messages.indexOf(message);
                    setTimeout(() => { this.copiedIndex = null; }, 1500);
                },

                copyDocument() {
                    navigator.clipboard.writeText(this.document.content);
                    this.documentCopied = true;
                    setTimeout(() => { this.documentCopied = false; }, 1500);
                },
            };
        }
    </script>
</x-layouts.app>
