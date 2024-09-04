<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class widgetRequest extends FormRequest
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
            'end_date' => 'required|date',
            'campus_flag' => 'required|integer',
            'type' => 'required|string',
            'login_as' => 'required|string',
        ];
    }
}
