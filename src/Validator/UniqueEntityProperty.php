<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class UniqueEntityProperty extends Constraint
{
    /**
     * @param class-string $entityName
     * @param string $property
     */
    public function __construct(public string $entityName, public string $property, public string $message, mixed $options = null, array $groups = null, mixed $payload = null)
    {
        parent::__construct($options, $groups, $payload);
    }
}