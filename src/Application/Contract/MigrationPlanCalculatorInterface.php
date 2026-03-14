<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\ExecutionDirection;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;

interface MigrationPlanCalculatorInterface
{
    public function forLatest(MigrationConfiguration $configuration): MigrationPlanResultInterface;

    public function forVersion(
        MigrationConfiguration $configuration,
        MigrationVersion $migrationVersion,
        ExecutionDirection $executionDirection,
    ): MigrationPlanResultInterface;

    public function forRollback(MigrationConfiguration $configuration): MigrationPlanResultInterface;
}
