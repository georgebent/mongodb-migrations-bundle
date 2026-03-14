<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Model;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationExecutionResultInterface;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Contract\ErrorInterface;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;

final readonly class MigrationExecutionResult implements MigrationExecutionResultInterface
{
    /**
     * @param MigrationVersion[] $processedMigrationVersions
     */
    public function __construct(
        private bool $success,
        private array $processedMigrationVersions = [],
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

    /**
     * @return MigrationVersion[]
     */
    public function processedMigrationVersions(): array
    {
        return $this->processedMigrationVersions;
    }
}
