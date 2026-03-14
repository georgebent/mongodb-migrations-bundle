<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Factory;

use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationDefinition;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;

final readonly class MigrationDefinitionFactory
{
    public function create(
        MigrationVersion $migrationVersion,
        string $migrationClassName,
    ): MigrationDefinition {
        return new MigrationDefinition($migrationVersion, $migrationClassName);
    }
}
