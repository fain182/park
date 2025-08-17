<?php

declare(strict_types=1);

namespace Park\Rules;

class CanBeAccessedOnlyUsingRule extends AbstractRule
{
    private array $publicClasses;

    public function __construct(string $module, array $publicClasses, array $exceptions = [])
    {
        parent::__construct($module, $exceptions);
        $this->publicClasses = $publicClasses;
    }


    public function validate(array $dependencies): array
    {
        $violations = [];
        
        foreach ($dependencies as $from => $usedModules) {
            foreach ($usedModules as $to) {
                if ($this->isModuleOrSubmodule($to, $this->module) && !$this->isModuleOrSubmodule($from, $this->module)) {
                    if ($this->isException($to, $this->exceptions)) {
                        continue;
                    }
                    
                    $isPublicApi = false;
                    foreach ($this->publicClasses as $publicClass) {
                        if ($this->matchesPattern($to, $publicClass)) {
                            $isPublicApi = true;
                            break;
                        }
                    }
                    
                    if (!$isPublicApi) {
                        $violations[] = "Module '{$from}' cannot access private class '{$to}' from module '{$this->module}' (violation: canBeAccessedOnlyUsing)";
                    }
                }
            }
        }

        return $violations;
    }
}