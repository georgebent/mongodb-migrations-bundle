<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Model;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationPlanResultInterface;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Contract\ErrorInterface;

final readonly class MigrationPlanResult implements MigrationPlanResultInterface
{
    public function __construct(
        private bool $success,
        private ?MigrationPlan $migrationPlan = null,
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

    public function migrationPlan(): ?MigrationPlan
    {
        return $this->migrationPlan;
    }
}
