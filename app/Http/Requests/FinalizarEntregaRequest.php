<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use App\Rules\ValidarTelefone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinalizarEntregaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        /** @var Usuario|null $usuario */
        $usuario = Auth::user();
        return [
            // No seu FormRequest

            'idOcorrencia' => [
                'required',
                'exists:tb_ocorrencia,idOcorrencia',
                function ($attribute, $value, $fail) use ($usuario) {
                    if (!$usuario) {
                        $fail('Usuário não autenticado.');
                        return;
                    }

                    // Carregar a ocorrência com a custódia para verificar a esquadra
                    $ocorrencia = \App\Models\Ocorrencia::with('custodia')->find($value);

                    if (!$ocorrencia) {
                        $fail('Ocorrência não encontrada.');
                        return;
                    }

                    // 1. O dono da ocorrência pode acessar
                    $isDono = $ocorrencia->idUsuario === $usuario->idUsuario;

                    // 2. Se for policial, verifica se a custódia atual está na esquadra dele
                    $isPolicialComAcesso = false;
                    if ($usuario->isPolicial()) {
                        $idEsquadraUsuario = $usuario->idEsquadra; // Usando o accessor que criamos acima

                        // Verifica se a ocorrência está custodiada na esquadra do policial
                        $isPolicialComAcesso = $ocorrencia->custodia &&
                            $ocorrencia->custodia->tipoDetentor === 'ESQUADRA' &&
                            $ocorrencia->custodia->idDetentor === $idEsquadraUsuario;
                    }

                    if (!$isDono && !$isPolicialComAcesso) {
                        $fail('O usuário não tem permissão para acessar esta ocorrência.');
                    }
                },
            ],

            'idUsuarioRecebedor' => 'required|exists:tb_usuario,idUsuario',
            'tokenConfirmacao' => 'nullable|string', // Token enviado ao dono para validar
            'descricaoEntrega' => 'required|string|max:1000',
            'fotos' => 'nullable|array|max:3',
            'fotos.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'idOcorrencia.required' => 'O campo idOcorrencia é obrigatório.',
            'idOcorrencia.exists' => 'O idOcorrencia fornecido não existe.',
            'idUsuarioRecebedor.required' => 'O campo idUsuarioRecebedor é obrigatório.',
            'idUsuarioRecebedor.exists' => 'O idUsuarioRecebedor fornecido não existe.',
            'tokenConfirmacao.string' => 'O token de confirmação deve ser uma string.',
            'descricaoEntrega.required' => 'O campo descricaoEntrega é obrigatório.',
            'descricaoEntrega.string' => 'O campo descricaoEntrega deve ser uma string.',
            'descricaoEntrega.max' => 'O campo descricaoEntrega não pode exceder 1000 caracteres.',
            'fotos.array' => 'O campo fotos deve ser um array.',
            'fotos.max' => 'Você pode enviar no máximo 3 fotos.',
            'fotos.*.image' => 'Cada foto deve ser uma imagem.',
            'fotos.*.mimes' => 'Cada foto deve estar no formato jpg, jpeg ou png.',
            'fotos.*.max' => 'Cada foto não pode exceder 2048 KB.',
        ];
    }
}
