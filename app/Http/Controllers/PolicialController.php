<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePolicialRequest;
use App\Http\Requests\UpdatePolicialRequest;
use App\Models\Policial;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PolicialController extends Controller
{

    public function registrarPolicial(SavePolicialRequest $request)
    {
        try {
            /** @var Usuario|null $usuarioLogado */
            $usuarioLogado = Auth::user();

            $policial = DB::transaction(function () use ($request, $usuarioLogado) {
                // Filtra apenas os campos da tabela tb_usuario
                $dadosUsuario = Arr::only($request->validated(), [
                    'nome',
                    'identificacao',
                    'tipoIdentificacao',
                    'telefone',
                    'email'
                ]);

                $usuario = Usuario::create([
                    ...$dadosUsuario,
                    'senha' => Hash::make($request->senha),
                    'tipoUsuario' => 'POLICIAL',
                ]);

                // Filtra apenas os campos da tabela tb_policial
                $dadosPolicial = Arr::only($request->validated(), [
                    'idRole',
                    'nip',
                    'patente'
                ]);

                $policial = Policial::create([
                    ...$dadosPolicial,
                    'idRole' => 2,
                    'nip' => now(),
                    'patente' => now(),
                    'idEsquadra' => $usuarioLogado->policial->idEsquadra,
                    'idUsuario' => $usuario->idUsuario,
                ]);

                return $policial->load('usuario'); // Retorna o policial com os dados de usuário
            });

            return response()->json([
                'message' => 'Policial cadastrado com sucesso',
                'body' => $policial
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao cadastrar policial',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function atualizarPolicial(UpdatePolicialRequest $request, $id)
    {
        try {
            $usuario = DB::transaction(function () use ($request, $id) {
                $usuario = Usuario::findOrFail($id);
                $policial = $usuario->policial;

                $dadosValidados = $request->validated();

                // 1. Tratar a Foto (se enviada)
                if ($request->hasFile('foto')) {
                    if ($usuario->foto) {
                        Storage::disk('public')->delete($usuario->foto);
                    }
                    $dadosValidados['foto'] = $request->file('foto')->store('fotos-usuarios', 'public');
                }

                // 2. Atualizar Tabela tb_usuario
                $dadosUsuario = Arr::only($dadosValidados, [
                    'nome',
                    'identificacao',
                    'tipoIdentificacao',
                    'telefone',
                    'email',
                    'foto'
                ]);
                $usuario->update($dadosUsuario);

                // 3. Atualizar Tabela tb_policial
                $dadosPolicial = Arr::only($dadosValidados, [
                    'idRole',
                    'nip',
                    'patente'
                ]);
                $policial->update($dadosPolicial);

                return $usuario->load('policial.esquadra', 'policial.role.permissoes');
            });

            return response()->json([
                'message' => 'Dados do policial atualizados com sucesso',
                'body' => $usuario
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao atualizar policial',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
