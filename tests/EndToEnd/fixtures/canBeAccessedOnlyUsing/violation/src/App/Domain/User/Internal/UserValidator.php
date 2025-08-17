<?php

declare(strict_types=1);

namespace App\Domain\User\Internal;

class UserValidator
{
    public function validate(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}