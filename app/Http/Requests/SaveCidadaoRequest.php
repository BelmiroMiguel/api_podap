<?php

namespace App\Http\Requests;

use App\Rules\ValidarTelefone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SaveCidadaoRequest extends FormRequest
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
            'nome' => 'required|string|max:255',
            'identificacao' => 'required|string|unique:tb_usuario,identificacao', // BI ou Passaporte
            'tipoIdentificacao' => 'required|in:BI,PASSAPORTE',
            'telefone' => ['required', 'unique:tb_usuario,telefone', new ValidarTelefone],
            'email' => 'required|email|unique:tb_usuario,email',
            'senha' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ]

        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'identificacao.required' => 'O campo identificação é obrigatório.',
            'identificacao.unique' => 'A identificação já está em uso.',
            'tipoIdentificacao.required' => 'O campo tipo de identificação é obrigatório.',
            'tipoIdentificacao.in' => 'O tipo de identificação deve ser BI ou PASSAPORTE.',
            'telefone.required' => 'O campo telefone é obrigatório.',
            'telefone.unique' => 'O número de telefone já está em uso.',
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'O email deve ser um endereço válido.',
            'email.unique' => 'O email já está em uso.',
            'senha.required' => 'O campo senha é obrigatório.',
            'senha' => 'A senha deve ter pelo menos 8 caracteres e incluir letras maiúsculas, números e símbolos.',
            'senha.confirmed' => 'A confirmação da senha não corresponde.',
        ];
    }
}
