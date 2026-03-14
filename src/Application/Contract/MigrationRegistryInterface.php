<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationDefinition;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;

interface MigrationRegistryInterface
{
    public function find(MigrationConfiguration $configuration, MigrationVersion $migrationVersion): ?MigrationDefinition;

    /**
     * @return MigrationDefinition[]
     */
    public function all(MigrationConfiguration $configuration): array;
}
