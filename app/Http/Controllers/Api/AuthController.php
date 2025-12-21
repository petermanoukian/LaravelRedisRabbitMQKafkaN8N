<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\AuthService;
use App\Http\Requests\Api\LoginRequest;

class AuthController extends Controller
{
    public function __construct(protected AuthService $auth) {}

    public function checkAuth(Request $request): JsonResponse
    {
        return response()->json($this->auth->checkAuth($request));
    }


    
    public function csrfCookie(Request $request): JsonResponse
    {
        return response()->json($this->auth->confirmCsrf());
    }

    public function loggedUser(Request $request): JsonResponse
    {
        return response()->json($this->auth->getLoggedUser($request));
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return response()->json($this->auth->handleLogin($request));
    }

    public function logout(Request $request): JsonResponse
    {
        return response()->json($this->auth->handleLogout($request));
    }
}
