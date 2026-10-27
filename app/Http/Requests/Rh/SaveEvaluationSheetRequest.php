<?php

namespace App\Http\Requests\Rh;

use App\Models\EvaluationCriterion;
use App\Models\EvaluationSheet;
use App\Models\MonthlyEvaluation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveEvaluationSheetRequest extends FormRequest
{
    /**
     * Seuls les évaluateurs autorisés pour ce membre peuvent remplir une fiche, et jamais sur une note clôturée.
     */
    public function authorize(): bool
    {
        $evaluation = $this->evaluation();

        return ! $evaluation->is_locked
            && EvaluationSheet::groupsAvailableTo($this->user(), $evaluation->employee) !== [];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'evaluator_group' => ['required', 'string', Rule::in(EvaluationSheet::groupsAvailableTo($this->user(), $this->evaluation()->employee))],
            'scores' => ['nullable', 'array'],
            'scores.*.score' => ['nullable', 'integer', 'between:1,5'],
            'scores.*.observation' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Une note de 1, 2 ou 5 doit être justifiée par une observation datée.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                foreach ((array) $this->input('scores', []) as $criterionId => $entry) {
                    $score = (int) ($entry['score'] ?? 0);

                    if (in_array($score, EvaluationCriterion::SCORES_REQUIRING_OBSERVATION, true) && blank($entry['observation'] ?? null)) {
                        $validator->errors()->add(
                            "scores.{$criterionId}.observation",
                            'Une note de 1, 2 ou 5 doit être justifiée par une observation datée (un fait, un chiffre, un exemple).'
                        );
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'evaluator_group.in' => 'Vous ne pouvez pas évaluer ce membre au titre de ce groupe d\'évaluateurs.',
            'scores.*.score.between' => 'Chaque note doit être comprise entre 1 et 5 (ou « n.c. »).',
        ];
    }

    private function evaluation(): MonthlyEvaluation
    {
        return $this->route('evaluation');
    }
}
