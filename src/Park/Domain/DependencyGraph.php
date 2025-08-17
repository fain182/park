<?php

declare(strict_types=1);

namespace Park\Domain;

use Park\Analyzer\AstCodeAnalyzer;
use Park\Analyzer\CodeAnalyzerInterface;
use Park\Scanner\FileScanner;

class DependencyGraph
{
    /** @var array<string, string[]> */
    private array $dependencies;

    /** @param array<string, string[]> $dependencies */
    private function __construct(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    public static function fromDirectory(string $directory, ?CodeAnalyzerInterface $analyzer = null, ?FileScanner $scanner = null): self
    {
        $analyzer = $analyzer ?? new AstCodeAnalyzer();
        $scanner = $scanner ?? new FileScanner();
        
        $dependencies = [];
        
        foreach ($scanner->scanPhpFiles($directory) as $file) {
            $content = $file->getContents();
            $result = $analyzer->analyzeFile($content);
            
            if ($result['namespace']) {
                $dependencies[$result['namespace']] = $result['dependencies'];
            }
        }
        
        return new self($dependencies);
    }

    /** @return string[] */
    public function getDependencies(string $class): array
    {
        return $this->dependencies[$class] ?? [];
    }

    /** @return array<string, string[]> */
    public function getAllDependencies(): array
    {
        return $this->dependencies;
    }

    public function hasClass(string $class): bool
    {
        return isset($this->dependencies[$class]);
    }

    /** @return string[] */
    public function getAllClasses(): array
    {
        return array_keys($this->dependencies);
    }
}