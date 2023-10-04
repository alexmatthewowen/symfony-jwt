<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Domain\Entity\User;
use App\Http\RequestPayload\LoginPayload;
use App\Repository\UserRepositoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    path: '/login',
    name: 'login',
    methods: 'POST'
)]
final readonly class LoginController
{
    public function __construct(
        private JWTTokenManagerInterface $tokenManager,
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $hasher
    ) {
    }

    public function __invoke(LoginPayload $payload): JsonResponse
    {
        $user = $this->userRepository->findByEmail($payload->getEmail());

        if (!$user instanceof User || !$this->hasher->verify($user->getPassword(), $payload->getPassword())) {
            return new JsonResponse([
                'code' => '401',
                'message' => 'Invalid login credentials.',
            ],
            Response::HTTP_UNAUTHORIZED
            );
        }

        return new JsonResponse(['token' => $this->tokenManager->create($user)]);
    }
}