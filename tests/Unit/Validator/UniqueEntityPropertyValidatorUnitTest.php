<?php

declare(strict_types=1);

namespace Tests\Unit\Validator;

use App\Domain\Entity\User;
use App\Repository\UserRepositoryInterface;
use App\Validator\UniqueEntityProperty;
use App\Validator\UniqueEntityPropertyValidator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class UniqueEntityPropertyValidatorUnitTest extends ConstraintValidatorTestCase
{
    private readonly EntityManagerInterface&MockObject $entityManager;

    public function testThrowsOnInvalidConstraintPassed(): void
    {
        $constraint = new Type('string');
        $this->expectExceptionObject(new UnexpectedTypeException($constraint, UniqueEntityProperty::class));

        $this->validator->validate('test', $constraint);
    }

    public function testViolationRaisedOnEntityFound(): void
    {
        $constraint = new UniqueEntityProperty(User::class, 'email', 'Email is in use.');

        $repository = $this->createMock(UserRepositoryInterface::class);
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($repository)
        ;

        $user = new User(
            Uuid::uuid4(),
            'alex@test.com',
            'password'
        );
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'alex@test.com'])
            ->willReturn($user)
        ;

        $this->validator->validate('alex@test.com', $constraint);

        $this->buildViolation('Email is in use.')->assertRaised();
    }

    public function testNoViolationsOnNoEntitiesFound(): void
    {
        $constraint = new UniqueEntityProperty(User::class, 'email', 'Email is in use.');

        $repository = $this->createMock(UserRepositoryInterface::class);
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($repository)
        ;

        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'alex@test.com'])
            ->willReturn(null)
        ;

        $this->validator->validate('alex@test.com', $constraint);

        $this->assertNoViolation();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        return new UniqueEntityPropertyValidator($this->entityManager);
    }
}