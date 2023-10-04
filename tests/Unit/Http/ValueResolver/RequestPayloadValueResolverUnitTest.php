<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueResolver;

use App\Http\Exception\ParameterValidationException;
use App\Http\RequestPayload\RegisterPayload;
use App\Http\ValueResolver\RequestPayloadValueResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestPayloadValueResolverUnitTest extends TestCase
{
    private readonly ValidatorInterface&MockObject $validator;
    private readonly RequestPayloadValueResolver $resolver;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->resolver = new RequestPayloadValueResolver($this->validator);
    }

    public static function unsupportedTypeProvider(): \Generator
    {
        yield 'with string' => [
            'type' => 'dummy-string'
        ];

        yield 'with unsupported object' => [
            'type' => Uuid::class
        ];
    }

    /**
     * @dataProvider unsupportedTypeProvider
     */
    public function testEmptyArrayOnUnsupportedType(mixed $type): void
    {
        $result = $this->resolver->resolve(new Request(), new ArgumentMetadata(
            'dummy-type',
            $type,
            false,
            false,
            null
        ));

        $this->assertSame([], $result);
    }

    public function testThrowsOnViolationsFound(): void
    {
        $violationList = new ConstraintViolationList([new ConstraintViolation(
            'Dummy violation',
            null,
            [],
            null,
            'dummy',
            null
        )]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($this->callback(static fn (RegisterPayload $payload): bool => true))
            ->willReturn($violationList)
        ;

        $this->expectExceptionObject(new ParameterValidationException($violationList));

        $this->resolver->resolve(
            new Request(content: \json_encode([
                'email' => 'alex.owen@test.com',
                'password' => 'Password12!'
            ]))
            , new ArgumentMetadata(
            'dummy-type',
            RegisterPayload::class,
            false,
            false,
            null
        ));
    }

    public function testDtoReturnedOnSuccess(): void
    {
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($this->callback(static fn (RegisterPayload $payload): bool => true))
            ->willReturn(new ConstraintViolationList())
        ;

        $result = $this->resolver->resolve(
            new Request(content: \json_encode([
                'email' => 'alex.owen@test.com',
                'password' => 'Password12!'
            ])),
            new ArgumentMetadata(
            'dummy-type',
            RegisterPayload::class,
            false,
            false,
            null
        ));

        $this->assertInstanceOf(RegisterPayload::class, $result[0] ?? null);
    }
}