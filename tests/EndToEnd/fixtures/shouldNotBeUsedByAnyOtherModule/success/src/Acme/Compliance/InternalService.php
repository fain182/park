<?php

declare(strict_types=1);

namespace Acme\Compliance;

class InternalService
{
    private ComplianceChecker $checker;
    
    public function __construct(ComplianceChecker $checker)
    {
        $this->checker = $checker;
    }
    
    public function validate(): bool
    {
        return $this->checker->check();
    }
}