<?php

declare(strict_types=1);

namespace Park\Validator;

use Park\Analyzer\CodeAnalyzer;

class RuleValidator
{
    private CodeAnalyzer $analyzer;

    public function __construct()
    {
        $this->analyzer = new CodeAnalyzer();
    }

    public function validate(array $rules, string $directory = 'src'): array
    {
        $violations = [];
        $dependencies = $this->analyzer->analyzeDependencies($directory);

        foreach ($rules as $rule) {
            $ruleViolations = $this->validateRule($rule, $dependencies);
            $violations = array_merge($violations, $ruleViolations);
        }

        return $violations;
    }

    private function validateRule(array $rule, array $dependencies): array
    {
        $violations = [];
        $module = $rule['module'];

        switch ($rule['rule']) {
            case 'shouldNotBeUsedByAnyOtherModule':
                $violations = $this->validateShouldNotBeUsedByAnyOtherModule($module, $dependencies);
                break;

            case 'shouldNotDependOn':
                $violations = $this->validateShouldNotDependOn($module, $rule['dependency'], $dependencies);
                break;

            case 'canDependOn':
                break;

            case 'shouldOnlyBeUsedBy':
                $violations = $this->validateShouldOnlyBeUsedBy($module, $rule['allowedModules'], $dependencies);
                break;
        }

        return $violations;
    }

    private function validateShouldNotBeUsedByAnyOtherModule(string $module, array $dependencies): array
    {
        $violations = [];
        
        foreach ($dependencies as $from => $usedModules) {
            foreach ($usedModules as $to) {
                if ($this->isModuleOrSubmodule($to, $module) && !$this->isModuleOrSubmodule($from, $module)) {
                    $violations[] = "Module '{$from}' should not use '{$module}' (violation: shouldNotBeUsedByAnyOtherModule)";
                }
            }
        }

        return $violations;
    }

    private function validateShouldNotDependOn(string $module, string $dependency, array $dependencies): array
    {
        $violations = [];
        
        foreach ($dependencies as $from => $usedModules) {
            if ($this->isModuleOrSubmodule($from, $module)) {
                foreach ($usedModules as $to) {
                    if ($this->isModuleOrSubmodule($to, $dependency)) {
                        $violations[] = "Module '{$from}' should not depend on '{$to}' (violation: shouldNotDependOn '{$dependency}')";
                    }
                }
            }
        }

        return $violations;
    }

    private function validateShouldOnlyBeUsedBy(string $module, array $allowedModules, array $dependencies): array
    {
        $violations = [];
        
        foreach ($dependencies as $from => $usedModules) {
            foreach ($usedModules as $to) {
                if ($this->isModuleOrSubmodule($to, $module)) {
                    $isAllowed = false;
                    foreach ($allowedModules as $allowedModule) {
                        if ($this->isModuleOrSubmodule($from, $allowedModule)) {
                            $isAllowed = true;
                            break;
                        }
                    }
                    
                    if (!$isAllowed) {
                        $violations[] = "Module '{$from}' is not allowed to use '{$module}' (violation: shouldOnlyBeUsedBy [" . implode(', ', $allowedModules) . "])";
                    }
                }
            }
        }

        return $violations;
    }

    private function isModuleOrSubmodule(string $class, string $module): bool
    {
        return str_starts_with($class, $module . '\\') || $class === $module;
    }
}