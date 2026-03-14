<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Model;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfigurationResultInterface;
use GeorgeBent\MongodbMigrationsBundle\Domain\Contract\ErrorInterface;

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
