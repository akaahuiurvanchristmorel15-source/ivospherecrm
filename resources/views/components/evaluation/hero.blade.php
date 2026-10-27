{{-- Carte héro sombre du tableau de bord RH (fond noir, halo coloré). --}}
@props([
    'glow' => 'bg-[#0066FF]/30',
])

<article {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-3xl bg-[#0B0F14] p-5 text-white sm:p-6']) }}>
    <div class="pointer-events-none absolute -right-16 -top-16 h-52 w-52 rounded-full blur-3xl {{ $glow }}"></div>
    <div class="relative">
        {{ $slot }}
    </div>
</article>
