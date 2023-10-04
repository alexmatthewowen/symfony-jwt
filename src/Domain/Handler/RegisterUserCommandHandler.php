<?php

declare(strict_types=1);

namespace App\Domain\Handler;

use App\Domain\Command\RegisterUserCommand;
use App\Domain\Entity\User;
use App\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

#[AsMessageHandler(handles: RegisterUserCommand::class)]
final readonly class RegisterUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $hasher
    ) {
    }

    public function __invoke(RegisterUserCommand $command): void
    {
        $user = new User(
            $command->id,
            $command->email,
            $this->hasher->hash($command->password)
        );

       $this->userRepository->save($user);
    }
}