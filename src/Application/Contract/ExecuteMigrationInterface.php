<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\ExecutionDirection;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;

interface ExecuteMigrationInterface
{
    public function execute(
        MigrationConfiguration $configuration,
        MigrationVersion $migrationVersion,
        ExecutionDirection $executionDirection,
    ): MigrationExecutionResultInterface;
}
