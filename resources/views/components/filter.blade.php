@props([
    'label' => 'Filtrer',
    'options' => [], // ['value' => 'label']
    'name' => 'filter',
    'selected' => null,
])

<div class="relative inline-block text-left">
    <select 
        name="{{ $name }}" 
        onchange="this.form ? this.form.submit() : null"
        {{ $attributes->merge(['class' => 'px-3 py-2 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] shadow-2xs transition-colors cursor-pointer']) }}
    >
        @if($label)
            <option value="">{{ $label }}</option>
        @endif
        @foreach($options as $val => $text)
            <option value="{{ $val }}" {{ (string)($selected ?? request($name)) === (string)$val ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>
</div>
