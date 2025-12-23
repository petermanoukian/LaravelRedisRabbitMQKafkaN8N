<?php

namespace App\Services\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use App\Repositories\UserRepository;
use App\Http\Requests\Api\LoginRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function __construct(protected UserRepositoryInterface $users) {}

    /*
    public function checkAuth(Request $request): array
    {
        return [
            'authenticated' => true,
            'isauthenticatedbysanctumfirstcheck' => 414,
            'user' => $request->user(),
        ];
    }
    */

    public function checkAuth(Request $request): array
    {
        $user = $request->user();

        if (!$user) {
            return [
                'authenticated' => false,
                'isauthenticatedbysanctumfirstcheck' => 10,
                'user' => null,
            ];
        }

        return [
            'authenticated' => true,
            'isauthenticatedbysanctumfirstcheck' => 414,
            'user' => $user,
        ];
    }



    public function confirmCsrf(): array
    {
        return ['csrf' => true];
    }

    public function getLoggedUser(Request $request): array
    {
        return $request->user()->toArray();
    }




    public function handleLogin(LoginRequest $request): array|JsonResponse
    {
        $user = $this->users->findByEmail($request->email);
        //$ip = $request->ip();
        $ip= '::2'; 
        if (! $user || ! Hash::check($request->password, $user->password)) {
            Log::warning('Login failed', [
                'email' => $request->email,
                'ip' => $ip,
            ]);

            return [
                'login_success' => false,
                'error' => 'Invalid credentials',
            ];
        }

        $token = $this->users->issueToken($user, $request->boolean('remember'));

        Log::info('Login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $ip,
        ]);

        return [
            'login_success' => true,
            'user' => $user->only(['id', 'name', 'email']),
            'token' => $token,
            'ip' => $ip,
        ];
    }



    public function handleLogout(Request $request): array
    {
        $this->users->revokeTokens($request->user());

        return ['message' => 'Logged out'];
    }

}
