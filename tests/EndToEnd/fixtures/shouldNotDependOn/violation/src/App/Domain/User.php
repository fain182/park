<?php

declare(strict_types=1);

namespace App\Domain;

use App\Infrastructure\Database;

class User
{
    private Database $database;
    
    public function __construct(Database $database)
    {
        $this->database = $database;
    }
    
    public function save(): void
    {
        $this->database->query('INSERT INTO users...');
    }
}