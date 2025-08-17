<?php

declare(strict_types=1);

namespace Park\Validator;

use Park\Domain\ArchitectureAnalysis;
use Park\Domain\DependencyGraph;
use Park\Rules\RuleInterface;

class RuleValidator
{
    /** @param RuleInterface[] $rules */
    public function validate(array $rules, string $directory = 'src'): array
    {
        $graph = DependencyGraph::fromDirectory($directory);
        $analysis = new ArchitectureAnalysis($rules, $graph);
        
        return $analysis->getViolations()->toMessages();
    }
}