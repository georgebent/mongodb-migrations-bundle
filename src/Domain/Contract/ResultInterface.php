<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Domain\Contract;

interface ResultInterface
{
    public function isSuccess(): bool;

    public function error(): ?ErrorInterface;
}
