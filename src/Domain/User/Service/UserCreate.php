<?php

namespace App\Domain\User\Service;

use App\Domain\User\Repository\UserCreateRepository;
use App\Exception\ValidationException;

final class UserCreate
{
    private $repository;

    public function __construct(UserCreateRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createUser(array $data): int
    {
        $this->validateNewUser($data);
        $userId = $this->repository->insertUser($data);

        return $userId;
    }

    private function validateNewUser(array $data): void
    {
        $errors = [];

        if (empty($data['username'])) {
            $errors['username'] = 'Input required';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Input required';
        } elseif (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Invalid email address';
        }

        if ($errors) {
            throw new ValidationException('Please check your input', $errors);
        }
    }
}