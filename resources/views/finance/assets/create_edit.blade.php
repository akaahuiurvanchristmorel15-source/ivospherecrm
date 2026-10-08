@php
    $isEdit = isset($asset);
    $title = $isEdit ? "Modifier l'Immobilisation {$asset->code}" : "Ajouter une Immobilisation & Actif";
    $inputClass = 'mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 focus:outline-none transition';
    $labelClass = 'block text-xs font-semibold uppercase tracking-wider text-slate-700';
@endphp

<x-layouts.app :title="$title">
    <div class="mx-auto w-full max-w-5xl space-y-6 pb-20">

        {{-- ── En-tête de page ─────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-5">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                    <a href="{{ route('finance.index') }}" class="hover:text-[#0066FF] transition-colors">Finance</a>
                    <span>/</span>
                    <a href="{{ route('finance.assets.index') }}" class="hover:text-[#0066FF] transition-colors">Immobilisations & Actifs</a>
                    <span>/</span>
                    <span class="text-slate-900 font-semibold">{{ $isEdit ? 'Modification' : 'Nouvelle acquisition' }}</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ $title }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Renseignez les spécifications financières, comptables et logistiques du matériel.
                </p>
            </div>

            <a href="{{ $isEdit ? route('finance.assets.show', $asset) : route('finance.assets.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Annuler / Retour
            </a>
        </div>

        {{-- ── Formulaire ──────────────────────────────────────────────────── --}}
        <form method="POST" action="{{ $isEdit ? route('finance.assets.update', $asset) : route('finance.assets.store') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            {{-- SECTION 1 : IDENTIFICATION DE L'ACTIF --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-[#0066FF] text-xs font-bold">1</span>
                        Identification & Domaine d'Affectation
                    </h2>
                    @if($isEdit)
                        <span class="font-mono text-xs font-bold bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg">Code : {{ $asset->code }}</span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label for="name" class="{{ $labelClass }}">Nom du matériel / Équipement <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $asset->name ?? '') }}" placeholder="Ex: Appareil photo hybride Sony Alpha 7 IV / Traceur Roland VG3-540" class="{{ $inputClass }}">
                        @error('name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="asset_category_id" class="{{ $labelClass }}">Catégorie d'Immobilisation <span class="text-rose-500">*</span></label>
                        <select name="asset_category_id" id="asset_category_id" required class="{{ $inputClass }}">
                            <option value="">Sélectionner une catégorie...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('asset_category_id', $asset->asset_category_id ?? '') == $category->id)>
                                    {{ $category->name }} (Amort. {{ $category->default_useful_life_years }} ans)
                                </option>
                            @endforeach
                        </select>
                        @error('asset_category_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="domain_id" class="{{ $labelClass }}">Domaine IVOSPHERE de Rattachement</label>
                        <select name="domain_id" id="domain_id" class="{{ $inputClass }}">
                            <option value="">Commun / Tous domaines</option>
                            @foreach($domains as $domain)
                                <option value="{{ $domain->id }}" @selected(old('domain_id', $asset->domain_id ?? '') == $domain->id)>
                                    {{ $domain->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('domain_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="brand" class="{{ $labelClass }}">Marque / Fabricant</label>
                        <input type="text" name="brand" id="brand" value="{{ old('brand', $asset->brand ?? '') }}" placeholder="Ex: Sony, Canon, Roland, Yamaha, Dell" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label for="model" class="{{ $labelClass }}">Modèle</label>
                        <input type="text" name="model" id="model" value="{{ old('model', $asset->model ?? '') }}" placeholder="Ex: Alpha 7 IV, TPD7, PowerEdge" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label for="serial_number" class="{{ $labelClass }}">Numéro de Série (S/N)</label>
                        <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $asset->serial_number ?? '') }}" placeholder="Ex: SN-8829-X01" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label for="condition" class="{{ $labelClass }}">État Physique du Matériel <span class="text-rose-500">*</span></label>
                        <select name="condition" id="condition" required class="{{ $inputClass }}">
                            <option value="neuf" @selected(old('condition', $asset->condition ?? 'neuf') === 'neuf')>Neuf</option>
                            <option value="tres_bon" @selected(old('condition', $asset->condition ?? '') === 'tres_bon')>Très bon état</option>
                            <option value="bon_etat" @selected(old('condition', $asset->condition ?? '') === 'bon_etat')>Bon état</option>
                            <option value="a_reparer" @selected(old('condition', $asset->condition ?? '') === 'a_reparer')>À réparer</option>
                            <option value="hors_service" @selected(old('condition', $asset->condition ?? '') === 'hors_service')>Hors service</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="{{ $labelClass }}">Description technique / Caractéristiques</label>
                        <textarea name="description" id="description" rows="2" placeholder="Spécifications, accessoires inclus, objectifs, licences rattachées..." class="{{ $inputClass }}">{{ old('description', $asset->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- SECTION 2 : ACQUISITION & VALEURS FINANCIÈRES --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold">2</span>
                        Acquisition & Valeur de l'Actif (Notion A)
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label for="acquisition_date" class="{{ $labelClass }}">Date d'Acquisition <span class="text-rose-500">*</span></label>
                        <input type="date" name="acquisition_date" id="acquisition_date" required value="{{ old('acquisition_date', isset($asset) && $asset->acquisition_date ? $asset->acquisition_date->format('Y-m-d') : date('Y-m-d')) }}" class="{{ $inputClass }}">
                        @error('acquisition_date') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="supplier_id" class="{{ $labelClass }}">Fournisseur d'Achat</label>
                        <select name="supplier_id" id="supplier_id" class="{{ $inputClass }}">
                            <option value="">Sélectionner un fournisseur...</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $asset->supplier_id ?? '') == $supplier->id)>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="purchase_price" class="{{ $labelClass }}">Prix d'Achat HT/TTC (FCFA) <span class="text-rose-500">*</span></label>
                        <input type="number" step="100" min="0" name="purchase_price" id="purchase_price" required value="{{ old('purchase_price', $asset->purchase_price ?? '0') }}" placeholder="Ex: 1500000" class="{{ $inputClass }}">
                        @error('purchase_price') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="additional_fees" class="{{ $labelClass }}">Frais Supplémentaires (FCFA)</label>
                        <input type="number" step="100" min="0" name="additional_fees" id="additional_fees" value="{{ old('additional_fees', $asset->additional_fees ?? '0') }}" placeholder="Transport, douane, mise en service..." class="{{ $inputClass }}">
                        <p class="text-[11px] text-slate-400 mt-1">S'ajoute au prix d'achat pour former la valeur brute.</p>
                    </div>

                    <div>
                        <label for="residual_value" class="{{ $labelClass }}">Valeur Résiduelle Estimée (FCFA)</label>
                        <input type="number" step="100" min="0" name="residual_value" id="residual_value" value="{{ old('residual_value', $asset->residual_value ?? '0') }}" placeholder="Valeur en fin de vie (ex: 10%)" class="{{ $inputClass }}">
                        <p class="text-[11px] text-slate-400 mt-1">Montant non amortissable en fin de période.</p>
                    </div>

                    <div>
                        <label for="status" class="{{ $labelClass }}">Statut d'Opération <span class="text-rose-500">*</span></label>
                        <select name="status" id="status" required class="{{ $inputClass }}">
                            <option value="en_service" @selected(old('status', $asset->status ?? 'en_service') === 'en_service')>En service (interne)</option>
                            <option value="disponible" @selected(old('status', $asset->status ?? '') === 'disponible')>Disponible (prêt/location)</option>
                            <option value="loue" @selected(old('status', $asset->status ?? '') === 'loue')>Actuellement loué</option>
                            <option value="en_maintenance" @selected(old('status', $asset->status ?? '') === 'en_maintenance')>En maintenance</option>
                            <option value="a_reparer" @selected(old('status', $asset->status ?? '') === 'a_reparer')>À réparer</option>
                            <option value="hors_service" @selected(old('status', $asset->status ?? '') === 'hors_service')>Hors service</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- SECTION 3 : AMORTISSEMENT COMPTABLE --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-700 text-xs font-bold">3</span>
                        Règles d'Amortissement Comptable
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label for="useful_life_years" class="{{ $labelClass }}">Durée d'Utilisation Prévue (Ans) <span class="text-rose-500">*</span></label>
                        <input type="number" min="0" max="50" name="useful_life_years" id="useful_life_years" required value="{{ old('useful_life_years', $asset->useful_life_years ?? 5) }}" class="{{ $inputClass }}">
                        <p class="text-[11px] text-slate-400 mt-1">Ex: 3 ans (informatique), 5 ans (machines print).</p>
                    </div>

                    <div>
                        <label for="depreciation_method" class="{{ $labelClass }}">Méthode d'Amortissement <span class="text-rose-500">*</span></label>
                        <select name="depreciation_method" id="depreciation_method" required class="{{ $inputClass }}">
                            <option value="lineaire" @selected(old('depreciation_method', $asset->depreciation_method ?? 'lineaire') === 'lineaire')>Linéaire (Standard SYSCOHADA)</option>
                            <option value="degressif" @selected(old('depreciation_method', $asset->depreciation_method ?? '') === 'degressif')>Dégressif</option>
                            <option value="non_amortissable" @selected(old('depreciation_method', $asset->depreciation_method ?? '') === 'non_amortissable')>Non amortissable</option>
                        </select>
                    </div>

                    <div>
                        <label for="depreciation_start_date" class="{{ $labelClass }}">Début de l'Amortissement</label>
                        <input type="date" name="depreciation_start_date" id="depreciation_start_date" value="{{ old('depreciation_start_date', isset($asset) && $asset->depreciation_start_date ? $asset->depreciation_start_date->format('Y-m-d') : '') }}" class="{{ $inputClass }}">
                        <p class="text-[11px] text-slate-400 mt-1">Par défaut : date d'acquisition.</p>
                    </div>
                </div>
            </div>

            {{-- SECTION 4 : EMPLACEMENT & COLLABORATEUR RESPONSABLE --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 text-xs font-bold">4</span>
                        Localisation & Responsabilité
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div class="lg:col-span-2">
                        <label for="location" class="{{ $labelClass }}">Localisation / Atelier Précis</label>
                        <input type="text" name="location" id="location" value="{{ old('location', $asset->location ?? '') }}" placeholder="Ex: Studio Médias Cocody, Armoire A1 / Atelier Print" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label for="responsible_employee_id" class="{{ $labelClass }}">Collaborateur Responsable</label>
                        <select name="responsible_employee_id" id="responsible_employee_id" class="{{ $inputClass }}">
                            <option value="">Non assigné</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(old('responsible_employee_id', $asset->responsible_employee_id ?? '') == $employee->id)>
                                    {{ $employee->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="responsible_department" class="{{ $labelClass }}">Service / Équipe</label>
                        <input type="text" name="responsible_department" id="responsible_department" value="{{ old('responsible_department', $asset->responsible_department ?? '') }}" placeholder="Ex: Régie technique, Studio" class="{{ $inputClass }}">
                    </div>
                </div>
            </div>

            {{-- SECTION 5 : LOCATION EXTERNE AUX CLIENTS --}}
            <div class="rounded-2xl border border-purple-200 bg-purple-50/40 p-6 shadow-xs space-y-5" x-data="{ isRental: {{ old('is_rental_eligible', $asset->is_rental_eligible ?? false) ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between border-b border-purple-100 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-600 text-white text-xs font-bold">5</span>
                        <div>
                            <h2 class="text-base font-bold text-purple-950">Location Client & Monétisation de l'Actif</h2>
                            <p class="text-xs text-purple-700">Autoriser ce matériel à être loué à des clients (génération de CA direct).</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_rental_eligible" value="1" x-model="isRental" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5" x-show="isRental" x-cloak x-transition>
                    <div>
                        <label for="rental_price_per_day" class="{{ $labelClass }}">Tarif Journalier de Location (FCFA / jour)</label>
                        <input type="number" step="500" min="0" name="rental_price_per_day" id="rental_price_per_day" value="{{ old('rental_price_per_day', $asset->rental_price_per_day ?? '0') }}" placeholder="Ex: 35000" class="{{ $inputClass }}">
                        <p class="text-[11px] text-slate-500 mt-1">Tarif appliqué par tranche de 24h.</p>
                    </div>

                    <div>
                        <label for="rental_deposit_amount" class="{{ $labelClass }}">Montant de la Caution Exigée (FCFA)</label>
                        <input type="number" step="1000" min="0" name="rental_deposit_amount" id="rental_deposit_amount" value="{{ old('rental_deposit_amount', $asset->rental_deposit_amount ?? '0') }}" placeholder="Ex: 150000" class="{{ $inputClass }}">
                        <p class="text-[11px] text-slate-500 mt-1">Caution demandée au client avant délivrance du matériel.</p>
                    </div>
                </div>
            </div>

            {{-- SECTION 6 : PIÈCES JOINTES & PHOTO --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-700 text-xs font-bold">6</span>
                        Pièces Justificatives & Photo
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="photo" class="{{ $labelClass }}">Photo du Matériel (JPG, PNG)</label>
                        <input type="file" name="photo" id="photo" accept="image/*" class="{{ $inputClass }}">
                        @if($isEdit && $asset->photo_path)
                            <div class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                                <span>Photo actuelle enregistrée :</span>
                                <a href="{{ asset('storage/' . $asset->photo_path) }}" target="_blank" class="font-medium text-[#0066FF] underline">Voir la photo</a>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label for="purchase_document" class="{{ $labelClass }}">Facture d'Achat / Contrat (PDF, Image)</label>
                        <input type="file" name="purchase_document" id="purchase_document" accept=".pdf,image/*" class="{{ $inputClass }}">
                        @if($isEdit && $asset->purchase_document_path)
                            <div class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                                <span>Facture enregistrée :</span>
                                <a href="{{ asset('storage/' . $asset->purchase_document_path) }}" target="_blank" class="font-medium text-[#0066FF] underline">Télécharger le document</a>
                            </div>
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="{{ $labelClass }}">Notes et Remarques</label>
                        <textarea name="notes" id="notes" rows="2" placeholder="Garantie constructeur, historique d'entretien préalable..." class="{{ $inputClass }}">{{ old('notes', $asset->notes ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- BOUTONS D'ACTION --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ $isEdit ? route('finance.assets.show', $asset) : route('finance.assets.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-6 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50 transition">
                    Annuler
                </a>
                <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#0066FF] px-8 text-sm font-semibold text-white shadow-xs hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    {{ $isEdit ? 'Enregistrer les Modifications' : 'Créer l\'Immobilisation' }}
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
