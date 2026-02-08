<?php

namespace App\Console\Commands;

use App\Models\HistoricoMovimentacao;
use Illuminate\Console\Command;
use App\Models\TokenEntregaOcorrencia;
use App\Models\Ocorrencia;
use Illuminate\Support\Facades\DB;

class LimparTokensExpirados extends Command
{
    // O nome que usarás para chamar o comando manualmente
    protected $signature = 'tokens:limpar-expirados';

    protected $description = 'Remove tokens de entrega expirados e reseta status das ocorrências';

    public function handle()
    {
        DB::transaction(function () {
            // 1. Procurar tokens que já passaram da data de expiração
            $tokensExpirados = TokenEntregaOcorrencia::where('dataExpiracao', '<', now())->get();

            if ($tokensExpirados->isEmpty()) {
                $this->info('Nenhum token expirado encontrado.');
                return;
            }

            foreach ($tokensExpirados as $token) {
                // 2. Carregar a ocorrência com o policial associado ao usuário (entregador/dono)
                $ocorrencia = Ocorrencia::with(['usuario.policial'])
                    ->where('idOcorrencia', $token->idOcorrencia)
                    ->where('statusProcesso', 'AGUARDANDO_CONFIRMACAO')
                    ->first();

                if ($ocorrencia) {
                    // 3. Voltar o status para 'PROCURANDO'
                    $ocorrencia->update(['statusProcesso' => 'PROCURANDO']);

                    // 4. Gravar no histórico o motivo da expiração
                    HistoricoMovimentacao::create([
                        'idOcorrencia' => $ocorrencia->idOcorrencia,
                        'origemDescricao' => 'AGUARDANDO_CONFIRMACAO',
                        'destinoDescricao' => 'PROCURANDO',
                        'descricao' => 'Tempo de confirmação do recebimento do objeto expirado',
                        'idPolicialIntermediario' => $ocorrencia->usuario->policial->idPolicial ?? null,
                    ]);

                    // 5. Eliminar o token expirado
                    $token->delete();
                }
            }

            $this->info($tokensExpirados->count() . ' processos de entrega expirados foram resetados.');
        });
    }
}
