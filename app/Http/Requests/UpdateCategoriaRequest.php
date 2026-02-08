<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        // Pega o ID da categoria da rota para ignorar no unique
        $idCategoria = $this->route('categoria');

        return [
            'descricao' => "required|string|max:255|unique:tb_categoria,descricao,{$idCategoria},idCategoria",
        ];
    }

    public function messages(): array
    {
        return [
            'descricao.required' => 'O campo descrição é obrigatório.',
            'descricao.unique' => 'Esta descrição já está sendo usada por outra categoria.',
        ];
    }
}
