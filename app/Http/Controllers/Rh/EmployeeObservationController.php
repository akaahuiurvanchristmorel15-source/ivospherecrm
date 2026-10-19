<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeObservation;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeObservationController extends Controller
{
    /**
     * Observations des responsables sur les collaborateurs qu'ils suivent.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', EmployeeObservation::class);

        $user = $request->user();
        $scope = EmployeeObservation::query()->observableBy($user);

        $query = (clone $scope)->with(['employee', 'author'])->latest('observed_on')->latest('id');

        foreach (['employee_id', 'nature', 'category'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        $observations = $query->paginate(15)->withQueryString();
        $employees = Employee::active()->observableBy($user)->orderBy('first_name')->orderBy('last_name')->get();
        $natureCounts = (clone $scope)->selectRaw('nature, count(*) as total')->groupBy('nature')->pluck('total', 'nature');

        return view('rh.observations.index', [
            'observations' => $observations,
            'employees' => $employees,
            'natureCounts' => $natureCounts,
            'natures' => EmployeeObservation::NATURES,
            'categories' => EmployeeObservation::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            ...$this->rules(),
        ], $this->messages());

        $employee = Employee::findOrFail($validated['employee_id']);
        Gate::authorize('create', [EmployeeObservation::class, $employee]);

        $observation = $employee->observations()->create([
            ...$validated,
            'author_id' => $request->user()->id,
            'visible_to_employee' => $request->boolean('visible_to_employee'),
        ]);

        ActivityLogger::log(
            'creation_observation_collaborateur',
            "Observation « {$observation->nature_label} » ajoutée pour {$employee->full_name} : {$observation->title}",
            $observation,
            $employee->domain_id
        );

        return back()->with('success', "Observation enregistrée pour {$employee->full_name}.");
    }

    public function update(Request $request, EmployeeObservation $observation): RedirectResponse
    {
        Gate::authorize('update', $observation);

        $validated = $request->validateWithBag('observation'.$observation->id, $this->rules(), $this->messages());

        $observation->update([
            ...$validated,
            'visible_to_employee' => $request->boolean('visible_to_employee'),
        ]);

        ActivityLogger::log(
            'maj_observation_collaborateur',
            "Observation de {$observation->employee->full_name} modifiée : {$observation->title}",
            $observation,
            $observation->employee->domain_id
        );

        return back()->with('success', 'Observation mise à jour.');
    }

    public function destroy(EmployeeObservation $observation): RedirectResponse
    {
        Gate::authorize('delete', $observation);

        $employee = $observation->employee;
        $title = $observation->title;
        $observation->delete();

        ActivityLogger::log(
            'suppression_observation_collaborateur',
            "Observation de {$employee->full_name} supprimée : {$title}",
            $employee,
            $employee->domain_id
        );

        return back()->with('success', "Observation « {$title} » supprimée.");
    }

    /**
     * Règles communes à la saisie et à la modification.
     *
     * @return array<string, list<mixed>>
     */
    private function rules(): array
    {
        return [
            'observed_on' => ['required', 'date', 'before_or_equal:today'],
            'nature' => ['required', Rule::in(array_keys(EmployeeObservation::NATURES))],
            'category' => ['required', Rule::in(array_keys(EmployeeObservation::CATEGORIES))],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
            'visible_to_employee' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Messages de validation en français.
     *
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'employee_id.required' => 'Choisissez le collaborateur concerné.',
            'employee_id.exists' => 'Ce collaborateur n\'existe pas.',
            'observed_on.required' => 'La date de l\'observation est obligatoire.',
            'observed_on.date' => 'La date de l\'observation n\'est pas valide.',
            'observed_on.before_or_equal' => 'La date de l\'observation ne peut pas être dans le futur.',
            'nature.required' => 'Indiquez la nature de l\'observation.',
            'nature.in' => 'La nature choisie n\'est pas valide.',
            'category.required' => 'Choisissez une catégorie.',
            'category.in' => 'La catégorie choisie n\'est pas valide.',
            'title.required' => 'L\'intitulé est obligatoire.',
            'title.max' => 'L\'intitulé ne doit pas dépasser 255 caractères.',
            'content.required' => 'Le texte de l\'observation est obligatoire.',
            'content.max' => 'Le texte de l\'observation ne doit pas dépasser 5000 caractères.',
        ];
    }
}
