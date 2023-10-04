<?php

declare(strict_types=1);

namespace App\Domain\Command;

use Ramsey\Uuid\UuidInterface;

final readonly class RegisterUserCommand
{
    public function __construct(
        public UuidInterface $id,
        public string $email,
        public string $password
    ) {
    }
}