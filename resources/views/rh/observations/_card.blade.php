{{--
    Une observation, avec modification / suppression pour l'auteur et l'administrateur.
    @param \App\Models\EmployeeObservation $observation
    @param bool $showEmployee  affiche le nom du collaborateur (liste générale)
--}}
@php
    $showEmployee ??= true;
    $natureBadge = match ($observation->nature) {
        'positive' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'a_ameliorer' => 'bg-amber-50 text-amber-800 border-amber-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
    $accent = match ($observation->nature) {
        'positive' => 'border-l-emerald-500',
        'a_ameliorer' => 'border-l-amber-500',
        default => 'border-l-slate-300',
    };
@endphp

@php
    $editErrors = $errors->getBag('observation'.$observation->id);
@endphp

<article x-data="{ editing: {{ $editErrors->any() ? 'true' : 'false' }} }" class="rounded-2xl border border-[#E2E8F0] border-l-4 {{ $accent }} bg-white p-4 sm:p-5 space-y-3">
    <div x-show="!editing" class="space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $natureBadge }}">{{ $observation->nature_label }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border border-[#E2E8F0] bg-[#F8FAFC] text-[#64748B]">{{ $observation->category_label }}</span>
                    @if($observation->visible_to_employee)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border border-blue-200 bg-blue-50 text-[#0066FF]">Visible par l'agent</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border border-[#E2E8F0] bg-white text-slate-500">Confidentielle</span>
                    @endif
                </div>
                <h3 class="text-sm font-bold text-[#0B0F14] break-words">{{ $observation->title }}</h3>
                <p class="text-[11px] text-[#64748B]">
                    @if($showEmployee)
                        <a href="{{ route('rh.observations.index', ['employee_id' => $observation->employee_id]) }}" class="font-semibold text-[#0066FF] hover:underline">{{ $observation->employee->full_name }}</a>
                        <span class="mx-1 text-slate-300">·</span>
                    @endif
                    {{ $observation->observed_on->format('d/m/Y') }}
                    <span class="mx-1 text-slate-300">·</span>
                    par {{ $observation->author?->name ?? 'Compte supprimé' }}
                </p>
            </div>

            @canany(['update', 'delete'], $observation)
                <div class="flex items-center gap-2 shrink-0">
                    @can('update', $observation)
                        <button type="button" @click="editing = true" class="px-3 py-1 rounded-lg bg-white border border-[#E2E8F0] hover:border-[#0066FF] hover:text-[#0066FF] text-xs font-bold text-slate-700 transition">Modifier</button>
                    @endcan
                    @can('delete', $observation)
                        <form method="POST" action="{{ route('rh.observations.destroy', $observation) }}" onsubmit="return confirm('Supprimer définitivement cette observation ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1 rounded-lg bg-white border border-[#E2E8F0] hover:border-rose-500 hover:text-rose-600 text-xs font-bold text-slate-700 transition">Supprimer</button>
                        </form>
                    @endcan
                </div>
            @endcanany
        </div>

        <p class="text-xs text-[#0B0F14] leading-relaxed whitespace-pre-line break-words">{{ $observation->content }}</p>
    </div>

    @can('update', $observation)
        <div x-show="editing" x-cloak style="display: none;" class="space-y-3">
            @if($editErrors->any())
                <x-alert type="error">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($editErrors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif
            @include('rh.observations._form', ['observation' => $observation])
        </div>
    @endcan
</article>
