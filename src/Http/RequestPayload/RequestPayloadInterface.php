<?php

declare(strict_types=1);

namespace App\Http\RequestPayload;

interface RequestPayloadInterface
{
    public static function fromArray(array $payload): self;
}