<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\User\User;
use App\Domain\User\UserRepository;

class UserService
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getUser(int $id): ?User
    {
        return $this->repository->findById($id);
    }
}