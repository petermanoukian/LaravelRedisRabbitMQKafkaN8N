<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

class UserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function revokeTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    public function issueToken(User $user, bool $remember = false): string
    {
        $ttlMinutes = $remember ? 60 * 24 * 30 : 60 * 2;
        $expiresAt = now()->addMinutes($ttlMinutes);

        $token = $user->createToken('login');
        $token->accessToken->expires_at = $expiresAt;
        $token->accessToken->save();

        Log::info('Issued token', [
            'user_id' => $user->id,
            'token' => $token->plainTextToken,
            'expires_at' => $expiresAt,
        ]);

        return $token->plainTextToken;
    }



}
