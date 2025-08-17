<?php

declare(strict_types=1);

namespace Park\Rules;

interface RuleInterface
{
    public function validate(array $dependencies): array;
}