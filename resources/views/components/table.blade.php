@props([
    'headers' => [],
    'pagination' => null,
])

<div class="bg-white border border-[#E2E8F0] rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm text-[#0B0F14]">
            @if(!empty($headers))
                <thead class="bg-[#F5F7FA]/70 border-b border-[#E2E8F0] text-[10px] sm:text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">
                    <tr>
                        @foreach($headers as $header)
                            <th scope="col" class="px-4 py-3 sm:px-6 sm:py-3.5 {{ is_array($header) && isset($header['align']) && $header['align'] === 'right' ? 'text-right' : '' }}">
                                {{ is_array($header) ? $header['label'] : $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="divide-y divide-slate-100 bg-white">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if($pagination)
        <div class="px-4 py-3 sm:px-6 sm:py-3.5 border-t border-[#E2E8F0] bg-[#F5F7FA]/50">
            {{ $pagination }}
        </div>
    @endif
</div>
