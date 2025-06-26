<?php

namespace App\Http\Requests\CafeManager;

use Illuminate\Foundation\Http\FormRequest;

class DayPartAddRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'meal_type' => 'required|string|max:32',
            'abbreviation' => 'required|string|max:2',
            'cafe_id' => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'meal_type.required' => __('general.day_part.meal_type_required'),
            'abbreviation.required' => __('general.day_part.abbreviation_required'),
            'cafe_id.required' => __('general.day_part.cafe_id_required'),
        ];
    }
}
