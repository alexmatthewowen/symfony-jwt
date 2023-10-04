<?php

declare(strict_types=1);

namespace App\Http\EventListener;

use App\Http\Exception\ParameterValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ParameterValidationExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if (!$throwable instanceof ParameterValidationException) {
            return;
        }

        $response = [
            'code' => '422',
            'message' => 'Parameter issues',
            'errors' => []
        ];

        foreach($throwable->violationList as $violation) {
            $response['errors'][$violation->getPropertyPath()] = $violation->getMessage();
        }

        $event->setResponse(new JsonResponse($response, Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}