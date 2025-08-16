<?php

declare(strict_types=1);

namespace App\Service;

use Acme\Compliance\ComplianceChecker;

class UserService
{
    private ComplianceChecker $complianceChecker;
    
    public function __construct(ComplianceChecker $complianceChecker)
    {
        $this->complianceChecker = $complianceChecker;
    }
    
    public function createUser(): void
    {
        $this->complianceChecker->check();
    }
}