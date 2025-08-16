<?php

declare(strict_types=1);

namespace App\Controller;

use App\Security\AuthService;

class UserController
{
    private AuthService $authService;
    
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    
    public function login(): void
    {
        $this->authService->authenticate('token');
    }
}