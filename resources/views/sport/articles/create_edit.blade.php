<x-layouts.app :title="isset($article) ? 'Modifier Article Sport #' . $article->id . ' — SPORT' : 'Nouvel Article Sport / Flocage — SPORT'">
    @php
        $isEdit = isset($article);

        $statuses = [
            'en_attente'     => 'En attente de validation',
            'en_confection'  => 'En cours de confection / flocage',
            'pret'           => 'Prêt pour retrait / livraison',
            'livré'          => 'Livré au client / équipe',
            'annulé'         => 'Annulé',
        ];

        $sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', 'Enfant (6-8 ans)', 'Enfant (10-12 ans)', 'Enfant (14 ans)', 'Standard / Unique'];
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <a href="{{ route('sport.articles.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux articles</span>
                </a>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                    {{ $isEdit ? 'Modifier l\'Article de Sport #' . $article->id : 'Nouvel Article de Sport & Flocage' }}
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Personnalisation de maillots, tenues de clubs, flocage des numéros et noms des joueurs.
                </p>
            </div>
        </div>

        <form 
            action="{{ $isEdit ? route('sport.articles.update', $article) : route('sport.articles.store') }}" 
            method="POST" 
            x-data="{
                quantity: {{ old('quantity', $article->quantity ?? 1) }},
                unit_price: {{ old('unit_price', $article->unit_price ?? 0) }},
                get total() {
                    return Math.max(0, (parseInt(this.quantity) || 0) * (parseFloat(this.unit_price) || 0));
                }
            }"
            class="bg-white rounded-2xl p-6 sm:p-8 border border-[#E2E8F0] shadow-xs space-y-6 text-xs"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Section 1 : Produit de base & Client -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">1. Article de Base & Bénéficiaire</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="product_id" class="block font-semibold text-[#0B0F14] mb-1">Article / Produit Catalogue</label>
                        <select 
                            id="product_id" 
                            name="product_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Sélectionner un produit (ou saisir ci-contre)</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" @selected(old('product_id', $article->product_id ?? null) == $p->id)>
                                    {{ $p->name }} ({{ number_format($p->selling_price, 0, ',', ' ') }} FCFA)
                                </option>
                            @endforeach
                        </select>
                        @error('product_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="new_product_name" class="block font-semibold text-[#0B0F14] mb-1">Ou Nom de l'Article Personnalisé</label>
                        <input 
                            id="new_product_name" 
                            type="text" 
                            name="new_product_name" 
                            placeholder="Ex: Maillot Domicile Pro, Short Entraînement..."
                            value="{{ old('new_product_name', $isEdit ? $article->product->name ?? '' : '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('new_product_name') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="customer_id" class="block font-semibold text-[#0B0F14] mb-1">Client / Acheteur</label>
                        <select 
                            id="customer_id" 
                            name="customer_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Sélectionner un client</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected(old('customer_id', $article->customer_id ?? null) == $c->id)>
                                    {{ $c->name }} ({{ $c->phone ?? $c->email ?? 'Sans contact' }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="team" class="block font-semibold text-[#0B0F14] mb-1">Équipe / Club Sportif</label>
                        <input 
                            id="team" 
                            type="text" 
                            name="team" 
                            placeholder="Ex: FC San Pedro, Lions de Marcory, Interne..."
                            value="{{ old('team', $article->team ?? '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('team') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2 : Spécifications & Personnalisation -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">2. Flocage & Caractéristiques</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label for="size" class="block font-semibold text-[#0B0F14] mb-1">Taille</label>
                        <select 
                            id="size" 
                            name="size" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Choisir</option>
                            @foreach($sizes as $sz)
                                <option value="{{ $sz }}" @selected(old('size', $article->size ?? '') == $sz)>{{ $sz }}</option>
                            @endforeach
                        </select>
                        @error('size') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="color" class="block font-semibold text-[#0B0F14] mb-1">Couleur(s)</label>
                        <input 
                            id="color" 
                            type="text" 
                            name="color" 
                            placeholder="Ex: Orange & Vert, Blanc..."
                            value="{{ old('color', $article->color ?? '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('color') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="number" class="block font-semibold text-[#0B0F14] mb-1">Numéro dos</label>
                        <input 
                            id="number" 
                            type="text" 
                            name="number" 
                            placeholder="Ex: 10, 7, 23..."
                            value="{{ old('number', $article->number ?? '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-mono font-bold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('number') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="custom_name" class="block font-semibold text-[#0B0F14] mb-1">Nom / Flocage</label>
                        <input 
                            id="custom_name" 
                            type="text" 
                            name="custom_name" 
                            placeholder="Ex: KASSI, DROGBA..."
                            value="{{ old('custom_name', $article->custom_name ?? '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-mono uppercase font-bold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('custom_name') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3 : Quantités & Tarification -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">3. Quantité & Montant</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="quantity" class="block font-semibold text-[#0B0F14] mb-1">Quantité commandée *</label>
                        <input 
                            id="quantity" 
                            type="number" 
                            min="1" 
                            name="quantity" 
                            x-model="quantity"
                            value="{{ old('quantity', $article->quantity ?? 1) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-semibold focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('quantity') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="unit_price" class="block font-semibold text-[#0B0F14] mb-1">Prix Unitaire (FCFA) *</label>
                        <input 
                            id="unit_price" 
                            type="number" 
                            min="0" 
                            step="100"
                            name="unit_price" 
                            x-model="unit_price"
                            value="{{ old('unit_price', $article->unit_price ?? 0) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-semibold focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('unit_price') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-[#F5F7FA] p-3 rounded-xl border border-[#E2E8F0] flex flex-col justify-center">
                        <span class="text-[#64748B] text-[11px] uppercase font-bold">Total Calculé</span>
                        <span class="text-base font-extrabold text-[#0066FF] mt-0.5">
                            <span x-text="new Intl.NumberFormat('fr-FR').format(total)"></span> FCFA
                        </span>
                    </div>
                </div>
            </div>

            <!-- Section 4 : Statut & Notes -->
            <div class="border-t border-[#E2E8F0] pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">4. Statut & Instructions Spéciales</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block font-semibold text-[#0B0F14] mb-1">Statut de Fabrication *</label>
                        <select 
                            id="status" 
                            name="status" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] font-medium focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                            required
                        >
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $article->status ?? 'en_attente') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="block font-semibold text-[#0B0F14] mb-1">Consignes Techniques & Logo</label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            rows="3" 
                            placeholder="Type de flocage (flex, sublimation, broderie), couleur des bandes, sponsors à ajouter..."
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >{{ old('notes', $article->notes ?? '') }}</textarea>
                        @error('notes') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Actions de soumission -->
            <div class="flex items-center justify-between border-t border-[#E2E8F0] pt-5">
                <a href="{{ route('sport.articles.index') }}" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] font-medium transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#0066FF] hover:bg-[#0052CC] text-white font-semibold transition-colors shadow-xs flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $isEdit ? 'Mettre à jour l\'article' : 'Enregistrer la commande' }}</span>
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
