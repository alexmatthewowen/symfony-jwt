<?php

declare(strict_types=1);

namespace App\Http\RequestPayload;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final readonly class RegisterPayload implements RequestPayloadInterface
{
    public static function fromArray(array $payload): RequestPayloadInterface
    {
        return new self($payload['email'] ?? null, $payload['password'] ?? null);
    }

    public function __construct(
        #[NotBlank]
        #[Email]
        #[Length(max: 120)]
        private mixed $email,

        #[NotBlank]
        #[Length(min: 8, max: 120)]
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