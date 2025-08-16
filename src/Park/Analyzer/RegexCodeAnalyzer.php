<?php

declare(strict_types=1);

namespace Park\Analyzer;

class RegexCodeAnalyzer implements CodeAnalyzerInterface
{
    public function analyzeFile(string $content): array
    {
        $namespace = $this->extractNamespace($content);
        $dependencies = $this->extractUsedClasses($content);
        
        return [
            'namespace' => $namespace,
            'dependencies' => $dependencies
        ];
    }

    private function extractNamespace(string $content): ?string
    {
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }

    private function extractUsedClasses(string $content): array
    {
        $usedClasses = [];
        
        if (preg_match_all('/use\s+([^;]+);/', $content, $matches)) {
            foreach ($matches[1] as $use) {
                $use = trim($use);
                if (!str_contains($use, ' as ')) {
                    $usedClasses[] = $use;
                } else {
                    $parts = explode(' as ', $use);
                    $usedClasses[] = trim($parts[0]);
                }
            }
        }
        
        if (preg_match_all('/new\s+([A-Z][a-zA-Z0-9_\\\\]+)/', $content, $matches)) {
            foreach ($matches[1] as $class) {
                if (!in_array($class, $usedClasses) && str_contains($class, '\\')) {
                    $usedClasses[] = $class;
                }
            }
        }
        
        return array_unique($usedClasses);
    }
}