<x-layouts.app title="Calendrier Collaboratif Unifié">
    <div class="space-y-6" x-data="{ viewMode: 'agenda' }">

        <!-- Top Header & Controls -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E2E8F0]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-[#0066FF]/10 text-[#0066FF] font-bold uppercase tracking-wider">Planning Transversal</span>
                    <span class="text-xs text-slate-500">Mois en cours : {{ $selectedDate->translatedFormat('F Y') }}</span>
                </div>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight mt-1">Calendrier Collaboratif IVOSPHERE</h1>
                <p class="text-xs text-slate-500">Vue unifiée des rendez-vous commerciaux, tournages médias, congés et livraisons logistiques.</p>
            </div>

            <div class="flex items-center gap-2">
                <!-- Navigation Month -->
                <div class="inline-flex rounded-xl border border-[#E2E8F0] bg-white p-1 text-xs shadow-xs">
                    <a href="{{ route('calendar.index', ['date' => $selectedDate->copy()->subMonth()->format('Y-m-d')]) }}" class="p-1.5 hover:bg-slate-50 rounded-lg text-slate-600">
                        ◀
                    </a>
                    <span class="px-3 py-1.5 font-bold text-[#0B0F14]">{{ $selectedDate->translatedFormat('F Y') }}</span>
                    <a href="{{ route('calendar.index', ['date' => $selectedDate->copy()->addMonth()->format('Y-m-d')]) }}" class="p-1.5 hover:bg-slate-50 rounded-lg text-slate-600">
                        ▶
                    </a>
                </div>

                <!-- Mode Switcher -->
                <div class="inline-flex rounded-xl border border-[#E2E8F0] bg-white p-1 text-xs shadow-xs">
                    <button 
                        @click="viewMode = 'agenda'" 
                        :class="viewMode === 'agenda' ? 'bg-[#0066FF] text-white shadow-xs font-semibold' : 'text-slate-600 font-medium'"
                        class="px-3 py-1.5 rounded-lg transition-all"
                    >
                        Vue Agenda
                    </button>
                    <button 
                        @click="viewMode = 'calendar'" 
                        :class="viewMode === 'calendar' ? 'bg-[#0066FF] text-white shadow-xs font-semibold' : 'text-slate-600 font-medium'"
                        class="px-3 py-1.5 rounded-lg transition-all"
                    >
                        Grille Mensuelle
                    </button>
                </div>
            </div>
        </div>

        <!-- Legend Pills -->
        <div class="flex items-center gap-2 flex-wrap text-xs">
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-[#E2E8F0]">
                <span class="w-2 h-2 rounded-full bg-[#0066FF]"></span>
                <span class="text-slate-700">Commercial & Devis</span>
            </span>
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-[#E2E8F0]">
                <span class="w-2 h-2 rounded-full bg-[#0B0F14]"></span>
                <span class="text-slate-700">Shootings Média & Events</span>
            </span>
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-[#E2E8F0]">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="text-slate-700">Livraisons Logistiques</span>
            </span>
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-[#E2E8F0]">
                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                <span class="text-slate-700">Absences & Congés RH</span>
            </span>
        </div>

        <!-- VUE AGENDA (Liste chronologique) -->
        <div x-show="viewMode === 'agenda'" class="space-y-4">
            <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden divide-y divide-slate-100">
                @forelse($allCalendarEvents as $ev)
                    <div class="p-4 hover:bg-[#F5F7FA] transition-colors flex items-center justify-between text-xs gap-4">
                        <div class="flex items-center gap-4">
                            <!-- Date badge -->
                            <div class="w-14 h-14 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] flex flex-col items-center justify-center shrink-0">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">
                                    {{ \Carbon\Carbon::parse($ev['start'])->translatedFormat('M') }}
                                </span>
                                <span class="text-base font-extrabold text-[#0B0F14]">
                                    {{ \Carbon\Carbon::parse($ev['start'])->format('d') }}
                                </span>
                            </div>

                            <div>
                                <span class="font-bold text-sm text-[#0B0F14] block">{{ $ev['title'] }}</span>
                                <div class="flex items-center gap-3 text-[11px] text-slate-500 mt-1">
                                    <span>🕒 {{ \Carbon\Carbon::parse($ev['start'])->format('H:i') }}</span>
                                    <span>📍 {{ $ev['location'] }}</span>
                                </div>
                            </div>
                        </div>

                        <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase" style="background-color: {{ $ev['color'] }}20; color: {{ $ev['color'] }}">
                            {{ $ev['type'] }}
                        </span>
                    </div>
                @empty
                    <div class="py-16 text-center text-slate-400 text-xs">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="font-semibold text-[#0B0F14]">Aucun événement programmé sur cette période.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- VUE GRILLE MENSUELLE -->
        <div x-show="viewMode === 'calendar'" class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs p-4" style="display: none;">
            <div class="grid grid-cols-7 gap-1 text-center font-bold text-[11px] text-slate-400 pb-2 border-b border-[#E2E8F0] mb-2 uppercase">
                <div>Lun</div>
                <div>Mar</div>
                <div>Mer</div>
                <div>Jeu</div>
                <div>Ven</div>
                <div>Sam</div>
                <div>Dim</div>
            </div>

            <!-- Simple 35-day grid visualization -->
            <div class="grid grid-cols-7 gap-1">
                @php
                    $startOfMonth = $selectedDate->copy()->startOfMonth();
                    $daysInMonth = $startOfMonth->daysInMonth;
                    $dayOfWeekOffset = $startOfMonth->dayOfWeekIso - 1;
                @endphp

                @for($blank = 0; $blank < $dayOfWeekOffset; $blank++)
                    <div class="h-24 p-1.5 rounded-lg bg-slate-50/50 border border-transparent"></div>
                @endfor

                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $currentDateStr = $selectedDate->copy()->day($day)->format('Y-m-d');
                        $dayEvents = $eventsByDate->get($currentDateStr, collect());
                        $isToday = $currentDateStr === date('Y-m-d');
                    @endphp
                    <div class="h-24 p-1.5 rounded-lg border {{ $isToday ? 'border-[#0066FF] bg-[#0066FF]/5' : 'border-[#E2E8F0] bg-white' }} overflow-y-auto text-left">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold {{ $isToday ? 'text-[#0066FF]' : 'text-slate-700' }}">{{ $day }}</span>
                            @if($dayEvents->isNotEmpty())
                                <span class="w-1.5 h-1.5 rounded-full bg-[#0066FF]"></span>
                            @endif
                        </div>
                        <div class="space-y-1 mt-1">
                            @foreach($dayEvents->take(2) as $evItem)
                                <div class="px-1 py-0.5 rounded text-[9px] font-medium truncate bg-slate-100 text-slate-800" title="{{ $evItem['title'] }}">
                                    {{ $evItem['title'] }}
                                </div>
                            @endforeach
                            @if($dayEvents->count() > 2)
                                <span class="text-[9px] text-[#0066FF] font-semibold">+{{ $dayEvents->count() - 2 }}</span>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </div>

    </div>
</x-layouts.app>
