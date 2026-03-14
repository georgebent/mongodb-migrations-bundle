<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationDefinition;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;

interface MigrationRegistryInterface
{
    public function find(MigrationConfiguration $configuration, MigrationVersion $version): ?MigrationDefinition;

    /**
     * @return MigrationDefinition[]
     */
    public function all(MigrationConfiguration $configuration): array;
}
