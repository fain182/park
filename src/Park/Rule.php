<?php

declare(strict_types=1);

namespace Park;

use Park\Rules\CanBeAccessedOnlyUsingRule;
use Park\Rules\CanDependOnRule;
use Park\Rules\RuleInterface;
use Park\Rules\ShouldNotBeUsedByAnyOtherModuleRule;
use Park\Rules\ShouldNotDependOnRule;
use Park\Rules\ShouldOnlyBeUsedByRule;

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

    public function shouldNotBeUsedByAnyOtherModule(): RuleInterface
    {
        return new ShouldNotBeUsedByAnyOtherModuleRule($this->module, $this->exceptions);
    }

    public function shouldNotDependOn(string $dependency): RuleInterface
    {
        return new ShouldNotDependOnRule($this->module, $dependency, $this->exceptions);
    }

    public function canDependOn(string $dependency): RuleInterface
    {
        return new CanDependOnRule($this->module, $dependency, $this->exceptions);
    }

    public function shouldOnlyBeUsedBy(array $allowedModules): RuleInterface
    {
        return new ShouldOnlyBeUsedByRule($this->module, $allowedModules, $this->exceptions);
    }

    public function canBeAccessedOnlyUsing(array $publicClasses): RuleInterface
    {
        return new CanBeAccessedOnlyUsingRule($this->module, $publicClasses, $this->exceptions);
    }
}