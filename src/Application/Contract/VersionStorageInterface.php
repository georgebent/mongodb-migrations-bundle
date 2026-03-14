<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;

interface VersionStorageInterface
{
    public function current(MigrationConfiguration $configuration): ?MigrationVersion;

    public function latestExecuted(MigrationConfiguration $configuration): ?MigrationVersion;

    public function has(MigrationConfiguration $configuration, MigrationVersion $version): bool;

    public function markExecuted(MigrationConfiguration $configuration, MigrationVersion $version): void;

    public function markRolledBack(MigrationConfiguration $configuration, MigrationVersion $version): void;

    /**
     * @return MigrationVersion[]
     */
    public function all(MigrationConfiguration $configuration): array;
}
