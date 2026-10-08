<x-layouts.app title="Messagerie Interne d'Équipe">
    <div 
        class="h-[calc(100dvh-12rem)] md:h-[calc(100vh-10rem)] flex rounded-2xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden relative" 
        x-data="internalChatApp({
            initialMessages: @js($messages->map(fn($m) => [
                'id' => $m->id,
                'content' => $m->content,
                'sender_name' => $m->sender_id === auth()->id() ? 'Vous' : ($m->sender?->name ?? 'Collaborateur'),
                'is_me' => $m->sender_id === auth()->id(),
                'created_at' => $m->created_at->format('H:i'),
            ])->values()),
            postUrl: '{{ route('messages.store') }}',
            fetchUrl: '{{ route('messages.index', array_filter(['channel_id' => $activeChannel?->id, 'user_id' => $activeRecipient?->id])) }}',
            csrfToken: '{{ csrf_token() }}',
            channelId: '{{ $activeChannel ? $activeChannel->id : '' }}',
            recipientId: '{{ $activeRecipient ? $activeRecipient->id : '' }}',
            initialUnreadUsers: @js($unreadByUser ?? []),
            currentUserId: {{ auth()->id() }}
        })"
    >

        <!-- Toast de notification en direct pour nouveau message reçu -->
        <div 
            x-show="incomingToast" 
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
            class="fixed top-20 right-4 sm:right-8 z-50 max-w-sm w-full bg-white border border-[#0066FF]/30 shadow-2xl rounded-2xl p-3.5 flex items-center justify-between gap-3 text-xs ring-4 ring-[#0066FF]/10 select-none"
            style="display: none;"
        >
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-[#0066FF] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs shadow-[#0066FF]/30">
                    💬
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="font-extrabold text-[#0B0F14] truncate" x-text="incomingToast?.sender_name"></span>
                        <span class="text-[10px] text-slate-400 font-semibold" x-text="incomingToast?.is_direct ? 'Privé' : incomingToast?.channel_name"></span>
                    </div>
                    <p class="text-slate-600 truncate text-[11px] mt-0.5" x-text="incomingToast?.content"></p>
                </div>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                <a 
                    :href="incomingToast?.target_url" 
                    class="px-2.5 py-1.5 rounded-lg bg-[#0066FF] hover:bg-blue-600 text-white font-bold text-[11px] transition-colors shadow-2xs"
                >
                    Voir
                </a>
                <button 
                    type="button" 
                    @click="incomingToast = null" 
                    class="p-1 rounded-lg text-slate-400 hover:text-slate-600"
                    aria-label="Fermer"
                >
                    ✕
                </button>
            </div>
        </div>

        <!-- 1. Mobile Drawer Backdrop -->
        <div 
            x-show="mobileChannelsOpen" 
            x-cloak
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileChannelsOpen = false" 
            class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-xs z-50 md:hidden"
            style="display: none;"
        ></div>

        <!-- 2. Sidebar (Channels & Direct Messages) - Desktop Permanent + Mobile Slide-over Drawer -->
        <aside 
            :class="mobileChannelsOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 md:static md:z-auto w-72 sm:w-80 bg-[#F5F7FA] border-r border-[#E2E8F0] flex flex-col shrink-0 transition-transform duration-250 ease-in-out h-full shadow-2xl md:shadow-none select-none"
        >
            <!-- Header Sidebar -->
            <div class="p-3.5 sm:p-4 border-b border-[#E2E8F0] flex items-center justify-between bg-[#F5F7FA]">
                <div>
                    <h2 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                        Salons d'Équipe
                    </h2>
                    <span class="text-[10px] text-slate-400">Communication interne</span>
                </div>
                
                <div class="flex items-center gap-1.5">
                    <button 
                        type="button" 
                        @click="newChannelModal = true" 
                        class="w-7 h-7 rounded-lg bg-white border border-[#E2E8F0] text-slate-600 hover:text-[#0066FF] hover:border-[#0066FF]/30 text-xs font-bold flex items-center justify-center transition-colors shadow-2xs touch-target" 
                        title="Créer un nouveau salon"
                        aria-label="Nouveau salon"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    </button>

                    <!-- Bouton Fermer sur Mobile -->
                    <button 
                        type="button" 
                        @click="mobileChannelsOpen = false" 
                        class="md:hidden w-7 h-7 rounded-lg bg-white border border-[#E2E8F0] text-slate-400 hover:text-slate-700 flex items-center justify-center transition-colors touch-target"
                        aria-label="Fermer le volet des salons"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- List of Channels and Direct Messages -->
            <div class="flex-1 overflow-y-auto p-3 space-y-4 text-xs">
                <!-- Salons thématiques -->
                <div class="space-y-1">
                    <span class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Salons thématiques</span>
                    @foreach($channels as $ch)
                        @php
                            $isActiveChannel = $activeChannel && $activeChannel->id === $ch->id;
                        @endphp
                        <a 
                            href="{{ route('messages.index', ['channel_id' => $ch->id]) }}"
                            class="flex items-center justify-between px-2.5 py-2 rounded-xl transition-all {{ $isActiveChannel ? 'bg-[#0066FF] text-white font-bold shadow-xs' : 'text-slate-700 hover:bg-white hover:text-[#0B0F14]' }}"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="{{ $isActiveChannel ? 'text-white/80' : 'text-slate-400' }} font-bold text-xs">#</span>
                                <span class="truncate">{{ $ch->name }}</span>
                            </div>
                            @if($isActiveChannel)
                                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                            @endif
                        </a>
                    @endforeach
                </div>

                <!-- Messages directs avec badges temps réel -->
                <div class="space-y-1 pt-2 border-t border-[#E2E8F0]">
                    <span class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Messages directs</span>
                    @foreach($users as $u)
                        @php
                            $isActiveUser = $activeRecipient && $activeRecipient->id === $u->id;
                            $initUnread = $unreadByUser[$u->id] ?? 0;
                        @endphp
                        <a 
                            href="{{ route('messages.index', ['user_id' => $u->id]) }}"
                            class="flex items-center justify-between px-2.5 py-2 rounded-xl transition-all {{ $isActiveUser ? 'bg-[#0066FF] text-white font-bold shadow-xs' : 'text-slate-700 hover:bg-white hover:text-[#0B0F14]' }}"
                        >
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="relative flex h-2 w-2 shrink-0">
                                    <span class="w-2 h-2 rounded-full {{ $u->is_online ? 'bg-emerald-500 ring-2 ring-emerald-200' : 'bg-slate-300' }}"></span>
                                </span>
                                <span class="truncate">{{ $u->name }}</span>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                @if($isActiveUser)
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                @endif
                                <span 
                                    x-show="unreadUsers['{{ $u->id }}'] > 0"
                                    x-text="unreadUsers['{{ $u->id }}']"
                                    class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#0066FF] text-white shadow-xs"
                                    style="{{ $initUnread > 0 ? '' : 'display: none;' }}"
                                >
                                    {{ $initUnread > 0 ? $initUnread : '' }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </aside>

        <!-- 3. Main Chat Conversation Area -->
        <main class="flex-1 flex flex-col min-w-0 bg-white w-full h-full relative">
            <!-- Channel / Conversation Top Header -->
            <div class="h-14 px-3 sm:px-6 border-b border-[#E2E8F0] flex items-center justify-between shrink-0 bg-white/95 backdrop-blur-xs z-10">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <!-- Bouton Déclencheur Mobile Drawer Salons -->
                    <button 
                        type="button" 
                        @click="mobileChannelsOpen = true" 
                        class="md:hidden inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-[#F5F7FA] hover:bg-slate-200 border border-[#E2E8F0] text-slate-700 text-xs font-bold transition-colors touch-target shrink-0"
                        title="Ouvrir les salons et contacts"
                        aria-label="Ouvrir la liste des salons"
                    >
                        <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                        <span class="hidden sm:inline">Salons</span>
                    </button>

                    <div class="flex items-baseline gap-1.5 min-w-0">
                        <span class="font-extrabold text-[#0B0F14] text-xs sm:text-sm truncate">
                            {{ $activeRecipient ? '@' . $activeRecipient->name : ($activeChannel ? '#' . $activeChannel->name : '#général') }}
                        </span>
                        @if($activeChannel && $activeChannel->description)
                            <span class="text-[11px] text-slate-400 hidden lg:inline truncate max-w-sm">— {{ $activeChannel->description }}</span>
                        @endif
                    </div>
                </div>

                <!-- Info indicateur -->
                <div class="flex items-center gap-2 shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>En direct</span>
                    </span>
                </div>
            </div>

            <!-- Messages Stream (Dynamic Alpine rendering with auto-scroll) -->
            <div 
                x-ref="messageStream"
                class="flex-1 overflow-y-auto p-3.5 sm:p-6 space-y-3.5 text-xs bg-[#FBFBFD]"
            >
                <template x-if="messages.length === 0">
                    <div class="py-16 sm:py-24 text-center text-slate-400">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 text-[#0066FF] mx-auto mb-3 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <p class="text-xs font-bold text-[#0B0F14]">Aucun message dans cette conversation.</p>
                        <p class="text-[11px] text-slate-400 mt-1">Écrivez le premier message ci-dessous pour lancer l'échange.</p>
                    </div>
                </template>

                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex flex-col max-w-full" :class="msg.is_me ? 'items-end' : 'items-start'">
                        <div class="flex items-center gap-1.5 mb-1 px-1">
                            <span class="font-bold text-[#0B0F14] text-[11px]" x-text="msg.is_me ? 'Vous' : msg.sender_name"></span>
                            <span class="text-[10px] text-slate-400" x-text="msg.created_at"></span>
                            <template x-if="msg.sending">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse" title="Envoi en cours..."></span>
                            </template>
                        </div>
                        <div 
                            class="max-w-[85%] sm:max-w-md md:max-w-lg p-3 rounded-2xl shadow-2xs whitespace-pre-line leading-relaxed break-words"
                            :class="msg.is_me ? 'bg-[#0066FF] text-white rounded-br-xs' : 'bg-white text-[#0B0F14] border border-[#E2E8F0] rounded-bl-xs'"
                            x-text="msg.content"
                        ></div>
                    </div>
                </template>
            </div>

            <!-- Composer Input Bar (Asynchronous AJAX submission without page reload) -->
            <div class="p-2.5 sm:p-4 border-t border-[#E2E8F0] shrink-0 bg-white">
                <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <input 
                            type="text" 
                            x-model="inputContent"
                            :disabled="isSending"
                            placeholder="Envoyer un message..." 
                            class="w-full px-3.5 sm:px-4 py-2.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-xs sm:text-sm text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0066FF] focus:bg-white transition-all touch-target disabled:opacity-50"
                            required
                            autocomplete="off"
                        />
                    </div>

                    <button 
                        type="submit" 
                        :disabled="!inputContent.trim() || isSending"
                        class="px-3.5 sm:px-5 py-2.5 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-bold shadow-xs shadow-[#0066FF]/20 transition-all touch-target flex items-center justify-center gap-1.5 shrink-0 disabled:opacity-50 disabled:cursor-not-allowed"
                        title="Envoyer le message"
                    >
                        <span class="hidden sm:inline">Envoyer</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
            </div>
        </main>

        <!-- 4. Modal: Nouveau Salon -->
        <div 
            x-show="newChannelModal" 
            x-cloak
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto p-4 flex items-center justify-center bg-[#0B0F14]/60 backdrop-blur-xs" 
            style="display: none;"
        >
            <div @click.outside="newChannelModal = false" class="bg-white rounded-2xl max-w-sm w-full p-5 sm:p-6 shadow-2xl border border-[#E2E8F0] text-xs">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#E2E8F0]">
                    <h3 class="text-sm font-extrabold text-[#0B0F14]">Créer un Nouveau Salon</h3>
                    <button type="button" @click="newChannelModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 touch-target" aria-label="Fermer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('messages.channels.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="channel-name-input" class="block font-semibold text-[#0B0F14] mb-1">Nom du salon (sans espace) *</label>
                        <input id="channel-name-input" type="text" name="name" placeholder="ex: atelier-print" class="w-full px-3 py-2 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-xs focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" required />
                    </div>
                    <div>
                        <label for="channel-desc-input" class="block font-semibold text-[#0B0F14] mb-1">Description</label>
                        <input id="channel-desc-input" type="text" name="description" placeholder="Objectif du salon..." class="w-full px-3 py-2 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-xs focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" />
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E2E8F0]">
                        <button type="button" @click="newChannelModal = false" class="px-3.5 py-2 rounded-xl border border-[#E2E8F0] text-slate-600 hover:bg-slate-50 font-semibold transition-colors">Annuler</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#0066FF] text-white font-bold hover:bg-[#0052cc] shadow-xs shadow-[#0066FF]/20 transition-all">Créer le salon</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Script Alpine.js pour la gestion temps réel et l'envoi asynchrone sans rechargement -->
    <script>
        function internalChatApp(config) {
            return {
                messages: config.initialMessages || [],
                unreadUsers: config.initialUnreadUsers || {},
                newChannelModal: false,
                mobileChannelsOpen: false,
                inputContent: '',
                isSending: false,
                incomingToast: null,
                lastKnownIncomingId: null,
                pollTimer: null,

                init() {
                    this.scrollToBottom();

                    // Initialiser le dernier ID entrant connu
                    const lastMsg = this.messages[this.messages.length - 1];
                    if (lastMsg) {
                        this.lastKnownIncomingId = lastMsg.id;
                    }

                    // Polling réactif toutes les 1500 ms (1.5 secondes)
                    this.pollTimer = setInterval(() => {
                        this.pollNewMessages();
                    }, 1500);

                    // Rafraîchir immédiatement dès que l'utilisateur revient sur l'onglet ou l'écran du téléphone
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden) {
                            this.pollNewMessages();
                        }
                    });
                    window.addEventListener('focus', () => {
                        this.pollNewMessages();
                    });
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        if (this.$refs.messageStream) {
                            this.$refs.messageStream.scrollTop = this.$refs.messageStream.scrollHeight;
                        }
                    });
                },

                playChime() {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                        osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15);
                        gain.gain.setValueAtTime(0.08, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.25);
                    } catch (e) {}
                },

                async sendMessage() {
                    const text = this.inputContent.trim();
                    if (!text || this.isSending) return;

                    this.isSending = true;
                    this.inputContent = '';

                    // Ajout optimiste immédiat
                    const tempId = 'temp-' + Date.now();
                    const now = new Date();
                    const timeFormatted = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

                    this.messages.push({
                        id: tempId,
                        content: text,
                        sender_name: 'Vous',
                        is_me: true,
                        created_at: timeFormatted,
                        sending: true
                    });
                    this.scrollToBottom();

                    try {
                        const response = await fetch(config.postUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                content: text,
                                channel_id: config.channelId || null,
                                recipient_id: config.recipientId || null
                            })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            if (data.message) {
                                const idx = this.messages.findIndex(m => m.id === tempId);
                                if (idx !== -1) {
                                    this.messages[idx] = data.message;
                                }
                            }
                        }
                    } catch (error) {
                        console.error('Erreur lors de l\'envoi du message:', error);
                    } finally {
                        this.isSending = false;
                        this.scrollToBottom();
                    }
                },

                async pollNewMessages() {
                    try {
                        const response = await fetch(config.fetchUrl, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();

                            // 1. Mettre à jour les messages de la conversation active si changés
                            if (data.messages && Array.isArray(data.messages)) {
                                const lastServerMsg = data.messages[data.messages.length - 1];
                                const lastLocalMsg = this.messages[this.messages.length - 1];

                                if (data.messages.length !== this.messages.length || 
                                    (lastServerMsg && (!lastLocalMsg || lastServerMsg.id != lastLocalMsg.id))) {
                                    
                                    const hadNewIncoming = lastServerMsg && !lastServerMsg.is_me && (!lastLocalMsg || lastServerMsg.id != lastLocalMsg.id);
                                    this.messages = data.messages;
                                    this.scrollToBottom();

                                    if (hadNewIncoming) {
                                        this.playChime();
                                    }
                                }
                            }

                            // 2. Mettre à jour les compteurs de messages non-lus dans la barre latérale
                            if (data.unread_users) {
                                this.unreadUsers = data.unread_users;
                            }

                            // 3. Notifier en direct (Toast) si un nouveau message arrive pour l'utilisateur
                            if (data.latest_incoming) {
                                const inc = data.latest_incoming;
                                if (!this.lastKnownIncomingId) {
                                    this.lastKnownIncomingId = inc.id;
                                } else if (inc.id > this.lastKnownIncomingId) {
                                    this.lastKnownIncomingId = inc.id;

                                    // Si le message n'est pas dans la conversation actuellement ouverte, afficher le Toast
                                    const isCurrent = (config.channelId && inc.channel_id == config.channelId) || 
                                                      (config.recipientId && inc.sender_id == config.recipientId);

                                    if (!isCurrent) {
                                        this.incomingToast = inc;
                                        this.playChime();
                                        setTimeout(() => {
                                            if (this.incomingToast?.id === inc.id) {
                                                this.incomingToast = null;
                                            }
                                        }, 6000);
                                    }
                                }
                            }
                        }
                    } catch (err) {
                        // Tolérance aux micro-coupures réseau
                    }
                }
            };
        }
    </script>
</x-layouts.app>
