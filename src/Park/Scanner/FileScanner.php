<?php

declare(strict_types=1);

namespace Park\Scanner;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class FileScanner
{
    /**
     * Scan directory for PHP files and return them
     * 
     * @param string $directory Directory to scan
     * @return iterable<SplFileInfo> PHP files found
     */
    public function scanPhpFiles(string $directory): iterable
    {
        if (!is_dir($directory)) {
            return [];
        }
        
        $finder = new Finder();
        $finder->files()
               ->name('*.php')
               ->in($directory)
               ->exclude(['vendor', 'tests']);
        
        return $finder;
    }
}