<x-layouts.app title="Simulateurs de Devis & Calculatrices Métiers">
    <div class="space-y-6" x-data="{ tab: 'print' }">

        <!-- Top Header & Tabs -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E2E8F0]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-[#0066FF]/10 text-[#0066FF] font-bold uppercase tracking-wider">Outils Métiers</span>
                    <span class="text-xs text-slate-500">Calcul instantané & Chiffrage</span>
                </div>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight mt-1">Simulateurs de Devis Métiers</h1>
                <p class="text-xs text-slate-500">Estimez les coûts de production, locations et budgets événementiels en direct.</p>
            </div>

            <!-- Tab Switcher -->
            <div class="inline-flex rounded-xl border border-[#E2E8F0] bg-white p-1 text-xs shadow-xs">
                <button 
                    @click="tab = 'print'" 
                    :class="tab === 'print' ? 'bg-[#0066FF] text-white shadow-xs font-semibold' : 'text-slate-600 font-medium'"
                    class="px-3.5 py-1.5 rounded-lg transition-all"
                >
                    🖨️ Devis PRINT
                </button>
                <button 
                    @click="tab = 'rental'" 
                    :class="tab === 'rental' ? 'bg-[#0066FF] text-white shadow-xs font-semibold' : 'text-slate-600 font-medium'"
                    class="px-3.5 py-1.5 rounded-lg transition-all"
                >
                    🎥 Location MEDIA
                </button>
                <button 
                    @click="tab = 'event'" 
                    :class="tab === 'event' ? 'bg-[#0066FF] text-white shadow-xs font-semibold' : 'text-slate-600 font-medium'"
                    class="px-3.5 py-1.5 rounded-lg transition-all"
                >
                    🎪 Budget Event
                </button>
            </div>
        </div>

        <!-- 1. SIMULATEUR PRINT -->
        <div 
            x-show="tab === 'print'" 
            class="grid grid-cols-1 lg:grid-cols-12 gap-6"
            x-data="{
                formatCost: 50,
                supportCost: 35,
                finishingCost: 20,
                quantity: 1000,
                marginPercent: 30,
                get unitProductionCost() {
                    return Number(this.formatCost) + Number(this.supportCost) + Number(this.finishingCost);
                },
                get unitSellingPrice() {
                    return Math.round(this.unitProductionCost * (1 + (Number(this.marginPercent) / 100)));
                },
                get totalAmount() {
                    return this.unitSellingPrice * Number(this.quantity);
                }
            }"
        >
            <!-- Parameters (7 cols) -->
            <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4 text-xs">
                <h2 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider mb-2">Paramètres Techniques d'Impression</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="sim-format-select" class="block font-semibold text-[#0B0F14] mb-1">Format / Dimensions</label>
                        <select id="sim-format-select" x-model="formatCost" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                            <option value="30">Flyer A5 (14.8 × 21 cm)</option>
                            <option value="50" selected>Flyer / Dépliant A4 (21 × 29.7 cm)</option>
                            <option value="90">Affiche A3 (29.7 × 42 cm)</option>
                            <option value="250">Bâche PVC 1m²</option>
                            <option value="450">Roll-up Premium (85 × 200 cm)</option>
                        </select>
                    </div>

                    <div>
                        <label for="sim-support-select" class="block font-semibold text-[#0B0F14] mb-1">Type de Support Papier</label>
                        <select id="sim-support-select" x-model="supportCost" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                            <option value="20">Papier Couché Brillant 135g</option>
                            <option value="35" selected>Papier Couché Demi-Mat 250g</option>
                            <option value="55">Cartonné Rigide 350g</option>
                            <option value="75">Vinyle Adhésif Haute Résistance</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="sim-finishing-select" class="block font-semibold text-[#0B0F14] mb-1">Finition & Pelliculage</label>
                        <select id="sim-finishing-select" x-model="finishingCost" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                            <option value="0">Aucune (Brut d'impression)</option>
                            <option value="20" selected>Pelliculage Mat Recto</option>
                            <option value="35">Pelliculage Brillant Recto/Verso</option>
                            <option value="60">Vernis Sélectif 3D + Soft Touch</option>
                        </select>
                    </div>

                    <div>
                        <label for="sim-qty-input" class="block font-semibold text-[#0B0F14] mb-1">Tirage / Quantité d'exemplaires</label>
                        <input id="sim-qty-input" type="number" x-model="quantity" min="10" step="50" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                    </div>
                </div>

                <div>
                    <label for="sim-margin-range" class="block font-semibold text-[#0B0F14] mb-1">
                        Marge Commerciale Souhaitée : <span class="text-[#0066FF] font-bold" x-text="marginPercent + '%'"></span>
                    </label>
                    <input id="sim-margin-range" type="range" x-model="marginPercent" min="10" max="80" step="5" class="w-full accent-[#0066FF]" />
                </div>
            </div>

            <!-- Live Quotation Result (5 cols) -->
            <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 pb-3 border-b border-[#E2E8F0] mb-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#0066FF]"></span>
                        <h3 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">Devis PRINT Estimé</h3>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-[#F5F7FA]">
                            <span class="text-slate-500">Coût de revient unitaire :</span>
                            <span class="font-bold text-[#0B0F14]" x-text="unitProductionCost + ' FCFA'"></span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-[#F5F7FA]">
                            <span class="text-slate-500">Prix unitaire facturé (HT) :</span>
                            <span class="font-bold text-[#0066FF]" x-text="unitSellingPrice + ' FCFA'"></span>
                        </div>
                        <div class="p-4 rounded-xl bg-[#0066FF]/5 border border-[#0066FF]/30 mt-4 text-center">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Montant Total Devis Estimé</span>
                            <p class="text-2xl font-black text-[#0066FF] mt-1" x-text="totalAmount.toLocaleString('fr-FR') + ' FCFA'"></p>
                            <span class="text-[10px] text-slate-400 mt-1 block">Sur la base de <span x-text="quantity"></span> unités</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-[#E2E8F0] mt-4">
                    <a href="{{ route('commercial.quotations.create') }}" class="w-full py-2.5 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-bold shadow-md shadow-[#0066FF]/20 flex items-center justify-center gap-2 transition-all">
                        <span>Créer le Devis Officiel</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. SIMULATEUR LOCATION MEDIA -->
        <div 
            x-show="tab === 'rental'" 
            class="grid grid-cols-1 lg:grid-cols-12 gap-6"
            x-data="{
                packDailyCost: 75000,
                days: 3,
                withOperator: true,
                operatorDailyCost: 35000,
                cautionRequired: 150000,
                get rentalSubtotal() {
                    return Number(this.packDailyCost) * Number(this.days);
                },
                get operatorTotal() {
                    return this.withOperator ? (Number(this.operatorDailyCost) * Number(this.days)) : 0;
                },
                get totalRental() {
                    return this.rentalSubtotal + this.operatorTotal;
                }
            }"
            style="display: none;"
        >
            <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4 text-xs">
                <h2 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider mb-2">Configuration Location Matériel Audiovisuel</h2>

                <div>
                    <label for="sim-kit-select" class="block font-semibold text-[#0B0F14] mb-1">Kit Équipement</label>
                    <select id="sim-kit-select" x-model="packDailyCost" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                        <option value="45000">Pack Découverte : Boîtier Sony A7IV + Objectif 24-70mm (45 000 F/jour)</option>
                        <option value="75000" selected>Pack Pro Tournage : Sony FX3 + Kit 3 Optiques Fixes + Trépied (75 000 F/jour)</option>
                        <option value="120000">Pack Cinéma : Caméra Blackmagic 6K Pro + Mattebox + Follow Focus + V-Mount (120 000 F/jour)</option>
                        <option value="60000">Pack Éclairage & Audio : 2x Aputure 300d II + 2x Micros HF Sennheiser (60 000 F/jour)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="sim-days-input" class="block font-semibold text-[#0B0F14] mb-1">Nombre de jours de location</label>
                        <input id="sim-days-input" type="number" x-model="days" min="1" max="30" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                    </div>
                    <div>
                        <label for="sim-caution-input" class="block font-semibold text-[#0B0F14] mb-1">Caution Obligatoire (remboursable)</label>
                        <input id="sim-caution-input" type="number" x-model="cautionRequired" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="withOperator" class="rounded border-[#E2E8F0] text-[#0066FF] focus:ring-0" />
                        <span class="font-bold text-[#0B0F14]">Inclure un cadreur / technicien audiovisuel dédié (+35 000 FCFA/jour)</span>
                    </label>
                </div>
            </div>

            <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 pb-3 border-b border-[#E2E8F0] mb-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#0B0F14]"></span>
                        <h3 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">Devis Location MEDIA</h3>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-[#F5F7FA]">
                            <span class="text-slate-500">Location matériel :</span>
                            <span class="font-bold text-[#0B0F14]" x-text="rentalSubtotal.toLocaleString('fr-FR') + ' FCFA'"></span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-[#F5F7FA]" x-show="withOperator">
                            <span class="text-slate-500">Technicien / Opérateur :</span>
                            <span class="font-bold text-[#0B0F14]" x-text="operatorTotal.toLocaleString('fr-FR') + ' FCFA'"></span>
                        </div>
                        <div class="p-4 rounded-xl bg-[#0B0F14]/5 border border-[#0B0F14]/20 mt-4 text-center">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Location Estimé</span>
                            <p class="text-2xl font-black text-[#0B0F14] mt-1" x-text="totalRental.toLocaleString('fr-FR') + ' FCFA'"></p>
                            <span class="text-[10px] text-slate-500 mt-1 block">+ Caution requise : <span x-text="Number(cautionRequired).toLocaleString('fr-FR')"></span> FCFA</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-[#E2E8F0] mt-4">
                    <a href="{{ route('media.rentals.create') }}" class="w-full py-2.5 rounded-xl bg-[#0B0F14] hover:bg-slate-800 text-white text-xs font-bold shadow-md flex items-center justify-center gap-2 transition-all">
                        <span>Établir le Bon de Location</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 3. SIMULATEUR BUDGET EVENT -->
        <div 
            x-show="tab === 'event'" 
            class="grid grid-cols-1 lg:grid-cols-12 gap-6"
            x-data="{
                guests: 150,
                venueCost: 500000,
                cateringPerGuest: 12000,
                avSoundCost: 350000,
                hostessesCount: 4,
                get cateringTotal() {
                    return Number(this.guests) * Number(this.cateringPerGuest);
                },
                get hostessesTotal() {
                    return Number(this.hostessesCount) * 25000;
                },
                get totalEventBudget() {
                    return Number(this.venueCost) + this.cateringTotal + Number(this.avSoundCost) + this.hostessesTotal;
                }
            }"
            style="display: none;"
        >
            <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4 text-xs">
                <h2 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider mb-2">Postes Budgétaires Événementiels</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="event-guests-input" class="block font-semibold text-[#0B0F14] mb-1">Nombre d'invités / participants</label>
                        <input id="event-guests-input" type="number" x-model="guests" min="20" step="10" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                    </div>
                    <div>
                        <label for="event-venue-input" class="block font-semibold text-[#0B0F14] mb-1">Lieu / Salle (FCFA)</label>
                        <input id="event-venue-input" type="number" x-model="venueCost" step="50000" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="event-catering-input" class="block font-semibold text-[#0B0F14] mb-1">Traiteur / Cocktail (par invité)</label>
                        <input id="event-catering-input" type="number" x-model="cateringPerGuest" step="1000" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                    </div>
                    <div>
                        <label for="event-av-input" class="block font-semibold text-[#0B0F14] mb-1">Scénographie, Sonorisation & Lumière</label>
                        <input id="event-av-input" type="number" x-model="avSoundCost" step="25000" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                    </div>
                </div>

                <div>
                    <label for="event-hostesses-input" class="block font-semibold text-[#0B0F14] mb-1">Nombre d'hôtesses d'accueil (25 000 F / hôtesse)</label>
                    <input id="event-hostesses-input" type="number" x-model="hostessesCount" min="0" max="20" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                </div>
            </div>

            <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 pb-3 border-b border-[#E2E8F0] mb-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                        <h3 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">Budget Global Événementiel</h3>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#F5F7FA]">
                            <span class="text-slate-500">Traiteur (<span x-text="guests"></span> pax) :</span>
                            <span class="font-bold text-[#0B0F14]" x-text="cateringTotal.toLocaleString('fr-FR') + ' FCFA'"></span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#F5F7FA]">
                            <span class="text-slate-500">Technique & Lumière :</span>
                            <span class="font-bold text-[#0B0F14]" x-text="Number(avSoundCost).toLocaleString('fr-FR') + ' FCFA'"></span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#F5F7FA]">
                            <span class="text-slate-500">Accueil & Hôtesses :</span>
                            <span class="font-bold text-[#0B0F14]" x-text="hostessesTotal.toLocaleString('fr-FR') + ' FCFA'"></span>
                        </div>
                        <div class="p-4 rounded-xl bg-purple-50 border border-purple-200 mt-3 text-center">
                            <span class="text-[11px] font-bold text-purple-900 uppercase tracking-wider block">Budget Total Clé en Main</span>
                            <p class="text-2xl font-black text-purple-950 mt-1" x-text="totalEventBudget.toLocaleString('fr-FR') + ' FCFA'"></p>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-[#E2E8F0] mt-4">
                    <a href="{{ route('media.events.create') }}" class="w-full py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold shadow-md flex items-center justify-center gap-2 transition-all">
                        <span>Créer la Fiche Événement</span>
                    </a>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
