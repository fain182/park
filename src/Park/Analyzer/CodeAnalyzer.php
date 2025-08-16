<?php

declare(strict_types=1);

namespace Park\Analyzer;

use Symfony\Component\Finder\Finder;

class CodeAnalyzer
{
    public function analyzeDependencies(string $directory = 'src'): array
    {
        $dependencies = [];
        $finder = new Finder();
        
        if (!is_dir($directory)) {
            return $dependencies;
        }
        
        $finder->files()->name('*.php')->in($directory)->exclude(['vendor', 'tests']);
        
        foreach ($finder as $file) {
            $content = $file->getContents();
            $namespace = $this->extractNamespace($content);
            
            if ($namespace) {
                $usedClasses = $this->extractUsedClasses($content);
                $dependencies[$namespace] = $usedClasses;
            }
        }
        
        return $dependencies;
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