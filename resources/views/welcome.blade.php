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

    $field = 'block w-full rounded-2xl border border-black/10 bg-white/80 px-4 py-3 text-base text-[#0B0F14] placeholder:text-black/30 transition focus:border-black/40 focus:outline-none focus:ring-4 focus:ring-black/5 sm:text-sm night:border-white/10 night:bg-white/[0.04] night:text-white night:placeholder:text-white/25 night:focus:border-white/40 night:focus:ring-white/10';
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
    <x-pwa-meta theme-color="#000000" />
    <title>IVOSPHERE — Impression, sport, numérique, évènements et assurance</title>
    <meta name="description" content="IVOSPHERE regroupe cinq pôles de services professionnels : impression, matériel sportif, solutions numériques, photo et évènements, assurance. Demandez votre devis.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Les contenus ne sont masqués pour l'animation que si JavaScript fonctionne ; thème (nuit par défaut) appliqué avant l'affichage --}}
    <script>
        document.documentElement.classList.add('js');
        (function () {
            var theme = 'night';
            try { theme = localStorage.getItem('ivo-offer-theme') || 'night'; } catch (e) {}
            document.documentElement.setAttribute('data-offer-theme', theme);
        })();
    </script>
    <style>
        [x-cloak]{display:none !important}
        .font-display{font-family:'Inter',system-ui,sans-serif;letter-spacing:-0.035em}

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
        [data-nav][aria-current="true"]{color:#0B0F14;background:rgba(0,0,0,.06)}
        [data-offer-theme=night] [data-nav][aria-current="true"]{color:#fff;background:rgba(255,255,255,.1)}

        /* ── Card stack (Détail des prestations) ───────────────────── */
        [data-stack-card]{transform-origin:center top;will-change:transform}

        /* ── Scroll horizontal épinglé (Pourquoi IVOSPHERE) ──────────── */
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
            [data-stack-card]{transform:none !important}
            [data-magnetic-arrow]{transform:none !important}
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-[#F5F7FA] font-sans text-[#0B0F14] antialiased transition-colors night:bg-black night:text-white"
      x-data="{
          menu: false,
          night: document.documentElement.getAttribute('data-offer-theme') === 'night',
          toggleTheme() {
              this.night = ! this.night;
              const theme = this.night ? 'night' : 'day';
              document.documentElement.setAttribute('data-offer-theme', theme);
              try { localStorage.setItem('ivo-offer-theme', theme); } catch (e) {}
          }
      }"
      @keydown.escape.window="menu = false">

    <a href="#contenu" class="sr-only rounded-full bg-[#0B0F14] px-4 py-2 text-sm font-semibold text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60]">Aller au contenu</a>

    {{-- ═══ Navigation en pilule flottante ═══ --}}
    <header id="site-header" class="fixed inset-x-0 top-4 z-40 flex flex-col items-center px-4">
        <nav class="flex max-w-full items-center gap-1 rounded-full border border-black/10 bg-white/70 p-1.5 pl-2 shadow-xl shadow-black/5 backdrop-blur-xl night:border-white/15 night:bg-black/60 night:shadow-black/50" aria-label="Navigation principale">
            <a href="#accueil" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white ring-1 ring-black/5" aria-label="IVOSPHERE, retour à l'accueil">
                <img src="{{ asset('images/logo-emblem.png') }}" alt="" class="h-5 w-auto">
            </a>
            <div class="hidden items-center lg:flex">
                @foreach($navLinks as $href => $label)
                    <a href="{{ $href }}" data-nav class="rounded-full px-3 py-1.5 text-xs text-black/55 transition hover:text-black night:text-white/55 night:hover:text-white">{{ $label }}</a>
                @endforeach
                <a href="{{ route('offres.index') }}" class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold text-[#0B0F14] night:text-white">
                    Abonnements
                    <span class="rounded-full bg-[#0066FF] px-1.5 py-px text-[9px] font-bold uppercase text-white">Nouveau</span>
                </a>
            </div>
            <button type="button" @click="toggleTheme()" class="flex h-8 w-8 items-center justify-center rounded-full text-black/60 transition hover:bg-black/5 night:text-white/70 night:hover:bg-white/10"
                    :aria-label="night ? 'Passer en mode jour' : 'Passer en mode nuit'" :title="night ? 'Mode jour' : 'Mode nuit'">
                <svg x-show="night" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
                <svg x-show="! night" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
            </button>
            <a href="#devis" class="ml-0.5 flex h-8 items-center whitespace-nowrap rounded-full bg-[#0B0F14] px-4 text-xs font-semibold text-white transition hover:bg-black night:bg-white night:text-black night:hover:bg-white/90">Devis</a>
            <button type="button" @click="menu = !menu" :aria-expanded="menu" aria-controls="menu-mobile" aria-label="Menu"
                    class="flex h-8 w-8 items-center justify-center rounded-full text-black/70 hover:bg-black/5 night:text-white/80 night:hover:bg-white/10 lg:hidden">
                <svg x-show="!menu" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16"/></svg>
                <svg x-show="menu" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </nav>

        <nav id="menu-mobile" x-show="menu" x-cloak x-transition.opacity.duration.150ms @click="menu = false"
             class="mt-2 w-full max-w-sm rounded-3xl border border-black/10 bg-white/90 p-3 shadow-2xl backdrop-blur-xl night:border-white/15 night:bg-black/85 lg:hidden" aria-label="Navigation mobile">
            <ul class="space-y-0.5 text-sm font-medium">
                @foreach($navLinks as $href => $label)
                    <li><a href="{{ $href }}" class="block rounded-2xl px-4 py-3 text-[#0B0F14] active:bg-black/5 night:text-white night:active:bg-white/10">{{ $label }}</a></li>
                @endforeach
                <li><a href="{{ route('offres.index') }}" class="block rounded-2xl px-4 py-3 font-semibold text-[#0066FF] night:text-[#7AB0FF]">Abonnements</a></li>
            </ul>
            @if(Route::has('login'))
                <a href="{{ route('login') }}" class="mt-2 flex items-center justify-center rounded-full border border-black/15 px-4 py-3 text-sm font-semibold night:border-white/15">Espace employé</a>
            @endif
        </nav>
    </header>

    <main id="contenu" class="relative overflow-x-clip">
        <div class="pointer-events-none absolute -top-40 left-1/2 h-[600px] w-[1000px] -translate-x-1/2 rounded-full bg-[#0066FF]/[0.07] blur-3xl night:bg-white/[0.06]" aria-hidden="true"></div>

        {{-- ═══ 1. Hero ═══ --}}
        <section id="accueil" x-data="heroSlider(2)" @visibilitychange.window="document.hidden ? stop() : start()"
                 class="relative isolate scroll-mt-16 overflow-hidden">

            {{-- Photos de l'équipe en fond, fondu enchaîné et léger décalage au défilement --}}
            <div data-parallax class="pointer-events-none absolute inset-x-0 -bottom-10 -top-10 -z-10 select-none will-change-transform" aria-hidden="true">
                <img src="{{ asset('images/team-ivosphere-1.jpg') }}" alt=""
                     class="absolute inset-0 h-full w-full object-cover object-[center_28%] transition-all duration-1000 ease-out"
                     :class="active === 0 ? 'opacity-40 scale-100' : 'opacity-0 scale-105'" loading="eager">
                <img src="{{ asset('images/team-ivosphere-2.jpg') }}" alt=""
                     class="absolute inset-0 h-full w-full object-cover object-[center_22%] transition-all duration-1000 ease-out"
                     :class="active === 1 ? 'opacity-40 scale-100' : 'opacity-0 scale-105'" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-r from-[#F5F7FA] via-[#F5F7FA]/90 to-[#F5F7FA]/60 night:from-black night:via-black/85 night:to-black/50"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-[#F5F7FA]/60 via-transparent to-[#F5F7FA] night:from-black/60 night:to-black"></div>
            </div>

            <div class="mx-auto max-w-7xl px-4 pb-10 pt-32 sm:px-6 sm:pt-40 lg:px-8">
                <div class="grid grid-cols-1 items-end gap-10 lg:grid-cols-12">
                    <div class="lg:col-span-7">
                        <h1 data-reveal="up" style="--d:80ms" class="font-display text-4xl font-extrabold leading-[1.02] sm:text-6xl lg:text-7xl">
                            Des solutions professionnelles pour donner vie à vos projets
                        </h1>
                        <p data-reveal="up" style="--d:160ms" class="mt-6 max-w-xl text-base leading-relaxed text-black/55 sm:text-lg night:text-white/55">
                            IVOSPHERE réunit cinq pôles de services : impression, matériel sportif, solutions numériques, photo et évènements, assurance. Un seul partenaire, des prestations adaptées à votre besoin.
                        </p>
                        <div data-reveal="up" style="--d:240ms" class="mt-8 grid gap-3 sm:flex sm:flex-wrap">
                            <a href="#prestations" class="inline-flex h-12 items-center justify-center rounded-full bg-[#0B0F14] px-7 text-sm font-semibold text-white transition hover:bg-black night:bg-white night:text-black night:hover:bg-white/90">Découvrir nos services</a>
                            <a href="#devis" class="inline-flex h-12 items-center justify-center rounded-full border border-black/15 bg-white/60 px-7 text-sm font-semibold backdrop-blur transition hover:border-black/40 night:border-white/15 night:bg-white/5 night:hover:border-white/40">Demander un devis</a>
                        </div>

                        {{-- Choix de la photo d'équipe --}}
                        <div data-reveal="up" style="--d:320ms" class="mt-8 flex items-center gap-3" role="group" aria-label="Photos de l'équipe IVOSPHERE">
                            @foreach([1, 2] as $n)
                                <button type="button" @click="go({{ $n - 1 }})" :aria-pressed="active === {{ $n - 1 }}" aria-label="Afficher la photo d'équipe {{ $n }}"
                                        class="flex h-8 items-center focus-visible:outline-none">
                                    <span class="block h-1.5 rounded-full transition-all duration-300"
                                          :class="active === {{ $n - 1 }} ? 'w-8 bg-[#0B0F14] night:bg-white' : 'w-4 bg-black/20 hover:bg-black/40 night:bg-white/25 night:hover:bg-white/50'"></span>
                                </button>
                            @endforeach
                            <span class="text-xs text-black/45 night:text-white/45">L'équipe IVOSPHERE</span>
                        </div>
                    </div>

                    {{-- Les 5 pôles en un coup d'œil --}}
                    <div class="lg:col-span-5" data-reveal="up" style="--d:200ms">
                        <div class="rounded-[28px] border border-black/[0.08] bg-white/55 p-5 backdrop-blur-2xl night:border-white/10 night:bg-white/[0.04]">
                            <p class="px-1 text-[11px] font-medium uppercase tracking-wider text-black/40 night:text-white/40">Nos pôles</p>
                            <ul class="mt-3 space-y-1.5">
                                @foreach($poles as $key => $p)
                                    <li>
                                        <a href="#pole-{{ $key }}" class="group flex items-center gap-3 rounded-2xl px-3 py-2.5 transition hover:bg-black/[0.04] night:hover:bg-white/[0.06]">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background: {{ $p['color'] }}22; color: {{ $p['color'] }}">
                                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">@foreach($p['icon'] as $path)<path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>@endforeach</svg>
                                            </span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-semibold">{{ $p['name'] }}</span>
                                                <span class="block truncate text-xs text-black/45 night:text-white/45">{{ $p['tagline'] }}</span>
                                            </span>
                                            <svg class="h-4 w-4 shrink-0 text-black/30 transition group-hover:translate-x-0.5 group-hover:text-black night:text-white/30 night:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Nom géant en filigrane --}}
            <p class="pointer-events-none select-none text-center font-display text-[19vw] font-extrabold leading-[0.8] text-black/[0.06] night:text-white/[0.07] lg:text-[250px]" aria-hidden="true">IVOSPHERE</p>
        </section>

        {{-- ═══ 2. Nos prestations : Bento Grid ═══ --}}
        <section id="prestations" class="relative scroll-mt-24 py-20 sm:py-28" aria-labelledby="prestations-titre" x-data="bentoSection()">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div data-reveal="up" class="text-center">
                    <span class="rounded-full border border-black/10 bg-white/60 px-3 py-1 text-[11px] font-medium text-black/60 night:border-white/15 night:bg-white/5 night:text-white/70">Prestations</span>
                    <h2 id="prestations-titre" class="mt-5 font-display text-3xl font-extrabold sm:text-5xl">Nos prestations</h2>
                    <p class="mx-auto mt-4 max-w-xl text-base text-black/50 night:text-white/50">Cinq domaines d'expertise pour répondre à vos besoins professionnels.</p>
                </div>

                <div @scroll.passive="handleScroll($event)"
                     class="bento-mobile-track -mx-4 mt-12 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 pt-2 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-12 lg:gap-5">
                    @foreach($poles as $key => $p)
                        @php
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
                           x-data="magneticCard()" @mousemove="move($event)" @mouseleave="leave()"
                           class="bento-card-mobile group relative flex flex-col justify-between overflow-hidden rounded-[28px] border border-black/[0.08] bg-white/55 p-6 backdrop-blur-2xl transition-all duration-300 ease-out hover:-translate-y-1.5 hover:border-black/20 hover:bg-white/80 focus-visible:outline-none sm:p-7 night:border-white/10 night:bg-white/[0.04] night:hover:border-white/25 night:hover:bg-white/[0.07] {{ $bentoSpan }}">

                            <div class="pointer-events-none absolute -right-12 -top-12 h-44 w-44 rounded-full opacity-0 blur-3xl transition-opacity duration-500 group-hover:opacity-25" style="background: {{ $p['color'] }}" aria-hidden="true"></div>

                            <div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl transition-all duration-300 group-hover:scale-110 group-hover:rotate-6"
                                          style="background: {{ $p['color'] }}22; color: {{ $p['color'] }}">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            @foreach($p['icon'] as $path)<path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>@endforeach
                                        </svg>
                                    </span>
                                    <span class="rounded-full border border-black/10 px-3 py-1 text-[11px] font-medium text-black/45 night:border-white/15 night:text-white/50">Pôle 0{{ $loop->iteration }}</span>
                                </div>

                                <h3 class="mt-6 font-display text-xl font-bold sm:text-2xl">{{ $p['name'] }}</h3>
                                <p class="mt-1 text-xs font-medium uppercase tracking-wider text-black/40 night:text-white/40">{{ $p['tagline'] }}</p>
                                <p class="mt-3 text-sm leading-relaxed text-black/55 night:text-white/55">{{ $p['intro'] }}</p>

                                <div class="mt-5 flex flex-wrap gap-1.5">
                                    @foreach(array_slice($p['services'], 0, $loop->index === 0 ? 3 : 2) as $s)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-black/[0.05] px-3 py-1 text-xs text-black/65 night:bg-white/[0.06] night:text-white/70">
                                            <span class="h-1.5 w-1.5 rounded-full" style="background: {{ $p['color'] }}"></span>{{ $s }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-7 flex items-center justify-between">
                                <span class="text-sm font-semibold">Voir le détail</span>
                                <span data-magnetic-arrow
                                      class="flex h-10 w-10 items-center justify-center rounded-full border border-black/10 transition-all duration-200 group-hover:border-transparent group-hover:bg-[#0B0F14] group-hover:text-white night:border-white/15 night:group-hover:bg-white night:group-hover:text-black"
                                      :style="{ transform: `translate3d(${mx}px, ${my}px, 0)` }">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-4 flex items-center justify-center gap-1.5 sm:hidden" aria-hidden="true">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="h-1.5 rounded-full transition-all duration-300"
                              :class="currentSlide === {{ $i }} ? 'w-6 bg-[#0B0F14] night:bg-white' : 'w-1.5 bg-black/20 night:bg-white/25'"></span>
                    @endfor
                </div>
            </div>
        </section>

        {{-- ═══ 3. Détail des prestations : CARD STACK ═══
             Chaque pôle est une carte « collante » dont les visuels (configurables dans les
             paramètres) défilent en arrière-plan, sous un voile qui garde le texte lisible. --}}
        <section class="pb-20 sm:pb-28" aria-labelledby="detail-titre">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 id="detail-titre" data-reveal="up" class="font-display text-3xl font-extrabold sm:text-5xl">Détail des prestations</h2>

                <div class="mt-12 pb-4">
                    @foreach($poles as $key => $p)
                        @php
                            $slides = collect($p['gallery'])->filter(fn ($img) => ! empty($img['image']))->values();
                        @endphp
                        <article id="pole-{{ $key }}" data-stack-card
                                 x-data="poleSlides({{ $slides->count() }})" @mouseenter="pause()" @mouseleave="resume()"
                                 style="--stack-top: calc(6rem + {{ $loop->index * 0.75 }}rem)"
                                 class="relative isolate mb-6 flex min-h-[30rem] scroll-mt-24 flex-col justify-end overflow-hidden rounded-[28px] border border-black/[0.08] bg-white shadow-[0_-10px_30px_-14px_rgba(11,15,20,.18)] sm:min-h-[34rem] md:sticky md:top-[var(--stack-top)] night:border-white/10 night:bg-[#0c0c0d] night:shadow-[0_-10px_30px_-14px_rgba(0,0,0,.8)]">

                            {{-- Arrière-plan : visuels en fondu enchaîné avec léger zoom --}}
                            <div class="absolute inset-0 -z-10" aria-hidden="true">
                                @forelse($slides as $img)
                                    <img src="{{ Str::startsWith($img['image'], ['http://', 'https://', '/']) ? $img['image'] : asset($img['image']) }}"
                                         alt="" loading="lazy"
                                         class="absolute inset-0 h-full w-full object-cover transition-[opacity,transform] duration-[1400ms] ease-out"
                                         :class="current === {{ $loop->index }} ? 'opacity-100 scale-105' : 'opacity-0 scale-100'"
                                         @unless($loop->first) x-cloak @endunless>
                                @empty
                                    <div class="absolute inset-0" style="background: radial-gradient(120% 90% at 85% 15%, {{ $p['color'] }}55, transparent 60%), radial-gradient(80% 70% at 10% 100%, {{ $p['color'] }}22, transparent 70%);"></div>
                                @endforelse

                                {{-- Voiles de lisibilité (jour / nuit) --}}
                                <div class="absolute inset-0 bg-gradient-to-r from-white via-white/85 to-white/25 night:from-black night:via-black/80 night:to-black/20"></div>
                                <div class="absolute inset-0 bg-gradient-to-t from-white/90 via-transparent to-transparent night:from-black/85"></div>
                            </div>

                            {{-- Filigrane : numéro du pôle --}}
                            <span class="pointer-events-none absolute right-6 top-4 hidden select-none font-display text-[7rem] sm:block font-extrabold leading-none text-black/[0.06] sm:right-10 sm:text-[10rem] night:text-white/[0.07]" aria-hidden="true">0{{ $loop->iteration }}</span>

                            <div class="relative grid gap-8 p-6 sm:p-10 lg:grid-cols-12 lg:items-end lg:gap-12">
                                <div class="lg:col-span-7">
                                    <span data-reveal="bar" class="mb-5 block h-1 w-12 rounded-full" style="background: {{ $p['color'] }}"></span>
                                    <div data-reveal="up" style="--d:100ms">
                                        <p class="text-xs font-medium uppercase tracking-wider text-black/45 night:text-white/50">{{ $p['tagline'] }}</p>
                                        <h3 class="mt-2 font-display text-3xl font-extrabold sm:text-5xl">{{ $p['name'] }}</h3>
                                        <p class="mt-4 max-w-lg text-base leading-relaxed text-black/60 night:text-white/65">{{ $p['intro'] }}</p>
                                        <ul class="mt-6 flex flex-wrap gap-2">
                                            @foreach($p['services'] as $s)
                                                <li class="inline-flex items-center gap-2 rounded-full border border-black/10 bg-white/70 px-3.5 py-1.5 text-xs font-medium backdrop-blur night:border-white/15 night:bg-black/40">
                                                    <span class="h-1.5 w-1.5 rounded-full" style="background: {{ $p['color'] }}"></span>
                                                    {{ $s }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-4 lg:col-span-5 lg:items-end">
                                    {{-- Légende et indicateurs du diaporama --}}
                                    @if($slides->isNotEmpty())
                                        <div class="flex items-center gap-3 rounded-full border border-black/10 bg-white/70 py-1.5 pl-4 pr-2 backdrop-blur night:border-white/15 night:bg-black/50">
                                            <span class="max-w-[12rem] truncate text-xs font-medium">
                                                @foreach($slides as $img)
                                                    <span x-show="current === {{ $loop->index }}" @if(! $loop->first) x-cloak @endif>{{ $img['title'] ?? $p['short'] }}</span>
                                                @endforeach
                                            </span>
                                            @if($slides->count() > 1)
                                                <span class="flex items-center gap-1" role="group" aria-label="Visuels {{ $p['short'] }}">
                                                    @foreach($slides as $img)
                                                        <button type="button" @click="go({{ $loop->index }})" aria-label="Afficher le visuel {{ $loop->iteration }}" class="flex h-6 items-center px-0.5">
                                                            <span class="block h-1.5 rounded-full transition-all duration-300" :class="current === {{ $loop->index }} ? 'w-5 bg-[#0B0F14] night:bg-white' : 'w-1.5 bg-black/25 night:bg-white/30'"></span>
                                                        </button>
                                                    @endforeach
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="flex flex-wrap items-center gap-3 lg:justify-end">
                                        @auth
                                            @if(auth()->user()->role === 'administrateur')
                                                <a href="{{ route('admin.settings.index') }}#pole-showcase-images" class="text-xs text-black/50 hover:underline night:text-white/50">Gérer ces visuels</a>
                                            @endif
                                        @endauth
                                        <a href="#devis" @click="$dispatch('choose-pole', '{{ $key }}')" data-reveal="up" style="--d:300ms"
                                           class="inline-flex h-12 items-center gap-2 rounded-full bg-[#0B0F14] px-6 text-sm font-semibold text-white transition hover:bg-black night:bg-white night:text-black night:hover:bg-white/90">
                                            Demander un devis {{ $p['short'] }}
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <span data-stack-shade class="pointer-events-none absolute inset-0 rounded-[28px] bg-black opacity-0" aria-hidden="true"></span>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ═══ 4. Pourquoi choisir IVOSPHERE : SCROLL HORIZONTAL ÉPINGLÉ ═══ --}}
        <section id="pourquoi" data-hscroll class="relative scroll-mt-16" aria-labelledby="pourquoi-titre">
            <div class="hs-pin py-20 sm:py-28">
                <div class="mx-auto flex w-full max-w-7xl items-end justify-between gap-6 px-4 sm:px-6 lg:px-8">
                    <h2 id="pourquoi-titre" data-reveal="up" class="max-w-2xl font-display text-3xl font-extrabold sm:text-5xl">Pourquoi choisir IVOSPHERE ?</h2>
                    <p class="hs-meta shrink-0 text-sm text-black/40 night:text-white/40" aria-hidden="true"><span data-hs-count class="text-[#0B0F14] night:text-white">01</span> / {{ str_pad(count($reasons), 2, '0', STR_PAD_LEFT) }}</p>
                </div>

                <ul data-hs-track class="hs-track mt-12 lg:mt-14">
                    @foreach($reasons as [$title, $text])
                        <li class="hs-card flex min-h-[16rem] flex-col rounded-[28px] border border-black/[0.08] bg-white/55 p-6 backdrop-blur-2xl sm:p-7 night:border-white/10 night:bg-white/[0.04]">
                            <span class="font-display text-5xl font-extrabold text-black/10 night:text-white/15">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="mt-auto pt-10 font-display text-xl font-bold">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-black/55 night:text-white/55">{{ $text }}</p>
                        </li>
                    @endforeach
                </ul>

                <div class="hs-meta mx-auto mt-10 w-full max-w-7xl px-4 sm:px-6 lg:px-8" aria-hidden="true">
                    <div class="h-px w-full bg-black/10 night:bg-white/15"><span data-hs-bar class="block h-px origin-left scale-x-0 bg-[#0B0F14] night:bg-white"></span></div>
                </div>
            </div>
        </section>

        {{-- ═══ 5. Notre processus ═══ --}}
        <section id="processus" class="scroll-mt-24 py-20 sm:py-28" aria-labelledby="processus-titre">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div data-reveal="up" class="text-center">
                    <span class="rounded-full border border-black/10 bg-white/60 px-3 py-1 text-[11px] font-medium text-black/60 night:border-white/15 night:bg-white/5 night:text-white/70">Processus</span>
                    <h2 id="processus-titre" class="mt-5 font-display text-3xl font-extrabold sm:text-5xl">Notre processus</h2>
                    <p class="mx-auto mt-4 max-w-xl text-base text-black/50 night:text-white/50">Six étapes, de votre première demande au suivi après livraison.</p>
                </div>

                <ol class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($steps as $i => [$title, $text])
                        <li data-reveal="up" {!! $d($i, 100) !!} class="rounded-[28px] border border-black/[0.08] bg-white/55 p-6 backdrop-blur-2xl night:border-white/10 night:bg-white/[0.04]">
                            <span class="font-display text-5xl font-extrabold text-black/10 night:text-white/15">0{{ $i + 1 }}</span>
                            <h3 class="mt-6 text-base font-semibold">{{ $title }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-black/55 night:text-white/55">{{ $text }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- ═══ 6. Réalisations ═══ --}}
        <section id="realisations" class="scroll-mt-24 pb-20 sm:pb-28" aria-labelledby="realisations-titre" x-data="{ filter: 'all' }">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div data-reveal="up" class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <h2 id="realisations-titre" class="font-display text-3xl font-extrabold sm:text-5xl">Réalisations et projets</h2>
                        <p class="mt-4 text-base text-black/50 night:text-white/50">Un aperçu de nos travaux dans chacun de nos pôles.</p>
                    </div>
                    @if($projects->isNotEmpty())
                        <div class="-mx-1 flex gap-1 overflow-x-auto rounded-full border border-black/10 bg-white/60 p-1 [scrollbar-width:none] night:border-white/10 night:bg-white/5" role="group" aria-label="Filtrer les projets">
                            @foreach(['all' => 'Galerie', 'done' => 'Projets réalisés', 'ongoing' => 'Prestations en cours'] as $value => $label)
                                <button type="button" @click="filter = '{{ $value }}'" :aria-pressed="filter === '{{ $value }}'"
                                        :class="filter === '{{ $value }}' ? 'bg-[#0B0F14] text-white night:bg-white night:text-black' : 'text-black/55 night:text-white/55'"
                                        class="shrink-0 rounded-full px-4 py-2 text-xs font-semibold transition-colors">{{ $label }}</button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($projects->isNotEmpty())
                    <ul class="mt-10 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                        @foreach($projects as $project)
                            @php $pole = $poles[$project['pole'] ?? ''] ?? null; @endphp
                            <li x-show="filter === 'all' || filter === '{{ $project['status'] ?? 'done' }}'" data-reveal="scale" {!! $d($loop->index % 3, 90) !!}
                                class="group overflow-hidden rounded-3xl border border-black/[0.08] bg-white/55 night:border-white/10 night:bg-white/[0.04]">
                                <div class="aspect-[4/3] overflow-hidden bg-black/5 night:bg-white/5">
                                    @if(!empty($project['image']))
                                        <img src="{{ asset('storage/' . $project['image']) }}" alt="{{ $project['title'] }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                    @endif
                                </div>
                                <div class="p-4">
                                    <p class="text-sm font-semibold">{{ $project['title'] }}</p>
                                    <p class="mt-1 flex items-center gap-2 text-xs text-black/50 night:text-white/50">
                                        @if($pole)<span class="h-2 w-2 rounded-full" style="background: {{ $pole['color'] }}"></span>{{ $pole['short'] }}@endif
                                        @if(($project['status'] ?? 'done') === 'ongoing')<span class="rounded-full bg-amber-400/15 px-2 py-0.5 font-semibold text-amber-600 night:text-amber-300">En cours</span>@endif
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div data-reveal="up" class="mt-10 rounded-[28px] border border-dashed border-black/15 bg-white/40 px-6 py-16 text-center night:border-white/15 night:bg-white/[0.02]">
                        <p class="font-display text-xl font-bold">Nos réalisations arrivent bientôt</p>
                        <p class="mx-auto mt-2 max-w-md text-sm text-black/50 night:text-white/50">Vous souhaitez voir des exemples de travaux dans un pôle précis ? Demandez-nous des références, nous vous les envoyons.</p>
                        <a href="#devis" class="mt-6 inline-flex h-11 items-center rounded-full bg-[#0B0F14] px-6 text-sm font-semibold text-white night:bg-white night:text-black">Demander des références</a>
                    </div>
                @endif
            </div>
        </section>

        {{-- ═══ 7. Devis ═══ --}}
        <section id="devis" class="scroll-mt-24 pb-24 sm:pb-32" aria-labelledby="devis-titre" x-data="{ pole: '' }" @choose-pole.window="pole = $event.detail">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-5 rounded-[32px] border border-black/[0.08] bg-gradient-to-b from-white to-white/40 p-5 sm:p-10 lg:grid-cols-12 lg:gap-12 night:border-white/10 night:from-white/[0.08] night:to-white/[0.02]">
                    <div class="lg:col-span-5" data-reveal="up">
                        <h2 id="devis-titre" class="font-display text-3xl font-extrabold sm:text-5xl">Vous avez un projet ? Parlons-en.</h2>
                        <p class="mt-4 max-w-md text-base leading-relaxed text-black/55 night:text-white/55">Décrivez votre besoin en quelques lignes. Nous vous répondons avec une proposition claire et un devis détaillé.</p>

                        <ul class="mt-8 space-y-4 text-sm">
                            @if($phone)
                                <li><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="flex items-center gap-3 font-semibold hover:underline"><span class="w-24 shrink-0 font-normal text-black/45 night:text-white/45">Téléphone</span>{{ $phone }}</a></li>
                            @endif
                            @if($whatsapp)
                                <li><a href="https://wa.me/{{ preg_replace('/\D+/', '', $whatsapp) }}" target="_blank" rel="noopener" class="flex items-center gap-3 font-semibold hover:underline"><span class="w-24 shrink-0 font-normal text-black/45 night:text-white/45">WhatsApp</span>{{ $whatsapp }}</a></li>
                            @endif
                            @if($email)
                                <li><a href="mailto:{{ $email }}" class="flex items-center gap-3 font-semibold hover:underline"><span class="w-24 shrink-0 font-normal text-black/45 night:text-white/45">Email</span><span class="break-all">{{ $email }}</span></a></li>
                            @endif
                            @if($address)
                                <li class="flex items-start gap-3"><span class="w-24 shrink-0 text-black/45 night:text-white/45">Adresse</span><span class="font-semibold">{{ $address }}</span></li>
                            @endif
                        </ul>
                    </div>

                    <div class="lg:col-span-7" data-reveal="up" style="--d:120ms">
                        @if(session('quote_sent'))
                            <div role="status" class="rounded-[28px] border border-emerald-500/30 bg-emerald-500/10 p-6 sm:p-8">
                                <p class="font-display text-xl font-bold">Demande envoyée</p>
                                <p class="mt-2 text-sm text-black/55 night:text-white/60">Merci. Nous revenons vers vous très vite avec une réponse à votre demande.</p>
                            </div>
                        @else
                            <form method="POST" action="{{ $quoteRoute ?? '#' }}" class="rounded-[28px] border border-black/[0.08] bg-white/60 p-5 backdrop-blur-2xl sm:p-8 night:border-white/10 night:bg-white/[0.04]" novalidate>
                                @csrf
                                <div class="hidden" aria-hidden="true"><label>Ne pas remplir<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                                @if($errors->any())
                                    <div role="alert" class="mb-5 rounded-2xl border border-rose-400/40 bg-rose-500/10 p-3.5 text-sm text-rose-700 night:text-rose-200">
                                        <ul class="space-y-0.5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                                    </div>
                                @endif

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="q-name" class="mb-1.5 block text-xs font-medium text-black/60 night:text-white/60">Nom et prénom</label>
                                        <input id="q-name" name="name" type="text" required autocomplete="name" value="{{ old('name') }}" class="{{ $field }}">
                                    </div>
                                    <div>
                                        <label for="q-phone" class="mb-1.5 block text-xs font-medium text-black/60 night:text-white/60">Téléphone</label>
                                        <input id="q-phone" name="phone" type="tel" required autocomplete="tel" inputmode="tel" value="{{ old('phone') }}" class="{{ $field }}">
                                    </div>
                                    <div>
                                        <label for="q-email" class="mb-1.5 block text-xs font-medium text-black/60 night:text-white/60">Email <span class="font-normal text-black/40 night:text-white/40">(facultatif)</span></label>
                                        <input id="q-email" name="email" type="email" autocomplete="email" inputmode="email" value="{{ old('email') }}" class="{{ $field }}">
                                    </div>
                                    <div>
                                        <label for="q-pole" class="mb-1.5 block text-xs font-medium text-black/60 night:text-white/60">Pôle concerné</label>
                                        <select id="q-pole" name="pole" x-model="pole" class="{{ $field }}">
                                            <option value="" class="text-[#0B0F14]">Je ne sais pas encore</option>
                                            @foreach($poles as $key => $p)
                                                <option value="{{ $key }}" class="text-[#0B0F14]">{{ $p['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="q-message" class="mb-1.5 block text-xs font-medium text-black/60 night:text-white/60">Votre projet</label>
                                        <textarea id="q-message" name="message" rows="4" required placeholder="Quantités, dates, budget, contraintes…" class="{{ $field }}">{{ old('message') }}</textarea>
                                    </div>
                                </div>

                                <button type="submit" class="mt-6 flex h-12 w-full items-center justify-center gap-2 rounded-full bg-[#0B0F14] px-7 text-sm font-semibold text-white transition hover:bg-black sm:w-auto night:bg-white night:text-black night:hover:bg-white/90">
                                    Envoyer ma demande de devis
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- ═══ 8. Pied de page ═══ --}}
    <footer class="border-t border-black/[0.08] night:border-white/10">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 sm:py-16 md:grid-cols-12 lg:px-8">
            <div class="md:col-span-5" data-reveal="up">
                <span class="inline-flex rounded-xl bg-white px-2.5 py-1.5 ring-1 ring-black/5">
                    <img src="{{ asset('images/logo-horizontal.png') }}" alt="IVOSPHERE" class="h-8 w-auto">
                </span>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-black/50 night:text-white/50">Impression, matériel sportif, solutions numériques, photo et évènements, assurance : cinq pôles au service de vos projets.</p>
            </div>

            <nav class="md:col-span-4" aria-label="Pôles" data-reveal="up" style="--d:100ms">
                <p class="text-sm font-semibold">Nos pôles</p>
                <ul class="mt-4 space-y-2.5 text-sm text-black/50 night:text-white/50">
                    @foreach($poles as $key => $p)
                        <li><a href="#pole-{{ $key }}" class="hover:text-black night:hover:text-white">{{ $p['name'] }}</a></li>
                    @endforeach
                    <li><a href="{{ route('offres.index') }}" class="font-semibold text-[#0B0F14] hover:underline night:text-white">Abonnements</a></li>
                </ul>
            </nav>

            <div class="md:col-span-3" data-reveal="up" style="--d:200ms">
                <p class="text-sm font-semibold">Contact</p>
                <ul class="mt-4 space-y-2.5 text-sm text-black/50 night:text-white/50">
                    @if($phone)<li><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="hover:text-black night:hover:text-white">{{ $phone }}</a></li>@endif
                    @if($email)<li><a href="mailto:{{ $email }}" class="break-all hover:text-black night:hover:text-white">{{ $email }}</a></li>@endif
                    @if($address)<li>{{ $address }}</li>@endif
                    <li><a href="#devis" class="font-semibold text-[#0B0F14] hover:underline night:text-white">Demander un devis</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-black/[0.08] night:border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-black/45 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8 night:text-white/45">
                <p>© {{ now()->year }} IVOSPHERE. Tous droits réservés.</p>
                @if(Route::has('login'))
                    <a href="{{ route('login') }}" class="hover:text-black night:hover:text-white">Accès équipe</a>
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

        /* Diaporama d'arrière-plan d'une carte pôle : en pause au survol, onglet masqué ou mouvement réduit. */
        function poleSlides(total) {
            const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            return {
                current: 0,
                timer: null,
                paused: false,
                init() {
                    this.start();
                    document.addEventListener('visibilitychange', () => document.hidden ? this.stop() : this.start());
                },
                start() {
                    if (reduced || total < 2 || this.timer || this.paused) return;
                    this.timer = setInterval(() => { this.current = (this.current + 1) % total; }, 4500);
                },
                stop() { clearInterval(this.timer); this.timer = null; },
                pause() { this.paused = true; this.stop(); },
                resume() { this.paused = false; this.start(); },
                go(i) { this.current = i; this.stop(); this.start(); },
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
    @livewireScriptConfig
</body>
</html>