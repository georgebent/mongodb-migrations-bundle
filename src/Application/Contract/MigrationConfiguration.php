<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

final readonly class MigrationConfiguration
{
    public function __construct(
        private string $databaseName,
        private string $migrationsNamespace,
        private string $migrationsDirectory,
        private string $versionCollectionName,
        private ?string $databaseUrl = null,
        private ?string $configurationSource = null,
    ) {}

    public function databaseName(): string
    {
        return $this->databaseName;
    }

    public function migrationsNamespace(): string
    {
        return $this->migrationsNamespace;
    }

    public function migrationsDirectory(): string
    {
        return $this->migrationsDirectory;
    }

    public function versionCollectionName(): string
    {
        return $this->versionCollectionName;
    }

    public function databaseUrl(): ?string
    {
        return $this->databaseUrl;
    }

    public function configurationSource(): ?string
    {
        return $this->configurationSource;
    }
}
