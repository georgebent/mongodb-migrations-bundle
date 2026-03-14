<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Domain\Contract;

interface ErrorInterface
{
    public function identifier(): string;

    public function message(): string;
}
