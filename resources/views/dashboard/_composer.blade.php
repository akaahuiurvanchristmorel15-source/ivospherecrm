@php $compact = $compact ?? false; @endphp

<form @submit.prevent="ask(question)" class="relative rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_8px_24px_-12px_rgba(15,23,42,0.08)] focus-within:border-[#0066FF]/40 focus-within:ring-4 focus-within:ring-[#0066FF]/5 transition">
    <textarea
        x-model="question"
        @input="resize($el)"
        @keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); ask(question); }"
        rows="{{ $compact ? 1 : 2 }}"
        maxlength="500"
        placeholder="{{ $compact ? 'Posez une autre question…' : "Comment puis-je vous aider aujourd'hui ?" }}"
        aria-label="Question à l'assistant"
        class="block w-full resize-none border-0 bg-transparent px-4 sm:px-5 pt-4 pb-1 text-[15px] text-[#0B0F14] placeholder:text-[#94A3B8] focus:outline-none focus:ring-0"
    ></textarea>

    <div class="flex items-center justify-between gap-2 px-2.5 sm:px-3 pb-2.5 pt-1">
        <div class="relative" @click.outside="menuOpen = false">
            <button type="button" @click="menuOpen = ! menuOpen" title="Explorer les modules" aria-label="Explorer les modules"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#475569] hover:bg-[#F5F7FA] hover:text-[#0B0F14] transition">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14"/></svg>
            </button>

            <div x-show="menuOpen" x-transition.origin.bottom.left x-cloak
                class="absolute z-30 {{ $compact ? 'bottom-full mb-2' : 'top-full mt-2' }} left-0 w-72 max-h-80 overflow-y-auto rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] p-1.5 shadow-lg">
                <p class="px-2.5 pt-1.5 pb-1 text-[10px] font-medium uppercase tracking-wider text-[#94A3B8]">Demander le contenu d'un module</p>
                @foreach($modules as $moduleLabel => $pages)
                    @continue($moduleLabel === 'Général')
                    <button type="button" @click="ask(@js('Que contient le module '.$moduleLabel.' ?'))"
                        class="w-full flex items-center justify-between gap-2 rounded-lg px-2.5 py-2 text-left text-sm text-[#0B0F14] hover:bg-[#F5F7FA] transition">
                        <span class="truncate">{{ $moduleLabel }}</span>
                        <span class="text-[11px] text-[#94A3B8] tabular-nums">{{ $pages->count() }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-1.5">
            @if($compact)
                <button type="button" @click="reset()" class="h-8 rounded-lg px-2.5 text-xs text-[#64748B] hover:bg-[#F5F7FA] hover:text-[#0B0F14] transition">Nouvelle discussion</button>
            @endif
            <button type="submit" :disabled="thinking || ! question.trim()" aria-label="Envoyer"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#0B0F14] text-[#FFFFFF] hover:bg-[#1E293B] transition disabled:bg-[#E2E8F0] disabled:text-[#94A3B8]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-6 6m6-6l6 6"/></svg>
            </button>
        </div>
    </div>
</form>

@unless($compact)
    <div class="mt-2.5 flex items-center justify-between px-2 text-xs text-[#64748B]">
        <a href="{{ route('my-day') }}" class="hover:text-[#0B0F14] transition">Ma journée</a>
        <a href="{{ route('dashboard.indicators') }}" class="inline-flex items-center gap-1 hover:text-[#0B0F14] transition">
            Voir les indicateurs
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
@endunless
