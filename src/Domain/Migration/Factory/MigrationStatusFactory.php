<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory;

use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationStatus;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationStatusNumbers;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;

final readonly class MigrationStatusFactory
{
    public function create(
        string $databaseName,
        ?string $configurationSource,
        string $versionCollectionName,
        string $migrationsNamespace,
        string $migrationsDirectory,
        ?MigrationVersion $currentVersion,
        ?MigrationVersion $latestVersion,
        MigrationStatusNumbers $statusNumbers,
    ): MigrationStatus {
        return new MigrationStatus(
            MigrationStatus::DATABASE_DRIVER_MONGODB,
            $databaseName,
            $configurationSource,
            $versionCollectionName,
            $migrationsNamespace,
            $migrationsDirectory,
            $currentVersion,
            $latestVersion,
            $statusNumbers,
        );
    }
}
