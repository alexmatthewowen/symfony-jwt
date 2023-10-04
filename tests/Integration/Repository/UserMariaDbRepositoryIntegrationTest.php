<?php

declare(strict_types=1);

namespace Tests\Integration\Repository;

use App\Domain\Entity\User;
use App\Repository\UserMariaDbRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserMariaDbRepositoryIntegrationTest extends KernelTestCase
{
    private readonly EntityManagerInterface $em;
    private readonly UserMariaDbRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = $this->getContainer()->get(EntityManagerInterface::class);
        $this->repository = $this->getContainer()->get(UserMariaDbRepository::class);
    }

    protected function tearDown(): void
    {
        $this->em
            ->createQueryBuilder()
            ->delete(User::class)
            ->getQuery()
            ->execute()
        ;
    }

    public function testSavePersistsUser(): void
    {
        $id = Uuid::uuid4();
        $user = new User(
            $id,
            'test@test123.com',
            'password123'
        );

        $this->repository->save($user);
        $this->em->flush();
        $this->em->clear();

        $user = $this->em->find(User::class, $id);
        $this->assertInstanceOf(User::class, $user);
    }

    public function testFindByEmailWhenEmailExists(): void
    {
        $email = 'alex.owen@test123.com';
        $user = new User(
            Uuid::uuid4(),
            $email,
            'password123'
        );
        $this->em->persist($user);
        $this->em->flush();
        $this->em->clear();

        $user = $this->repository->findByEmail($email);
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($email, $user->getEmail());
    }

    public function testFindByEmailWhenEmailDoesNotExist(): void
    {
        $email = 'alex.owen@test123.com';
        $user = new User(
            Uuid::uuid4(),
            $email,
            'password123'
        );
        $this->em->persist($user);
        $this->em->flush();
        $this->em->clear();

        $user = $this->repository->findByEmail('email.does.not@exist.com');
        $this->assertNull($user);
    }
}