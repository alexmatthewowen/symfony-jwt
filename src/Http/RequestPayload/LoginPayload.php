<?php

declare(strict_types=1);

namespace App\Http\RequestPayload;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

final readonly class LoginPayload implements RequestPayloadInterface
{
    public static function fromArray(array $payload): RequestPayloadInterface
    {
        return new self($payload['email'] ?? null, $payload['password'] ?? null);
    }

    public function __construct(

        #[NotBlank]
        #[Email]
        private mixed $email,

        #[NotBlank]
        #[Type('string')]
        private mixed $password
    ) {
    }

    public function getEmail(): string
    {
        return (string) $this->email;
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }
}