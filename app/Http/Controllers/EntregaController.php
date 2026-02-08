<?php

namespace App\Http\Controllers;

use App\Models\Ocorrencia;
use App\Models\EntregaFinal;
use App\Models\HistoricoMovimentacao;
use App\Models\CustodiaAtual;
use App\Http\Requests\FinalizarEntregaRequest;
use App\Models\TokenEntregaOcorrencia;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Mail\TokenEntregaOcorrenciaMail;
use Illuminate\Support\Facades\Mail;

class EntregaController extends Controller
{
    public function finalizarEntrega(FinalizarEntregaRequest $request)
    {
        try {
            /** @var Usuario $usuarioLogado */
            $usuarioLogado = Auth::user();

            $resultado = DB::transaction(function () use ($request, $usuarioLogado) {

                // 1. Buscar Ocorrência e verificar se já não foi entregue
                $ocorrencia = Ocorrencia::findOrFail($request->idOcorrencia);

                if ($ocorrencia->statusProcesso === 'AGUARDANDO_CONFIRMACAO') {
                    throw new \Exception("Processo de devolução já está em andamento para esta ocorrência.");
                }

                if ($ocorrencia->statusProcesso === 'ENTREGUE') {
                    throw new \Exception("Este item já foi devolvido.");
                }

                // 2. Upload das fotos da entrega (comprovativo)
                $caminhosFotos = [];
                if ($request->hasFile('fotos')) {
                    foreach ($request->file('fotos') as $foto) {
                        $caminhosFotos[] = $foto->store('fotos-entregas', 'public');
                    }
                }

                // 3. Criar o registro na tb_entrega_final
                // Lembre-se: PK na sua migration é idEntregaFinl
                $entrega = EntregaFinal::create([
                    'idOcorrencia' => $ocorrencia->idOcorrencia,
                    'idUsuarioEntregador' => $usuarioLogado->idUsuario,
                    'idUsuarioRecebedor' => $request->idUsuarioRecebedor,
                    'tokenConfirmacao' => $request->tokenConfirmacao,
                    'descricaoEntrega' => $request->descricaoEntrega,
                    'fotosEntrega' => $caminhosFotos,
                ]);

                // 4. Atualizar Status da Ocorrência para ENTREGUE
                $ocorrencia->update(['statusProcesso' => 'AGUARDANDO_CONFIRMACAO']);

                // 5. Remover/Limpar a Custódia Atual (O item não está mais na esquadra)
                CustodiaAtual::where('idOcorrencia', $ocorrencia->idOcorrencia)->delete();

                // Gerar Token único e salvar na tabela isolada
                $token = Str::random(32);
                TokenEntregaOcorrencia::updateOrCreate(
                    ['idOcorrencia' => $ocorrencia->idOcorrencia],
                    [
                        'token' => $token,
                        'idUsuarioRecebedor' => $request->idUsuarioRecebedor,
                        'dataExpiracao' => now()->addHours(48), // Define 48h a partir de agora
                    ]
                );

                // 6. Registrar o último passo no Histórico
                HistoricoMovimentacao::create([
                    'idOcorrencia' => $ocorrencia->idOcorrencia,
                    'origemDescricao' => 'PROCURANDO',
                    'destinoDescricao' => 'AGUARDANDO_CONFIRMACAO',
                    'descricao' => 'Processamento de entrega ao dono legítimo via confirmação de token.',
                    'idPolicialIntermediario' => $usuarioLogado->policial?->idPolicial,
                ]);

                $usuarioRecebidor = Usuario::findOrFail($request->idUsuarioRecebedor);

                Mail::to($usuarioRecebidor->email)->send(new TokenEntregaOcorrenciaMail($usuarioRecebidor, $ocorrencia, $token));

                return $entrega->load(['ocorrencia.item', 'recebedor']);
            });

            return response()->json([
                'message' => 'Entrega finalizada com sucesso. Ocorrência encerrada.',
                'body' => $resultado
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function confirmarRecebimento(Request $request, $token)
    {
        try {
            //  Verificar se o token existe
            $registroToken = TokenEntregaOcorrencia::where('token', '=', $token)->first();

            if (!$registroToken) {
                return view('confirmacao_ocorrencia', [
                    'status' => 2,
                    'token' => $registroToken
                ]);
            }

            $ocorrencia = Ocorrencia::with(['usuario.policial'])->find($registroToken->idOcorrencia);

            //  Finalizar a Ocorrência definitivamente
            $ocorrencia->update(['statusProcesso' => 'ENTREGUE']);

            CustodiaAtual::where('idOcorrencia', $ocorrencia->idOcorrencia)
                ->update([
                    'tipoDetentor' =>  'CIDADAO',
                    'idDetentor' =>  $registroToken->idUsuarioRecebedor,
                    'dataCadastro' =>  now(),
                ]);

            HistoricoMovimentacao::create([
                'idOcorrencia' => $ocorrencia->idOcorrencia,
                'origemDescricao' => 'AGUARDANDO_CONFIRMACAO',
                'destinoDescricao' => 'ENTREGUE',
                'descricao' => 'Confirmado recebimento do item pelo Dono',
                'idPolicialIntermediario' => $ocorrencia->usuario->policial->idPolicial ?? null,
            ]);

            // Remover o token para não ser usado de novo
            $registroToken->delete();

            return view('confirmacao_ocorrencia', [
                'status' => 1,
                'token' => $registroToken
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
    }


    public function negarRecebimento(Request $request, $token)
    {
        try {
            // 🔍 Verificar se o token existe
            $registroToken = TokenEntregaOcorrencia::where('token', $token)->first();

            if (!$registroToken) {
                return view('confirmacao_ocorrencia', [
                    'status' => 2, // token inválido
                    'token' => $registroToken
                ]);
            }

            $ocorrencia = Ocorrencia::with(['usuario.policial'])->find($registroToken->idOcorrencia);

            if (!$ocorrencia) {
                return view('confirmacao_ocorrencia', [
                    'status' => 2, // ocorrência não encontrada
                    'token' => $registroToken
                ]);
            }

            // 🔄 Atualizar status do processo
            $ocorrencia->update([
                'statusProcesso' => 'PROCURANDO'
            ]);

            // 🗂️ Criar histórico
            HistoricoMovimentacao::create([
                'idOcorrencia' => $ocorrencia->idOcorrencia,
                'origemDescricao' => 'AGUARDANDO_CONFIRMACAO',
                'destinoDescricao' => 'PROCURANDO',
                'descricao' => 'O Suposto Dono do item negou o recebimento',
                'idPolicialIntermediario' => $ocorrencia->usuario->policial->idPolicial ?? null,
                'dataMovimentacao' => now(),
            ]);

            // ❌ Remover o token
            $registroToken->delete();

            return view('confirmacao_ocorrencia', [
                'status' => 3, // negação registrada
                'token' => $registroToken
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
    }


    public function getFoto($filename)
    {
        $path = "fotos-entregas/{$filename}";

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->file(storage_path("app/public/{$path}"));
    }
}
