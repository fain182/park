<?php

declare(strict_types=1);

namespace Park\Rules;

interface RuleInterface
{
    public function getModule(): string;
    
    public function getExceptions(): array;
    
    public function validate(array $dependencies): array;
}