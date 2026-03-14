<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Domain\Migration;

final readonly class MigrationStatus
{
    public const string DATABASE_DRIVER_MONGODB = 'MongoDB';

    public function __construct(
        private string $databaseDriver,
        private string $databaseName,
        private ?string $configurationSource,
        private string $versionCollectionName,
        private string $migrationsNamespace,
        private string $migrationsDirectory,
        private ?MigrationVersion $currentVersion,
        private ?MigrationVersion $latestVersion,
        private MigrationStatusNumbers $statusNumbers,
    ) {}

    public function databaseDriver(): string
    {
        return $this->databaseDriver;
    }

    public function databaseName(): string
    {
        return $this->databaseName;
    }

    public function configurationSource(): ?string
    {
        return $this->configurationSource;
    }

    public function versionCollectionName(): string
    {
        return $this->versionCollectionName;
    }

    public function migrationsNamespace(): string
    {
        return $this->migrationsNamespace;
    }

    public function migrationsDirectory(): string
    {
        return $this->migrationsDirectory;
    }

    public function currentVersion(): ?MigrationVersion
    {
        return $this->currentVersion;
    }

    public function latestVersion(): ?MigrationVersion
    {
        return $this->latestVersion;
    }

    public function numbers(): MigrationStatusNumbers
    {
        return $this->statusNumbers;
    }
}
