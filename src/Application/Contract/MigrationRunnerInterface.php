<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationPlan;

interface MigrationRunnerInterface
{
    public function run(
        MigrationConfiguration $configuration,
        MigrationPlan $migrationPlan,
    ): MigrationExecutionResultInterface;
}
