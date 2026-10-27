<?php

namespace App\Http\Requests\Rh;

use App\Models\MonthlyEvaluation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ContestEvaluationRequest extends FormRequest
{
    /**
     * Seul le membre évalué peut contester sa note, par écrit, dans les 48 heures qui suivent sa clôture.
     */
    public function authorize(): bool
    {
        /** @var MonthlyEvaluation $evaluation */
        $evaluation = $this->route('evaluation');

        return $evaluation->employee->user_id === $this->user()->id
            && $evaluation->canBeContested();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contestation' => ['required', 'string', 'min:10', 'max:3000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contestation.required' => 'Le motif de la contestation est obligatoire.',
            'contestation.min' => 'La contestation doit contenir au moins 10 caractères.',
        ];
    }
}
