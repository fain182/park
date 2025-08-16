<?php

declare(strict_types=1);

namespace Park\Validator;

use Park\Analyzer\AstCodeAnalyzer;
use Park\Analyzer\CodeAnalyzerInterface;
use Park\Scanner\FileScanner;

class RuleValidator
{
    private CodeAnalyzerInterface $analyzer;
    private FileScanner $scanner;

    public function __construct(?CodeAnalyzerInterface $analyzer = null, ?FileScanner $scanner = null)
    {
        $this->analyzer = $analyzer ?? new AstCodeAnalyzer();
        $this->scanner = $scanner ?? new FileScanner();
    }

    public function validate(array $rules, string $directory = 'src'): array
    {
        $violations = [];
        $dependencies = $this->analyzeDependencies($directory);

        foreach ($rules as $rule) {
            $ruleViolations = $this->validateRule($rule, $dependencies);
            $violations = array_merge($violations, $ruleViolations);
        }

        return $violations;
    }

    private function analyzeDependencies(string $directory): array
    {
        $dependencies = [];
        
        foreach ($this->scanner->scanPhpFiles($directory) as $file) {
            $content = $file->getContents();
            $result = $this->analyzer->analyzeFile($content);
            
            if ($result['namespace']) {
                $dependencies[$result['namespace']] = $result['dependencies'];
            }
        }
        
        return $dependencies;
    }

    private function validateRule(array $rule, array $dependencies): array
    {
        $violations = [];
        $module = $rule['module'];
        $exceptions = $rule['exceptions'] ?? [];

        switch ($rule['rule']) {
            case 'shouldNotBeUsedByAnyOtherModule':
                $violations = $this->validateShouldNotBeUsedByAnyOtherModule($module, $exceptions, $dependencies);
                break;

            case 'shouldNotDependOn':
                $violations = $this->validateShouldNotDependOn($module, $rule['dependency'], $exceptions, $dependencies);
                break;

            case 'canDependOn':
                break;

            case 'shouldOnlyBeUsedBy':
                $violations = $this->validateShouldOnlyBeUsedBy($module, $rule['allowedModules'], $exceptions, $dependencies);
                break;
        }

        return $violations;
    }

    private function validateShouldNotBeUsedByAnyOtherModule(string $module, array $exceptions, array $dependencies): array
    {
        $violations = [];
        
        foreach ($dependencies as $from => $usedModules) {
            foreach ($usedModules as $to) {
                if ($this->isModuleOrSubmodule($to, $module) && !$this->isModuleOrSubmodule($from, $module)) {
                    if ($this->isException($to, $exceptions)) {
                        continue;
                    }
                    $violations[] = "Module '{$from}' should not use '{$module}' (violation: shouldNotBeUsedByAnyOtherModule)";
                }
            }
        }

        return $violations;
    }

    private function validateShouldNotDependOn(string $module, string $dependency, array $exceptions, array $dependencies): array
    {
        $violations = [];
        
        foreach ($dependencies as $from => $usedModules) {
            if ($this->isModuleOrSubmodule($from, $module) && !$this->isException($from, $exceptions)) {
                foreach ($usedModules as $to) {
                    if ($this->isModuleOrSubmodule($to, $dependency)) {
                        $violations[] = "Module '{$from}' should not depend on '{$to}' (violation: shouldNotDependOn '{$dependency}')";
                    }
                }
            }
        }

        return $violations;
    }

    private function validateShouldOnlyBeUsedBy(string $module, array $allowedModules, array $exceptions, array $dependencies): array
    {
        $violations = [];
        
        foreach ($dependencies as $from => $usedModules) {
            foreach ($usedModules as $to) {
                if ($this->isModuleOrSubmodule($to, $module) && !$this->isException($to, $exceptions)) {
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

    private function isException(string $class, array $exceptions): bool
    {
        foreach ($exceptions as $exception) {
            if (str_ends_with($exception, '*')) {
                $pattern = substr($exception, 0, -1);
                // Match if class starts with pattern, or if class equals pattern without trailing backslash
                if (str_starts_with($class, $pattern) || $class === rtrim($pattern, '\\')) {
                    return true;
                }
            } elseif ($class === $exception) {
                return true;
            }
        }
        
        return false;
    }
}