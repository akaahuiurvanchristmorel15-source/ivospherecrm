@php
    // ── Données de configuration (une seule définition, réutilisée partout) ──
    $val = fn ($key, $default = '') => old($key, $settings[$key]->value ?? $default);

    $colors = [
        'indigo' => ['Indigo', '#0066FF'], 'emerald' => ['Émeraude', '#10B981'], 'sky' => ['Ciel', '#0EA5E9'],
        'purple' => ['Violet', '#A855F7'], 'amber' => ['Ambre', '#F59E0B'], 'rose' => ['Rose', '#F43F5E'],
        'teal' => ['Sarcelle', '#14B8A6'], 'slate' => ['Ardoise', '#64748B'],
    ];
    $colorHex = collect($colors)->map(fn ($c) => $c[1])->all();

    $icons = [
        'briefcase' => 'Mallette', 'truck' => 'Camion', 'building-office' => 'Bâtiment', 'academic-cap' => 'Formation',
        'shield-check' => 'Bouclier', 'shopping-bag' => 'Commerce', 'globe-alt' => 'International', 'chart-bar' => 'Conseil',
        'printer' => 'Imprimante', 'cpu-chip' => 'Tech', 'trophy' => 'Sport', 'camera' => 'Média',
    ];

    // [name, label, type, défaut, classes de colonne, placeholder, mono, requis, aide]
    $sections = [
        [
            'title' => '1. Identité et mentions légales',
            'subtitle' => 'Coordonnées officielles affichées sur les devis et les factures',
            'blocks' => [
                ['label' => 'Structure et coordonnées', 'grid' => 'sm:grid-cols-2 lg:grid-cols-4', 'fields' => [
                    ['company_name', 'Raison sociale', 'text', 'GROUPE IVOSPHERE', 'sm:col-span-2', '', false, true, null],
                    ['company_legal_form', 'Forme juridique', 'text', 'SARL', '', 'SARL, SA, SAS', false, false, null],
                    ['company_capital', 'Capital social', 'text', '', '', '10 000 000 FCFA', false, false, null],
                    ['company_tagline', 'Slogan officiel', 'text', 'ERP Administratif, Commercial et Multi-Domaines', 'sm:col-span-2', '', false, false, null],
                    ['company_email', 'Email de contact', 'email', '', '', 'contact@exemple.com', false, true, null],
                    ['company_phone', 'Téléphone', 'text', '', '', '+225 00 00 00 00 00', false, true, null],
                    ['company_address', 'Adresse du siège', 'text', '', 'sm:col-span-2 lg:col-span-3', 'Commune, quartier, ville', false, true, null],
                    ['company_postal_box', 'Boîte postale', 'text', '', '', 'BP 456 Abidjan 01', false, false, null],
                ]],
                ['label' => 'Identifiants fiscaux et administratifs', 'note' => 'Conforme OHADA et DGI', 'grid' => 'sm:grid-cols-2 lg:grid-cols-3', 'fields' => [
                    ['company_rccm', 'Code RCCM', 'text', '', '', 'CI-ABJ-03-2024-B12-12345', true, false, 'Registre du commerce'],
                    ['company_cc', 'Compte contribuable (CC / NIF)', 'text', '', '', '2101234 A', true, false, 'Identification fiscale (DGI)'],
                    ['company_tax_regime', 'Régime fiscal', 'text', '', '', 'Régime Réel Normal (RRN)', false, false, 'Modalité de déclaration'],
                    ['company_tax_center', 'Centre des impôts', 'text', '', '', 'DGE, CDI Cocody, CDI Plateau', false, false, 'Centre de rattachement'],
                    ['company_cnps', 'Numéro employeur CNPS', 'text', '', '', '109283-A', true, false, 'Cotisations sociales'],
                ]],
                ['label' => 'Coordonnées bancaires & règlements', 'grid' => 'sm:grid-cols-3', 'fields' => [
                    ['company_bank_name', 'Banque principale', 'text', '', '', 'Société Générale, Ecobank', false, false, null],
                    ['company_bank_rib', 'RIB / IBAN', 'text', '', 'sm:col-span-2', 'CI034 01001 012345678901 45', true, false, 'Imprimé au bas des factures pour les virements'],
                    ['company_mobile_money', 'Numéro Mobile Money (Règlements)', 'text', '', 'sm:col-span-3', '+225 07 00 00 00 00 (Wave, Orange, MTN)', false, false, 'Affiché sur les factures pour les règlements par Mobile Money'],
                ]],
            ],
        ],
        [
            'title' => '2. Configuration financière',
            'subtitle' => 'Devise, TVA et exercice comptable actif',
            'blocks' => [
                ['label' => null, 'grid' => 'sm:grid-cols-3', 'fields' => [
                    ['currency', 'Devise du système', 'text', 'FCFA', '', '', true, true, null],
                    ['default_tax_rate', 'TVA standard (%)', 'number', 0, '', '', true, true, 'Taux de TVA par défaut appliqué aux nouveaux articles et devis/factures.'],
                    ['fiscal_year', 'Exercice fiscal actif', 'text', now()->year, '', '', true, true, null],
                ]],
            ],
        ],
    ];

    $in = 'w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-base text-[#0B0F14] placeholder:text-slate-400 transition focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/20 sm:text-sm';
    $btn = 'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-1';
    $ghost = $btn . ' border border-[#E2E8F0] bg-white text-slate-700 hover:bg-[#F5F7FA]';
    $primary = $btn . ' bg-[#0066FF] text-white hover:bg-[#0052CC]';

    $activeCount = $domains->where('is_active', true)->count();
