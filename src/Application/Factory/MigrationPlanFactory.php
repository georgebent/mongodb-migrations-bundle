<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Factory;

use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationDefinition;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationPlan;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\ExecutionDirection;

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
