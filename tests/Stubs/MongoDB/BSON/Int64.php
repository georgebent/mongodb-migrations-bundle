<?php

declare(strict_types=1);

namespace MongoDB\BSON;

final class Int64 implements \Stringable
{
    public function __construct(
        private readonly string $value = '0',
    ) {}

    public function __toString(): string
    {
        return $this->value;
    }
}
