<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class FiltroUsuarioRequest extends FormRequest
{
    /**
     * Determine se o utilizador está autorizado a fazer este pedido.
     * Normalmente restrito a administradores ou gestores.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Regras de validação para a filtragem de utilizadores.
     */
        public function rules(): array
        {
            return [
                'filtroTexto' => 'nullable|string', // Busca por nome, email ou identificação
                'tipoUsuario' => 'nullable|in:CIDADAO,POLICIAL,ADMIN',
                'tipoUsuarioIncluds' => 'nullable|array',
                'tipoUsuarioIncluds.*' => 'in:CIDADAO,POLICIAL,ADMIN',

                // Filtros específicos de Policial
                'nip' => 'nullable|string|max:20',
                'idEsquadra' => 'nullable|exists:tb_esquadra,idEsquadra',

                // Status e Datas
                'statusConta' => 'nullable|in:ATIVO,INATIVO,BLOQUEADO',
                'dataCriacaoInicio' => 'nullable|date',
                'dataCriacaoFim' => 'nullable|date|after_or_equal:dataCriacaoInicio',

                // Paginação e Ordenação
                'limit' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
                'ordem' => 'nullable|in:recente,antigo,nome_az,nome_za',
            ];
        }

    /**
     * Mensagens de erro personalizadas.
     */
    public function messages(): array
    {
        return [
            'tipoUsuario.in' => 'O tipo de usuário selecionado é inválido.',
            'tipoUsuarioIncluds.array' => 'Os tipos de usuário devem ser enviados como um array.',
            'statusConta.in' => 'O status da conta selecionado é inválido.',
            'idEsquadra.exists' => 'A esquadra selecionada não existe no sistema.',
            'dataCriacaoFim.after_or_equal' => 'A data final deve ser posterior ou igual à data inicial.',
            'limit.max' => 'Não é possível solicitar mais de 100 usuários por página.',
            'ordem.in' => 'A ordenação deve ser: recente, antigo, nome_az ou nome_za.',
            'page.min' => 'O número da página deve ser pelo menos 1.',
        ];
    }
}
