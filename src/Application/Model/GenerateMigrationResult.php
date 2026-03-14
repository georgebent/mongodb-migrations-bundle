<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Model;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\GenerateMigrationResultInterface;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Contract\ErrorInterface;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;

final readonly class GenerateMigrationResult implements GenerateMigrationResultInterface
{
    public function __construct(
        private bool $success,
        private ?MigrationVersion $generatedMigrationVersion = null,
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

    public function generatedMigrationVersion(): ?MigrationVersion
    {
        return $this->generatedMigrationVersion;
    }
}
