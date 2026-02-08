<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaveCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ajuste a lógica de autorização conforme sua necessidade (ex: apenas admin)
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'descricao' => 'required|string|max:255|unique:tb_categoria,descricao',
        ];
    }

    public function messages(): array
    {
        return [
            'descricao.required' => 'O campo descrição é obrigatório.',
            'descricao.unique' => 'Esta categoria já existe.',
            'descricao.max' => 'A descrição não pode ter mais de 255 caracteres.',
        ];
    }
}
