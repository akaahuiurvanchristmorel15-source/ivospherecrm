<x-layouts.app title="IVOSPHERE AI — Assistant de Gestion & Studio Créatif">
    <div class="space-y-6" x-data="{ tab: 'assistant', promptInput: '' }">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E2E8F0]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-[#0066FF]/10 text-[#0066FF] font-bold uppercase tracking-wider flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0066FF] animate-pulse"></span>
                        Moteur IA Intégré
                    </span>
                    <span class="text-xs text-slate-500">Données ERP Temps Réel</span>
                </div>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight mt-1">Espace IVOSPHERE AI</h1>
                <p class="text-xs text-slate-500">Interrogez vos indicateurs en langage naturel et générez vos contenus d'affaires instantanément.</p>
            </div>

            <!-- Tab Switcher -->
            <div class="inline-flex rounded-xl border border-[#E2E8F0] bg-white p-1 text-xs shadow-xs">
                <button 
                    @click="tab = 'assistant'" 
                    :class="tab === 'assistant' ? 'bg-[#0066FF] text-white shadow-xs font-semibold' : 'text-slate-600 hover:text-[#0B0F14] font-medium'"
                    class="px-4 py-2 rounded-lg transition-all flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <span>Assistant de Gestion</span>
                </button>
                <button 
                    @click="tab = 'studio'" 
                    :class="tab === 'studio' ? 'bg-[#0066FF] text-white shadow-xs font-semibold' : 'text-slate-600 hover:text-[#0B0F14] font-medium'"
                    class="px-4 py-2 rounded-lg transition-all flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Studio de Génération</span>
                </button>
            </div>
        </div>

        <!-- TAB 1: ASSISTANT DE GESTION -->
        <div x-show="tab === 'assistant'" class="space-y-6">

            <!-- Natural Language Query Card -->
            <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs">
                <form action="{{ route('ai.query') }}" method="POST" class="space-y-4">
                    @csrf
                    <label for="ai-query-input" class="block text-xs font-bold text-[#0B0F14] uppercase tracking-wider">
                        Posez une question sur les données de l'entreprise
                    </label>

                    <div class="relative">
                        <textarea 
                            id="ai-query-input"
                            name="query" 
                            rows="2" 
                            x-model="promptInput"
                            placeholder="Exemple : « Donne-moi le chiffre d'affaires de TECH ce mois-ci » ou « Quels produits sont bientôt en rupture ? »"
                            class="w-full p-4 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-sm text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0066FF] focus:border-[#0066FF] transition-all"
                            required
                        >{{ session('aiQuery') }}</textarea>

                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-[11px] text-slate-400">Interroge en temps réel Commandes, Factures, Stocks, Clients & Projets.</span>
                            <button 
                                type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-bold shadow-md shadow-[#0066FF]/20 transition-all flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Analyser les données</span>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Quick Prompt Chips -->
                <div class="mt-5 pt-4 border-t border-[#E2E8F0]">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">Questions suggérées :</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($defaultSuggestions as $sugg)
                            <button 
                                type="button"
                                @click="promptInput = '{{ addslashes($sugg) }}'"
                                class="px-3 py-1.5 rounded-lg bg-[#F5F7FA] hover:bg-slate-200 text-xs text-[#0B0F14] border border-[#E2E8F0] transition-colors text-left"
                            >
                                💬 {{ $sugg }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Query Answer Result (if submitted) -->
            @if(session('aiResponse'))
                <div class="bg-white rounded-2xl p-6 border-2 border-[#0066FF]/30 shadow-md">
                    <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0]">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-[#0066FF] text-white flex items-center justify-center font-bold text-xs">
                                IA
                            </div>
                            <div>
                                <span class="text-xs font-bold text-[#0B0F14]">Réponse d'IVOSPHERE AI</span>
                                <span class="text-[11px] text-slate-400 block">Intention détectée : {{ session('aiResponse')['intent'] }}</span>
                            </div>
                        </div>
                        <button 
                            type="button" 
                            onclick="navigator.clipboard.writeText(`{{ addslashes(session('aiResponse')['answer']) }}`); alert('Copié dans le presse-papier !');"
                            class="px-2.5 py-1 rounded-md text-xs font-medium text-slate-500 hover:text-[#0B0F14] hover:bg-[#F5F7FA] border border-[#E2E8F0] transition-colors"
                        >
                            Copier
                        </button>
                    </div>

                    <div class="py-4 text-sm text-[#0B0F14] whitespace-pre-line leading-relaxed">
                        {!! nl2br(e(session('aiResponse')['answer'])) !!}
                    </div>

                    @if(!empty(session('aiResponse')['suggestions']))
                        <div class="pt-3 border-t border-[#E2E8F0] flex flex-wrap items-center gap-2">
                            <span class="text-[11px] font-medium text-slate-400">Poursuivre :</span>
                            @foreach(session('aiResponse')['suggestions'] as $followUp)
                                <button 
                                    type="button"
                                    @click="promptInput = '{{ addslashes($followUp) }}'"
                                    class="inline-flex items-center gap-1 text-xs text-[#0066FF] font-medium hover:underline"
                                >
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    <span>{{ $followUp }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

        </div>

        <!-- TAB 2: STUDIO DE GÉNÉRATION AUTOMATIQUE -->
        <div x-show="tab === 'studio'" class="space-y-6" style="display: none;">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Configuration Form (5 cols) -->
                <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs">
                    <h2 class="text-sm font-bold text-[#0B0F14] uppercase tracking-wider mb-4">Générateur de Contenus Métiers</h2>

                    <form action="{{ route('ai.generate') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Content Type -->
                        <div>
                            <label for="ai-type-select" class="block text-xs font-semibold text-[#0B0F14] mb-1">Type de Document</label>
                            <select id="ai-type-select" name="type" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-xs text-[#0B0F14] focus:ring-1 focus:ring-[#0066FF]" required>
                                <option value="commercial_pitch">Offre / Proposition commerciale</option>
                                <option value="product_description">Fiche Description Produit</option>
                                <option value="invoice_followup_email">Email de Relance Facture Impayée</option>
                                <option value="social_post">Publication Réseaux Sociaux (LinkedIn/FB)</option>
                                <option value="slogans">Slogans & Accroches de Campagne</option>
                                <option value="video_script">Script Vidéo / Spot 30s</option>
                                <option value="executive_report">Compte Rendu / Synthèse Exécutive</option>
                            </select>
                        </div>

                        <!-- Target Domain -->
                        <div>
                            <label for="ai-domain-select" class="block text-xs font-semibold text-[#0B0F14] mb-1">Domaine d'Activité</label>
                            <select id="ai-domain-select" name="domain" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-xs text-[#0B0F14] focus:ring-1 focus:ring-[#0066FF]">
                                <option value="IVOSPHERE PRINT">IVOSPHERE PRINT (Imprimerie & Tirage)</option>
                                <option value="IVOSPHERE SPORT">IVOSPHERE SPORT (Équipements sportifs)</option>
                                <option value="IVOSPHERE TECH">IVOSPHERE TECH (Logiciels & IA)</option>
                                <option value="IVOSPHERE MEDIA & ÉVÈNEMENTS">IVOSPHERE MEDIA (Production & Location)</option>
                                <option value="IVOSPHERE ASSURANCE">IVOSPHERE ASSURANCE (Courtage & Santé)</option>
                                <option value="IVOSPHERE GROUP">IVOSPHERE GLOBAL</option>
                            </select>
                        </div>

                        <!-- Subject / Topic -->
                        <div>
                            <label for="ai-topic-input" class="block text-xs font-semibold text-[#0B0F14] mb-1">Sujet ou Objet du document</label>
                            <input 
                                id="ai-topic-input"
                                type="text" 
                                name="topic" 
                                placeholder="Ex: Impression de 5 000 catalogues luxe, Refonte ERP..." 
                                class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-xs text-[#0B0F14] focus:ring-1 focus:ring-[#0066FF]"
                                required
                            />
                        </div>

                        <!-- Target Audience -->
                        <div>
                            <label for="ai-target-input" class="block text-xs font-semibold text-[#0B0F14] mb-1">Cible visée</label>
                            <input 
                                id="ai-target-input"
                                type="text" 
                                name="target" 
                                placeholder="Ex: PME industrielles, Directions Financières..." 
                                class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-xs text-[#0B0F14] focus:ring-1 focus:ring-[#0066FF]"
                            />
                        </div>

                        <button 
                            type="submit" 
                            class="w-full py-2.5 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-bold shadow-md shadow-[#0066FF]/20 transition-all flex items-center justify-center gap-2 mt-4"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Générer le document</span>
                        </button>
                    </form>
                </div>

                <!-- Studio Output Canvas (7 cols) -->
                <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0] mb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#0066FF]"></span>
                                <h3 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">Document Généré</h3>
                            </div>
                            @if(session('generatedContent'))
                                <button 
                                    type="button" 
                                    onclick="navigator.clipboard.writeText(`{{ addslashes(session('generatedContent')['content']) }}`); alert('Contenu copié !');"
                                    class="px-2.5 py-1 rounded-md text-xs font-medium bg-[#0066FF] text-white hover:bg-[#0052cc] transition-colors"
                                >
                                    Copier le texte
                                </button>
                            @endif
                        </div>

                        @if(session('generatedContent'))
                            <div class="space-y-3">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#0066FF]/10 text-[#0066FF]">
                                        {{ session('generatedContent')['format'] }}
                                    </span>
                                    @foreach(session('generatedContent')['tags'] as $tag)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                            #{{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                                <h4 class="text-base font-bold text-[#0B0F14]">{{ session('generatedContent')['title'] }}</h4>
                                <div class="p-4 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-xs text-[#0B0F14] whitespace-pre-line leading-relaxed font-mono">
                                    {{ session('generatedContent')['content'] }}
                                </div>
                            </div>
                        @else
                            <div class="py-16 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-xs font-medium text-[#0B0F14]">Aucun contenu généré pour le moment</p>
                                <p class="text-[11px] text-slate-400 mt-1 max-w-sm mx-auto">
                                    Sélectionnez les options dans le formulaire de gauche pour générer des pitchs, descriptions produits, emails ou scripts.
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-[#E2E8F0] mt-4 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Générateur conforme à la charte et à la typographie IVOSPHERE</span>
                        <span>Norme 60-30-10</span>
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-layouts.app>
