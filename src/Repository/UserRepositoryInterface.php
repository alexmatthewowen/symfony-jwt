<?php

namespace App\Repository;

use App\Domain\Entity\User;
use Doctrine\Persistence\ObjectRepository;

interface UserRepositoryInterface extends ObjectRepository
{
    public function save(User $user): void;

    public function findByEmail(string $email): ?User;
}