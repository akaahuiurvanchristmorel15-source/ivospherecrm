@php
    // ── Coordonnées : à définir dans config/company.php (rien n'est affiché si vide) ──
    $phone    = config('company.phone');
    $whatsapp = config('company.whatsapp');
    $email    = config('company.email', config('mail.from.address'));
    $address  = config('company.address');

    // ── Récupération des visuels vitrine configurés dans les paramètres de l'ERP ──
    $savedPoleImages = class_exists(\App\Models\Setting::class)
        ? (array) \App\Models\Setting::get('pole_showcase_images', [])
        : [];

    // ── Les 5 pôles : source unique pour le hero, les cartes, le détail et le formulaire ──
    $poles = [
        'print' => [
            'name' => 'IVOSPHERE PRINT', 'short' => 'Print', 'color' => '#0066FF',
            'tagline' => 'Imprimerie, papeterie et librairie.',
            'intro' => "Tous vos supports imprimés, de la carte de visite à la fourniture de bureau, avec un bon à tirer validé avant chaque tirage.",
            'services' => ['Impression de vos supports de communication', 'Papeterie professionnelle et personnalisée', 'Librairie et fournitures'],
            'gallery' => [
                ['title' => $savedPoleImages['print'][0]['title'] ?? 'Impression & Tirages', 'image' => $savedPoleImages['print'][0]['image'] ?? null],
                ['title' => $savedPoleImages['print'][1]['title'] ?? 'Papeterie personnalisée', 'image' => $savedPoleImages['print'][1]['image'] ?? null],
                ['title' => $savedPoleImages['print'][2]['title'] ?? 'Packaging & Fournitures', 'image' => $savedPoleImages['print'][2]['image'] ?? null],
            ],
            'icon' => ['M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z'],
        ],
        'sport' => [
            'name' => 'IVOSPHERE SPORT', 'short' => 'Sport', 'color' => '#10B981',
            'tagline' => 'Matériel et équipements sportifs.',
            'intro' => "Clubs, écoles et entreprises : équipez vos équipes avec du matériel adapté et des tenues à vos couleurs.",
            'services' => ['Matériel et équipements sportifs', 'Flocage et personnalisation des tenues', 'Fournitures pour clubs et associations'],
            'gallery' => [
                ['title' => $savedPoleImages['sport'][0]['title'] ?? 'Équipements & Maillots', 'image' => $savedPoleImages['sport'][0]['image'] ?? null],
                ['title' => $savedPoleImages['sport'][1]['title'] ?? 'Flocage & Personnalisation', 'image' => $savedPoleImages['sport'][1]['image'] ?? null],
                ['title' => $savedPoleImages['sport'][2]['title'] ?? 'Matériel Clubs & Écoles', 'image' => $savedPoleImages['sport'][2]['image'] ?? null],
            ],
            'icon' => ['M8 21h8m-4-4v4m-5-8a5 5 0 0010 0V5H7v6zM7 5H4a2 2 0 00-2 2v1a4 4 0 004 4h1m10-7h3a2 2 0 012 2v1a4 4 0 01-4 4h-1'],
        ],
        'tech' => [
            'name' => 'IVOSPHERE TECH', 'short' => 'Tech', 'color' => '#6366F1',
            'tagline' => 'Web, mobile, automatisation et IA.',
            'intro' => "Des outils numériques conçus pour votre activité, du site vitrine à l'automatisation de vos tâches répétitives.",
            'services' => ['Développement web et mobile', 'Automatisation de processus', 'Solutions d\'intelligence artificielle', 'Matériel informatique'],
            'gallery' => [
                ['title' => $savedPoleImages['tech'][0]['title'] ?? 'Web & Apps mobiles', 'image' => $savedPoleImages['tech'][0]['image'] ?? null],
                ['title' => $savedPoleImages['tech'][1]['title'] ?? 'Automatisation & IA', 'image' => $savedPoleImages['tech'][1]['image'] ?? null],
                ['title' => $savedPoleImages['tech'][2]['title'] ?? 'Matériel & Réseaux', 'image' => $savedPoleImages['tech'][2]['image'] ?? null],
            ],
            'icon' => ['M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ],
        'media' => [
            'name' => 'IVOSPHERE MEDIA & ÉVÈNEMENTS', 'short' => 'Media & Évènements', 'color' => '#F59E0B',
            'tagline' => 'Photo, son, location et organisation.',
            'intro' => "Capturer, sonoriser et organiser : nous prenons en charge vos évènements de la préparation au jour J.",
            'services' => ['Photographie', 'Sonorisation', 'Location de matériel', 'Organisation d\'évènements'],
            'gallery' => [
                ['title' => $savedPoleImages['media'][0]['title'] ?? 'Photo & Reportages', 'image' => $savedPoleImages['media'][0]['image'] ?? null],
                ['title' => $savedPoleImages['media'][1]['title'] ?? 'Sonorisation & Événements', 'image' => $savedPoleImages['media'][1]['image'] ?? null],
                ['title' => $savedPoleImages['media'][2]['title'] ?? 'Location Audiovisuelle', 'image' => $savedPoleImages['media'][2]['image'] ?? null],
            ],
            'icon' => ['M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z', 'M15 13a3 3 0 11-6 0 3 3 0 016 0z'],
        ],
        'assurance' => [
            'name' => 'IVOSPHERE ASSURANCE', 'short' => 'Assurance', 'color' => '#F43F5E',
            'tagline' => 'Accompagnement en assurance.',
            'intro' => "Un interlocuteur pour comprendre vos besoins de protection, comparer les offres et suivre vos contrats.",
            'services' => ['Analyse de vos besoins de couverture', 'Conseil dans le choix des contrats', 'Suivi de vos polices et de vos démarches'],
            'gallery' => [
                ['title' => $savedPoleImages['assurance'][0]['title'] ?? 'Entreprises & Flottes', 'image' => $savedPoleImages['assurance'][0]['image'] ?? null],
                ['title' => $savedPoleImages['assurance'][1]['title'] ?? 'Santé & Prévoyance', 'image' => $savedPoleImages['assurance'][1]['image'] ?? null],
                ['title' => $savedPoleImages['assurance'][2]['title'] ?? 'Partenariat ATLANTA', 'image' => $savedPoleImages['assurance'][2]['image'] ?? null],
            ],
            'icon' => ['M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ],
    ];

    $reasons = [
        ['Professionnalisme', 'Des interlocuteurs identifiés, des engagements clairs et un travail soigné jusqu\'aux détails.'],
        ['Plusieurs expertises', 'Impression, sport, numérique, évènementiel et assurance : un seul partenaire pour des besoins variés.'],
        ['Accompagnement personnalisé', 'Nous prenons le temps de comprendre votre situation avant de proposer quoi que ce soit.'],
        ['Solutions sur mesure', 'Chaque prestation est adaptée à votre budget, à votre contexte et à vos objectifs.'],
        ['Qualité', 'Des matériaux, des équipements et des méthodes choisis pour durer et bien représenter votre image.'],
        ['Respect des délais', 'Un calendrier annoncé dès le devis et tenu jusqu\'à la livraison.'],
    ];

    $steps = [
        ['Votre besoin', 'Vous nous présentez votre projet par téléphone, message ou formulaire.'],
        ['Étude du projet', 'Nous analysons vos attentes, vos contraintes et votre budget.'],
        ['Proposition et devis', 'Vous recevez une offre détaillée, avec prix et délais.'],
        ['Réalisation', 'Nos équipes produisent, installent ou organisent selon le devis validé.'],
        ['Livraison', 'Votre commande est livrée ou votre prestation est réalisée à la date convenue.'],
        ['Suivi', 'Nous restons disponibles après la livraison pour tout ajustement.'],
    ];

    $navLinks = ['#prestations' => 'Prestations', '#pourquoi' => 'Pourquoi IVOSPHERE', '#processus' => 'Processus', '#realisations' => 'Réalisations', '#devis' => 'Contact'];

    // Réalisations : à fournir par le contrôleur → [['title'=>..., 'pole'=>'print', 'status'=>'done|ongoing', 'image'=>'chemin/storage.jpg'], ...]
    $projects = collect($projects ?? []);

    $field = 'block w-full rounded-xl border border-white/15 bg-white/5 px-3.5 py-3 text-base text-white placeholder:text-white/40 transition focus:border-white/60 focus:outline-none focus:ring-2 focus:ring-white/20 sm:text-sm';
    $quoteRoute = Route::has('quote.store') ? route('quote.store') : null;

    // Délai d'apparition en cascade (plafonné pour ne jamais faire attendre le visiteur)
    $d = fn (int $i, int $step = 80) => 'style="--d:' . (min($i, 5) * $step) . 'ms"';
@endphp
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#FFFFFF">
    <title>IVOSPHERE — Impression, sport, numérique, évènements et assurance</title>
    <meta name="description" content="IVOSPHERE regroupe cinq pôles de services professionnels : impression, matériel sportif, solutions numériques, photo et évènements, assurance. Demandez votre devis.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Les contenus ne sont masqués pour l'animation que si JavaScript fonctionne --}}
    <script>document.documentElement.classList.add('js')</script>
    <style>
        [x-cloak]{display:none !important}
        .font-display{font-family:'Bricolage Grotesque','Inter',system-ui,sans-serif}

        /* ── Animations au défilement ─────────────────────────────── */
        .js [data-reveal]{
            opacity:0;
            transition:
                opacity .8s cubic-bezier(.22,1,.36,1) var(--d,0ms),
                transform .8s cubic-bezier(.22,1,.36,1) var(--d,0ms),
                border-color .2s, background-color .2s, color .2s;
        }
        .js [data-reveal="up"]{transform:translateY(28px)}
        .js [data-reveal="scale"]{transform:scale(.94)}
        .js [data-reveal="bar"]{opacity:1;transform:scaleX(0);transform-origin:left center;transition-duration:.9s}
        .js [data-reveal="bar-y"]{opacity:1;transform:scaleY(0);transform-origin:center top;transition-duration:.9s}
        .js [data-reveal].is-visible{opacity:1;transform:none}

        /* Lien actif dans la navigation (suivi du défilement) */
        [data-nav]{position:relative}
        [data-nav]::after{content:"";position:absolute;left:0;right:0;bottom:-6px;height:2px;border-radius:2px;background:#0066FF;transform:scaleX(0);transform-origin:left;transition:transform .3s cubic-bezier(.22,1,.36,1)}
        [data-nav][aria-current="true"]{color:#0B0F14}
        [data-nav][aria-current="true"]::after{transform:scaleX(1)}

        /* ── Card stack (Détail des prestations) ───────────────────── */
        [data-stack-card]{transform-origin:center top;will-change:transform}

        /* ── Scroll horizontal épinglé (Pourquoi IVOSPHERE) ────────────
           Par défaut (mobile, mouvement réduit, sans JS) : carrousel natif à glisser.
           Avec la classe .hs-active (ajoutée par le JS sur grand écran) :
           la section est épinglée et le défilement vertical fait avancer la piste. */
        .hs-track{position:relative;display:flex;gap:1rem;overflow-x:auto;scroll-snap-type:x mandatory;scrollbar-width:none;padding-inline:max(1rem,calc((100vw - 80rem)/2 + 1rem))}
        .hs-track::-webkit-scrollbar{display:none}
        .hs-card{flex:0 0 min(82vw,22rem);scroll-snap-align:center}
        .hs-meta{display:none}
        .hs-active .hs-pin{position:sticky;top:0;height:100vh;display:flex;flex-direction:column;justify-content:center;overflow:hidden;padding-top:5rem;padding-bottom:3rem}
        .hs-active .hs-track{overflow:visible;scroll-snap-type:none;will-change:transform;gap:1.5rem;padding-inline:max(2rem,calc((100vw - 80rem)/2 + 2rem))}
        .hs-active .hs-card{flex-basis:24rem;scroll-snap-align:none}
        /* ── Bento Grid & Défilement horizontal mobile (Prestations) ── */
        .bento-mobile-track{scrollbar-width:none;-ms-overflow-style:none}
        .bento-mobile-track::-webkit-scrollbar{display:none}
        @media (max-width: 639px){
            .bento-card-mobile{flex:0 0 85vw;max-width:24rem;scroll-snap-align:center}
        }

        @media (prefers-reduced-motion: reduce){
            html{scroll-behavior:auto !important}
            .js [data-reveal]{opacity:1 !important;transform:none !important;transition:none !important}
            [data-nav]::after{transition:none}
            [data-stack-card]{transform:none !important}
            [data-magnetic-arrow]{transform:none !important}
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white font-sans text-[#0B0F14] antialiased" x-data="{ menu: false }" @keydown.escape.window="menu = false">

    <a href="#contenu" class="sr-only rounded-lg bg-[#0066FF] px-4 py-2 text-sm font-semibold text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60]">Aller au contenu</a>

    {{-- ═══ En-tête (fixe : reste visible pendant tout le défilement) ═══ --}}
    <header id="site-header" class="sticky top-0 z-40 border-b border-[#E2E8F0] bg-white/90 backdrop-blur transition-shadow duration-300">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="#accueil" class="shrink-0" aria-label="IVOSPHERE, retour à l'accueil">
                <img src="{{ asset('images/logo.png') }}" alt="IVOSPHERE" class="h-9 w-auto">
            </a>

            <nav class="hidden items-center gap-7 text-sm font-medium text-[#64748B] lg:flex" aria-label="Navigation principale">
                @foreach($navLinks as $href => $label)
                    <a href="{{ $href }}" data-nav class="transition-colors hover:text-[#0B0F14]">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                @if(Route::has('login'))
                    <a href="{{ route('login') }}" class="hidden rounded-xl px-3 py-2 text-sm font-medium text-[#64748B] hover:text-[#0B0F14] sm:block">Espace employé</a>
                @endif
                <a href="#devis" class="hidden rounded-xl bg-[#0066FF] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0052CC] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-2 sm:inline-flex">Demander un devis</a>
                <button type="button" @click="menu = !menu" :aria-expanded="menu" aria-controls="menu-mobile" aria-label="Menu"
                        class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#E2E8F0] text-[#0B0F14] lg:hidden">
                    <svg x-show="!menu" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg x-show="menu" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Progression de la lecture --}}
        <span id="scroll-progress" class="pointer-events-none absolute -bottom-px left-0 h-0.5 w-full origin-left scale-x-0 bg-[#0066FF]" aria-hidden="true"></span>

        <nav id="menu-mobile" x-show="menu" x-cloak x-transition.opacity.duration.150ms @click="menu = false"
             class="border-t border-[#E2E8F0] bg-white px-4 pb-5 pt-3 lg:hidden" aria-label="Navigation mobile">
            <ul class="space-y-1 text-base font-medium">
                @foreach($navLinks as $href => $label)
                    <li><a href="{{ $href }}" class="block rounded-lg px-3 py-3 text-[#0B0F14] active:bg-[#F5F7FA]">{{ $label }}</a></li>
                @endforeach
            </ul>
            <a href="#devis" class="mt-3 flex items-center justify-center rounded-xl bg-[#0066FF] px-4 py-3 text-sm font-semibold text-white">Demander un devis</a>
            @if(Route::has('login'))
                <a href="{{ route('login') }}" class="mt-2 flex items-center justify-center rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm font-semibold text-[#0B0F14]">Espace employé</a>
            @endif
        </nav>
    </header>

    <main id="contenu">

        {{-- ═══ 1. Hero : fade / slide + parallax léger ═══ --}}
        <section id="accueil" x-data="heroSlider(2)" @visibilitychange.window="document.hidden ? stop() : start()"
                 class="relative isolate scroll-mt-16 overflow-hidden border-b border-[#E2E8F0] bg-white">

            {{-- Photos de l'équipe en fond, fondu enchaîné et léger décalage au défilement --}}
            <div data-parallax class="pointer-events-none absolute inset-x-0 -bottom-10 -top-10 -z-10 select-none will-change-transform" aria-hidden="true">
                <img src="{{ asset('images/team-ivosphere-1.jpg') }}" alt=""
                     class="absolute inset-0 h-full w-full object-cover object-[center_28%] transition-all duration-1000 ease-out"
                     :class="active === 0 ? 'opacity-50 lg:opacity-65 scale-100' : 'opacity-0 scale-105'" loading="eager">
                <img src="{{ asset('images/team-ivosphere-2.jpg') }}" alt=""
                     class="absolute inset-0 h-full w-full object-cover object-[center_22%] transition-all duration-1000 ease-out"
                     :class="active === 1 ? 'opacity-50 lg:opacity-65 scale-100' : 'opacity-0 scale-105'" loading="lazy">

                {{-- Voiles garantissant la lisibilité du texte --}}
                <div class="absolute inset-0 bg-gradient-to-r from-white via-white/90 to-white/70 lg:via-white/80 lg:to-white/35"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-white/70 via-transparent to-white/95"></div>
            </div>

            <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-12 lg:gap-12 lg:px-8 lg:py-28">
                <div class="lg:col-span-7">
                    <h1 data-reveal="up" class="font-display text-4xl font-bold leading-[1.08] tracking-tight text-[#0B0F14] sm:text-5xl lg:text-6xl">
                        Des solutions professionnelles pour donner vie à vos projets
                    </h1>
                    <p data-reveal="up" style="--d:120ms" class="mt-6 max-w-xl text-base leading-relaxed text-[#475569] sm:text-lg">
                        IVOSPHERE réunit cinq pôles de services : impression, matériel sportif, solutions numériques, photo et évènements, assurance. Un seul partenaire, des prestations adaptées à votre besoin.
                    </p>
                    <div data-reveal="up" style="--d:240ms" class="mt-8 grid gap-3 sm:flex sm:flex-wrap">
                        <a href="#prestations" class="inline-flex items-center justify-center rounded-xl bg-[#0B0F14] px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-[#0066FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-2">Découvrir nos services</a>
                        <a href="#devis" class="inline-flex items-center justify-center rounded-xl border border-[#CBD5E1] bg-white/90 px-6 py-3.5 text-sm font-semibold text-[#0B0F14] shadow-sm backdrop-blur transition hover:border-[#0B0F14] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-2">Demander un devis</a>
                    </div>

                    {{-- Choix de la photo d'équipe --}}
                    <div data-reveal="up" style="--d:340ms" class="mt-8 flex items-center gap-3" role="group" aria-label="Photos de l'équipe IVOSPHERE">
                        @foreach([1, 2] as $n)
                            <button type="button" @click="go({{ $n - 1 }})" :aria-pressed="active === {{ $n - 1 }}" aria-label="Afficher la photo d'équipe {{ $n }}"
                                    class="flex h-8 items-center focus-visible:outline-none">
                                <span class="block h-1.5 rounded-full transition-all duration-300"
                                      :class="active === {{ $n - 1 }} ? 'w-8 bg-[#0066FF]' : 'w-4 bg-[#94A3B8]/60 hover:bg-[#94A3B8]'"></span>
                            </button>
                        @endforeach
                        <span class="text-xs text-[#64748B]">L'équipe IVOSPHERE</span>
                    </div>
                </div>
                
            </div>
        </section>

        {{-- ═══ 2. Nos prestations : Bento Grid + Scroll Reveal + Hover Lift + Icon Animation + Magnetic Arrow + Horizontal Scroll mobile ═══ --}}
        <section id="prestations" class="scroll-mt-16 bg-[#F5F7FA] py-16 sm:py-24" aria-labelledby="prestations-titre"
                 x-data="bentoSection()">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div data-reveal="up" class="max-w-2xl">
                        <h2 id="prestations-titre" class="mt-3 font-display text-3xl font-bold tracking-tight text-[#0B0F14] sm:text-4xl">Nos prestations</h2>
                        <p class="mt-3 text-base text-[#64748B] sm:text-lg">Cinq domaines d'expertise pour répondre à vos besoins professionnels.</p>
                    </div>
                </div>

                {{-- Track Bento : défilement horizontal fluide sur mobile (snap-x), Bento Grid 12 colonnes dès sm --}}
                <div @scroll.passive="handleScroll($event)"
                     class="bento-mobile-track -mx-4 mt-8 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 pt-2 sm:mx-0 sm:mt-10 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-12 lg:gap-5">
                    @foreach($poles as $key => $p)
                        @php
                            // Répartition Bento 12 colonnes sur grand écran (7 + 5 = 12, 4 + 4 + 4 = 12)
                            $bentoSpan = match($loop->index) {
                                0 => 'lg:col-span-7 sm:col-span-2',
                                1 => 'lg:col-span-5 sm:col-span-1',
                                2 => 'lg:col-span-4 sm:col-span-1',
                                3 => 'lg:col-span-4 sm:col-span-1',
                                4 => 'lg:col-span-4 sm:col-span-2',
                                default => 'lg:col-span-4 sm:col-span-1',
                            };
                        @endphp
                        <a href="#pole-{{ $key }}"
                           data-reveal="up" {!! $d($loop->index, 90) !!}
                           x-data="magneticCard()"
                           @mousemove="move($event)"
                           @mouseleave="leave()"
                           class="bento-card-mobile group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-[#E2E8F0] bg-white p-6 transition-all duration-300 ease-out hover:-translate-y-2 hover:border-slate-400 hover:shadow-2xl hover:shadow-slate-300/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] sm:p-7 {{ $bentoSpan }}">

                            {{-- Halo lumineux d'accentuation coloré au survol --}}
                            <div class="pointer-events-none absolute -right-12 -top-12 h-44 w-44 rounded-full opacity-0 blur-3xl transition-opacity duration-500 group-hover:opacity-20"
                                 style="background: {{ $p['color'] }}" aria-hidden="true"></div>

                            {{-- Filigrane SVG oversize en arrière-plan --}}
                            <div class="pointer-events-none absolute -bottom-6 -right-6 h-36 w-36 opacity-[0.03] transition-all duration-500 group-hover:scale-110 group-hover:opacity-[0.08]" aria-hidden="true">
                                <svg class="h-full w-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                                    @foreach($p['icon'] as $path)<path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>@endforeach
                                </svg>
                            </div>

                            {{-- En-tête de carte : Icône avec animation au survol + Badge de pôle --}}
                            <div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="relative flex h-12 w-12 items-center justify-center rounded-2xl transition-all duration-300 ease-out group-hover:scale-110 group-hover:rotate-6 group-hover:shadow-md"
                                          style="background: {{ $p['color'] }}1A; color: {{ $p['color'] }}">
                                        <svg class="h-6 w-6 transition-transform duration-300 ease-out group-hover:scale-105" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            @foreach($p['icon'] as $path)<path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>@endforeach
                                        </svg>
                                    </span>

                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[#E2E8F0] bg-[#F5F7FA] px-3 py-1 text-xs font-semibold text-[#64748B] transition-colors group-hover:border-slate-300 group-hover:text-[#0B0F14]">
                                        <span class="h-1.5 w-1.5 rounded-full" style="background: {{ $p['color'] }}"></span>
                                        <span>Pôle 0{{ $loop->iteration }}</span>
                                    </span>
                                </div>

                                {{-- Titre & Accroche --}}
                                <h3 class="mt-5 font-display text-xl font-bold tracking-tight text-[#0B0F14] transition-colors group-hover:text-[#0B0F14] sm:text-2xl">
                                    {{ $p['name'] }}
                                </h3>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-[#64748B]">{{ $p['tagline'] }}</p>
                                <p class="mt-3 text-sm leading-relaxed text-[#64748B]">{{ $p['intro'] }}</p>

                                {{-- Badges services inclus (enrichissement Bento) --}}
                                <div class="mt-4 flex flex-wrap gap-1.5">
                                    @foreach(array_slice($p['services'], 0, $loop->index === 0 ? 3 : 2) as $s)
                                        <span class="inline-flex items-center gap-1 rounded-lg border border-[#E2E8F0]/80 bg-[#F5F7FA] px-2.5 py-1 text-xs font-medium text-[#475569] transition-colors group-hover:border-slate-300 group-hover:bg-white">
                                            <svg class="h-3 w-3 shrink-0" style="color: {{ $p['color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            <span>{{ $s }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Pied de carte avec Magnetic Arrow --}}
                            <div class="mt-6 flex items-center justify-between border-t border-[#E2E8F0]/80 pt-4">
                                <span class="text-sm font-semibold text-[#0B0F14] transition-colors group-hover:text-[#0066FF]">
                                    Voir le détail
                                </span>

                                <span data-magnetic-arrow
                                      class="relative flex h-10 w-10 items-center justify-center rounded-full border border-[#E2E8F0] bg-[#F5F7FA] text-[#0B0F14] transition-all duration-200 ease-out group-hover:border-[#0066FF] group-hover:bg-[#0066FF] group-hover:text-white group-hover:shadow-md"
                                      :style="{ transform: `translate3d(${mx}px, ${my}px, 0)` }">
                                    <svg class="h-4 w-4 transition-transform duration-200 ease-out group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Pagination bullets sur mobile --}}
                <div class="mt-4 flex items-center justify-center gap-1.5 sm:hidden" aria-hidden="true">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="h-1.5 rounded-full transition-all duration-300"
                              :class="currentSlide === {{ $i }} ? 'w-6 bg-[#0066FF]' : 'w-1.5 bg-[#CBD5E1]'"></span>
                    @endfor
                </div>
            </div>
        </section>

        {{-- ═══ 3. Détail des prestations : CARD STACK ═══
             Chaque pôle est une carte « collante » : en défilant, la carte suivante vient
             recouvrir la précédente, qui se réduit légèrement et s'assombrit.
             Empilement actif dès md ; sur mobile, les cartes restent simplement l'une sous l'autre. --}}
        <section class="bg-[#F5F7FA] pb-16 sm:pb-24" aria-labelledby="detail-titre">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 id="detail-titre" data-reveal="up" class="font-display text-3xl font-bold tracking-tight sm:text-4xl">Détail des prestations</h2>

                <div class="mt-10 pb-4">
                    @foreach($poles as $key => $p)
                        <article id="pole-{{ $key }}" data-stack-card
                                 style="--stack-top: calc(5rem + {{ $loop->index * 0.75 }}rem)"
                                 class="relative mb-6 grid scroll-mt-20 gap-6 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-[0_-10px_30px_-14px_rgba(11,15,20,.18)] sm:p-10 md:sticky md:top-[var(--stack-top)] lg:grid-cols-12 lg:gap-12">

                            <div class="lg:col-span-5">
                                <span data-reveal="bar" class="mb-4 block h-1 w-12 rounded-full" style="background: {{ $p['color'] }}"></span>
                                <div data-reveal="up" style="--d:100ms">
                                    <h3 class="font-display text-2xl font-bold tracking-tight">{{ $p['name'] }}</h3>
                                    <p class="mt-3 max-w-md text-base leading-relaxed text-[#64748B]">{{ $p['intro'] }}</p>
                                </div>
                            </div>

                            <div class="lg:col-span-7 flex flex-col justify-between">
                                {{-- Galerie de 3 visuels par pôle (administrable via Paramètres) --}}
                                <div>
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="rounded-md bg-[#F5F7FA] px-2 py-0.5 text-[11px] font-medium text-[#64748B]">
                                            {{ $p['short'] }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-3.5">
                                        @foreach($p['gallery'] as $img)
                                            <div data-reveal="up" {!! $d($loop->index + 1, 90) !!}
                                                 class="group/img relative flex aspect-[4/3] flex-col justify-end overflow-hidden rounded-2xl border border-[#E2E8F0] bg-[#F5F7FA] transition-all duration-300 hover:-translate-y-1 hover:border-slate-400 hover:shadow-lg">
                                                @if(!empty($img['image']))
                                                    <img src="{{ Str::startsWith($img['image'], ['http://', 'https://', '/']) ? $img['image'] : asset($img['image']) }}"
                                                         alt="{{ $img['title'] ?? $p['name'] }}"
                                                         class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 ease-out group-hover/img:scale-105" loading="lazy">
                                                    <div class="absolute inset-0 bg-gradient-to-t from-[#0B0F14]/80 via-[#0B0F14]/20 to-transparent"></div>
                                                @else
                                                    {{-- Placeholder élégant en attente des photos --}}
                                                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-[#F8FAFC] to-[#EDF2F7] p-4 text-center text-slate-400 transition-colors group-hover/img:text-[#0066FF]">
                                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                            </svg>
                                                        </span>
                                                        <span class="mt-2 text-xs font-semibold text-[#0B0F14]">{{ $img['title'] ?? 'Visuel 0' . $loop->iteration }}</span>
                                                        <span class="mt-0.5 text-[10px] text-[#94A3B8]">Configurable dans Paramètres</span>
                                                    </div>
                                                @endif

                                                @if(!empty($img['title']) && !empty($img['image']))
                                                    <div class="relative z-10 p-3">
                                                        <span class="inline-block rounded-md bg-black/60 px-2 py-0.5 text-xs font-medium text-white backdrop-blur">
                                                            {{ $img['title'] }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-[#E2E8F0]/80 pt-4">
                                    <a href="#devis" @click="$dispatch('choose-pole', '{{ $key }}')" data-reveal="up" style="--d:400ms"
                                       class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0066FF] hover:text-[#0052CC] hover:underline">
                                        <span>Demander un devis {{ $p['short'] }}</span>
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </a>

                                    @auth
                                        @if(auth()->user()->role === 'administrateur')
                                            <a href="{{ route('admin.settings.index') }}#pole-showcase-images" class="text-xs text-[#64748B] hover:text-[#0B0F14] hover:underline">
                                                Gérer ces visuels dans les paramètres
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            </div>

                            {{-- Voile qui s'assombrit quand la carte suivante la recouvre --}}
                            <span data-stack-shade class="pointer-events-none absolute inset-0 rounded-3xl bg-[#0B0F14] opacity-0" aria-hidden="true"></span>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ═══ 4. Pourquoi choisir IVOSPHERE : SCROLL HORIZONTAL ÉPINGLÉ ═══
             Sur grand écran, la section reste fixée et le défilement vertical fait glisser les
             cartes vers la gauche. Sur mobile / mouvement réduit : carrousel à glisser au doigt. --}}
        <section id="pourquoi" data-hscroll class="relative scroll-mt-16 bg-[#0B0F14] text-white" aria-labelledby="pourquoi-titre">
            <div class="hs-pin py-16 sm:py-24">
                <div class="mx-auto flex w-full max-w-7xl items-end justify-between gap-6 px-4 sm:px-6 lg:px-8">
                    <h2 id="pourquoi-titre" data-reveal="up" class="max-w-2xl font-display text-3xl font-bold tracking-tight sm:text-4xl">Pourquoi choisir IVOSPHERE ?</h2>
                    <p class="hs-meta shrink-0 font-display text-sm text-white/50" aria-hidden="true"><span data-hs-count class="text-white">01</span> / {{ str_pad(count($reasons), 2, '0', STR_PAD_LEFT) }}</p>
                </div>

                <ul data-hs-track class="hs-track mt-10 lg:mt-14">
                    @foreach($reasons as [$title, $text])
                        <li class="hs-card flex min-h-[15rem] flex-col rounded-2xl border border-white/15 bg-white/5 p-6 sm:p-7">
                            <span class="font-display text-sm font-semibold text-[#6AA5FF]">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="mt-auto pt-10 font-display text-xl font-semibold">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-white/65">{{ $text }}</p>
                        </li>
                    @endforeach
                </ul>

                <div class="hs-meta mx-auto mt-10 w-full max-w-7xl px-4 sm:px-6 lg:px-8" aria-hidden="true">
                    <div class="h-px w-full bg-white/15"><span data-hs-bar class="block h-px origin-left scale-x-0 bg-white"></span></div>
                </div>
            </div>
        </section>

        {{-- ═══ 5. Notre processus : REVEAL ═══ --}}
        <section id="processus" class="scroll-mt-16 bg-white py-16 sm:py-24" aria-labelledby="processus-titre">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div data-reveal="up" class="max-w-2xl">
                    <h2 id="processus-titre" class="font-display text-3xl font-bold tracking-tight sm:text-4xl">Notre processus</h2>
                    <p class="mt-3 text-base text-[#64748B] sm:text-lg">Six étapes, de votre première demande au suivi après livraison.</p>
                </div>

                <ol class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-6 lg:gap-5">
                    @foreach($steps as $i => [$title, $text])
                        <li class="relative">
                            @if(! $loop->last)
                                {{-- Le trait se dessine d'étape en étape --}}
                                <span data-reveal="bar-y" {!! $d($i, 140) !!} class="absolute left-5 top-12 h-[calc(100%-1rem)] w-px bg-[#CBD5E1] sm:hidden" aria-hidden="true"></span>
                                <span data-reveal="bar" {!! $d($i, 140) !!} class="absolute left-12 right-[-0.75rem] top-5 hidden h-px bg-[#CBD5E1] lg:block" aria-hidden="true"></span>
                            @endif
                            <span data-reveal="scale" {!! $d($i, 140) !!} class="relative flex h-10 w-10 items-center justify-center rounded-full border border-[#0B0F14] bg-white text-sm font-bold">{{ $i + 1 }}</span>
                            <div data-reveal="up" {!! $d($i, 140) !!}>
                                <h3 class="mt-4 font-display text-base font-semibold">{{ $title }}</h3>
                                <p class="mt-1.5 text-sm leading-relaxed text-[#64748B]">{{ $text }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- ═══ 6. Réalisations ═══ --}}
        <section id="realisations" class="scroll-mt-16 bg-[#F5F7FA] py-16 sm:py-24" aria-labelledby="realisations-titre"
                 x-data="{ filter: 'all' }">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div data-reveal="up" class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <h2 id="realisations-titre" class="font-display text-3xl font-bold tracking-tight sm:text-4xl">Réalisations et projets</h2>
                        <p class="mt-3 text-base text-[#64748B] sm:text-lg">Un aperçu de nos travaux dans chacun de nos pôles.</p>
                    </div>
                    @if($projects->isNotEmpty())
                        <div class="-mx-1 flex gap-1.5 overflow-x-auto px-1 [scrollbar-width:none]" role="group" aria-label="Filtrer les projets">
                            @foreach(['all' => 'Galerie', 'done' => 'Projets réalisés', 'ongoing' => 'Prestations en cours'] as $value => $label)
                                <button type="button" @click="filter = '{{ $value }}'" :aria-pressed="filter === '{{ $value }}'"
                                        :class="filter === '{{ $value }}' ? 'bg-[#0B0F14] text-white' : 'bg-white text-[#64748B] hover:text-[#0B0F14]'"
                                        class="shrink-0 rounded-lg border border-[#E2E8F0] px-4 py-2 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]">{{ $label }}</button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($projects->isNotEmpty())
                    <ul class="mt-10 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                        @foreach($projects as $project)
                            @php $pole = $poles[$project['pole'] ?? ''] ?? null; @endphp
                            <li x-show="filter === 'all' || filter === '{{ $project['status'] ?? 'done' }}'" data-reveal="scale" {!! $d($loop->index % 3, 90) !!}
                                class="group overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white">
                                <div class="aspect-[4/3] overflow-hidden bg-[#E2E8F0]">
                                    @if(!empty($project['image']))
                                        <img src="{{ asset('storage/' . $project['image']) }}" alt="{{ $project['title'] }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                    @endif
                                </div>
                                <div class="p-3.5 sm:p-4">
                                    <p class="text-sm font-semibold text-[#0B0F14]">{{ $project['title'] }}</p>
                                    <p class="mt-1 flex items-center gap-2 text-xs text-[#64748B]">
                                        @if($pole)<span class="h-2 w-2 rounded-full" style="background: {{ $pole['color'] }}"></span>{{ $pole['short'] }}@endif
                                        @if(($project['status'] ?? 'done') === 'ongoing')<span class="rounded bg-amber-50 px-1.5 py-0.5 font-semibold text-amber-700">En cours</span>@endif
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div data-reveal="up" class="mt-10 rounded-2xl border border-dashed border-[#CBD5E1] bg-white px-6 py-14 text-center">
                        <p class="font-display text-lg font-semibold">Nos réalisations arrivent bientôt</p>
                        <p class="mx-auto mt-2 max-w-md text-sm text-[#64748B]">Vous souhaitez voir des exemples de travaux dans un pôle précis ? Demandez-nous des références, nous vous les envoyons.</p>
                        <a href="#devis" class="mt-5 inline-flex rounded-xl bg-[#0B0F14] px-5 py-3 text-sm font-semibold text-white hover:bg-[#0066FF]">Demander des références</a>
                    </div>
                @endif
            </div>
        </section>

        {{-- ═══ 7. Appel à l'action ═══ --}}
        <section id="devis" class="scroll-mt-16 bg-[#0066FF] py-16 text-white sm:py-24" aria-labelledby="devis-titre"
                 x-data="{ pole: '' }" @choose-pole.window="pole = $event.detail">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-8">

                <div class="lg:col-span-5" data-reveal="up">
                    <h2 id="devis-titre" class="font-display text-3xl font-bold tracking-tight sm:text-4xl">Vous avez un projet ? Parlons-en.</h2>
                    <p class="mt-4 max-w-md text-base leading-relaxed text-white/80">Décrivez votre besoin en quelques lignes. Nous vous répondons avec une proposition claire et un devis détaillé.</p>

                    <ul class="mt-8 space-y-4 text-sm">
                        @if($phone)
                            <li><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="flex items-center gap-3 font-semibold hover:underline"><span class="w-24 shrink-0 font-normal text-white/70">Téléphone</span>{{ $phone }}</a></li>
                        @endif
                        @if($whatsapp)
                            <li><a href="https://wa.me/{{ preg_replace('/\D+/', '', $whatsapp) }}" target="_blank" rel="noopener" class="flex items-center gap-3 font-semibold hover:underline"><span class="w-24 shrink-0 font-normal text-white/70">WhatsApp</span>{{ $whatsapp }}</a></li>
                        @endif
                        @if($email)
                            <li><a href="mailto:{{ $email }}" class="flex items-center gap-3 font-semibold hover:underline"><span class="w-24 shrink-0 font-normal text-white/70">Email</span><span class="break-all">{{ $email }}</span></a></li>
                        @endif
                        @if($address)
                            <li class="flex items-start gap-3"><span class="w-24 shrink-0 text-white/70">Adresse</span><span class="font-semibold">{{ $address }}</span></li>
                        @endif
                    </ul>
                </div>

                <div class="lg:col-span-7" data-reveal="up" style="--d:120ms">
                    @if(session('quote_sent'))
                        <div role="status" class="rounded-2xl bg-white p-6 text-[#0B0F14] sm:p-8">
                            <p class="font-display text-xl font-semibold">Demande envoyée</p>
                            <p class="mt-2 text-sm text-[#64748B]">Merci. Nous revenons vers vous très vite avec une réponse à votre demande.</p>
                        </div>
                    @else
                        <form method="POST" action="{{ $quoteRoute ?? '#' }}" class="rounded-2xl bg-[#0B0F14] p-5 sm:p-8" novalidate>
                            @csrf
                            <div class="hidden" aria-hidden="true"><label>Ne pas remplir<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                            @if($errors->any())
                                <div role="alert" class="mb-5 rounded-xl border border-rose-300/40 bg-rose-500/10 p-3.5 text-sm text-rose-100">
                                    <ul class="space-y-0.5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                                </div>
                            @endif

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="q-name" class="mb-1.5 block text-sm font-semibold">Nom et prénom</label>
                                    <input id="q-name" name="name" type="text" required autocomplete="name" value="{{ old('name') }}" class="{{ $field }}">
                                </div>
                                <div>
                                    <label for="q-phone" class="mb-1.5 block text-sm font-semibold">Téléphone</label>
                                    <input id="q-phone" name="phone" type="tel" required autocomplete="tel" inputmode="tel" value="{{ old('phone') }}" class="{{ $field }}">
                                </div>
                                <div>
                                    <label for="q-email" class="mb-1.5 block text-sm font-semibold">Email <span class="font-normal text-white/50">(facultatif)</span></label>
                                    <input id="q-email" name="email" type="email" autocomplete="email" inputmode="email" value="{{ old('email') }}" class="{{ $field }}">
                                </div>
                                <div>
                                    <label for="q-pole" class="mb-1.5 block text-sm font-semibold">Pôle concerné</label>
                                    <select id="q-pole" name="pole" x-model="pole" class="{{ $field }}">
                                        <option value="" class="text-[#0B0F14]">Je ne sais pas encore</option>
                                        @foreach($poles as $key => $p)
                                            <option value="{{ $key }}" class="text-[#0B0F14]">{{ $p['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="q-message" class="mb-1.5 block text-sm font-semibold">Votre projet</label>
                                    <textarea id="q-message" name="message" rows="4" required placeholder="Quantités, dates, budget, contraintes…" class="{{ $field }}">{{ old('message') }}</textarea>
                                </div>
                            </div>

                            <button type="submit" class="mt-6 flex w-full items-center justify-center rounded-xl bg-white px-6 py-3.5 text-sm font-semibold text-[#0B0F14] transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#0B0F14] sm:w-auto">Envoyer ma demande de devis</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>
    </main>

    {{-- ═══ 8. Pied de page ═══ --}}
    <footer class="bg-[#0B0F14] text-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 sm:py-16 md:grid-cols-12 lg:px-8">
            <div class="md:col-span-5" data-reveal="up">
                <img src="{{ asset('images/logo.png') }}" alt="IVOSPHERE" class="h-9 w-auto brightness-0 invert">
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/60">Impression, matériel sportif, solutions numériques, photo et évènements, assurance : cinq pôles au service de vos projets.</p>
            </div>

            <nav class="md:col-span-4" aria-label="Pôles" data-reveal="up" style="--d:100ms">
                <p class="text-sm font-semibold">Nos pôles</p>
                <ul class="mt-4 space-y-2.5 text-sm text-white/60">
                    @foreach($poles as $key => $p)
                        <li><a href="#pole-{{ $key }}" class="hover:text-white">{{ $p['name'] }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <div class="md:col-span-3" data-reveal="up" style="--d:200ms">
                <p class="text-sm font-semibold">Contact</p>
                <ul class="mt-4 space-y-2.5 text-sm text-white/60">
                    @if($phone)<li><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="hover:text-white">{{ $phone }}</a></li>@endif
                    @if($email)<li><a href="mailto:{{ $email }}" class="break-all hover:text-white">{{ $email }}</a></li>@endif
                    @if($address)<li>{{ $address }}</li>@endif
                    <li><a href="#devis" class="font-semibold text-white hover:underline">Demander un devis</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-white/50 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>© {{ now()->year }} IVOSPHERE. Tous droits réservés.</p>
                @if(Route::has('login'))
                    <a href="{{ route('login') }}" class="hover:text-white">Accès équipe</a>
                @endif
            </div>
        </div>
    </footer>

    <script>
        /* Diaporama du hero : se met en pause onglet masqué, et ne démarre pas si l'utilisateur limite les animations. */
        function heroSlider(total) {
            const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            return {
                active: 0,
                timer: null,
                init() { this.start(); },
                start() {
                    if (reduced || this.timer) return;
                    this.timer = setInterval(() => { this.active = (this.active + 1) % total; }, 7000);
                },
                stop() { clearInterval(this.timer); this.timer = null; },
                go(i) { this.active = i; this.stop(); this.start(); },
            };
        }

        /* Suivi du défilement horizontal sur mobile (section Prestations) */
        function bentoSection() {
            return {
                currentSlide: 1,
                handleScroll(e) {
                    const el = e.target;
                    const card = el.firstElementChild;
                    const step = card ? (card.offsetWidth + 16) : 300;
                    const index = Math.round(el.scrollLeft / step);
                    this.currentSlide = Math.min(5, Math.max(1, index + 1));
                }
            };
        }

        /* Effet d'attraction magnétique sur la flèche de chaque carte Bento */
        function magneticCard() {
            const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            return {
                mx: 0,
                my: 0,
                move(e) {
                    if (reduced) return;
                    const arrow = e.currentTarget.querySelector('[data-magnetic-arrow]');
                    if (!arrow) return;
                    const rect = arrow.getBoundingClientRect();
                    const cx = rect.left + rect.width / 2;
                    const cy = rect.top + rect.height / 2;
                    const dx = e.clientX - cx;
                    const dy = e.clientY - cy;
                    const dist = Math.hypot(dx, dy);
                    // Attraction magnétique dans un rayon de 160px autour du bouton flèche
                    if (dist < 160) {
                        this.mx = Math.max(-8, Math.min(8, dx * 0.22));
                        this.my = Math.max(-8, Math.min(8, dy * 0.22));
                    } else {
                        this.mx = 0;
                        this.my = 0;
                    }
                },
                leave() {
                    this.mx = 0;
                    this.my = 0;
                }
            };
        }

        (function () {
            const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const items = document.querySelectorAll('[data-reveal]');
            const clamp = (v, a, b) => Math.min(b, Math.max(a, v));

            /* 1. Apparition au défilement (une seule fois par élément) */
            if (!('IntersectionObserver' in window) || reduced) {
                items.forEach(el => el.classList.add('is-visible'));
            } else {
                const reveal = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        const el = entry.target;
                        el.classList.add('is-visible');
                        reveal.unobserve(el);
                        // Une fois affiché, on retire le délai pour que les survols restent instantanés.
                        setTimeout(() => el.style.setProperty('--d', '0ms'), 1800);
                    });
                }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
                items.forEach(el => reveal.observe(el));
            }

            /* 2. Lien de navigation actif selon la section visible */
            const links = [...document.querySelectorAll('[data-nav]')];
            const sections = links.map(l => document.querySelector(l.getAttribute('href'))).filter(Boolean);
            if ('IntersectionObserver' in window && sections.length) {
                const spy = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        links.forEach(l => l.setAttribute('aria-current', String(l.getAttribute('href') === '#' + entry.target.id)));
                    });
                }, { rootMargin: '-45% 0px -50% 0px' });
                sections.forEach(s => spy.observe(s));
            }

            /* 3. En-tête, progression, parallax, card stack et scroll horizontal */
            const header = document.getElementById('site-header');
            const bar = document.getElementById('scroll-progress');
            const parallax = document.querySelector('[data-parallax]');
            const desktop = window.matchMedia('(min-width: 1024px)');
            const medium = window.matchMedia('(min-width: 768px)');
            let ticking = false;

            /* 3a. Card stack : la carte recouverte se réduit et s'assombrit */
            const stackCards = [...document.querySelectorAll('[data-stack-card]')];

            function updateStack() {
                const on = !reduced && medium.matches;
                stackCards.forEach((card, i) => {
                    const next = stackCards[i + 1];
                    const shade = card.querySelector('[data-stack-shade]');
                    let p = 0;
                    if (on && next) {
                        const gap = next.getBoundingClientRect().top - card.getBoundingClientRect().top;
                        p = clamp(1 - gap / card.offsetHeight, 0, 1);
                    }
                    card.style.transform = p > 0 ? 'scale(' + (1 - p * 0.05).toFixed(4) + ')' : '';
                    if (shade) shade.style.opacity = (p * 0.08).toFixed(3);
                });
            }

            /* 3b. Scroll horizontal épinglé (grand écran uniquement) */
            const hs = document.querySelector('[data-hscroll]');
            const hsTrack = hs && hs.querySelector('[data-hs-track]');
            const hsBar = hs && hs.querySelector('[data-hs-bar]');
            const hsCount = hs && hs.querySelector('[data-hs-count]');
            let hsDist = 0;

            function measureHs() {
                if (!hs || !hsTrack) return;
                const on = !reduced && desktop.matches;
                hs.classList.toggle('hs-active', on);
                if (!on) {
                    hs.style.height = '';
                    hsTrack.style.transform = '';
                    return;
                }
                hsTrack.style.transform = 'none';
                const last = hsTrack.lastElementChild;
                const padL = parseFloat(getComputedStyle(hsTrack).paddingLeft) || 0;
                hsDist = Math.max(0, last.offsetLeft + last.offsetWidth + padL - window.innerWidth);
                hs.style.height = (window.innerHeight + hsDist) + 'px';
            }

            function updateHs() {
                if (!hs || !hs.classList.contains('hs-active')) return;
                const span = hs.offsetHeight - window.innerHeight;
                const p = span > 0 ? clamp(-hs.getBoundingClientRect().top / span, 0, 1) : 0;
                hsTrack.style.transform = 'translate3d(' + (-p * hsDist).toFixed(1) + 'px,0,0)';
                if (hsBar) hsBar.style.transform = 'scaleX(' + p.toFixed(4) + ')';
                if (hsCount) {
                    const n = hsTrack.children.length;
                    hsCount.textContent = String(Math.min(n, Math.floor(p * n) + 1)).padStart(2, '0');
                }
            }

            function update() {
                const y = window.scrollY;
                const max = document.documentElement.scrollHeight - window.innerHeight;
                header.classList.toggle('shadow-sm', y > 8);
                if (!reduced && bar) bar.style.transform = 'scaleX(' + (max > 0 ? Math.min(1, y / max) : 0) + ')';
                if (!reduced && parallax && desktop.matches) {
                    parallax.style.transform = 'translate3d(0,' + Math.min(40, y * 0.12) + 'px,0)';
                }
                updateStack();
                updateHs();
                ticking = false;
            }

            function relayout() { measureHs(); update(); }

            window.addEventListener('scroll', () => {
                if (!ticking) { requestAnimationFrame(update); ticking = true; }
            }, { passive: true });
            window.addEventListener('resize', relayout);
            window.addEventListener('load', relayout);
            if (document.fonts && document.fonts.ready) document.fonts.ready.then(relayout);
            relayout();
        })();
    </script>
</body>
</html>