<?php

declare(strict_types=1);

namespace Park\Analyzer;

interface CodeAnalyzerInterface
{
    /**
     * Analyze dependencies from a single PHP file content
     * 
     * @param string $content PHP file content
     * @return array{namespace: string|null, dependencies: string[]} File analysis result
     */
    public function analyzeFile(string $content): array;
}