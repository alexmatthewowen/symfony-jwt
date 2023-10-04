<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Domain\Command\RegisterUserCommand;
use App\Domain\Entity\User;
use App\Http\RequestPayload\RegisterPayload;
use App\Repository\UserRepositoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    path: '/register',
    name: 'register',
    methods: 'POST'
)]
final readonly class RegisterController
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private JWTTokenManagerInterface $jwtManager,
        private MessageBusInterface $commandBus
    ) {
    }

    public function __invoke(RegisterPayload $payload): JsonResponse
    {
        $id = Uuid::uuid4();
        $this->commandBus->dispatch(new RegisterUserCommand(
            $id,
            $payload->getEmail(),
            $payload->getPassword()
        ));

        $user = $this->userRepository->find($id);

        if (!$user instanceof User) {
            throw new \RuntimeException('Unable to find user.');
        }

        return new JsonResponse(
            [
            'token' => $this->jwtManager->create($user)
            ],
            Response::HTTP_CREATED
        );
    }
}