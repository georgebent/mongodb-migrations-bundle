<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Model;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationExecutionResultInterface;
use GeorgeBent\MongodbMigrationsBundle\Domain\Contract\ErrorInterface;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;

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
