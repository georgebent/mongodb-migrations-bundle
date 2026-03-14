<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Factory;

use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationDefinition;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;

final readonly class MigrationDefinitionFactory
{
    public function create(
        MigrationVersion $migrationVersion,
        string $migrationClassName,
    ): MigrationDefinition {
        return new MigrationDefinition($migrationVersion, $migrationClassName);
    }
}
