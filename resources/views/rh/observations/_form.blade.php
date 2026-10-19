{{--
    Champs d'une observation (saisie ou modification).
    @param \App\Models\EmployeeObservation|null $observation  observation modifiée (null = saisie)
    @param \Illuminate\Support\Collection|null $employees     collaborateurs proposés à la saisie
    @param \App\Models\Employee|null $employee                collaborateur imposé (fiche agent)
--}}
@php
    $observation ??= null;
    $employees ??= null;
    $employee ??= null;
    $isEdit = $observation !== null;
    // Après un envoi refusé, on réaffiche la saisie (la modification a son propre sac d'erreurs)
    $useOld = $isEdit ? $errors->getBag('observation'.$observation->id)->any() : $errors->any();
    $value = fn (string $field, mixed $default = null) => $useOld ? old($field) : ($isEdit ? $observation->{$field} : $default);
    $observedOn = $useOld ? old('observed_on') : ($isEdit ? $observation->observed_on->format('Y-m-d') : now()->format('Y-m-d'));
    $fieldClass = 'w-full px-3 py-2 rounded-xl border border-[#E2E8F0] bg-white text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]';
    $prefix = $isEdit ? 'obs-'.$observation->id : 'obs-new';
@endphp

<form method="POST" action="{{ $isEdit ? route('rh.observations.update', $observation) : route('rh.observations.store') }}" class="space-y-3 text-xs">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @unless($isEdit)
            @if($employee)
                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            @else
                <div class="sm:col-span-2">
                    <label for="{{ $prefix }}-employee" class="block font-semibold text-[#0B0F14] mb-1">Collaborateur *</label>
                    <select id="{{ $prefix }}-employee" name="employee_id" required class="{{ $fieldClass }}">
                        <option value="">Choisir un collaborateur…</option>
                        @foreach($employees ?? [] as $emp)
                            <option value="{{ $emp->id }}" @selected((string) ($useOld ? old('employee_id') : request('employee_id')) === (string) $emp->id)>
                                {{ $emp->full_name }} ({{ $emp->employee_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        @endunless

        <div>
            <label for="{{ $prefix }}-date" class="block font-semibold text-[#0B0F14] mb-1">Date de l'observation *</label>
            <input id="{{ $prefix }}-date" type="date" name="observed_on" value="{{ $observedOn }}" max="{{ now()->format('Y-m-d') }}" required class="{{ $fieldClass }}">
        </div>

        <div>
            <label for="{{ $prefix }}-category" class="block font-semibold text-[#0B0F14] mb-1">Catégorie *</label>
            <select id="{{ $prefix }}-category" name="category" required class="{{ $fieldClass }}">
                @foreach(\App\Models\EmployeeObservation::CATEGORIES as $key => $label)
                    <option value="{{ $key }}" @selected($value('category') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <fieldset>
        <legend class="block font-semibold text-[#0B0F14] mb-1">Nature *</legend>
        <div class="grid grid-cols-3 gap-2">
            @foreach(\App\Models\EmployeeObservation::NATURES as $key => $label)
                @php
                    $tone = match ($key) {
                        'positive' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-800',
                        'a_ameliorer' => 'peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-800',
                        default => 'peer-checked:border-slate-500 peer-checked:bg-slate-100 peer-checked:text-slate-800',
                    };
                @endphp
                <label class="cursor-pointer">
                    <input type="radio" name="nature" value="{{ $key }}" class="peer sr-only" required @checked($value('nature', 'positive') === $key)>
                    <span class="block text-center px-2 py-2 rounded-xl border border-[#E2E8F0] font-semibold text-[#64748B] transition peer-focus-visible:ring-2 peer-focus-visible:ring-[#0066FF] {{ $tone }}">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <div>
        <label for="{{ $prefix }}-title" class="block font-semibold text-[#0B0F14] mb-1">Intitulé *</label>
        <input id="{{ $prefix }}-title" type="text" name="title" value="{{ $value('title') }}" maxlength="255" required placeholder="Ex : Excellent accueil d'un client mécontent" class="{{ $fieldClass }}">
    </div>

    <div>
        <label for="{{ $prefix }}-content" class="block font-semibold text-[#0B0F14] mb-1">Observation détaillée *</label>
        <textarea id="{{ $prefix }}-content" name="content" rows="4" maxlength="5000" required placeholder="Faits constatés, contexte, suite donnée…" class="{{ $fieldClass }}">{{ $value('content') }}</textarea>
    </div>

    <label class="flex items-start gap-2 cursor-pointer">
        <input type="checkbox" name="visible_to_employee" value="1" class="mt-0.5 rounded border-[#E2E8F0] text-[#0066FF] focus:ring-[#0066FF]" @checked($useOld ? old('visible_to_employee') : (bool) $observation?->visible_to_employee)>
        <span>
            <span class="font-semibold text-[#0B0F14]">Visible par le collaborateur</span>
            <span class="block text-[11px] text-[#64748B]">Si coché, l'observation apparaît dans son espace « Mon espace ». Sinon elle reste réservée aux responsables.</span>
        </span>
    </label>

    <div class="pt-2 flex justify-end gap-2">
        @if($isEdit)
            <button type="button" @click="editing = false" class="px-4 py-2 rounded-xl border border-[#E2E8F0] font-bold text-slate-700">Annuler</button>
        @endif
        <button type="submit" class="px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white font-bold">
            {{ $isEdit ? 'Enregistrer les modifications' : 'Enregistrer l\'observation' }}
        </button>
    </div>
</form>
