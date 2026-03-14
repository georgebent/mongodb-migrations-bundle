<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Model;

use GeorgeBent\MongoDBMigrationsBundle\Domain\Contract\ErrorInterface;

final readonly class ApplicationError implements ErrorInterface
{
    public function __construct(
        private string $errorIdentifier,
        private string $errorMessage,
    ) {}

    public function identifier(): string
    {
        return $this->errorIdentifier;
    }

    public function message(): string
    {
        return $this->errorMessage;
    }
}
