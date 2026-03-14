<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationPlan;

interface MigrationRunnerInterface
{
    public function run(
        MigrationConfiguration $configuration,
        MigrationPlan $migrationPlan,
    ): MigrationExecutionResultInterface;
}
