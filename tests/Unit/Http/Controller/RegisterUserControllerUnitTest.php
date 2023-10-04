<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Handler;

use App\Domain\Command\RegisterUserCommand;
use App\Domain\Entity\User;
use App\Http\Controller\RegisterController;
use App\Http\RequestPayload\RegisterPayload;
use App\Repository\UserRepositoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class RegisterUserControllerUnitTest extends TestCase
{
    private readonly UserRepositoryInterface&MockObject $userRepository;
    private readonly JWTTokenManagerInterface&MockObject $tokenManager;
    private readonly MessageBusInterface&MockObject $commandBus;
    private readonly RegisterController $controller;
    private RegisterPayload $payload;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->tokenManager = $this->createMock(JWTTokenManagerInterface::class);
        $this->commandBus = $this->createMock(MessageBusInterface::class);
        $this->controller = new RegisterController(
            $this->userRepository,
            $this->tokenManager,
            $this->commandBus
        );

        $this->payload = new RegisterPayload('alex.owen@testing.org', 'PasswordUnhashed');

        $this->commandBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (RegisterUserCommand $command): bool {
                $this->assertSame(
                    $this->payload->getEmail(),
                    $command->email
                );
                $this->assertSame(
                    $this->payload->getPassword(),
                    $command->password
                );

                return true;
            }))
            ->willReturn(new Envelope($this->payload))
        ;
    }

    public function testSuccessfulRegistration(): void
    {
        $user = new User(
            Uuid::uuid4(),
            $this->payload->getEmail(),
            'PasswordPretendHash'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with($this->callback(static fn (UuidInterface $uuid): bool => true))
            ->willReturn($user)
        ;

        $this->tokenManager
            ->expects($this->once())
            ->method('create')
            ->with($user)
            ->willReturn('dummy-jwt')
        ;

        $response = ($this->controller)($this->payload);
        $this->assertSame(
            \json_encode(
                [
                    'token' => 'dummy-jwt'
                ]
            ),
            $response->getContent()
        );
    }

    public function testExceptionThrownWhenUserNotFound(): void
    {
        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with($this->callback(static fn (UuidInterface $uuid): bool => true))
            ->willReturn(null)
        ;

        $this->tokenManager
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->expectExceptionObject(new \RuntimeException('Unable to find user.'));

        ($this->controller)($this->payload);
    }
}