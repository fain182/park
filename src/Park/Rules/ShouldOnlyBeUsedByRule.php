<?php

declare(strict_types=1);

namespace Park\Rules;

class ShouldOnlyBeUsedByRule extends AbstractRule
{
    private array $allowedModules;

    public function __construct(string $module, array $allowedModules, array $exceptions = [])
    {
        parent::__construct($module, $exceptions);
        $this->allowedModules = $allowedModules;
    }


    public function validate(array $dependencies): array
    {
        $violations = [];
        
        foreach ($dependencies as $from => $usedModules) {
            foreach ($usedModules as $to) {
                if ($this->isModuleOrSubmodule($to, $this->module) && !$this->isException($to, $this->exceptions)) {
                    $isAllowed = false;
                    foreach ($this->allowedModules as $allowedModule) {
                        if ($this->isModuleOrSubmodule($from, $allowedModule)) {
                            $isAllowed = true;
                            break;
                        }
                    }
                    
                    if (!$isAllowed) {
                        $violations[] = "Module '{$from}' is not allowed to use '{$this->module}' (violation: shouldOnlyBeUsedBy [" . implode(', ', $this->allowedModules) . "])";
                    }
                }
            }
        }

        return $violations;
    }
}