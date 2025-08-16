<?php

declare(strict_types=1);

namespace App\Security;

class AuthService
{
    public function authenticate(string $token): bool
    {
        return true;
    }
}