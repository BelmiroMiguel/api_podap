<?php

namespace App\Http\Controllers;

use App\Http\Requests\FiltroOcorrenciaRequest;
use App\Http\Requests\SaveItemOcorrenciaRequest;
use App\Http\Requests\SaveOcorrenciaRequest;
use App\Models\CustodiaAtual;
use App\Models\HistoricoMovimentacao;
use App\Models\Item;
use App\Models\Ocorrencia;
use App\Models\TokenEntregaOcorrencia;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OcorrenciaController extends Controller
{
    public function getOcorrencias(FiltroOcorrenciaRequest $request)
    {
        try {
            /** @var Usuario $usuarioLogado */
            $usuarioLogado = Auth::user();

            // Define a quantidade de itens por página (padrão 15)
            $limit = $request->get('limit', 15);

            // Inicializamos a Query.
            // Usamos select('tb_ocorrencia.*') para evitar colisão de IDs após o Join.
            // O Join com tb_item é necessário para podermos ordenar pelo título do item.
            $query = Ocorrencia::query()
                ->select('tb_ocorrencia.*')
                ->join('tb_item', 'tb_ocorrencia.idItem', '=', 'tb_item.idItem')
                ->join('tb_usuario', 'tb_ocorrencia.idUsuario', '=', 'tb_usuario.idUsuario')
                ->with(['item.categoria', 'usuario.policial.esquadra', 'custodia.armazem']);

            // --- FILTROS DE IDENTIFICAÇÃO ---

            $query->where('tb_ocorrencia.eliminado',  false);

            // Filtra ocorrências cadastradas por um usuário específico
            $query->when($request->idUsuarioCadastro, function ($q, $id) {
                $q->where('tb_ocorrencia.idUsuario', $id);
            });

            // --- FILTROS DO ITEM (Via Relacionamento ou Join) ---

            // Filtro de busca textual (Título ou Descrição do Item)
            $query->when($request->filtroTexto, function ($q, $filtroTexto) {
                $q->where(function ($qr) use ($filtroTexto) {
                    $qr->where('tb_item.titulo', 'like', "%{$filtroTexto}%")
                        ->orWhere('tb_item.descricao', 'like', "%{$filtroTexto}%")
                        ->orWhere('tb_usuario.nome', 'like', "%{$filtroTexto}%")
                        ->orWhere('tb_usuario.telefone', 'like', "%{$filtroTexto}%")
                        ->orWhere('tb_usuario.email', 'like', "%{$filtroTexto}%");
                });
            });

            // Filtro por Categoria do Item
            $query->when($request->idCategoria, function ($q, $idCategoria) {
                $q->where('tb_item.idCategoria', $idCategoria);
            });

            $query->when($request->idCategoriaIncluds, function ($q) use ($request) {
                $q->whereIn('tb_item.idCategoria', $request->idCategoriaIncluds);
            });

            // --- FILTROS DA OCORRÊNCIA ---

            // Filtro por Tipo (PERDIDO ou ACHADO)
            $query->when($request->tipoOcorrencia, function ($q, $tipo) {
                $q->where('tb_ocorrencia.tipoOcorrencia', $tipo);
            });

            $query->when($request->tipoOcorrenciaIncluds, function ($q) use ($request) {
                $q->whereIn('tb_ocorrencia.tipoOcorrencia', $request->tipoOcorrenciaIncluds);
            });


            // Filtro pelo Status atual do processo
            $query->when($request->statusProcesso, function ($q, $status) {
                $q->where('tb_ocorrencia.statusProcesso', $status);
            });

            // Filtro por Intervalo de Datas do Evento (Quando o objeto foi perdido/achado)
            $query->when($request->dataInicio, function ($q, $data) {
                $q->whereDate('tb_ocorrencia.dataEvento', '>=', $data);
            });

            $query->when($request->dataFim, function ($q, $data) {
                $q->whereDate('tb_ocorrencia.dataEvento', '<=', $data);
            });

            // --- FILTROS DE LÓGICA DE NEGÓCIO ---

            // Filtro "Apenas Minha Esquadra": Restringe a busca à esquadra do policial logado
            // Só tem efeito se o usuário for do tipo POLICIAL
            if ($request->boolean('apenasEsquadra') && $usuarioLogado != null && $usuarioLogado->tipoUsuario === 'POLICIAL') {
                $idEsquadra = $usuarioLogado->policial->idEsquadra;

                $query->whereHas('custodia', function ($q) use ($idEsquadra) {
                    $q->where('tipoDetentor', 'ESQUADRA')
                        ->where('idDetentor', $idEsquadra);
                });
            }

            // Filtro para mostrar apenas itens já entregues (Devolvidos) ou apenas os pendentes
            $query->when($request->has('jaDevolvidos'), function ($q) use ($request) {
                if ($request->boolean('jaDevolvidos')) {
                    $q->where('tb_ocorrencia.statusProcesso', 'ENTREGUE');
                } else {
                    $q->where('tb_ocorrencia.statusProcesso', '!=', 'ENTREGUE');
                }
            });

            // --- ORDENAÇÃO ---

            // Define a ordem dos resultados com base no parâmetro 'ordem'
            switch ($request->ordem) {
                case 'az':
                    $query->orderBy('tb_item.titulo', 'asc');
                    break;
                case 'za':
                    $query->orderBy('tb_item.titulo', 'desc');
                    break;
                case 'antigo':
                    $query->orderBy('tb_ocorrencia.dataCadastro', 'asc');
                    break;
                case 'recente':
                default:
                    $query->orderBy('tb_ocorrencia.dataCadastro', 'desc');
                    break;
            }

            // Executa a paginação
            $resultado = $query->paginate($limit);

            return response()->json([
                'message' => 'Listagem recuperada com sucesso',
                'body' => $resultado->items(), // Retorna apenas a lista de objetos (T)
                'paginacao' => [
                    'page' => $resultado->currentPage(),
                    'totalPages' => $resultado->lastPage(),
                    'limit' => $resultado->perPage(),
                    'totalItems' => $resultado->total(),
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao listar ocorrências',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function countOcorrencias(FiltroOcorrenciaRequest $request)
    {
        try {
            /** @var Usuario $usuarioLogado */
            $usuarioLogado = Auth::user();

            // Inicializamos a Query.
            // Usamos select('tb_ocorrencia.*') para evitar colisão de IDs após o Join.
            // O Join com tb_item é necessário para podermos ordenar pelo título do item.
            $query = Ocorrencia::query()
                ->select('tb_ocorrencia.*')
                ->join('tb_item', 'tb_ocorrencia.idItem', '=', 'tb_item.idItem')
                ->with(['item.categoria', 'usuario', 'custodia.armazem']);

            // --- FILTROS DE IDENTIFICAÇÃO ---
            $query->where('tb_ocorrencia.eliminado',  false);

            // Filtra ocorrências cadastradas por um usuário específico
            $query->when($request->idUsuarioCadastro, function ($q, $id) {
                $q->where('tb_ocorrencia.idUsuario', $id);
            });

            // --- FILTROS DO ITEM (Via Relacionamento ou Join) ---

            // Filtro de busca textual (Título ou Descrição do Item)
            $query->when($request->filtroTexto, function ($q, $filtroTexto) {
                $q->where(function ($qr) use ($filtroTexto) {
                    $qr->where('tb_item.titulo', 'like', "%{$filtroTexto}%")
                        ->orWhere('tb_item.descricao', 'like', "%{$filtroTexto}%");
                });
            });

            // Filtro por Categoria do Item
            $query->when($request->idCategoria, function ($q, $idCategoria) {
                $q->where('tb_item.idCategoria', $idCategoria);
            });

            // --- FILTROS DA OCORRÊNCIA ---

            // Filtro por Tipo (PERDIDO ou ACHADO)
            $query->when($request->tipoOcorrencia, function ($q, $tipo) {
                $q->where('tb_ocorrencia.tipoOcorrencia', $tipo);
            });

            // Filtro pelo Status atual do processo
            $query->when($request->statusProcesso, function ($q, $status) {
                $q->where('tb_ocorrencia.statusProcesso', $status);
            });

            // Filtro por Intervalo de Datas do Evento (Quando o objeto foi perdido/achado)
            $query->when($request->dataInicio, function ($q, $data) {
                $q->whereDate('tb_ocorrencia.dataEvento', '>=', $data);
            });

            $query->when($request->dataFim, function ($q, $data) {
                $q->whereDate('tb_ocorrencia.dataEvento', '<=', $data);
            });

            // --- FILTROS DE LÓGICA DE NEGÓCIO ---

            // Filtro "Apenas Minha Esquadra": Restringe a busca à esquadra do policial logado
            // Só tem efeito se o usuário for do tipo POLICIAL
            if ($request->boolean('apenasEsquadra')  && $usuarioLogado != null && $usuarioLogado->tipoUsuario === 'POLICIAL') {
                $idEsquadra = $usuarioLogado->policial->idEsquadra;

                $query->whereHas('custodia', function ($q) use ($idEsquadra) {
                    $q->where('tipoDetentor', 'ESQUADRA')
                        ->where('idDetentor', $idEsquadra);
                });
            }

            // Filtro para mostrar apenas itens já entregues (Devolvidos) ou apenas os pendentes
            $query->when($request->has('jaDevolvidos'), function ($q) use ($request) {
                if ($request->boolean('jaDevolvidos')) {
                    $q->where('tb_ocorrencia.statusProcesso', 'ENTREGUE');
                } else {
                    $q->where('tb_ocorrencia.statusProcesso', '!=', 'ENTREGUE');
                }
            });

            // --- ORDENAÇÃO ---

            // Define a ordem dos resultados com base no parâmetro 'ordem'
            switch ($request->ordem) {
                case 'az':
                    $query->orderBy('tb_item.titulo', 'asc');
                    break;
                case 'za':
                    $query->orderBy('tb_item.titulo', 'desc');
                    break;
                case 'antigo':
                    $query->orderBy('tb_ocorrencia.dataCadastro', 'asc');
                    break;
                case 'recente':
                default:
                    $query->orderBy('tb_ocorrencia.dataCadastro', 'desc');
                    break;
            }

            $resultado = $query->count();

            return response()->json([
                'message' => 'Listagem recuperada com sucesso',
                'body' => $resultado
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao listar ocorrências',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function registrarOcorrencia(SaveOcorrenciaRequest $request)
    {
        try {
            $resultado = DB::transaction(function () use ($request) {
                /** @var Usuario|null $usuarioLogado */
                $usuarioLogado = Auth::user();
                $dados = $request->validated();

                // 1. Upload de Múltiplas Fotos do Item
                $caminhosFotos = [];
                if ($request->hasFile('fotos')) {
                    foreach ($request->file('fotos') as $foto) {
                        $caminhosFotos[] = $foto->store('fotos-ocorrencias', 'public');
                    }
                }

                // 2. Criar o Item
                $dadosItem = Arr::only($dados, ['idCategoria', 'titulo', 'descricao', 'detalhe']);
                $item = Item::create([
                    ...$dadosItem,
                    'fotosItem' => $caminhosFotos, // Salva o array de nomes (o Cast no Model cuida do JSON)
                ]);


                // Criar a Ocorrência
                $ocorrencia = Ocorrencia::create([
                    'idItem' => $item->idItem,
                    'idUsuario' => $usuarioLogado->idUsuario,
                    'tipoOcorrencia' => $dados['tipoOcorrencia'],
                    'statusProcesso' => 'PROCURANDO',
                    'dataEvento' => $dados['dataEvento'],
                    'localEvento' => $dados['localEvento'],
                ]);

                // 5. Definir Custódia Atual
                $dadosCustodia = [
                    'idOcorrencia' => $ocorrencia->idOcorrencia,
                    'dataCadastro' => now(),
                ];

                if ($usuarioLogado->tipoUsuario === 'POLICIAL') {
                    $dadosCustodia['tipoDetentor'] = 'ESQUADRA';
                    $dadosCustodia['idDetentor'] = $usuarioLogado->policial->idEsquadra;
                    $dadosCustodia['idArmazem'] = $request->idArmazem; // Pode ser nulo
                } else {
                    $dadosCustodia['tipoDetentor'] = 'CIDADAO';
                    $dadosCustodia['idDetentor'] = $usuarioLogado->idUsuario;
                }

                CustodiaAtual::create($dadosCustodia);

                // 6. Criar primeiro registro no Histórico
                HistoricoMovimentacao::create([
                    'idOcorrencia' => $ocorrencia->idOcorrencia,
                    'origemDescricao' => 'INICIO_DO_SISTEMA',
                    'destinoDescricao' => 'OCORRENCIA_REGISTADA',
                    'descricao' => 'Abertura do processo de ocorrência',
                    'idPolicialIntermediario' => $usuarioLogado->policial?->idPolicial,
                ]);

                return $ocorrencia->load(['item', 'custodia']);
            });

            return response()->json([
                'message' => 'Ocorrência registrada com sucesso',
                'body' => $resultado
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao registrar ocorrência',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function deletarOcorrencia($idOcorrencia)
    {
        try {
            DB::transaction(function () use ($idOcorrencia) {
                /** @var Usuario|null $usuarioLogado */
                $usuarioLogado = Auth::user();

                if (!$usuarioLogado) {
                    abort(401, 'Usuário não autenticado.');
                }

                // Buscar ocorrência
                $ocorrencia = Ocorrencia::with(['item'])
                    ->where('idOcorrencia', $idOcorrencia)
                    ->first();

                if (!$ocorrencia) {
                    abort(404, 'Ocorrência não foi encontrada.');
                }

                if ($ocorrencia->statusProcesso == 'ENTREGUE') {
                    abort(400, 'Este processo já foi finalizado e não pode ser alterado');
                }

                if ($ocorrencia->statusProcesso == 'AGUARDANDO_CONFIRMACAO') {
                    abort(400, 'Há um processo para esta ocorrência no momento');
                }

                // 🔐 Validação de permissão
                $ehDono = $ocorrencia->idUsuario === $usuarioLogado->idUsuario;
                $ehPolicial = $usuarioLogado->tipoUsuario === 'POLICIAL';

                if (!$ehDono && !$ehPolicial) {
                    abort(403, 'Você não tem permissão para eliminar esta ocorrência.');
                }

                // 🗂️ Remover histórico
                HistoricoMovimentacao::where('idOcorrencia', $idOcorrencia)->delete();

                // 🗂️ Remover custódia atual
                CustodiaAtual::where('idOcorrencia', $idOcorrencia)->delete();

                // 🖼️ Remover fotos do item
                if ($ocorrencia->item?->fotosItem) {
                    foreach ($ocorrencia->item->fotosItem as $foto) {
                        Storage::disk('public')->delete($foto);
                    }
                }

                // 🗑️ Remover ocorrência
                $ocorrencia->delete();

                // 🗑️ Remover item
                if ($ocorrencia->item) {
                    $ocorrencia->item->delete();
                }
            });

            return response()->json([
                'message' => 'Ocorrência eliminada com sucesso'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' =>  $th->getMessage(),
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function marcarOcorrenciaRecuperada($idOcorrencia)
    {
        try {
            DB::transaction(function () use ($idOcorrencia) {
                /** @var Usuario|null $usuarioLogado */
                $usuarioLogado = Auth::user();

                if (!$usuarioLogado) {
                    abort(401, 'Usuário não autenticado.');
                }

                // Buscar ocorrência
                $ocorrencia = Ocorrencia::with(['item'])
                    ->where('idOcorrencia', $idOcorrencia)
                    ->first();

                if (!$ocorrencia) {
                    abort(404, 'Ocorrência não foi encontrada.');
                }

                if ($ocorrencia->statusProcesso == 'ENTREGUE') {
                    abort(400, 'Este processo já foi finalizado e não pode ser alterado');
                }

                if ($ocorrencia->statusProcesso == 'AGUARDANDO_CONFIRMACAO') {
                    abort(400, 'Há um processo para esta ocorrência no momento');
                }

                // 🔐 Validação de permissão
                $ehDono = $ocorrencia->idUsuario === $usuarioLogado->idUsuario;
                $ehPolicial = $usuarioLogado->tipoUsuario === 'POLICIAL';

                if (!$ehDono && !$ehPolicial) {
                    abort(403, 'Você não tem permissão para alterar esta ocorrência.');
                }

                // 🔄 Alterar status da ocorrência
                $ocorrencia->statusProcesso = 'ENTREGUE';
                $ocorrencia->save();

                // Opcional: adicionar histórico de movimentação
                HistoricoMovimentacao::create([
                    'idOcorrencia' => $idOcorrencia,
                    'origemDescricao' => 'PROCURANDO',
                    'destinoDescricao' => 'ENTREGUE',
                    'descricao' => 'Ocorrência marcada como recuperada',
                    'dataMovimentacao' => now(),
                    'idPolicialIntermediario' => $usuarioLogado->policial?->idPolicial,
                ]);
            });

            return response()->json([
                'message' => 'Ocorrência marcada como recuperada com sucesso'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' =>  $th->getMessage(),
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function getFoto($filename)
    {
        $path = "fotos-ocorrencias/{$filename}";

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->file(storage_path("app/public/{$path}"));
    }
}
