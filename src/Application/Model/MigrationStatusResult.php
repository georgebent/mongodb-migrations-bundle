<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Model;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationStatusResultInterface;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Contract\ErrorInterface;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationStatus;

final readonly class MigrationStatusResult implements MigrationStatusResultInterface
{
    public function __construct(
        private bool $success,
        private ?MigrationStatus $migrationStatus = null,
        private ?ErrorInterface $error = null,
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function error(): ?ErrorInterface
    {
        return $this->error;
    }

    public function migrationStatus(): ?MigrationStatus
    {
        return $this->migrationStatus;
    }
}
