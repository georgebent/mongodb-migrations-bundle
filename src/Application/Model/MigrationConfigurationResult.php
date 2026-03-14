<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Model;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfigurationResultInterface;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Contract\ErrorInterface;

final readonly class MigrationConfigurationResult implements MigrationConfigurationResultInterface
{
    public function __construct(
        private bool $success,
        private ?MigrationConfiguration $migrationConfiguration = null,
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

    public function migrationConfiguration(): ?MigrationConfiguration
    {
        return $this->migrationConfiguration;
    }
}
