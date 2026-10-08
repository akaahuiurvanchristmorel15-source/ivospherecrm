<x-layouts.app>
    <x-slot:title>Agenda & Rendez-vous Commerciaux — IVOSPHERE ERP</x-slot>

    <div class="space-y-6" x-data="{ showModal: false, editModal: false, selectedApt: {} }">
        <!-- Header -->
        <x-page-header 
            title="Agenda & Rendez-vous Commerciaux" 
            description="Planification, suivi des entretiens de négociation et comptes-rendus clients & prospects"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                    ['label' => 'Rendez-vous']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button variant="primary" type="button" @click="showModal = true" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Planifier un Rendez-vous</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <!-- 4 Quick Stats (12-column grid) -->
        @php
            $totalApts = $appointments->total();
            $plannedCount = \App\Models\CommercialAppointment::where('status', 'planifié')->count();
            $doneCount = \App\Models\CommercialAppointment::where('status', 'effectué')->count();
            $todayCount = \App\Models\CommercialAppointment::whereDate('date', today())->count();
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-3">
                <x-stat-card 
                    title="RDV Prévus Aujourd'hui" 
                    :value="$todayCount" 
                    :change="now()->translatedFormat('d F Y')" 
                    changeType="up"
                />
            </div>
            <div class="lg:col-span-3">
                <x-stat-card 
                    title="À Venir / En Attente" 
                    :value="$plannedCount" 
                    change="Rencontres planifiées" 
                    changeType="neutral"
                />
            </div>
            <div class="lg:col-span-3">
                <x-stat-card 
                    title="Entretiens Effectués" 
                    :value="$doneCount" 
                    change="Avec compte-rendu consigné" 
                    changeType="up"
                />
            </div>
            <div class="lg:col-span-3">
                <x-stat-card 
                    title="Total Rendez-vous" 
                    :value="$totalApts" 
                    change="Clients & Prospects" 
                    changeType="neutral"
                />
            </div>
        </div>

        <!-- Filter Bar -->
        <x-card>
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Statut</label>
                    <select name="status" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        <option value="">Tous les statuts</option>
                        <option value="planifié" {{ request('status') === 'planifié' ? 'selected' : '' }}>Planifié</option>
                        <option value="effectué" {{ request('status') === 'effectué' ? 'selected' : '' }}>Effectué</option>
                        <option value="reporté" {{ request('status') === 'reporté' ? 'selected' : '' }}>Reporté</option>
                        <option value="annulé" {{ request('status') === 'annulé' ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Commercial</label>
                    <select name="user_id" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        <option value="">Tous les commerciaux</option>
                        @foreach($commercials as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-[#0B0F14] hover:bg-[#1E293B] text-white rounded-lg text-xs font-medium transition">
                        Filtrer
                    </button>
                    @if(request()->hasAny(['date', 'status', 'user_id']))
                        <a href="{{ route('commercial.appointments.index') }}" class="py-2 px-2.5 rounded-lg bg-[#F5F7FA] text-[#64748B] hover:text-[#0B0F14] border border-[#E2E8F0] text-xs">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Appointments Table -->
        <x-card :noPadding="true">
            <!-- Vue Table Desktop (>= md) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Date & Heure</th>
                            <th class="py-3 px-4">Objet / Titre</th>
                            <th class="py-3 px-4">Interlocuteur</th>
                            <th class="py-3 px-4">Commercial</th>
                            <th class="py-3 px-4">Statut</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($appointments as $apt)
                            <tr class="hover:bg-[#F5F7FA]/70 transition">
                                <td class="py-3.5 px-4 font-mono">
                                    <div class="font-bold text-[#0B0F14]">
                                        {{ $apt->date ? $apt->date->format('d/m/Y') : '-' }}
                                    </div>
                                    <div class="text-[11px] text-[#0066FF] font-semibold">
                                        {{ $apt->time ?: 'Non spécifié' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-[#0B0F14] text-xs block">{{ $apt->title }}</span>
                                    @if($apt->location)
                                        <span class="text-[11px] text-[#64748B] flex items-center gap-1 mt-0.5">
                                            <svg class="w-3 h-3 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>{{ $apt->location }}</span>
                                        </span>
                                    @endif
                                    @if($apt->notes)
                                        <span class="text-[10px] text-emerald-700 block mt-1 line-clamp-1">
                                            Notes: {{ $apt->notes }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($apt->customer)
                                        <a href="{{ route('commercial.customers.show', $apt->customer) }}" class="font-bold text-[#0066FF] hover:underline">
                                            {{ $apt->customer->name }}
                                        </a>
                                        <span class="block text-[10px] text-[#64748B]">Client &bull; {{ $apt->customer->code }}</span>
                                    @elseif($apt->prospect)
                                        <a href="{{ route('commercial.prospects.edit', $apt->prospect) }}" class="font-bold text-amber-600 hover:underline">
                                            {{ $apt->prospect->name }}
                                        </a>
                                        <span class="block text-[10px] text-[#64748B]">Prospect {{ $apt->prospect->company ? '(' . $apt->prospect->company . ')' : '' }}</span>
                                    @else
                                        <span class="text-[#64748B]">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-[#64748B]">
                                    {{ $apt->user?->name ?? 'Non assigné' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $statusBadge = match($apt->status) {
                                            'effectué' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'annulé' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            'reporté' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            default => 'bg-blue-50 text-blue-700 border-blue-200',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $statusBadge }}">
                                        {{ ucfirst($apt->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button 
                                            type="button" 
                                            @click="selectedApt = {{ Js::from($apt) }}; editModal = true;" 
                                            class="px-2.5 py-1 bg-[#F5F7FA] hover:bg-slate-200 text-[#0B0F14] rounded-lg text-xs font-medium transition border border-[#E2E8F0]"
                                        >
                                            Gérer / CR
                                        </button>
                                        <form action="{{ route('commercial.appointments.destroy', $apt) }}" method="POST" class="inline-block" onsubmit="return confirm('Supprimer ce rendez-vous ?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1 text-[#64748B] hover:text-rose-600 transition" title="Supprimer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12">
                                    <x-empty-state 
                                        title="Aucun rendez-vous planifié"
                                        description="Planifiez des entretiens commerciaux avec vos clients et prospects pour organiser votre agenda."
                                    >
                                        <x-slot:action>
                                            <x-button variant="primary" type="button" @click="showModal = true">
                                                Planifier un RDV
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
                @forelse($appointments as $apt)
                    @php
                        $statusBadge = match($apt->status) {
                            'effectué' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'annulé' => 'bg-rose-50 text-rose-700 border-rose-200',
                            'reporté' => 'bg-amber-50 text-amber-700 border-amber-200',
                            default => 'bg-blue-50 text-blue-700 border-blue-200',
                        };
                    @endphp
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="font-bold text-[#0B0F14] text-sm">{{ $apt->title }}</h4>
                                <div class="flex items-center gap-2 mt-0.5 text-xs">
                                    <span class="font-mono font-bold text-[#0066FF]">{{ $apt->date ? $apt->date->format('d/m/Y') : '-' }}</span>
                                    <span class="text-[#64748B]">&bull;</span>
                                    <span class="font-mono text-[#64748B]">{{ $apt->time ?: 'Non spécifié' }}</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border shrink-0 {{ $statusBadge }}">
                                {{ ucfirst($apt->status) }}
                            </span>
                        </div>

                        <!-- 2-col info grid -->
                        <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Interlocuteur</span>
                                @if($apt->customer)
                                    <a href="{{ route('commercial.customers.show', $apt->customer) }}" class="font-bold text-[#0066FF] hover:underline block truncate">
                                        {{ $apt->customer->name }}
                                    </a>
                                    <span class="text-[10px] text-[#64748B] block truncate">Client ({{ $apt->customer->code }})</span>
                                @elseif($apt->prospect)
                                    <a href="{{ route('commercial.prospects.edit', $apt->prospect) }}" class="font-bold text-amber-600 hover:underline block truncate">
                                        {{ $apt->prospect->name }}
                                    </a>
                                    <span class="text-[10px] text-[#64748B] block truncate">Prospect</span>
                                @else
                                    <span class="text-[#64748B]">—</span>
                                @endif
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Commercial</span>
                                <span class="font-medium text-[#0B0F14] block truncate">{{ $apt->user?->name ?? 'Non assigné' }}</span>
                            </div>
                        </div>

                        @if($apt->location)
                            <div class="text-xs text-[#64748B] flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#64748B] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="truncate">{{ $apt->location }}</span>
                            </div>
                        @endif

                        @if($apt->notes)
                            <div class="text-[11px] text-emerald-800 bg-emerald-50/70 px-2.5 py-1.5 rounded-lg border border-emerald-200">
                                <span class="font-bold">CR:</span> {{ $apt->notes }}
                            </div>
                        @endif

                        <!-- Actions bar -->
                        <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-between gap-2">
                            <button 
                                type="button" 
                                @click="selectedApt = {{ Js::from($apt) }}; editModal = true;" 
                                class="flex-1 py-2 px-3 rounded-lg bg-[#0066FF] hover:bg-blue-600 text-white text-xs font-semibold text-center transition flex items-center justify-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Gérer / CR</span>
                            </button>
                            <form action="{{ route('commercial.appointments.destroy', $apt) }}" method="POST" class="inline-block" onsubmit="return confirm('Supprimer ce rendez-vous ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-rose-600 hover:bg-rose-50 transition" title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <x-empty-state 
                        title="Aucun rendez-vous planifié"
                        description="Planifiez des entretiens commerciaux avec vos clients et prospects pour organiser votre agenda."
                    >
                        <x-slot:action>
                            <x-button variant="primary" type="button" @click="showModal = true">
                                Planifier un RDV
                            </x-button>
                        </x-slot:action>
                    </x-empty-state>
                @endforelse
            </div>

            @if($appointments->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">{{ $appointments->links() }}</div>
            @endif
        </x-card>

        <!-- 1. Modal Création de RDV -->
        <div 
            x-show="showModal" 
            x-transition 
            class="fixed inset-0 z-50 bg-[#0B0F14]/50 flex items-center justify-center p-4" 
            style="display: none;"
        >
            <div @click.outside="showModal = false" class="bg-white border border-[#E2E8F0] rounded-xl max-w-2xl w-full p-6 shadow-xl space-y-4">
                <div class="flex justify-between items-center border-b border-[#E2E8F0] pb-3">
                    <h3 class="text-base font-bold text-[#0B0F14]">Planifier un Rendez-vous Commercial</h3>
                    <button type="button" @click="showModal = false" class="text-[#64748B] hover:text-[#0B0F14]">✕</button>
                </div>

                <form action="{{ route('commercial.appointments.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Titre de la rencontre *</label>
                        <input type="text" name="title" required placeholder="Ex: Démo IVOSPHERE pour direction générale" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Commercial assigné</label>
                            <select name="user_id" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                                @foreach($commercials as $user)
                                    <option value="{{ $user->id }}" {{ auth()->id() == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Statut Initial</label>
                            <select name="status" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                                <option value="planifié">Planifié</option>
                                <option value="effectué">Déjà effectué</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Associer à un Client</label>
                            <select name="customer_id" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                                <option value="">-- Aucun (ou choisir un prospect) --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Ou associer à un Prospect</label>
                            <select name="prospect_id" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                                <option value="">-- Aucun --</option>
                                @foreach($prospects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} {{ $p->company ? '(' . $p->company . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date *</label>
                            <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Heure (Ex: 10:30)</label>
                            <input type="text" name="time" placeholder="10:30" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Lieu / Salle / Lien Visio</label>
                        <input type="text" name="location" placeholder="Ex: Siège client Plateau ou Google Meet" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Ordre du jour & Notes</label>
                        <textarea name="notes" rows="3" placeholder="Sujets à aborder, besoins identifiés, documents à préparer..." class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-[#E2E8F0]">
                        <x-button variant="secondary" type="button" @click="showModal = false">Annuler</x-button>
                        <x-button variant="primary" type="submit">Enregistrer le RDV</x-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Modal Mise à jour / Compte-Rendu -->
        <div 
            x-show="editModal" 
            x-transition 
            class="fixed inset-0 z-50 bg-[#0B0F14]/50 flex items-center justify-center p-4" 
            style="display: none;"
        >
            <div @click.outside="editModal = false" class="bg-white border border-[#E2E8F0] rounded-xl max-w-xl w-full p-6 shadow-xl space-y-4">
                <div class="flex justify-between items-center border-b border-[#E2E8F0] pb-3">
                    <h3 class="text-base font-bold text-[#0B0F14]">Gérer le Rendez-vous & Compte-Rendu</h3>
                    <button type="button" @click="editModal = false" class="text-[#64748B] hover:text-[#0B0F14]">✕</button>
                </div>

                <form :action="'{{ url('commercial/appointments') }}/' + selectedApt.id" method="POST" class="space-y-4 text-xs">
                    @csrf @method('PUT')
                    <input type="hidden" name="title" :value="selectedApt.title">
                    <input type="hidden" name="date" :value="selectedApt.date">
                    <input type="hidden" name="time" :value="selectedApt.time">
                    <input type="hidden" name="customer_id" :value="selectedApt.customer_id">
                    <input type="hidden" name="prospect_id" :value="selectedApt.prospect_id">

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Statut du rendez-vous</label>
                        <select name="status" x-model="selectedApt.status" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                            <option value="planifié">Planifié</option>
                            <option value="effectué">Effectué</option>
                            <option value="annulé">Annulé</option>
                            <option value="reporté">Reporté</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Compte-rendu & Prochaines actions commerciales</label>
                        <textarea name="notes" x-model="selectedApt.notes" rows="4" placeholder="Résumé de l'échange, accords trouvés, devis à envoyer, date de signature convenue..." class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-[#E2E8F0]">
                        <x-button variant="secondary" type="button" @click="editModal = false">Fermer</x-button>
                        <x-button variant="primary" type="submit">Enregistrer le Compte-rendu</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
