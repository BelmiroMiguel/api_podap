<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaveOcorrenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            // Dados do Item
            'idCategoria' => 'required|exists:tb_categoria,idCategoria',
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'detalhe' => 'nullable|array', // ex: ["cor" => "preto", "marca" => "apple"]
            'fotos' => 'nullable|array|max:5', // Limite de 5 fotos
            'fotos.*' => 'image|mimes:jpg,jpeg,png|max:2048',

            // Dados da Ocorrência
            'tipoOcorrencia' => 'required|in:PERDIDO,ACHADO',
            'dataEvento' => 'required|date|before_or_equal:now',
            'localEvento' => 'required|string|max:255',

            // Dados de Custódia (Opcionais se for o cidadão a reportar perda)
            'idArmazem' => 'nullable|exists:tb_armazem,idArmazem',
        ];
    }

    public function messages(): array
    {
        return [
            'fotos.*.image' => 'Cada arquivo deve ser uma imagem válida.',
            'dataEvento.before_or_equal' => 'A data do evento não pode ser no futuro.',
            'dataEvento.date' => 'A data do evento inválida.',

        ];
    }
}
