<?php

namespace App\Http\Controllers;

use App\Models\Policial;
use App\Models\Permissao;
use App\Http\Requests\SavePolicialPermissaoRequest;
use Illuminate\Support\Facades\DB;

class PermissaoController extends Controller
{
    /**
     * Sincroniza as permissões individuais de um policial
     */
    public function sincronizarPermissoesIndividuais(SavePolicialPermissaoRequest $request)
    {
        try {
            $resultado = DB::transaction(function () use ($request) {
                $policial = Policial::findOrFail($request->idPolicial);

                // Prepara os dados para o sync
                // O formato deve ser [idPermissao => ['permitido' => true/false]]
                $permissoesParaSincronizar = [];
                foreach ($request->permissoes as $item) {
                    $permissoesParaSincronizar[$item['idPermissao']] = [
                        'permitido' => $item['permitido']
                    ];
                }

                // O sync() remove o que não estiver no array e atualiza/insere o que estiver
                $policial->permissoesCustomizadas()->sync($permissoesParaSincronizar);

                return $policial->load('permissoesCustomizadas');
            });

            return response()->json([
                'message' => 'Permissões individuais atualizadas com sucesso',
                'body' => $resultado
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao processar permissões',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Lista todas as permissões do catálogo (para preencher o checkbox no front)
     */
    public function listarCatalogoPermissoes()
    {
        try {
            $permissoes = Permissao::all();
            return response()->json(['body' => $permissoes], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
