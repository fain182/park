<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Domain\User\Internal\UserValidator;

class UserService
{
    private UserRepository $repository;
    private UserValidator $validator;

    public function __construct(UserRepository $repository, UserValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    public function getUser(int $id): ?User
    {
        return $this->repository->findById($id);
    }
}