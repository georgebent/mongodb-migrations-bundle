<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;

interface VersionStorageInterface
{
    public function current(MigrationConfiguration $configuration): ?MigrationVersion;

    public function latestExecuted(MigrationConfiguration $configuration): ?MigrationVersion;

    public function has(MigrationConfiguration $configuration, MigrationVersion $migrationVersion): bool;

    public function markExecuted(MigrationConfiguration $configuration, MigrationVersion $migrationVersion): void;

    public function markRolledBack(MigrationConfiguration $configuration, MigrationVersion $migrationVersion): void;

    /**
     * @return MigrationVersion[]
     */
    public function all(MigrationConfiguration $configuration): array;
}
