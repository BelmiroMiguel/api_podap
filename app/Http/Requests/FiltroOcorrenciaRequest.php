<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class FiltroOcorrenciaRequest extends FormRequest
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
            'filtroTexto' => 'nullable|string', // Busca por título ou descrição
            'idCategoria' => 'nullable|exists:tb_categoria,idCategoria',
            'idCategoriaIncluds' => 'nullable|array',
            'idCategoriaIncluds.*' => 'exists:tb_categoria,idCategoria',
            'tipoOcorrencia' => 'nullable|in:PERDIDO,ACHADO',
            'tipoOcorrenciaIncluds' => 'nullable|array',
            'tipoOcorrenciaIncluds.*' => 'in:PERDIDO,ACHADO',
            'statusProcesso' => 'nullable|in:PROCURANDO,COM_CIDADAO,NA_POLICIA,ENTREGUE,CANCELADO',
            'dataInicio' => 'nullable|date',
            'dataFim' => 'nullable|date|after_or_equal:dataInicio',
            'limit' => 'nullable|integer|min:1|max:100', // Paginação dinâmica
            'page' => 'nullable|integer|min:1', // Valida que a página deve ser um número positivo

            // Novos Filtros
            'idUsuarioCadastro' => 'nullable|exists:tb_usuario,idUsuario',
            'apenasEsquadra' => 'nullable|boolean', // Filtro booleano
            'jaDevolvidos' => 'nullable|boolean',   // Filtro booleano
            'ordem' => 'nullable|in:recente,antigo,az,za', // Tipos de ordenação
        ];
    }

    /**
     * Customize the validation messages for the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'idCategoria.exists' => 'A categoria selecionada não é válida.',
            'tipoOcorrencia.in' => 'O tipo de ocorrência deve ser PERDIDO ou ACHADO.',
            'statusProcesso.in' => 'O status do processo é inválido.',
            'dataFim.after_or_equal' => 'A data de fim deve ser igual ou posterior à data de início.',
            'limit.integer' => 'O número de itens por página deve ser um número inteiro.',
            'limit.min' => 'O número de itens por página deve ser no mínimo 1.',
            'limit.max' => 'O número de itens por página não pode exceder 100.',
            'page.integer' => 'A página deve ser um número inteiro.',
            'page.min' => 'A página deve ser no mínimo 1.',
            'idUsuarioCadastro.exists' => 'O usuário selecionado não é válido.',
            'apenasEsquadra.boolean' => 'O filtro "apenas esquadra" deve ser verdadeiro ou falso.',
            'jaDevolvidos.boolean' => 'O filtro "já devolvidos" deve ser verdadeiro ou falso.',
            'ordem.in' => 'A ordem deve ser recente, antigo, az ou za.',
            'filtroTexto.string' => 'O filtro de texto deve ser uma string.',
            'idCategoriaIncluds.array' => 'As categorias incluídas devem estar em um array.',
            'idCategoriaIncluds.*.exists' => 'Uma ou mais categorias incluídas não são válidas.',
            'tipoOcorrenciaIncluds.array' => 'Os tipos de ocorrência incluídos devem estar em um array.',
            'tipoOcorrenciaIncluds.*.in' => 'Um ou mais tipos de ocorrência incluídos são inválidos.',
            'dataInicio.date' => 'A data de início deve ser uma data válida.',
            'dataFim.date' => 'A data de fim deve ser uma data válida.',
        ];
    }
}
