@php $compact = $compact ?? false; @endphp

<form @submit.prevent="ask(question)" class="relative rounded-[28px] border border-black/10 bg-white/75 backdrop-blur-2xl shadow-[0_30px_80px_-30px_rgba(11,15,20,0.35)] focus-within:border-black/25 night:border-white/15 night:bg-white/[0.07] night:shadow-[0_0_80px_-20px_rgba(255,255,255,0.18)] night:focus-within:border-white/30 transition">
    <textarea
        x-model="question"
        @input="resize($el)"
        @keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); ask(question); }"
        rows="{{ $compact ? 1 : 2 }}"
        maxlength="500"
        placeholder="{{ $compact ? 'Posez une autre question…' : "Comment puis-je vous aider aujourd'hui ?" }}"
        aria-label="Question à l'assistant"
        class="block w-full resize-none border-0 bg-transparent px-5 sm:px-6 pt-5 pb-1 text-[15px] text-[#0B0F14] placeholder:text-black/35 focus:outline-none focus:ring-0 night:text-white night:placeholder:text-white/35"
    ></textarea>

    <div class="flex items-center justify-between gap-2 px-3 sm:px-4 pb-3 pt-1">
        <div class="relative" @click.outside="menuOpen = false">
            <button type="button" @click="menuOpen = ! menuOpen" title="Explorer les modules" aria-label="Explorer les modules"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full text-black/55 hover:bg-black/5 hover:text-black night:text-white/60 night:hover:bg-white/10 night:hover:text-white transition">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14"/></svg>
            </button>

            <div x-show="menuOpen" x-transition.origin.bottom.left x-cloak
                class="absolute z-30 {{ $compact ? 'bottom-full mb-2' : 'top-full mt-2' }} left-0 w-72 max-h-80 overflow-y-auto rounded-2xl border border-black/10 bg-white/90 p-1.5 shadow-xl backdrop-blur-2xl night:border-white/15 night:bg-[#0B0F14]/95">
                <p class="px-3 pt-2 pb-1 text-[10px] font-medium uppercase tracking-wider text-black/40 night:text-white/40">Demander le contenu d'un module</p>
                @foreach($modules as $moduleLabel => $pages)
                    @continue($moduleLabel === 'Général')
                    <button type="button" @click="ask(@js('Que contient le module '.$moduleLabel.' ?'))"
                        class="w-full flex items-center justify-between gap-2 rounded-xl px-3 py-2 text-left text-sm text-[#0B0F14] hover:bg-black/5 night:text-white night:hover:bg-white/10 transition">
                        <span class="truncate">{{ $moduleLabel }}</span>
                        <span class="text-[11px] text-black/40 night:text-white/40 tabular-nums">{{ $pages->count() }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-1.5">
            @if($compact)
                <button type="button" @click="reset()" class="h-9 rounded-full px-3 text-xs text-black/50 hover:bg-black/5 hover:text-black night:text-white/55 night:hover:bg-white/10 night:hover:text-white transition">Nouvelle discussion</button>
            @endif
            <button type="submit" :disabled="thinking || ! question.trim()" aria-label="Envoyer"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#0B0F14] text-white hover:bg-black transition disabled:bg-black/10 disabled:text-black/30 night:bg-white night:text-black night:hover:bg-white/90 night:disabled:bg-white/10 night:disabled:text-white/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-6 6m6-6l6 6"/></svg>
            </button>
        </div>
    </div>
</form>
