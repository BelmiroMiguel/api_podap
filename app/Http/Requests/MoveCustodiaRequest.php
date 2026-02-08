<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MoveCustodiaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->tipoUsuario === 'POLICIAL';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'idOcorrencia' => 'required|exists:tb_ocorrencia,idOcorrencia',
            'idArmazem' => 'required|exists:tb_armazem,idArmazem',
            'observacao' => 'nullable|string|max:500',
        ];
    }

    /**
     * Customize the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'idOcorrencia.required' => 'O campo idOcorrencia é obrigatório.',
            'idOcorrencia.exists' => 'O idOcorrencia fornecido não existe.',
            'idArmazem.required' => 'O campo idArmazem é obrigatório.',
            'idArmazem.exists' => 'O idArmazem fornecido não existe.',
            'observacao.string' => 'O campo observacao deve ser uma string.',
            'observacao.max' => 'O campo observacao não pode ter mais de 500 caracteres.',
        ];
    }
}
