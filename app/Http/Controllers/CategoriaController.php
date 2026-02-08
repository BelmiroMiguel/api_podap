<?php

namespace App\Http\Controllers;

use App\Http\Requests\FiltroCategoriaRequest;
use App\Http\Requests\SaveCategoriaRequest;
use App\Models\Categoria;
use App\Http\Requests\UpdateCategoriaRequest;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{

    public function findAll(FiltroCategoriaRequest $request)
    {
        try {
            $filtroTexto = $request->get('filtroTexto');
            $limit = $request->get('limit', 15); // Padrão 10 itens por página

            $query = Categoria::query();

            // Aplica filtro de texto se existir
            if ($filtroTexto) {
                $query->where('descricao', 'like', '%' . $filtroTexto . '%');
            }

            $query->when($request->has('idCategoria'), function ($q) use ($request) {
                $q->where('idCategoria', '=', $request->get('idCategoria'));
            });

            $query->when($request->has('idCategoriaIncluds'), function ($q) use ($request) {
                $idCategoriaIncluds = (array) $request->get('idCategoriaIncluds');
                $q->whereIn('idCategoria', $idCategoriaIncluds);
            });

            // Ordenação alfabética
            $query->orderBy('descricao', 'asc');

            // paginate() já captura o parâmetro 'page' da URL automaticamente
            $resultado = $query->paginate($limit);

            return response()->json([
                'message' => 'Categorias recuperadas com sucesso',
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
                'message' => 'Erro ao listar categorias',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function countAll(FiltroCategoriaRequest $request)
    {
        try {
            $filtroTexto = $request->query('filtroTexto');
            $query = Categoria::query();

            if ($filtroTexto) {
                $query->where('descricao', 'like', '%' . $filtroTexto . '%');
            }

            $query->when($request->has('idCategoria'), function ($q) use ($request) {
                $q->where('idCategoria', '=', $request->get('idCategoria'));
            });

            $total = $query->count();

            return response()->json([
                'message' => 'Contagem realizada com sucesso',
                'body' => $total
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao contar categorias',
                'error' => $th->getMessage()
            ], 500);
        }
    }



    public function create(SaveCategoriaRequest $request)
    {
        try {
            $categoria = Categoria::create($request->validated());

            return response()->json([
                'message' => 'Categoria criada com sucesso',
                'body' => $categoria
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erro ao criar categoria', 'error' => $th->getMessage()], 500);
        }
    }

    public function findById($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            return response()->json([
                'message' => 'Categoria encontrada',
                'body' => $categoria
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Categoria não encontrada'], 404);
        }
    }

    public function update(UpdateCategoriaRequest $request, $id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $categoria->update($request->validated());

            return response()->json([
                'message' => 'Categoria atualizada com sucesso',
                'body' => $categoria
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erro ao atualizar categoria', 'error' => $th->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $categoria = Categoria::find($id);

            if (!$categoria) {
                return response()->json(['message' => 'A categoria não existe'], 400);
            }

            // Verifica se existem itens vinculados antes de deletar
            if ($categoria->itens()->count() > 0) {
                $categoria->update(['eliminado' => true]);
                return response()->json(['message' => 'Categoria excluída com sucesso'], 200);
            }

            $categoria->delete();

            return response()->json(['message' => 'Categoria excluída com sucesso'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erro ao excluir categoria', 'error' => $th->getMessage()], 500);
        }
    }

    public function ativar($id)
    {
        try {
            $categoria = Categoria::find($id);

            if (!$categoria) {
                return response()->json(['message' => 'A categoria não existe'], 400);
            }

            // Verifica se existem itens vinculados antes de deletar
            if ($categoria->itens()->count() > 0) {
                $categoria->update(['eliminado' => true]);
                return response()->json(['message' => 'Categoria excluída com sucesso'], 200);
            }

            $categoria->delete();

            return response()->json(['message' => 'Categoria excluída com sucesso'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erro ao reativar categoria', 'error' => $th->getMessage()], 500);
        }
    }
}