@endphp

<x-layouts.app>
    <x-slot:title>Paramètres généraux</x-slot>

    <div x-data="settingsDomainManager({
            initialShowBatch: {{ $errors->has('domains*') || request()->has('batch') ? 'true' : 'false' }},
            colors: {{ Js::from($colorHex) }},
            urls: {
                store: '{{ route('admin.domains.store') }}',
                update: '{{ route('admin.domains.update', '__ID__') }}',
                destroy: '{{ route('admin.domains.destroy', '__ID__') }}',
                edit: '{{ route('admin.domains.edit', '__ID__') }}'
            }
        })"
        @keydown.escape.window="modal.open = false; deleteModal = false"
        class="mx-auto max-w-5xl space-y-5 sm:space-y-6">

        <x-page-header
            title="Paramètres de la plateforme"
            description="Identité de l'entreprise, devise, options système et domaines d'activité">
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Administration', 'url' => route('admin.users.index')],
                    ['label' => 'Paramètres système']
                ]" />
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <a href="{{ route('admin.domains.index') }}" class="{{ $ghost }} !py-2 text-xs">Espace domaines dédié</a>
            </x-slot:actions>
        </x-page-header>

        @if ($errors->any())
            <x-alert type="danger" title="Erreurs de validation">
                <ul class="mt-1 list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </x-alert>
        @endif

        {{-- ═══ Formulaire : identité et finances ═══ --}}
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-5 sm:space-y-6">
            @csrf

            @foreach($sections as $sectionIndex => $section)
                <x-card :title="$section['title']" :subtitle="$section['subtitle']">
                    <div class="space-y-6">
                        @if($sectionIndex === 0)
                            {{-- Logo officiel de l'entreprise affiché sur les factures et devis --}}
                            <div x-data="{
                                preview: '{{ $val('company_logo') ? asset('storage/' . $val('company_logo')) : (file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : '') }}',
                                deleteLogo: false,
                                onFileSelected(e) {
                                    const file = e.target.files[0];
                                    if (file) {
                                        this.deleteLogo = false;
                                        const reader = new FileReader();
                                        reader.onload = (ev) => { this.preview = ev.target.result; };
                                        reader.readAsDataURL(file);
                                    }
                                },
                                remove() {
                                    this.deleteLogo = true;
                                    this.preview = '';
                                    if (this.$refs.logoInput) this.$refs.logoInput.value = '';
                                }
                            }" class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row items-center gap-4">
                                <input type="hidden" name="delete_logo" :value="deleteLogo ? '1' : '0'">
                                
                                <div class="relative w-20 h-20 rounded-xl bg-white border border-slate-200 p-2 flex items-center justify-center shrink-0 shadow-2xs overflow-hidden">
                                    <template x-if="preview">
                                        <img :src="preview" alt="Logo Entreprise" class="w-full h-full object-contain">
                                    </template>
                                    <template x-if="!preview">
                                        <div class="text-center text-slate-400 text-[10px] font-bold">AUCUN LOGO</div>
                                    </template>
                                </div>

                                <div class="flex-1 text-center sm:text-left space-y-1">
                                    <label class="block text-xs font-bold text-[#0B0F14]">Logo officiel de l'entreprise (Factures, Devis, Bordereaux)</label>
                                    <p class="text-[11px] text-slate-500">Affiché sur l'ensemble des documents commerciaux et factures au format PDF / Impression (JPG, PNG, WEBP, SVG · Max. 4 Mo).</p>
                                    
                                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:text-[#0066FF] hover:border-[#0066FF] cursor-pointer transition shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            <span x-text="preview ? 'Changer de logo' : 'Téléverser un logo'"></span>
                                            <input type="file" name="company_logo" x-ref="logoInput" @change="onFileSelected($event)" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="sr-only">
                                        </label>

                                        <button type="button" x-show="preview" @click="remove()" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">
                                            Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @foreach($section['blocks'] as $block)
                            <fieldset class="{{ !empty($block['note']) ? 'rounded-2xl border border-blue-100 bg-blue-50/30 p-4 sm:p-5' : '' }}">
                                @if($block['label'])
                                    <legend class="mb-3 flex w-full items-center justify-between gap-2 border-b border-[#E2E8F0] pb-2 text-sm font-semibold text-[#0B0F14]">
                                        {{ $block['label'] }}
                                        @if(!empty($block['note']))
                                            <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-[11px] font-semibold text-[#0066FF]">{{ $block['note'] }}</span>
                                        @endif
                                    </legend>
                                @endif
                                <div class="grid grid-cols-1 gap-4 {{ $block['grid'] }}">
                                    @foreach($block['fields'] as [$name, $label, $type, $default, $span, $placeholder, $mono, $required, $hint])
                                        <div class="{{ $span }}">
                                            <label for="{{ $name }}" class="mb-1.5 block text-xs font-semibold text-[#0B0F14]">
                                                {{ $label }}@if($required)<span class="text-rose-500"> *</span>@endif
                                            </label>
                                            <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}"
                                                   value="{{ $val($name, $default) }}" placeholder="{{ $placeholder }}"
                                                   @if($required) required @endif
                                                   @if($type === 'number') min="0" max="100" inputmode="numeric" @endif
                                                   class="{{ $in }} {{ $mono ? 'font-mono' : '' }}">
                                            @if($hint) <p class="mt-1 text-[11px] text-[#64748B]">{{ $hint }}</p> @endif
                                            @if($name === 'default_tax_rate')
                                                <div class="mt-2.5 rounded-lg border border-slate-200 bg-slate-50/70 p-2.5">
                                                    <label class="flex items-start gap-2 cursor-pointer select-none">
                                                        <input type="checkbox" name="apply_tax_to_products" value="1" class="mt-0.5 rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                                                        <span class="text-xs text-[#0B0F14] font-medium leading-tight">
                                                            Mettre à jour tous les articles existants avec ce taux
                                                            <span class="block text-[11px] font-normal text-slate-500 mt-0.5">Applique immédiatement cette nouvelle TVA à l'ensemble du catalogue.</span>
                                                        </span>
                                                    </label>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                </x-card>
            @endforeach

            {{-- Barre d'enregistrement fixe sur mobile --}}
            <div class="sticky bottom-0 z-10 -mx-4 border-t border-[#E2E8F0] bg-white/90 px-4 py-3 backdrop-blur sm:mx-0 sm:flex sm:justify-end sm:rounded-2xl sm:border">
                <button type="submit" class="{{ $primary }} w-full sm:w-auto">Enregistrer les paramètres</button>
            </div>
        </form>

        {{-- ═══ 3. Visuels vitrine des 5 Pôles (Page d'accueil) ═══ --}}
        <x-card id="pole-showcase-images">
            <x-slot:title>3. Visuels vitrine des 5 Pôles (Page d'accueil)</x-slot:title>
            <x-slot:subtitle>Configurez et importez les 3 images affichées pour chaque pôle dans la section « Détail des prestations » du site vitrine.</x-slot:subtitle>

            @php
                $showcasePoles = [
                    'print' => ['name' => 'IVOSPHERE PRINT', 'color' => '#0066FF', 'desc' => 'Imprimerie, papeterie et librairie'],
                    'sport' => ['name' => 'IVOSPHERE SPORT', 'color' => '#10B981', 'desc' => 'Matériel et équipements sportifs'],
                    'tech' => ['name' => 'IVOSPHERE TECH', 'color' => '#6366F1', 'desc' => 'Web, mobile, automatisation et IA'],
                    'media' => ['name' => 'IVOSPHERE MEDIA & ÉVÈNEMENTS', 'color' => '#F59E0B', 'desc' => 'Photo, son, location et organisation'],
                    'assurance' => ['name' => 'IVOSPHERE ASSURANCE', 'color' => '#F43F5E', 'desc' => 'Accompagnement en assurance & partenariat ATLANTA'],
                ];
            @endphp

            <div x-data="{ currentPole: 'print' }" class="space-y-6">
                {{-- Onglets de sélection des 5 pôles --}}
                <div class="flex flex-wrap gap-2 border-b border-[#E2E8F0] pb-3">
                    @foreach($showcasePoles as $pKey => $pData)
                        <button type="button" @click="currentPole = '{{ $pKey }}'"
                                :class="currentPole === '{{ $pKey }}' ? 'bg-[#0B0F14] text-white shadow-sm' : 'bg-[#F5F7FA] text-[#64748B] hover:text-[#0B0F14] hover:bg-slate-200/60'"
                                class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-semibold transition">
                            <span class="h-2 w-2 rounded-full" style="background: {{ $pData['color'] }}"></span>
                            <span>{{ $pData['name'] }}</span>
                        </button>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('admin.settings.pole-images') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    @foreach($showcasePoles as $pKey => $pData)
                        <div x-show="currentPole === '{{ $pKey }}'" x-cloak class="space-y-5">
                            <div class="flex items-center justify-between border-l-4 pl-3" style="border-color: {{ $pData['color'] }}">
                                <div>
                                    <h4 class="text-base font-bold text-[#0B0F14]">{{ $pData['name'] }}</h4>
                                    <p class="text-xs text-[#64748B]">{{ $pData['desc'] }}</p>
                                </div>
                                <span class="rounded-lg px-2.5 py-1 text-xs font-semibold" style="background: {{ $pData['color'] }}1A; color: {{ $pData['color'] }}">
                                    3 visuels requis
                                </span>
                            </div>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                                @for($i = 0; $i < 3; $i++)
                                    @php
                                        $currentImg = $poleShowcaseImages[$pKey][$i] ?? [];
                                        $imgPath = $currentImg['image'] ?? null;
                                        $imgTitle = $currentImg['title'] ?? '';
                                    @endphp
                                    <div class="flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-[#F5F7FA]/60 p-4 transition hover:border-[#CBD5E1]">
                                        <div>
                                            <div class="mb-3 flex items-center justify-between">
                                                <span class="text-xs font-bold text-[#0B0F14]">Visuel 0{{ $i + 1 }}</span>
                                                @if($imgPath)
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                        Actif
                                                    </span>
                                                @else
                                                    <span class="text-[11px] font-medium text-[#94A3B8]">En attente</span>
                                                @endif
                                            </div>

                                            {{-- Prévisualisation de l'image --}}
                                            <div class="relative mb-3 flex aspect-[4/3] items-center justify-center overflow-hidden rounded-xl border border-[#E2E8F0] bg-white">
                                                @if($imgPath)
                                                    <img src="{{ Str::startsWith($imgPath, ['http://', 'https://', '/']) ? $imgPath : asset($imgPath) }}"
                                                         alt="{{ $imgTitle }}" class="h-full w-full object-cover">
                                                @else
                                                    <div class="flex flex-col items-center justify-center p-4 text-center text-[#94A3B8]">
                                                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                        <span class="mt-1 text-[11px]">Pas d'image</span>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Titre / Légende du visuel --}}
                                            <div class="mb-3">
                                                <label class="mb-1 block text-[11px] font-semibold text-[#0B0F14]">Titre / Légende</label>
                                                <input type="text" name="titles[{{ $pKey }}][{{ $i }}]"
                                                       value="{{ old("titles.$pKey.$i", $imgTitle) }}"
                                                       placeholder="Ex: Titre du service"
                                                       class="{{ $in }} !py-2 !text-xs">
                                            </div>

                                            {{-- Sélecteur de fichier --}}
                                            <div>
                                                <label class="mb-1 block text-[11px] font-semibold text-[#0B0F14]">
                                                    {{ $imgPath ? 'Remplacer le visuel' : 'Ajouter une photo' }}
                                                </label>
                                                <input type="file" name="images[{{ $pKey }}][{{ $i }}]" accept="image/*"
                                                       class="block w-full text-xs text-[#64748B] file:mr-2 file:rounded-lg file:border-0 file:bg-[#0B0F14] file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-[#0066FF]">
                                                <p class="mt-1 text-[10px] text-[#94A3B8]">JPG, PNG, WebP (max 5 Mo)</p>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endforeach

                    <div class="flex justify-end border-t border-[#E2E8F0] pt-4">
                        <button type="submit" class="{{ $primary }}">
                            Enregistrer les visuels des pôles
                        </button>
                    </div>
                </form>
            </div>
        </x-card>

        {{-- ═══ 4. Domaines d'activité ═══ --}}
        <x-card>
            <x-slot:title>4. Domaines d'activité et pôles métiers</x-slot:title>
            <x-slot:subtitle>Créez un ou plusieurs pôles (PRINT, SPORT, TECH…) et gérez leur statut.</x-slot:subtitle>
            <x-slot:actions>
                <div class="grid grid-cols-2 gap-2 sm:flex">
                    <button type="button" @click="openCreate()" class="{{ $primary }} !py-2 text-xs">+ Nouveau domaine</button>
                    <button type="button" @click="showBatchSection = !showBatchSection" :aria-expanded="showBatchSection"
                            :class="showBatchSection ? '!bg-[#0B0F14] !text-white' : ''"
                            class="{{ $ghost }} !py-2 text-xs">
                        <span x-text="showBatchSection ? 'Fermer la saisie groupée' : 'Ajout groupé'"></span>
                    </button>
                </div>
            </x-slot:actions>

            {{-- Chiffres clés --}}
            <dl class="mb-5 grid grid-cols-3 gap-2 sm:gap-3">
                @foreach([
                    ['Domaines', $domains->count(), 'text-[#0B0F14]'],
                    ['Actifs', $activeCount . ' / ' . $domains->count(), 'text-emerald-600'],
                    ['Collaborateurs', $domains->sum('users_count'), 'text-[#0B0F14]'],
                ] as [$label, $value, $color])
                    <div class="rounded-xl bg-[#F5F7FA] p-3 sm:p-4">
                        <dt class="text-[11px] text-[#64748B]">{{ $label }}</dt>
                        <dd class="mt-0.5 text-lg font-bold sm:text-xl {{ $color }}">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            {{-- Saisie groupée --}}
            <section x-show="showBatchSection" x-cloak x-transition.opacity
                     class="mb-6 rounded-2xl border border-[#0066FF]/30 bg-[#F5F7FA]/60 p-3 sm:p-5">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-[#0B0F14]">Créer plusieurs domaines à la fois</h4>
                        <p class="text-xs text-[#64748B]">Une carte par domaine. Le code est généré depuis le nom.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="prefillSuggestions()" class="{{ $ghost }} !px-3 !py-1.5 text-xs">Suggestions</button>
                        <button type="button" @click="addRows(3)" class="{{ $ghost }} !px-3 !py-1.5 text-xs">+ 3 lignes</button>
                        <button type="button" @click="resetRows()" class="{{ $ghost }} !px-3 !py-1.5 text-xs hover:!text-rose-600">Vider</button>
                    </div>
                </div>

                <form action="{{ route('admin.domains.batch') }}" method="POST" class="space-y-3">
                    @csrf
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="grid grid-cols-1 gap-3 rounded-xl border border-[#E2E8F0] bg-white p-3.5 sm:grid-cols-2 lg:grid-cols-12">
                            <div class="flex items-center justify-between lg:col-span-12">
                                <span class="text-xs font-semibold text-[#64748B]" x-text="'Domaine ' + (index + 1)"></span>
                                <button type="button" @click="removeRow(index)" :disabled="rows.length <= 1"
                                        class="rounded-lg px-2 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-30">Retirer</button>
                            </div>
                            <label class="block text-xs font-semibold text-[#0B0F14] lg:col-span-5">Nom *
                                <input type="text" :name="`domains[${index}][name]`" x-model="row.name" @blur="suggestCode(row)" required
                                       placeholder="IVOSPHERE LOGISTIQUE" class="{{ $in }} mt-1 font-normal">
                            </label>
                            <label class="block text-xs font-semibold text-[#0B0F14] lg:col-span-3">Code *
                                <input type="text" :name="`domains[${index}][code]`" x-model="row.code" maxlength="50" required
                                       @input="row.code = clean(row.code)" placeholder="LOGISTIQUE"
                                       class="{{ $in }} mt-1 font-mono font-bold uppercase !text-[#0066FF]">
                            </label>
                            <label class="block text-xs font-semibold text-[#0B0F14] sm:col-span-2 lg:col-span-4">Description
                                <input type="text" :name="`domains[${index}][description]`" x-model="row.description"
                                       placeholder="Transport et flotte" class="{{ $in }} mt-1 font-normal">
                            </label>
                            <label class="block text-xs font-semibold text-[#0B0F14] lg:col-span-4">Couleur
                                <span class="mt-1 flex items-center gap-2">
                                    <span class="h-3.5 w-3.5 shrink-0 rounded-full" :style="`background:${hex(row.color)}`"></span>
                                    <select :name="`domains[${index}][color]`" x-model="row.color" class="{{ $in }} font-normal">
                                        @foreach($colors as $key => [$name]) <option value="{{ $key }}">{{ $name }}</option> @endforeach
                                    </select>
                                </span>
                            </label>
                            <label class="block text-xs font-semibold text-[#0B0F14] lg:col-span-4">Icône
                                <select :name="`domains[${index}][icon]`" x-model="row.icon" class="{{ $in }} mt-1 font-normal">
                                    @foreach($icons as $key => $name) <option value="{{ $key }}">{{ $name }}</option> @endforeach
                                </select>
                            </label>
                            <div class="flex items-end sm:col-span-2 lg:col-span-4">
                                <input type="hidden" :name="`domains[${index}][is_active]`" value="0">
                                <label class="flex cursor-pointer items-center gap-2 pb-2.5 text-sm text-slate-700">
                                    <input type="checkbox" :name="`domains[${index}][is_active]`" value="1" x-model="row.is_active"
                                           class="h-4 w-4 rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                                    Actif dès la création
                                </label>
                            </div>
                        </div>
                    </template>

                    <div class="flex flex-col gap-2 pt-1 sm:flex-row sm:justify-between">
                        <button type="button" @click="addRow()" class="{{ $ghost }}">+ Ajouter une ligne</button>
                        <button type="submit" class="{{ $primary }}" x-text="`Enregistrer ${rows.length} domaine${rows.length > 1 ? 's' : ''}`"></button>
                    </div>
                </form>
            </section>

            {{-- Liste --}}
            <div class="mb-3 flex items-center justify-between border-b border-[#E2E8F0] pb-2">
                <h3 class="text-sm font-semibold text-[#0B0F14]">Domaines existants ({{ $domains->count() }})</h3>
                <a href="{{ route('admin.domains.index') }}" class="text-xs font-semibold text-[#0066FF] hover:underline">Vue détaillée</a>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                @forelse($domains as $domain)
                    <article class="flex flex-col justify-between rounded-xl border border-[#E2E8F0] bg-white p-4 transition hover:border-[#0066FF]/50">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="flex items-center gap-2">
                                    <span class="h-3 w-3 shrink-0 rounded-full" style="background: {{ $colorHex[$domain->color] ?? '#64748B' }}"></span>
                                    <span class="font-mono text-xs font-bold text-[#0066FF]">{{ $domain->code }}</span>
                                </span>
                                <form method="POST" action="{{ route('admin.domains.toggle', $domain) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="Changer le statut"
                                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold transition {{ $domain->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'border-[#E2E8F0] bg-slate-100 text-[#64748B] hover:bg-slate-200' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $domain->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $domain->is_active ? 'Actif' : 'Inactif' }}
                                    </button>
                                </form>
                            </div>
                            <h4 class="mt-2 text-sm font-semibold text-[#0B0F14]">{{ $domain->name }}</h4>
                            <p class="mt-0.5 line-clamp-2 text-xs text-[#64748B]">{{ $domain->description ?: 'Aucune description.' }}</p>
                        </div>
                        <div class="mt-3 flex items-center justify-between border-t border-[#E2E8F0] pt-3">
                            <span class="text-xs text-[#64748B]"><strong class="text-[#0B0F14]">{{ $domain->users_count }}</strong> utilisateur(s)</span>
                            <div class="flex gap-1.5">
                                <button type="button" @click="openEdit({{ Js::from($domain->only(['id','name','code','description','color','icon','is_active'])) }})"
                                        class="{{ $ghost }} !px-3 !py-1.5 text-xs">Modifier</button>
                                <button type="button" @click="openDelete({{ Js::from($domain->only(['id','name','code'])) }})"
                                        class="{{ $btn }} border border-rose-200 !px-3 !py-1.5 text-xs text-rose-600 hover:bg-rose-50">Supprimer</button>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="col-span-full rounded-xl bg-[#F5F7FA] py-8 text-center text-sm text-[#64748B]">Aucun domaine configuré. Utilisez les boutons ci-dessus pour en ajouter.</p>
                @endforelse
            </div>
        </x-card>

        {{-- ═══ Modale création / modification (partagée) ═══ --}}
        <div x-show="modal.open" x-cloak x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="modal-title"
             class="fixed inset-0 z-50 flex items-end justify-center bg-[#0B0F14]/50 sm:items-center sm:p-4">
            <div @click.outside="modal.open = false"
                 class="max-h-[92dvh] w-full max-w-lg overflow-y-auto rounded-t-2xl bg-white p-5 shadow-xl sm:rounded-2xl sm:p-6">
                <div class="mb-4 flex items-center justify-between border-b border-[#E2E8F0] pb-3">
                    <h3 id="modal-title" class="text-base font-semibold text-[#0B0F14]" x-text="modal.mode === 'edit' ? 'Modifier le domaine' : 'Nouveau domaine'"></h3>
                    <button type="button" @click="modal.open = false" aria-label="Fermer" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="modal.action" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="modal.mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>

                    <label class="block text-xs font-semibold text-[#0B0F14]">Nom du domaine *
                        <input type="text" name="name" x-model="form.name" @blur="suggestCode(form)" required placeholder="IVOSPHERE LOGISTIQUE" class="{{ $in }} mt-1 font-normal">
                    </label>
                    <label class="block text-xs font-semibold text-[#0B0F14]">Code unique *
                        <input type="text" name="code" x-model="form.code" maxlength="50" required @input="form.code = clean(form.code)"
                               placeholder="LOGISTIQUE" class="{{ $in }} mt-1 font-mono font-bold uppercase !text-[#0066FF]">
                        <span class="mt-1 block text-[11px] font-normal text-[#64748B]">Lettres majuscules, chiffres, tirets</span>
                    </label>
                    <label class="block text-xs font-semibold text-[#0B0F14]">Description
                        <textarea name="description" x-model="form.description" rows="2" placeholder="Périmètre opérationnel" class="{{ $in }} mt-1 font-normal"></textarea>
                    </label>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <label class="block text-xs font-semibold text-[#0B0F14]">Couleur
                            <span class="mt-1 flex items-center gap-2">
                                <span class="h-3.5 w-3.5 shrink-0 rounded-full" :style="`background:${hex(form.color)}`"></span>
                                <select name="color" x-model="form.color" class="{{ $in }} font-normal">
                                    @foreach($colors as $key => [$name]) <option value="{{ $key }}">{{ $name }}</option> @endforeach
                                </select>
                            </span>
                        </label>
                        <label class="block text-xs font-semibold text-[#0B0F14]">Icône
                            <select name="icon" x-model="form.icon" class="{{ $in }} mt-1 font-normal">
                                @foreach($icons as $key => $name) <option value="{{ $key }}">{{ $name }}</option> @endforeach
                            </select>
                        </label>
                    </div>
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="h-4 w-4 rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                        Domaine actif dans l'ensemble de l'ERP
                    </label>

                    <div class="flex flex-col-reverse gap-2 border-t border-[#E2E8F0] pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <a x-show="modal.mode === 'edit'" :href="modal.editUrl" class="text-center text-xs font-semibold text-[#0066FF] hover:underline">Ouvrir la page complète</a>
                        <span x-show="modal.mode !== 'edit'"></span>
                        <div class="grid grid-cols-2 gap-2 sm:flex">
                            <button type="button" @click="modal.open = false" class="{{ $ghost }}">Annuler</button>
                            <button type="submit" class="{{ $primary }}" x-text="modal.mode === 'edit' ? 'Mettre à jour' : 'Créer'"></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ═══ Modale suppression ═══ --}}
        <div x-show="deleteModal" x-cloak x-transition.opacity role="alertdialog" aria-modal="true" aria-labelledby="delete-title"
             class="fixed inset-0 z-50 flex items-end justify-center bg-[#0B0F14]/50 sm:items-center sm:p-4">
            <div @click.outside="deleteModal = false" class="w-full max-w-md rounded-t-2xl bg-white p-5 shadow-xl sm:rounded-2xl sm:p-6">
                <h3 id="delete-title" class="text-base font-semibold text-[#0B0F14]">Supprimer ce domaine ?</h3>
                <p class="mt-2 text-sm text-slate-600">
                    Le domaine <strong class="text-[#0B0F14]" x-text="target.name"></strong>
                    (<span class="font-mono font-bold text-[#0066FF]" x-text="target.code"></span>) sera supprimé. Cette action est irréversible.
                </p>
                <p class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900">
                    Les collaborateurs rattachés seront détachés et les données associées passeront en gestion centrale, sans perte d'historique.
                </p>
                <form :action="deleteUrl" method="POST" class="mt-5 grid grid-cols-2 gap-2 sm:flex sm:justify-end">
                    @csrf @method('DELETE')
                    <button type="button" @click="deleteModal = false" class="{{ $ghost }}">Annuler</button>
                    <button type="submit" class="{{ $btn }} bg-rose-600 text-white hover:bg-rose-700">Supprimer</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function settingsDomainManager(config) {
            const blank = (color = 'indigo') => ({ name: '', code: '', description: '', color, icon: 'briefcase', is_active: true });
            const palette = Object.keys(config.colors);
            const url = (tpl, id) => tpl.replace('__ID__', id);

            return {
                showBatchSection: config.initialShowBatch,
                rows: [blank(), blank('emerald')],
                modal: { open: false, mode: 'create', action: config.urls.store, editUrl: '' },
                form: blank(),
                deleteModal: false,
                deleteUrl: '',
                target: { name: '', code: '' },

                hex(color) { return config.colors[color] || '#0066FF'; },
                clean(code) { return (code || '').toUpperCase().replace(/[^A-Z0-9_-]/g, ''); },

                suggestCode(item) {
                    if (item.code && item.code.trim()) return;
                    item.code = (item.name || '').trim().toUpperCase()
                        .replace(/^IVOSPHERE\s+/, '')
                        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^A-Z0-9]/g, '').slice(0, 20);
                },

                addRow() { this.rows.push(blank(palette[this.rows.length % palette.length])); },
                addRows(n) { for (let i = 0; i < n; i++) this.addRow(); },
                removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1); },
                resetRows() { this.rows = [blank()]; },

                prefillSuggestions() {
                    this.rows = [
                        { name: 'IVOSPHERE LOGISTIQUE', code: 'LOGISTIQUE', description: "Entreposage, chaîne d'approvisionnement et transport", color: 'teal', icon: 'truck', is_active: true },
                        { name: 'IVOSPHERE IMMOBILIER & BTP', code: 'IMMOBILIER', description: "Gestion de biens, transactions et travaux d'aménagement", color: 'amber', icon: 'building-office', is_active: true },
                        { name: 'IVOSPHERE FORMATION', code: 'FORMATION', description: 'Séminaires professionnels et développement des compétences', color: 'rose', icon: 'academic-cap', is_active: true },
                        { name: 'IVOSPHERE CONSEIL & STRATÉGIE', code: 'CONSEIL', description: "Organisation d'entreprise, audit et ingénierie d'affaires", color: 'slate', icon: 'chart-bar', is_active: true },
                    ];
                },

                openCreate() {
                    this.form = blank();
                    this.modal = { open: true, mode: 'create', action: config.urls.store, editUrl: '' };
                },
                openEdit(d) {
                    this.form = {
                        name: d.name, code: d.code, description: d.description || '',
                        color: d.color || 'indigo', icon: d.icon || 'briefcase', is_active: Boolean(d.is_active),
                    };
                    this.modal = { open: true, mode: 'edit', action: url(config.urls.update, d.id), editUrl: url(config.urls.edit, d.id) };
                },
                openDelete(d) {
                    this.target = { name: d.name, code: d.code };
                    this.deleteUrl = url(config.urls.destroy, d.id);
                    this.deleteModal = true;
                },
            };
        }
    </script>
</x-layouts.app>