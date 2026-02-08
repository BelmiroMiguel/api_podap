<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(AuthRequest $request)
    {
        try {
            $loginDto = (object) $request->validated();

            // 1. Tentar encontrar o usuário pelo Email ou pelo BI
            $usuario = Usuario::where('email', $loginDto->login)
                ->orWhere('identificacao', $loginDto->login)
                ->first();

            // 2. Verificar se o usuário existe e se a senha está correta
            if (!$usuario || !Hash::check($loginDto->senha, $usuario->senha)) {
                return response()->json([
                    'message' => 'Credenciais inválidas. Verifique o login e a senha.'
                ], 401);
            }

            // 3. Gerar o Token de acesso (Usando Laravel Sanctum)
            // O nome do token indica o tipo de acesso
            $token = $usuario->createToken('token_acesso_' . strtolower($usuario->tipoUsuario))->plainTextToken;

            // 4. Se for Policial, carregar os dados da esquadra dele para facilitar no App/Web
            if ($usuario->tipoUsuario === 'POLICIAL') {
                $usuario->load('policial.esquadra', 'policial.role.permissoes');
            }

            return response()->json([
                'message' => 'Login efetuado com sucesso',
                'token' => $token,
                'body' => $usuario,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao tentar efetuar login: ' . $th->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        // Revogar o token atual do usuário
        Auth::user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sessão encerrada com sucesso'], 200);
    }
}
