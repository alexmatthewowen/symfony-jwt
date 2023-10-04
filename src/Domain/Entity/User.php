<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Repository\UserMariaDbRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[Entity(repositoryClass: UserMariaDbRepository::class)]
class User implements UserInterface
{
    #[Id]
    #[Column(type: 'uuid_binary', unique: true)]
    private readonly UuidInterface $id;

    #[Column(name: 'email', type: 'string', length: 120, unique: true)]
    private string $email;

    #[Column(name: 'password', type: 'string', length: 120)]
    private string $password;

    public function __construct(
        UuidInterface $id,
        string $email,
        string $password
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->id->toString();
    }
}