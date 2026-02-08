<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
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
            'login' => 'required|string',
            'senha' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'O campo de login é obrigatório.',
            'senha.required' => 'O campo de senha é obrigatório.',
        ];
    }
}
