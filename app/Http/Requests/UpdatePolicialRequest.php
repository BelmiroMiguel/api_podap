<?php

namespace App\Http\Requests;

use App\Models\Policial;
use App\Rules\ValidarTelefone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;

class UpdatePolicialRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Usuario|null $usuarioLogado */
        $usuarioLogado = Auth::user();

        // Verifica se é policial e se tem a permissão de gerir
        return $usuarioLogado instanceof Usuario
            && $usuarioLogado->tipoUsuario === 'POLICIAL'
            && $usuarioLogado->policial?->temPermissao('gerir_policiais');
    }

    public function rules(): array
    {
        $idUsuario = $this->route('id');
        $usuario = Usuario::findOrFail($idUsuario);
        $idPolicial = $usuario->policial->idPolicial;

        /** @var Usuario|null $usuarioLogado */
        $usuarioLogado = Auth::user();
        $idEsquadraLogado = $usuarioLogado->policial->idEsquadra ?? null;

        return [
            // Dados de Usuário (usamos 'sometimes' para permitir updates parciais)
            'nome' => 'sometimes|string|max:255',
            'identificacao' => [
                'sometimes',
                Rule::unique('tb_usuario', 'identificacao')->ignore($idUsuario, 'idUsuario')
            ],
            'tipoIdentificacao' => 'sometimes|in:BI,PASSAPORTE',
            'telefone' => ['sometimes', new ValidarTelefone],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('tb_usuario', 'email')->ignore($idUsuario, 'idUsuario')
            ],
            'foto' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',

            // Dados de Policial
            'idRole' => [
                'sometimes',
                Rule::exists('tb_role', 'idRole')->where(function ($query) use ($idEsquadraLogado) {
                    $query->where('idEsquadra', $idEsquadraLogado);
                }),
            ],
            'nip' => [
                'sometimes',
                Rule::unique('tb_policial', 'nip')->ignore($idPolicial, 'idPolicial')
            ],
            'patente' => 'sometimes|string'
        ];
    }

    public function messages(): array
    {
        return [
            'idRole.exists' => 'O grupo selecionado não pertence à sua esquadra.',
            'nip.unique' => 'Este NIP já está registado noutro policial.',
            'identificacao.unique' => 'Este documento já está registado.',
        ];
    }
}
