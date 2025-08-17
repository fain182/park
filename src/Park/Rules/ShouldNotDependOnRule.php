<?php

declare(strict_types=1);

namespace Park\Rules;

class ShouldNotDependOnRule extends AbstractRule
{
    private string $dependency;

    public function __construct(string $module, string $dependency, array $exceptions = [])
    {
        parent::__construct($module, $exceptions);
        $this->dependency = $dependency;
    }


    public function validate(array $dependencies): array
    {
        $violations = [];
        
        foreach ($dependencies as $from => $usedModules) {
            if ($this->isModuleOrSubmodule($from, $this->module) && !$this->isException($from, $this->exceptions)) {
                foreach ($usedModules as $to) {
                    if ($this->isModuleOrSubmodule($to, $this->dependency)) {
                        $violations[] = "Module '{$from}' should not depend on '{$to}' (violation: shouldNotDependOn '{$this->dependency}')";
                    }
                }
            }
        }

        return $violations;
    }
}