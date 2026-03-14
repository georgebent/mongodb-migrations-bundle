<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Factory;

use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationDefinition;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationPlan;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\ExecutionDirection;

final readonly class MigrationPlanFactory
{
    /**
     * @param MigrationDefinition[] $migrationDefinitions
     */
    public function create(ExecutionDirection $executionDirection, array $migrationDefinitions): MigrationPlan
    {
        return new MigrationPlan($executionDirection, $migrationDefinitions);
    }
}
