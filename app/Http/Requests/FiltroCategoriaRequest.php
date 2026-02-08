<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FiltroCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filtroTexto' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'idCategoria' => 'nullable|integer|exists:categorias,id'
        ];
    }

    public function messages(): array
    {
        return [
            'limit.integer' => 'O limite deve ser um número inteiro.',
            'limit.max' => 'O limite máximo permitido é 100.',
            'page.integer' => 'A página deve ser um número inteiro.',
            'idCategoria.integer' => 'O ID da categoria deve ser um número inteiro.',
            'idCategoria.exists' => 'A categoria selecionada não existe.',
        ];
    }
}
