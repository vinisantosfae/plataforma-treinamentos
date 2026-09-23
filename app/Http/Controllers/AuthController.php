<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $data = $request->validate([
            'cpf' => ['required', 'string'],
            'senha' => ['required', 'string'],
            'perfil' => ['required', 'in:administrador,colaborador'],
        ]);

        abort_unless($data['senha'] === 'password', 422, 'CPF ou senha inválidos.');

        $cpf = preg_replace('/\D/', '', $data['cpf']);
        $users = json_decode(file_get_contents(resource_path('mock-auth-users.json')), true, 512, JSON_THROW_ON_ERROR);
        $mockUser = collect($users)->first(fn (array $user) => preg_replace('/\D/', '', $user['cpf']) === $cpf);

        abort_unless($mockUser, 422, 'CPF ou senha inválidos.');
        $pessoa = Pessoa::with('empresa')->find($mockUser['pessoaIdStw']);
        abort_unless($pessoa && $pessoa->cpf === $mockUser['cpf'] && $pessoa->is_admin === $mockUser['isAdmin'], 422, 'Usuário mock não está sincronizado.');
        abort_if($data['perfil'] === 'administrador' && ! $pessoa->is_admin, 403, 'Este CPF não possui acesso de administrador.');

        $user = User::firstOrCreate(
            ['email' => 'mock.'.$cpf.'@plataforma.test'],
            ['name' => $pessoa->nome, 'password' => Str::random(40)]
        );

        $token = $user->createToken('mock-login');
        $token->accessToken->pessoa_id_stw = $pessoa->pessoa_id_stw;
        $token->accessToken->save();

        return response()->json([
            'token' => $token->plainTextToken,
            'identity' => $this->identity($pessoa),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $pessoa = $request->user()->currentAccessToken()->pessoa()->with('empresa')->firstOrFail();

        return response()->json(['identity' => $this->identity($pessoa)]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(status: 204);
    }

    private function identity(Pessoa $pessoa): array
    {
        return [
            'pessoaIdStw' => $pessoa->pessoa_id_stw,
            'nome' => $pessoa->nome,
            'cpf' => $pessoa->cpf,
            'isAdmin' => $pessoa->is_admin,
            'empresa' => [
                'idStw' => $pessoa->empresa->id_stw,
                'razaoSocial' => $pessoa->empresa->razao_social,
            ],
        ];
    }
}
