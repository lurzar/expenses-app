<?php

namespace App\Modules\Planning\Requests;

use App\Modules\Planning\Support\PlanningCalculator;
use DomainException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

class PlanningStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()?->getAuthIdentifier();

        return [
            'month' => [
                'required',
                'integer',
                'between:1,12',
                Rule::unique('plannings', 'month')
                    ->where(fn ($query) => $query
                        ->where('user_id', $userId)
                        ->where('year', $this->integer('year'))
                        ->whereNull('deleted_at')),
            ],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'salary' => ['required', 'string', 'regex:/^(?:0|[1-9]\d{0,11})(?:\.\d{1,2})?$/'],
            'saving_rate' => ['required', 'string', 'numeric', 'min:0', 'max:100', 'regex:/^(?:0|[1-9]\d?|100)(?:\.\d{1,2})?$/'],
            'totals' => ['exclude'],
            'calculation' => ['exclude'],
            'savings_values' => ['present', 'array', 'max:50'],
            'savings_values.*.item' => ['required', 'string', 'max:120'],
            'savings_values.*.amount' => ['required', 'string', 'regex:/^(?:0|[1-9]\d{0,11})(?:\.\d{1,2})?$/'],
            'commitments_values' => ['present', 'array', 'max:50'],
            'commitments_values.*.item' => ['required', 'string', 'max:120'],
            'commitments_values.*.amount' => ['required', 'string', 'regex:/^(?:0|[1-9]\d{0,11})(?:\.\d{1,2})?$/'],
            'others_values' => ['present', 'array', 'max:50'],
            'others_values.*.item' => ['required', 'string', 'max:120'],
            'others_values.*.amount' => ['required', 'string', 'regex:/^(?:0|[1-9]\d{0,11})(?:\.\d{1,2})?$/'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            try {
                (new PlanningCalculator)->calculate(
                    income: $this->string('salary')->toString(),
                    savingRate: $this->string('saving_rate')->toString(),
                    sections: [
                        'savings' => $this->input('savings_values', []),
                        'commitments' => $this->input('commitments_values', []),
                        'others' => $this->input('others_values', []),
                    ],
                );
            } catch (DomainException|InvalidArgumentException) {
                $validator->errors()->add('calculation', 'The allocated amount cannot exceed monthly income.');
            }
        });
    }
}
