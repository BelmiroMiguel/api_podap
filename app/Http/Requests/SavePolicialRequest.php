<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use App\Rules\ValidarTelefone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SavePolicialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Usuario|null $usuarioLogado */
        $usuarioLogado = Auth::user();

        // 1. Verificar se está logado
        if (!$usuarioLogado) {
            return false;
        }

        return $usuarioLogado instanceof Usuario
            && $usuarioLogado->tipoUsuario === 'POLICIAL'
            && $usuarioLogado->policial?->temPermissao('gerir_policiais');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Usuario|null $usuarioLogado */
        $usuarioLogado = Auth::user();
        $idEsquadra = $usuarioLogado->policial->idEsquadra ?? null;

        return [
            // Dados de Usuário
            'nome' => 'required|string|max:255',
            'identificacao' => 'required|string|unique:tb_usuario,identificacao',
            'tipoIdentificacao' => 'required|in:BI,PASSAPORTE',
            'telefone' => ['required', new ValidarTelefone],
            'email' => 'required|email|unique:tb_usuario,email',
            'senha' => 'required|min:6',

            // Dados Específicos de Policial
            /*  'idRole' => [
                'required',
                // Valida se o idRole existe E se pertence à esquadra do logado
                Rule::exists('tb_role', 'idRole')->where(function ($query) use ($idEsquadra) {
                    $query->where('idEsquadra', $idEsquadra);
                }),
            ],
            'patente' => 'required|string' */
            'nip' => 'required|string|unique:tb_policial,nip',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'identificacao.required' => 'O campo identificação é obrigatório.',
            'identificacao.unique' => 'A identificação já está em uso.',
            'tipoIdentificacao.required' => 'O campo tipo de identificação é obrigatório.',
            'tipoIdentificacao.in' => 'O tipo de identificação deve ser BI ou PASSAPORTE.',
            'telefone.required' => 'O campo telefone é obrigatório.',
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'O email deve ser um endereço válido.',
            'email.unique' => 'O email já está em uso.',
            'senha.required' => 'O campo senha é obrigatório.',
            'senha.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'idRole.required' => 'O campo idRole é obrigatório.',
            'idRole.exists' => 'O idRole deve pertencer à sua esquadra.',
            'idEsquadra.required' => 'O campo idEsquadra é obrigatório.',
            'idEsquadra.exists' => 'O idEsquadra deve existir na tabela de esquadras.',
            'nip.required' => 'O campo NIP é obrigatório.',
            'nip.unique' => 'O NIP já está em uso.',
            'patente.required' => 'O campo patente é obrigatório.',
        ];
    }
}
