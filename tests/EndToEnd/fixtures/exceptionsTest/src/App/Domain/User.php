<?php

declare(strict_types=1);

namespace App\Domain;

use App\Infrastructure\Database;

class User
{
    private Database $db;
    
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
}