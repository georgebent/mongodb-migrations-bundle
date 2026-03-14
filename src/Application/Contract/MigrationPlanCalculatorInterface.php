<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\ExecutionDirection;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;

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
