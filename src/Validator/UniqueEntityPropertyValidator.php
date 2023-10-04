<?php

declare(strict_types=1);

namespace App\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class UniqueEntityPropertyValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEntityProperty) {
            throw new UnexpectedTypeException($constraint, UniqueEntityProperty::class);
        }

        $repository = $this->entityManager->getRepository($constraint->entityName);
        $entity = $repository->findOneBy([$constraint->property => $value]);

        if (null !== $entity) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}