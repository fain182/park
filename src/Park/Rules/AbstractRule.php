<?php

declare(strict_types=1);

namespace Park\Rules;

abstract class AbstractRule implements RuleInterface
{
    protected string $module;
    protected array $exceptions;

    public function __construct(string $module, array $exceptions = [])
    {
        $this->module = $module;
        $this->exceptions = $exceptions;
    }


    protected function isModuleOrSubmodule(string $class, string $module): bool
    {
        return str_starts_with($class, $module . '\\') || $class === $module;
    }

    protected function isException(string $class, array $exceptions): bool
    {
        foreach ($exceptions as $exception) {
            if ($this->matchesPattern($class, $exception)) {
                return true;
            }
        }
        
        return false;
    }

    protected function matchesPattern(string $class, string $pattern): bool
    {
        // Always treat as prefix match (namespace-like behavior)
        return str_starts_with($class, $pattern . '\\') || $class === $pattern;
    }
}