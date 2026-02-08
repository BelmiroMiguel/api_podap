<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use App\Rules\ValidarTelefone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCidadaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Usuario|null $usuarioLogado */
        $usuarioLogado = Auth::user();

        // 1. Verificar se está logado
        if (!$usuarioLogado) {
            return false;
        }

        return $usuarioLogado->tipoUsuario === 'CIDADAO' || ($usuarioLogado->tipoUsuario === 'POLICIAL'
            && $usuarioLogado->policial?->temPermissao('gerir_policiais'));
    }

    public function rules(): array
    {
        /** @var Usuario|null $usuarioLogado */
        $idUsuario = Auth::user()->idUsuario;

        return [
            'nome' => 'sometimes|string|max:255',
            'identificacao' => "sometimes|string|unique:tb_usuario,identificacao,{$idUsuario},idUsuario",
            'tipoIdentificacao' => 'sometimes|in:BI,PASSAPORTE',
            'telefone' => ['sometimes', new ValidarTelefone],
            'email' => "sometimes|email|unique:tb_usuario,email,{$idUsuario},idUsuario",
            'foto' => 'sometimes|image|mimes:jpg,jpeg,png|max:8192', // Max 8MB
        ];
    }

    public function messages(): array
    {
        return [
            'foto.image' => 'O arquivo deve ser uma imagem.',
            'foto.max' => 'A foto não pode ter mais de 8MB.',
            'identificacao.unique' => 'Esta identificação já está registada.',
            'email.unique' => 'Este email já está registado.',
        ];
    }
}
