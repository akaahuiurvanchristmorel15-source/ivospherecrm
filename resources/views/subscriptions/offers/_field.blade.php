<div class="{{ $class ?? '' }}">
    <label for="{{ $name }}" class="block text-xs font-medium text-black/60 night:text-white/60 mb-1.5">{{ $label }}@if($required ?? false) <span class="text-rose-500">*</span>@endif</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type ?? 'text' }}" value="{{ ($type ?? 'text') === 'password' ? '' : old($name) }}"
           @if($required ?? false) required @endif @if($autocomplete ?? null) autocomplete="{{ $autocomplete }}" @endif
           placeholder="{{ $placeholder ?? '' }}"
           class="w-full h-12 px-4 rounded-2xl border bg-white/80 text-sm text-[#0B0F14] placeholder:text-black/30 outline-hidden transition focus:border-black/40 focus:ring-4 focus:ring-black/5 night:bg-white/[0.04] night:text-white night:placeholder:text-white/25 night:focus:border-white/40 night:focus:ring-white/10 {{ $errors->has($name) ? 'border-rose-400 night:border-rose-400/70' : 'border-black/10 night:border-white/10' }}">
    @error($name)
        <p class="mt-1.5 text-[11px] text-rose-600 night:text-rose-300">{{ $message }}</p>
    @enderror
</div>
