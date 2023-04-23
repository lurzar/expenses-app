<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'month' => 'required|string',
            'year' => 'required|string',
            'salary' => 'required|string',
            'saving_rate' => 'required|string',
            'totals' => 'array',
            'savings_values' => 'array',
            'savings_values.*.item' => 'required|string',
            'savings_values.*.amount' => 'required|string',
            'commitments_values' => 'array',
            'commitments_values.*.item' => 'required|string',
            'commitments_values.*.amount' => 'required|string',
            'others_values' => 'array',
            'others_values.*.item' => 'required|string',
            'others_values.*.amount' => 'required|string',
        ];
    }
}
