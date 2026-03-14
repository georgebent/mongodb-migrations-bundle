<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\ExecutionDirection;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;

interface ExecuteMigrationInterface
{
    public function execute(
        MigrationConfiguration $configuration,
        MigrationVersion $migrationVersion,
        ExecutionDirection $executionDirection,
    ): MigrationExecutionResultInterface;
}
