<?php

namespace App\Http\Requests;

use App\Rules\ValidarIMEI;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaveItemOcorrenciaRequest extends FormRequest
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
        return [
            // --- Campos da tb_item ---
            'idCategoria' => 'required|exists:tb_categoria,idCategoria',
            'titulo'      => 'required|string|max:200',
            'descricao'   => 'required|string',
            'detalhe'     => 'required|array', // O JSON de detalhes técnicos
            'fotosItem'   => 'required|array|min:1', // Pelo menos uma foto (conforme o teu gosto)

            // --- Campos da tb_ocorrencia ---
            'tipoOcorrencia' => 'required|in:PERDIDO,ACHADO',
            'dataEvento'     => 'required|date|before_or_equal:now',
            'localEvento'    => 'required|string|max:255',
            'statusProcesso' => 'required|in:PROCURANDO,COM_CIDADAO,NA_POLICIA',

            // --- Exemplo de validação condicional dentro do JSON ---
            'detalhe.imei'   => ['required_if:idCategoria,2', new ValidarIMEI()],
        ];
    }

    // Personalizar as mensagens de erro (Opcional, mas bom para o utilizador)
    public function messages(): array
    {
        return [
            'fotosItem.required' => 'É obrigatório carregar pelo menos uma fotografia do objeto.',
            'idCategoria.exists' => 'A categoria selecionada não é válida.',
        ];
    }
}
