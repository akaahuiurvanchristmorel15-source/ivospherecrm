<x-layouts.app>
    <x-slot:title>Pointage et Présences — IVOSPHERE RH</x-slot>

    <div class="space-y-6" x-data="{ showModal: false }">
        <x-page-header 
            title="Pointage & Registre des Présences" 
            description="Contrôle des flux d'arrivées, départs manuels/automatiques, géofencing et ponctualité"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Présences']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('rh.attendance.poster') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-200 hover:border-[#0066FF] hover:text-[#0066FF] text-slate-800 text-xs font-bold shadow-xs transition">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Affiche QR Mensuelle (A4)</span>
                    </a>
                    <a href="{{ route('rh.attendance.terminal') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                        <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>Borne Écran (30s)</span>
                    </a>
                    <a href="{{ route('rh.attendance.scanner') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-bold shadow-xs shadow-[#0066FF]/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>Pointer Arrivée / Départ</span>
                    </a>
                    <x-button variant="secondary" type="button" @click="showModal = true" class="flex items-center gap-1.5 text-xs">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Rectification Manuelle</span>
                    </x-button>
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- KPI Présences du Jour -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card 
                title="Présents Aujourd'hui" 
                :value="$presentCount" 
                :change="'Sur ' . $totalActiveEmployees . ' collaborateurs'" 
                changeType="up"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Retards Constatés" 
                :value="$lateCount" 
                :change="'Tolérance : ' . $settings->punctuality_grace_minutes . ' min'" 
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Clôturés Auto (20h)" 
                :value="$autoCheckoutCount" 
                change="Départ automatique" 
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Absents Non Pointés" 
                :value="$absentCount" 
                change="À régulariser" 
                changeType="down"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </x-slot:icon>
            </x-stat-card>
        </div>

        <!-- Filtres -->
        <x-card>
            <form method="GET" action="{{ route('rh.attendance.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date du relevé</label>
                    <input type="date" name="date" value="{{ $date }}" class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                </div>
                
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Département</label>
                    <select name="department" class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        <option value="">Tous les départements</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" @selected(request('department') == $dept)>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Mode de sortie</label>
                    <select name="check_out_type" class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        <option value="">Tous les modes</option>
                        <option value="manual" @selected(request('check_out_type') == 'manual')>Sortie manuelle (scan)</option>
                        <option value="automatic" @selected(request('check_out_type') == 'automatic')>Clôture automatique (20h)</option>
                    </select>
                </div>
                
                <div class="sm:col-span-3 flex items-end">
                    <button type="submit" class="w-full py-2.5 px-4 bg-[#0B0F14] hover:bg-[#1E293B] text-white rounded-xl text-xs font-bold transition shadow-xs">
                        Filtrer les relevés
                    </button>
                </div>
            </form>
        </x-card>

        <!-- Table des pointages -->
        <x-card :noPadding="true">
            <!-- Vue Table Desktop (>= md) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Collaborateur</th>
                            <th class="py-3 px-4 text-center">Arrivée & Ponctualité</th>
                            <th class="py-3 px-4 text-center">Départ & Clôture</th>
                            <th class="py-3 px-4 text-center">Géolocalisation & QR</th>
                            <th class="py-3 px-4 text-center">Statut</th>
                            <th class="py-3 px-4">Notes / Contexte</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($attendances as $attendance)
                            <tr class="hover:bg-[#F5F7FA]/70 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-[#0B0F14]">{{ $attendance->employee->full_name }}</div>
                                    <div class="text-[11px] font-mono text-slate-400">
                                        {{ $attendance->employee->employee_code }} • {{ $attendance->employee->department ?? 'Général' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($attendance->check_in)
                                        <div class="font-mono font-bold text-[#0066FF] text-sm">
                                            {{ $attendance->check_in_at ? $attendance->check_in_at->format('H:i') : substr($attendance->check_in, 0, 5) }}
                                        </div>
                                        <div class="inline-flex items-center gap-1 mt-0.5 px-1.5 py-0.2 rounded text-[10px] font-bold {{ $attendance->delay_minutes > 0 ? 'bg-amber-50 text-amber-800' : 'bg-emerald-50 text-emerald-800' }}">
                                            <span>{{ $attendance->delay_minutes > 0 ? "+{$attendance->delay_minutes} min" : 'À l\'heure' }}</span>
                                            <span>({{ $attendance->punctuality_score }} pt)</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 font-mono">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($attendance->check_out)
                                        <div class="font-mono font-semibold text-slate-700 text-sm">
                                            {{ $attendance->check_out_at ? $attendance->check_out_at->format('H:i') : substr($attendance->check_out, 0, 5) }}
                                        </div>
                                        @if($attendance->isAutomaticCheckout())
                                            <span class="inline-flex items-center gap-1 mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                <span>⚠</span>
                                                <span>Auto 20h</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                                                Manuel
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">
                                            En cours
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($attendance->location_verified)
                                        <div class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-semibold">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>{{ round($attendance->distance_meters) }}m</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono">
                                            {{ $attendance->qr_type === 'premises_dynamic' ? 'QR Locaux' : 'Badge Perso' }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[11px]">Non vérifié</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @php
                                        $stAtt = match($attendance->status) {
                                            'present' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'absent' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            default => 'bg-amber-50 text-amber-700 border-amber-200'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $stAtt }}">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-[#64748B] text-[11px]">
                                    {{ $attendance->notes ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12">
                                    <x-empty-state 
                                        title="Aucun pointage trouvé"
                                        description="Aucun pointage n'a été enregistré pour cette date."
                                    >
                                        <x-slot:action>
                                            <x-button variant="primary" type="button" @click="showModal = true">
                                                Enregistrer un pointage
                                            </x-button>
                                        </x-slot:action>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Vue Cartes Mobile (< md) -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($attendances as $attendance)
                    @php
                        $stAtt = match($attendance->status) {
                            'present' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'absent' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-amber-50 text-amber-700 border-amber-200'
                        };
                    @endphp
                    <div class="bg-white rounded-2xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="font-bold text-[#0B0F14] text-sm">{{ $attendance->employee->full_name }}</h4>
                                <span class="font-mono text-xs text-[#64748B]">{{ $attendance->employee->employee_code }} • {{ $attendance->date->format('d/m/Y') }}</span>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border shrink-0 {{ $stAtt }}">
                                {{ ucfirst($attendance->status) }}
                            </span>
                        </div>

                        <!-- 2-col check-in / check-out grid -->
                        <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-3 rounded-xl border border-[#E2E8F0]">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Arrivée</span>
                                <div class="flex items-center gap-1.5 font-mono font-bold text-[#0066FF] text-sm">
                                    <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                    <span>{{ $attendance->check_in_at ? $attendance->check_in_at->format('H:i') : ($attendance->check_in ? substr($attendance->check_in, 0, 5) : '—') }}</span>
                                </div>
                                @if($attendance->check_in)
                                    <span class="text-[10px] text-slate-500 font-semibold block mt-0.5">
                                        {{ $attendance->delay_minutes > 0 ? "+{$attendance->delay_minutes} min" : 'À l\'heure' }} ({{ $attendance->punctuality_score }} pt)
                                    </span>
                                @endif
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Départ</span>
                                <div class="flex items-center gap-1.5 font-mono font-semibold text-slate-700 text-sm">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    <span>{{ $attendance->check_out_at ? $attendance->check_out_at->format('H:i') : ($attendance->check_out ? substr($attendance->check_out, 0, 5) : '—') }}</span>
                                </div>
                                @if($attendance->check_out)
                                    <span class="text-[10px] font-bold block mt-0.5 {{ $attendance->isAutomaticCheckout() ? 'text-amber-700' : 'text-blue-700' }}">
                                        {{ $attendance->isAutomaticCheckout() ? '⚠ Clôturé auto 20h' : 'Manuel' }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($attendance->notes)
                            <div class="text-[11px] text-[#64748B] bg-slate-50 px-2.5 py-1.5 rounded-lg border border-[#E2E8F0]">
                                <span class="font-medium text-[#0B0F14]">Note :</span> {{ $attendance->notes }}
                            </div>
                        @endif
                    </div>
                @empty
                    <x-empty-state 
                        title="Aucun pointage trouvé"
                        description="Aucun pointage n'a été enregistré pour cette date."
                    >
                        <x-slot:action>
                            <x-button variant="primary" type="button" @click="showModal = true">
                                Enregistrer un pointage
                            </x-button>
                        </x-slot:action>
                    </x-empty-state>
                @endforelse
            </div>

            @if($attendances->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $attendances->links() }}
                </div>
            @endif
        </x-card>
        
        <!-- Modal Rectification Pointage -->
        <div 
            x-show="showModal" 
            x-transition 
            class="fixed inset-0 z-50 bg-[#0B0F14]/50 flex items-center justify-center p-4" 
            style="display: none;"
        >
            <div @click.outside="showModal = false" class="bg-white border border-[#E2E8F0] rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
                <div class="flex justify-between items-center border-b border-[#E2E8F0] pb-3">
                    <h3 class="text-base font-bold text-[#0B0F14]">Enregistrer / Rectifier un Pointage</h3>
                    <button type="button" @click="showModal = false" class="text-[#64748B] hover:text-[#0B0F14]">✕</button>
                </div>

                <form action="{{ route('rh.attendance.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date du pointage *</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Employé *</label>
                        <select name="employee_id" required class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                            <option value="">Sélectionner un employé</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Heure d'arrivée</label>
                            <input type="time" name="check_in" class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Heure de départ</label>
                            <input type="time" name="check_out" class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Mode de départ</label>
                        <select name="check_out_type" class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                            <option value="manual">Manuel (pointage scanné)</option>
                            <option value="automatic">Automatique (clôture 20h)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Statut *</label>
                        <select name="status" required class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                            <option value="present">Présent</option>
                            <option value="absent">Absent</option>
                            <option value="retard">En retard</option>
                            <option value="congé">En congé</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Notes complémentaires</label>
                        <input type="text" name="notes" placeholder="Justification éventuelle..." class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-[#E2E8F0]">
                        <x-button variant="secondary" type="button" @click="showModal = false">Annuler</x-button>
                        <x-button variant="primary" type="submit">Enregistrer</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
