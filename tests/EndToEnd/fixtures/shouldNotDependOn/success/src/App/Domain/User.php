<?php

declare(strict_types=1);

namespace App\Domain;

class User
{
    public string $name;
    
    public function getName(): string
    {
        return $this->name;
    }
}