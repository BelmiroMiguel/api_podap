<?php

namespace App\Http\Controllers;

use App\Models\Ocorrencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    public function buscarMatches($idOcorrencia)
    {
        try {
            // 1. Detalhes do Item Alvo
            $alvo = Ocorrencia::with('item.categoria')->findOrFail($idOcorrencia);
            $itemAlvo = $alvo->item;
            $detalhesAlvo = $itemAlvo->detalhe ?? []; // Array vindo do Cast JSON

            $tipoBusca = ($alvo->tipoOcorrencia === 'PERDIDO') ? 'ACHADO' : 'PERDIDO';

            // 2. Query Base
            $query = Ocorrencia::query()
                ->select('tb_ocorrencia.*')
                ->join('tb_item', 'tb_ocorrencia.idItem', '=', 'tb_item.idItem')
                ->with(['item.categoria', 'custodia.armazem'])
                ->where('tb_ocorrencia.idOcorrencia', '!=', $idOcorrencia)
                ->where('tb_ocorrencia.tipoOcorrencia', $tipoBusca)
                ->where('tb_ocorrencia.statusProcesso', '!=', 'ENTREGUE')
                ->where('tb_item.idCategoria', $itemAlvo->idCategoria);

            // 3. Cruzamento de Atributos JSON (Filtro Dinâmico)
            // Se o item alvo tem "cor", "marca", "modelo" no JSON, procuramos por coincidências
            $query->where(function ($q) use ($detalhesAlvo, $itemAlvo) {
                // Primeiro: Tenta achar por similaridade de texto no Título (como antes)
                $termos = explode(' ', $itemAlvo->titulo);
                foreach ($termos as $termo) {
                    if (strlen($termo) > 2) {
                        $q->orWhere('tb_item.titulo', 'like', "%{$termo}%");
                    }
                }

                // Segundo: Busca Coincidência Exata nos campos JSON
                // Ex: onde detalhe->'cor' = 'Azul'
                foreach ($detalhesAlvo as $chave => $valor) {
                    if (!empty($valor)) {
                        $q->orWhere("tb_item.detalhe->{$chave}", 'like', "%{$valor}%");
                    }
                }
            });

            $matches = $query->limit(20)->get();

            // 4. Sistema de Pontuação (Score) para Ranking
            $resultado = $matches->map(function ($match) use ($detalhesAlvo, $itemAlvo) {
                $score = 0;
                $detalhesCandidato = $match->item->detalhe ?? [];

                // Peso 5: Cada campo JSON que coincide exatamente (Marca, Modelo, Cor...)
                foreach ($detalhesAlvo as $chave => $valor) {
                    if (
                        isset($detalhesCandidato[$chave]) &&
                        strtolower($detalhesCandidato[$chave]) === strtolower($valor)
                    ) {
                        $score += 5;
                    }
                }

                // Peso 1: Palavras soltas encontradas no Título/Descrição
                $termosAlvo = explode(' ', strtolower($itemAlvo->titulo));
                $textoCandidato = strtolower($match->item->titulo . ' ' . $match->item->descricao);
                foreach ($termosAlvo as $termo) {
                    if (strlen($termo) > 2 && str_contains($textoCandidato, $termo)) {
                        $score += 1;
                    }
                }

                $match->score_match = $score;
                return $match;
            })
                ->filter(fn($m) => $m->score_match > 0) // Remove o que não tem nenhuma relação
                ->sortByDesc('score_match')
                ->values();

            return response()->json([
                'message' => 'Análise de similaridade concluída',
                'body' => [
                    'item_analisado' => [
                        'titulo' => $itemAlvo->titulo,
                        'detalhes' => $detalhesAlvo
                    ],
                    'matches' => $resultado
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
