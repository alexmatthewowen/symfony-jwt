<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Handler;

use App\Domain\Command\RegisterUserCommand;
use App\Domain\Entity\User;
use App\Domain\Handler\RegisterUserCommandHandler;
use App\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

final class RegisterUserCommandHandlerUnitTest extends TestCase
{
    private readonly UserRepositoryInterface&MockObject $userRepository;
    private readonly PasswordHasherInterface&MockObject $hasher;
    private readonly RegisterUserCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->hasher = $this->createMock(PasswordHasherInterface::class);
        $this->handler = new RegisterUserCommandHandler(
            $this->userRepository,
            $this->hasher
        );
    }

    public function testUserSavedAsExpected(): void
    {
        $unhashed = 'AprettyInsecuRePassword12!';
        $hashed = 'TheHashedPassword';
        $command = new RegisterUserCommand(Uuid::uuid4(), 'alex.owen@test.com', $unhashed);

        $this->hasher
            ->expects($this->once())
            ->method('hash')
            ->with($unhashed)
            ->willReturn($hashed)
        ;

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (User $user) use ($command, $hashed): bool {
                $this->assertSame(
                    $command->id,
                    $user->getId()
                );
                $this->assertSame(
                    $command->email,
                    $user->getEmail()
                );
                $this->assertSame(
                    $hashed,
                    $user->getPassword()
                );

                return true;
            }))
        ;

        ($this->handler)($command);
    }
}