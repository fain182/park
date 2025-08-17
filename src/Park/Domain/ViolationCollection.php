<?php

declare(strict_types=1);

namespace Park\Domain;

class ViolationCollection
{
    /** @var Violation[] */
    private array $violations = [];

    public function add(Violation $violation): void
    {
        $this->violations[] = $violation;
    }

    public function isEmpty(): bool
    {
        return empty($this->violations);
    }

    public function count(): int
    {
        return count($this->violations);
    }

    /** @return Violation[] */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /** @return string[] */
    public function toMessages(): array
    {
        return array_map(fn(Violation $v) => $v->getMessage(), $this->violations);
    }
}