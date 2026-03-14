<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Model;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationStatusResultInterface;
use GeorgeBent\MongodbMigrationsBundle\Domain\Contract\ErrorInterface;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationStatus;

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
