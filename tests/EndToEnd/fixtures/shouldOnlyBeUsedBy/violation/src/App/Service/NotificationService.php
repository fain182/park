<?php

declare(strict_types=1);

namespace App\Service;

use App\Security\AuthService;

class NotificationService
{
    private AuthService $authService;
    
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    
    public function send(): void
    {
        $this->authService->authenticate('token');
    }
}