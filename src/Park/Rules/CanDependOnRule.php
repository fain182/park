<?php

declare(strict_types=1);

namespace Park\Rules;

class CanDependOnRule extends AbstractRule
{
    private string $dependency;

    public function __construct(string $module, string $dependency, array $exceptions = [])
    {
        parent::__construct($module, $exceptions);
        $this->dependency = $dependency;
    }

    public function getDependency(): string
    {
        return $this->dependency;
    }

    public function validate(array $dependencies): array
    {
        return [];
    }
}