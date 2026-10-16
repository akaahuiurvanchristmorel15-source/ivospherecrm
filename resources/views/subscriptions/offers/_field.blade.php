<div class="{{ $class ?? '' }}">
    <label for="{{ $name }}" class="block text-xs font-semibold text-[#0B0F14] mb-1">{{ $label }}@if($required ?? false) <span class="text-rose-500">*</span>@endif</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type ?? 'text' }}" value="{{ ($type ?? 'text') === 'password' ? '' : old($name) }}"
           @if($required ?? false) required @endif @if($autocomplete ?? null) autocomplete="{{ $autocomplete }}" @endif
           placeholder="{{ $placeholder ?? '' }}"
           class="w-full h-11 px-3.5 rounded-xl border {{ $errors->has($name) ? 'border-rose-400' : 'border-[#E2E8F0]' }} bg-white text-sm focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 transition outline-hidden">
    @error($name)
        <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
    @enderror
</div>
