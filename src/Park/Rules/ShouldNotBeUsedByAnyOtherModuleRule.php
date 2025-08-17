<?php

declare(strict_types=1);

namespace Park\Rules;

class ShouldNotBeUsedByAnyOtherModuleRule extends AbstractRule
{
    public function validate(array $dependencies): array
    {
        $violations = [];
        
        foreach ($dependencies as $from => $usedModules) {
            foreach ($usedModules as $to) {
                if ($this->isModuleOrSubmodule($to, $this->module) && !$this->isModuleOrSubmodule($from, $this->module)) {
                    if ($this->isException($to, $this->exceptions)) {
                        continue;
                    }
                    $violations[] = "Module '{$from}' should not use '{$this->module}' (violation: shouldNotBeUsedByAnyOtherModule)";
                }
            }
        }

        return $violations;
    }
}