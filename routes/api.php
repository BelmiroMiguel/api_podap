<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController,
    CategoriaController,
    CidadaoController,
    PolicialController,
    PermissaoController,
    OcorrenciaController,
    CustodiaController,
    EntregaController
};

// --- AUTH ---
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

// --- USUÁRIO (Arquivos) ---
Route::prefix('usuario')->group(function () {
    Route::get('/foto/{filename}', [CidadaoController::class, 'getFoto']);
});

// --- CIDADÃO ---
Route::prefix('cidadao')->group(function () {
    Route::post('/criar-conta', [CidadaoController::class, 'registrarCidadao']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [CidadaoController::class, 'findAll']);
        Route::get('/perfil', [CidadaoController::class, 'getPerfil']);
        Route::get('/telefone/{telefone}', [CidadaoController::class, 'findByTelefone']);
        Route::post('/atualizar-perfil', [CidadaoController::class, 'atualizarPerfil']);
    });
});

// --- POLICIAL ---
Route::prefix('policial')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/registrar', [PolicialController::class, 'registrarPolicial']);
        Route::post('/atualizar-perfil/{id}', [PolicialController::class, 'atualizarPolicial']);

        // Killer Feature: Gestão de Permissões Individuais
        Route::get('/permissoes-catalogo', [PermissaoController::class, 'listarCatalogoPermissoes']);
        Route::post('/permissoes-sincronizar', [PermissaoController::class, 'sincronizarPermissoesIndividuais']);
    });
});

// --- OCORRÊNCIA (Itens Perdidos/Achados) ---
Route::prefix('ocorrencia')->group(function () {
    // Arquivos do item
    Route::get('/foto/{filename}', [OcorrenciaController::class, 'getFoto']);
    Route::get('/', [OcorrenciaController::class, 'getOcorrencias']);
    Route::get('/detalhes/{id}', [OcorrenciaController::class, 'detalhesOcorrencia']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [OcorrenciaController::class, 'registrarOcorrencia']);
        Route::get('/count', [OcorrenciaController::class, 'countOcorrencias']);
        Route::get('/categorias', [OcorrenciaController::class, 'getCategorias']);
        Route::delete('/{idOcorrencia}', [OcorrenciaController::class, 'deletarOcorrencia']);
        Route::put('/recuperado/{idOcorrencia}', [OcorrenciaController::class, 'marcarOcorrenciaRecuperada']);
    });
});

Route::prefix('categorias')->group(function () {
    // Arquivos do item
    Route::get('/', [CategoriaController::class, 'findAll']);
    Route::get('/count', [CategoriaController::class, 'countAll']);
    Route::get('/id/{id}', [CategoriaController::class, 'findById']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('/{id}', [CategoriaController::class, 'delete']);
        Route::put('/ativar/{id}', [CategoriaController::class, 'ativar']);
        Route::put('/{id}', [CategoriaController::class, 'update']);
    });
});

// --- CUSTÓDIA (Movimentação) ---
Route::prefix('custodia')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/receber-na-esquadra', [CustodiaController::class, 'receberItemNaEsquadra']);
        Route::get('/armazens', [CustodiaController::class, 'listarArmazens']);
    });
});

// --- ENTREGA (Encerramento) ---
Route::prefix('entrega-ocorrencia')->group(function () {
    // Arquivos da entrega
    Route::get('/foto/{filename}', [EntregaController::class, 'getFoto']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [EntregaController::class, 'finalizarEntrega']);
    });
});
