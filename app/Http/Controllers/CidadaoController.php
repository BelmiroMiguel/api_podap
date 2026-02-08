<?php

namespace App\Http\Controllers;

use App\Http\Requests\FiltroUsuarioRequest;
use App\Http\Requests\SaveCidadaoRequest;
use App\Http\Requests\UpdateCidadaoRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CidadaoController extends Controller
{
    public function registrarCidadao(SaveCidadaoRequest $request)
    {
        try {
            $usuario = Usuario::create([
                ...$request->validated(),
                'senha' => Hash::make($request->senha),
                'tipoUsuario' => 'CIDADAO',
            ]);

            // O nome do token indica o tipo de acesso
            $token = $usuario->createToken('token_acesso_' . strtolower($usuario->tipoUsuario))->plainTextToken;

            return response()->json([
                'body' => $usuario,
                'token' => $token,
                'message' => 'Conta criada com sucesso',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erro ao criar conta', 'error' => $th->getMessage()], 500);
        }
    }

    public function atualizarPerfil(UpdateCidadaoRequest $request)
    {
        try {
            /** @var Usuario $usuarioLogado */
            $usuarioLogado = Auth::user();
            $updateDto = $request->validated();

            // Lógica de Upload de Foto
            if ($request->hasFile('foto')) {
                // Deletar foto antiga se existir
                if ($usuarioLogado->foto) {
                    Storage::disk('public')->delete($usuarioLogado->foto);
                }

                // Salvar nova foto na pasta 'perfil'
                $caminho = $request->file('foto')->store('fotos-usuarios', 'public');
                $updateDto['foto'] = $caminho;
            }

            $usuarioLogado->update($updateDto);

            return response()->json([
                'message' => 'Perfil atualizado com sucesso',
                'body' => $usuarioLogado
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao atualizar perfil',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getPerfil()
    {
        try {
            return response()->json([
                'body' => Auth::user(),
                'message' => 'Perfil recuperado com sucesso'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function findByTelefone($telefone)
    {
        try {
            $usuario = Usuario::where('telefone', '=', $telefone)->firstOrFail();

            return response()->json([
                'message' => 'Usuário encontrada',
                'body' => $usuario
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }
    }

    public function getFoto($filename)
    {
        $path = "fotos-usuarios/{$filename}";

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->file(storage_path("app/public/{$path}"));
    }

    public function findAll(FiltroUsuarioRequest $request)
    {
        try {
            /** @var Usuario $usuarioLogado */
            $usuarioLogado = Auth::user();

            // Define a quantidade de itens por página
            $limit = $request->limit ?? 15;

            // Inicializamos a Query com Left Join para capturar dados de Policial se existirem
            $query = Usuario::query()
                ->select('tb_usuario.*')
                ->leftJoin('tb_policial', 'tb_usuario.idUsuario', '=', 'tb_policial.idUsuario')
                ->with(['policial.esquadra', 'policial.role.permissoes']);

            // --- FILTROS DE IDENTIFICAÇÃO ---

            // Busca textual abrangente (Nome, Identificação, NIP, Telefone, Email)
            $query->when($request->filtroTexto, function ($q, $texto) {
                $q->where(function ($qr) use ($texto) {
                    $qr->where('tb_usuario.nome', 'like', "%{$texto}%")
                        ->orWhere('tb_usuario.identificacao', 'like', "%{$texto}%")
                        ->orWhere('tb_usuario.telefone', 'like', "%{$texto}%")
                        ->orWhere('tb_usuario.email', 'like', "%{$texto}%")
                        ->orWhere('tb_policial.nip', 'like', "%{$texto}%");
                });
            });

            // --- FILTROS DE TIPO E STATUS ---

            // Filtro por Tipo de Usuário (Único ou Múltiplos)
            $query->when($request->tipoUsuario, function ($q, $tipo) {
                $q->where('tb_usuario.tipoUsuario', $tipo);
            });

            $query->when($request->tipoUsuarioIncluds, function ($q, $tipos) {
                $q->whereIn('tb_usuario.tipoUsuario', $tipos);
            });

            // Filtro por Status da Conta
            $query->when($request->statusConta, function ($q, $status) {
                $q->where('tb_usuario.statusConta', $status);
            });

            // --- FILTROS DE DATA ---

            $query->when($request->dataCriacaoInicio, function ($q, $data) {
                $q->whereDate('tb_usuario.dataCadastro', '>=', $data);
            });

            $query->when($request->dataCriacaoFim, function ($q, $data) {
                $q->whereDate('tb_usuario.dataCadastro', '<=', $data);
            });

            // --- FILTROS DE LÓGICA DE NEGÓCIO ---

            // Filtro "Apenas Minha Esquadra": Mostra apenas policiais da mesma esquadra do logado
            if ($request->boolean('apenasEsquadra') && $usuarioLogado->tipoUsuario === 'POLICIAL') {
                $idEsquadra = $usuarioLogado->policial->idEsquadra;
                $query->where('tb_policial.idEsquadra', $idEsquadra);
            }

            // --- ORDENAÇÃO ---

            switch ($request->ordem) {
                case 'az':
                    $query->orderBy('tb_usuario.nome', 'asc');
                    break;
                case 'za':
                    $query->orderBy('tb_usuario.nome', 'desc');
                    break;
                case 'antigo':
                    $query->orderBy('tb_usuario.dataCadastro', 'asc');
                    break;
                case 'recente':
                default:
                    $query->orderBy('tb_usuario.dataCadastro', 'desc');
                    break;
            }

            // Executa a paginação
            $resultado = $query->paginate($limit);

            return response()->json([
                'message' => 'Listagem de usuários recuperada com sucesso',
                'body' => $resultado->items(),
                'paginacao' => [
                    'page' => $resultado->currentPage(),
                    'totalPages' => $resultado->lastPage(),
                    'limit' => $resultado->perPage(),
                    'totalItems' => $resultado->total(),
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao listar usuários',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
