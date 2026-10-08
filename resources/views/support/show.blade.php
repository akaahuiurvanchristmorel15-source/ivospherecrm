<x-layouts.app :title="'Ticket ' . $ticket->ticket_number">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Top Header & Status bar -->
        <div class="pb-4 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('support.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux tickets</span>
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">{{ $ticket->ticket_number }}</h1>
                    <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase {{ $ticket->status === 'resolu' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ str_replace('_', ' ', $ticket->status) }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $ticket->priority === 'urgente' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700' }}">
                        Priorité {{ $ticket->priority }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">{{ $ticket->subject }}</p>
            </div>

            <!-- Quick Status Change Form -->
            <form action="{{ route('support.status', $ticket) }}" method="POST" class="flex items-center gap-2">
                @csrf
                @method('PATCH')
                <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-xs text-[#0B0F14] font-semibold focus:ring-1 focus:ring-[#0066FF] shadow-xs">
                    <option value="nouveau" {{ $ticket->status === 'nouveau' ? 'selected' : '' }}>Statut : Nouveau</option>
                    <option value="en_cours" {{ $ticket->status === 'en_cours' ? 'selected' : '' }}>Statut : En cours</option>
                    <option value="attente_client" {{ $ticket->status === 'attente_client' ? 'selected' : '' }}>Statut : Attente client</option>
                    <option value="resolu" {{ $ticket->status === 'resolu' ? 'selected' : '' }}>Statut : Résolu</option>
                    <option value="ferme" {{ $ticket->status === 'ferme' ? 'selected' : '' }}>Statut : Fermé</option>
                </select>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Discussion Thread (2 cols) -->
            <div class="md:col-span-2 space-y-4">
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4">
                    <h2 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider pb-3 border-b border-[#E2E8F0]">
                        Fil de Discussion & Historique
                    </h2>

                    <!-- Messages list -->
                    <div class="space-y-4">
                        @foreach($ticket->messages as $msg)
                            <div class="p-4 rounded-xl {{ $msg->is_internal_note ? 'bg-amber-50 border border-amber-200' : 'bg-[#F5F7FA] border border-[#E2E8F0]' }} text-xs">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-[#0B0F14]">
                                            {{ $msg->user ? $msg->user->name : ($msg->customer ? $msg->customer->name : 'Support') }}
                                        </span>
                                        @if($msg->is_internal_note)
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase bg-amber-200 text-amber-800">Note Interne</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-slate-400">{{ $msg->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="text-slate-700 whitespace-pre-line leading-relaxed">
                                    {{ $msg->message }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Reply Form -->
                    <div class="pt-4 border-t border-[#E2E8F0]">
                        <form action="{{ route('support.reply', $ticket) }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <div>
                                <label for="reply-message-textarea" class="block font-bold text-[#0B0F14] mb-1">Ajouter une réponse ou consigne</label>
                                <textarea id="reply-message-textarea" name="message" rows="3" class="w-full p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#0066FF]" placeholder="Tapez votre réponse ici..." required></textarea>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                                    <input type="checkbox" name="is_internal_note" value="1" class="rounded border-[#E2E8F0] text-[#0066FF] focus:ring-0" />
                                    <span>Note interne confidentielle (invisible pour le client)</span>
                                </label>

                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white font-bold shadow-md shadow-[#0066FF]/20 transition-colors shrink-0 text-center">
                                    Publier le message
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

            <!-- Details Sidebar (1 col) -->
            <div class="space-y-4 text-xs">
                <div class="bg-white rounded-2xl p-5 border border-[#E2E8F0] shadow-xs space-y-3">
                    <h3 class="font-bold text-[#0B0F14] uppercase tracking-wider text-[11px] pb-2 border-b border-[#E2E8F0]">Fiche du Ticket</h3>
                    
                    <div>
                        <span class="text-slate-400 block text-[11px]">Demandeur / Client</span>
                        <span class="font-bold text-[#0B0F14]">
                            @if($ticket->customer)
                                {{ $ticket->customer->company ? $ticket->customer->name . ' (' . $ticket->customer->company . ')' : $ticket->customer->name }}
                            @else
                                Collaborateur interne
                            @endif
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Catégorie</span>
                        <span class="font-semibold text-[#0B0F14] capitalize">{{ $ticket->category }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Pôle concerné</span>
                        <span class="font-semibold text-[#0B0F14]">{{ $ticket->domain ? $ticket->domain->name : 'Général' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Agent Assigné</span>
                        <span class="font-semibold text-[#0066FF]">{{ $ticket->user ? $ticket->user->name : 'Non assigné' }}</span>
                    </div>

                    @if($ticket->closed_at)
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-emerald-600 font-bold block">Résolu le {{ $ticket->closed_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
</x-layouts.app>
