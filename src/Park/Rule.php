<?php

declare(strict_types=1);

namespace Park;

class Rule
{
    private string $module;
    private array $exceptions = [];

    private function __construct(string $module)
    {
        $this->module = $module;
    }

    public static function module(string $module): self
    {
        return new self($module);
    }

    public function except(string|array $exceptions): self
    {
        $this->exceptions = is_array($exceptions) ? $exceptions : [$exceptions];
        return $this;
    }

    public function shouldNotBeUsedByAnyOtherModule(): array
    {
        return [
            'module' => $this->module,
            'rule' => 'shouldNotBeUsedByAnyOtherModule',
            'exceptions' => $this->exceptions
        ];
    }

    public function shouldNotDependOn(string $dependency): array
    {
        return [
            'module' => $this->module,
            'rule' => 'shouldNotDependOn',
            'dependency' => $dependency,
            'exceptions' => $this->exceptions
        ];
    }

    public function canDependOn(string $dependency): array
    {
        return [
            'module' => $this->module,
            'rule' => 'canDependOn',
            'dependency' => $dependency,
            'exceptions' => $this->exceptions
        ];
    }

    public function shouldOnlyBeUsedBy(array $allowedModules): array
    {
        return [
            'module' => $this->module,
            'rule' => 'shouldOnlyBeUsedBy',
            'allowedModules' => $allowedModules,
            'exceptions' => $this->exceptions
        ];
    }

    public function canBeAccessedOnlyUsing(array $publicClasses): array
    {
        return [
            'module' => $this->module,
            'rule' => 'canBeAccessedOnlyUsing',
            'publicClasses' => $publicClasses,
            'exceptions' => $this->exceptions
        ];
    }
}