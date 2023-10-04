<?php

declare(strict_types=1);

namespace App\Http\ValueResolver;

use App\Http\Exception\ParameterValidationException;
use App\Http\RequestPayload\RequestPayloadInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class RequestPayloadValueResolver implements ValueResolverInterface
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        try {
            $reflection = new \ReflectionClass($type);
        } catch (\ReflectionException) {
            return [];
        }

        if (!$reflection->implementsInterface(RequestPayloadInterface::class)) {
            return [];
        }

        $json = \json_decode($request->getContent(), true, JSON_THROW_ON_ERROR);
        $dto = ($type)::fromArray($json);

        $errors = $this->validator->validate($dto);

        if (0 < $errors->count()) {
            throw new ParameterValidationException($errors);
        }

        return [$dto];
    }
}