<!DOCTYPE html>
<html lang="fr" class="h-full bg-[#F5F7FA]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Plateforme de Gestion' }} — {{ config('app.name', 'IVOSPHERE ERP') }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        try { if (localStorage.getItem('ivo-theme') === 'dark') { document.documentElement.classList.add('dark'); } } catch (e) {}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans text-[#0B0F14] bg-[#F5F7FA] flex flex-col md:flex-row overflow-hidden selection:bg-[#0066FF] selection:text-white" x-data="{ sidebarOpen: false }">

    <!-- Mobile sidebar backdrop -->
    <div 
        x-show="sidebarOpen" 
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false" 
        class="fixed inset-0 bg-[#0B0F14]/70 backdrop-blur-xs z-40 md:hidden"
        style="display: none;"
    ></div>

    <!-- 2. MAIN WORKSPACE (TOPBAR + CONTENT + MOBILE BOTTOM BAR) -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden relative">
        
        <!-- Topbar (#FFFFFF) -->
        <x-topbar />

        <!-- Scrollable Content Area (#F5F7FA with safe bottom padding for mobile tab bar) -->
        <main class="flex-1 overflow-y-auto p-3.5 sm:p-6 lg:p-8 pb-24 md:pb-8 bg-[#F5F7FA]">
            <div class="max-w-7xl mx-auto flex flex-col min-h-full">
                <div class="flex-1 space-y-5 sm:space-y-6">

                    <!-- Global Flash Notifications -->
                    @if(session('success'))
                        <x-alert type="success">
                            {{ session('success') }}
                        </x-alert>
                    @endif

                    @if(session('error'))
                        <x-alert type="error">
                            {{ session('error') }}
                        </x-alert>
                    @endif

                    @if(session('warning'))
                        <x-alert type="warning">
                            {{ session('warning') }}
                        </x-alert>
                    @endif

                    @if(session('info'))
                        <x-alert type="info">
                            {{ session('info') }}
                        </x-alert>
                    @endif

                    @isset($header)
                        <div class="mb-4 sm:mb-6">
                            {{ $header }}
                        </div>
                    @endisset

                    <!-- Page Content Slot -->
                    {{ $slot }}

                </div>

                <!-- Pied de page discret (§ Structure générale) -->
                <x-footer />
            </div>
        </main>

        <!-- Mobile Bottom Navigation Bar (Fixed 390px Thumb-Zone) -->
        <x-bottom-nav />
    </div>

    <!-- Spotlight Search Global Modal (CTRL+K) -->
    <x-spotlight-search />

    <!-- Product QR Code & EAN Modal (Accessible globalement) -->
    <script src="{{ asset('vendor/qrcodejs/qrcode.min.js') }}"></script>
    <x-product-qr-modal />

    @stack('scripts')
</body>
</html>
