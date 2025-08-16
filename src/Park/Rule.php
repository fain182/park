<?php

declare(strict_types=1);

namespace Park;

class Rule
{
    private string $module;

    private function __construct(string $module)
    {
        $this->module = $module;
    }

    public static function module(string $module): self
    {
        return new self($module);
    }

    public function shouldNotBeUsedByAnyOtherModule(): array
    {
        return [
            'module' => $this->module,
            'rule' => 'shouldNotBeUsedByAnyOtherModule'
        ];
    }

    public function shouldNotDependOn(string $dependency): array
    {
        return [
            'module' => $this->module,
            'rule' => 'shouldNotDependOn',
            'dependency' => $dependency
        ];
    }

    public function canDependOn(string $dependency): array
    {
        return [
            'module' => $this->module,
            'rule' => 'canDependOn',
            'dependency' => $dependency
        ];
    }

    public function shouldOnlyBeUsedBy(array $allowedModules): array
    {
        return [
            'module' => $this->module,
            'rule' => 'shouldOnlyBeUsedBy',
            'allowedModules' => $allowedModules
        ];
    }
}