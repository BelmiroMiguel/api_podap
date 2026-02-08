<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;

class SavePolicialPermissaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::user();

        // Apenas policiais com permissão específica podem gerir permissões de outros
        return $usuario instanceof Usuario
            && $usuario->tipoUsuario === 'POLICIAL'
            && $usuario->policial?->temPermissao('gerir_permissoes_individuais');
    }

    public function rules(): array
    {
        return [
            'idPolicial' => 'required|exists:tb_policial,idPolicial',
            'permissoes' => 'required|array',
            'permissoes.*.idPermissao' => 'required|exists:tb_permissao,idPermissao',
            'permissoes.*.permitido' => 'required|boolean', // true = concede, false = revoga
        ];
    }

    public function messages(): array
    {
        return [
            'permissoes.*.idPermissao.exists' => 'Uma das permissões selecionadas é inválida.',
            'permissoes.*.permitido.boolean' => 'O valor de permitido deve ser verdadeiro ou falso.',
        ];
    }
}
