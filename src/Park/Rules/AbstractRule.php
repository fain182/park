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

    public function getModule(): string
    {
        return $this->module;
    }

    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    protected function isModuleOrSubmodule(string $class, string $module): bool
    {
        return str_starts_with($class, $module . '\\') || $class === $module;
    }

    protected function isException(string $class, array $exceptions): bool
    {
        foreach ($exceptions as $exception) {
            if (str_ends_with($exception, '*')) {
                $pattern = substr($exception, 0, -1);
                if (str_starts_with($class, $pattern) || $class === rtrim($pattern, '\\')) {
                    return true;
                }
            } elseif ($class === $exception) {
                return true;
            }
        }
        
        return false;
    }

    protected function matchesPattern(string $class, string $pattern): bool
    {
        if (str_ends_with($pattern, '*')) {
            $prefix = substr($pattern, 0, -1);
            return str_starts_with($class, $prefix) || $class === rtrim($prefix, '\\');
        }
        
        return $class === $pattern;
    }
}