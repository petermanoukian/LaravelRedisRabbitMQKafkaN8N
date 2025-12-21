<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function issueToken(User $user): string;

    public function revokeTokens(User $user): void;
}
