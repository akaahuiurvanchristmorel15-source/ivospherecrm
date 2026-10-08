<!DOCTYPE html>
<html lang="fr" class="h-full scroll-smooth bg-[#F5F7FA]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'IVOSPHERE — Plateforme Intégrée de Gestion d’Entreprise' }}</title>
    <meta name="description" content="Une seule plateforme pour piloter toute votre entreprise : Ressources humaines, Gestion commerciale, CRM, Finance, Trésorerie, Stocks, Immobilisations et Opérations multi-activités.">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        html { font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    </style>
</head>
<body class="min-h-full antialiased font-sans text-[#0B0F14] bg-[#F5F7FA] selection:bg-[#0066FF] selection:text-white"
      x-data="{ 
          demoModalOpen: false, 
          selectedPole: '',
          demoSubmitted: false,
          demoName: '',
          demoEmail: '',
          demoPhone: '',
          demoCompany: '',
          submitDemo() {
              this.demoSubmitted = true;
              setTimeout(() => {
                  this.demoSubmitted = false;
                  this.demoModalOpen = false;
                  this.demoName = '';
                  this.demoEmail = '';
                  this.demoPhone = '';
                  this.demoCompany = '';
              }, 2500);
          }
      }"
      @open-demo-modal.window="demoModalOpen = true; selectedPole = $event.detail?.pole || ''">

    <!-- Top Navigation -->
    <x-landing.navbar />

    <!-- Main Content -->
    <main id="main-content">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <x-landing.footer />

    <!-- Modal Démo & Découverte Interactive -->
    <div x-show="demoModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="modal-title">
        <!-- Backdrop -->
        <div x-show="demoModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-[#0B0F14]/70 backdrop-blur-xs" 
             @click="demoModalOpen = false"></div>

        <!-- Modal Box -->
        <div class="flex min-h-full items-end sm:items-center justify-center p-3 sm:p-4 text-center">
            <div x-show="demoModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-lg border border-[#E2E8F0]">
                
                <!-- Close Button -->
                <button type="button" 
                        @click="demoModalOpen = false"
                        class="absolute top-4 right-4 p-2 rounded-xl text-[#64748B] hover:text-[#0B0F14] hover:bg-slate-100 transition focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#0066FF]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Modal Body -->
                <div class="p-6 sm:p-8">
                    <template x-if="!demoSubmitted">
                        <form @submit.prevent="submitDemo()" class="space-y-4">
                            <div class="space-y-1">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-50 text-[11px] font-semibold text-[#0066FF] border border-blue-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#0066FF]"></span>
                                    Démonstration personnalisée
                                </div>
                                <h3 id="modal-title" class="text-xl sm:text-2xl font-bold text-[#0B0F14] tracking-tight">
                                    Découvrir IVOSPHERE
                                </h3>
                                <p class="text-xs sm:text-sm text-[#64748B]">
                                    Nos experts configurent un environnement de test adapté à vos pôles d'activités en moins de 24h.
                                </p>
                            </div>

                            <div x-show="selectedPole" class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-[#0B0F14] flex items-center justify-between">
                                <span>Pôle d'intérêt : <strong x-text="selectedPole" class="text-[#0066FF]"></strong></span>
                                <button type="button" @click="selectedPole = ''" class="text-[11px] text-[#64748B] hover:underline">Modifier</button>
                            </div>

                            <div class="space-y-3 pt-2">
                                <div>
                                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1">Nom complet *</label>
                                    <input type="text" x-model="demoName" required placeholder="Ex: Jean Kouassi" 
                                           class="w-full h-11 px-3.5 rounded-xl border border-[#E2E8F0] text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 transition outline-hidden">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1">Email professionnel *</label>
                                        <input type="email" x-model="demoEmail" required placeholder="contact@entreprise.ci" 
                                               class="w-full h-11 px-3.5 rounded-xl border border-[#E2E8F0] text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 transition outline-hidden">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1">Téléphone / WhatsApp *</label>
                                        <input type="tel" x-model="demoPhone" required placeholder="+225 07 00 00 00 00" 
                                               class="w-full h-11 px-3.5 rounded-xl border border-[#E2E8F0] text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 transition outline-hidden">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1">Nom de votre entreprise *</label>
                                    <input type="text" x-model="demoCompany" required placeholder="Ex: Groupe Atlantique SARL" 
                                           class="w-full h-11 px-3.5 rounded-xl border border-[#E2E8F0] text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 transition outline-hidden">
                                </div>
                            </div>

                            <div class="pt-4 flex items-center justify-end gap-3">
                                <button type="button" 
                                        @click="demoModalOpen = false"
                                        class="h-11 px-4 rounded-xl border border-[#E2E8F0] text-xs font-semibold text-[#64748B] hover:text-[#0B0F14] hover:bg-slate-50 transition">
                                    Annuler
                                </button>
                                <button type="submit" 
                                        class="h-11 px-6 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-xs font-semibold text-white shadow-xs transition flex items-center gap-2">
                                    <span>Planifier ma démo</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </template>

                    <template x-if="demoSubmitted">
                        <div class="py-8 text-center space-y-3">
                            <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto border border-emerald-200">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-[#0B0F14]">Demande bien reçue !</h3>
                            <p class="text-xs sm:text-sm text-[#64748B] max-w-sm mx-auto">
                                Merci. Un conseiller IVOSPHERE prendra contact avec vous sous 2 heures pour organiser votre démonstration.
                            </p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
