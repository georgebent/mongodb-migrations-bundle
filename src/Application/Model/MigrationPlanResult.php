<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Model;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationPlanResultInterface;
use GeorgeBent\MongodbMigrationsBundle\Domain\Contract\ErrorInterface;

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
