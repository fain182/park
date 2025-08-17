<?php

declare(strict_types=1);

namespace Park\Validator;

use Park\Analyzer\AstCodeAnalyzer;
use Park\Analyzer\CodeAnalyzerInterface;
use Park\Rules\RuleInterface;
use Park\Scanner\FileScanner;

class RuleValidator
{
    private CodeAnalyzerInterface $analyzer;
    private FileScanner $scanner;

    public function __construct(?CodeAnalyzerInterface $analyzer = null, ?FileScanner $scanner = null)
    {
        $this->analyzer = $analyzer ?? new AstCodeAnalyzer();
        $this->scanner = $scanner ?? new FileScanner();
    }

    /** @param RuleInterface[] $rules */
    public function validate(array $rules, string $directory = 'src'): array
    {
        $violations = [];
        $dependencies = $this->analyzeDependencies($directory);

        foreach ($rules as $rule) {
            $ruleViolations = $rule->validate($dependencies);
            $violations = array_merge($violations, $ruleViolations);
        }

        return $violations;
    }

    private function analyzeDependencies(string $directory): array
    {
        $dependencies = [];
        
        foreach ($this->scanner->scanPhpFiles($directory) as $file) {
            $content = $file->getContents();
            $result = $this->analyzer->analyzeFile($content);
            
            if ($result['namespace']) {
                $dependencies[$result['namespace']] = $result['dependencies'];
            }
        }
        
        return $dependencies;
    }

}