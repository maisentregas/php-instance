<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InsurePersonRequest extends FormRequest
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
            'person' => 'required',
            'person.document' => 'required|string',
            'person.first_name' => 'required|string',
            'person.last_name' => 'required|string',
            'person.birthday' => 'required|string',
            'person.email' => 'required|string|email',
            'person.phone' => 'string'
        ];
    }
}
