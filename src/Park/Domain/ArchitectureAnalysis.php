<?php

declare(strict_types=1);

namespace Park\Domain;

use Park\Rules\RuleInterface;

class ArchitectureAnalysis
{
    /** @var RuleInterface[] */
    private array $rules;
    private DependencyGraph $graph;
    private ?ViolationCollection $violations = null;

    /** @param RuleInterface[] $rules */
    public function __construct(array $rules, DependencyGraph $graph)
    {
        $this->rules = $rules;
        $this->graph = $graph;
    }

    public function getViolations(): ViolationCollection
    {
        if ($this->violations === null) {
            $this->violations = $this->analyzeRules();
        }
        
        return $this->violations;
    }

    public function hasViolations(): bool
    {
        return !$this->getViolations()->isEmpty();
    }

    private function analyzeRules(): ViolationCollection
    {
        $violations = new ViolationCollection();
        $dependencies = $this->graph->getAllDependencies();
        
        foreach ($this->rules as $rule) {
            $ruleViolations = $rule->validate($dependencies);
            
            foreach ($ruleViolations as $message) {
                // Extract from/to from message - this is temporary until we refactor rules
                $violation = new Violation('', '', $message);
                $violations->add($violation);
            }
        }
        
        return $violations;
    }
}