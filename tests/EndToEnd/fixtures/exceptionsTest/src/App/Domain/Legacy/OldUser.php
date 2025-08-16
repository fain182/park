<?php

declare(strict_types=1);

namespace App\Domain\Legacy;

use App\Infrastructure\Database;

class OldUser
{
    private Database $db;
    
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
}