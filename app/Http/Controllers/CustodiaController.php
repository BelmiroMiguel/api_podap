<?php

namespace App\Http\Controllers;

use App\Models\Ocorrencia;
use App\Models\CustodiaAtual;
use App\Models\HistoricoMovimentacao;
use App\Http\Requests\MoveCustodiaRequest;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CustodiaController extends Controller
{
    public function receberItemNaEsquadra(MoveCustodiaRequest $request)
    {
        try {
            $resultado = DB::transaction(function () use ($request) {
                /** @var Usuario $usuarioLogado */
                $usuarioLogado = Auth::user();
                $policial = $usuarioLogado->policial;

                // 1. Buscar a ocorrência e a custódia atual
                $ocorrencia = Ocorrencia::with('custodia')->findOrFail($request->idOcorrencia);
                $custodiaAntiga = $ocorrencia->custodia;

                // Definir descrição da origem para o histórico
                $origemTxt = $custodiaAntiga->tipoDetentor === 'CIDADAO'
                    ? "Cidadão: " . $custodiaAntiga->detentor->nome
                    : "Setor: " . $custodiaAntiga->armazem->descricaoSetor;

                // 2. Atualizar o Status da Ocorrência para 'NA_POLICIA'
                $ocorrencia->update(['statusProcesso' => 'NA_POLICIA']);

                // 3. Atualizar a Custódia Atual
                $custodiaAntiga->update([
                    'tipoDetentor' => 'ESQUADRA',
                    'idDetentor'   => $policial->idEsquadra,
                    'idArmazem'    => $request->idArmazem,
                ]);

                // 4. Registrar no Histórico de Movimentação
                $movimentacao = HistoricoMovimentacao::create([
                    'idOcorrencia' => $ocorrencia->idOcorrencia,
                    'origemDescricao' => $origemTxt,
                    'destinoDescricao' => "Esquadra (Armazém ID: {$request->idArmazem}) - " . ($request->observacao ?? 'Recebimento de balcão'),
                    'idPolicialIntermediario' => $policial->idPolicial,
                ]);

                return $ocorrencia->load(['item', 'custodia.armazem', 'historico']);
            });

            return response()->json([
                'message' => 'Custódia transferida para a esquadra com sucesso',
                'body' => $resultado
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao mover custódia',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
