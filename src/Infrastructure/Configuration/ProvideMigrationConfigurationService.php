<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Configuration;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfigurationProviderInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfigurationResultInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationConfigurationResult;

final readonly class ProvideMigrationConfigurationService implements MigrationConfigurationProviderInterface
{
    public function __construct(
        private string $databaseName,
        private string $migrationsNamespace,
        private string $migrationsDirectory,
        private string $versionCollectionName,
        private ?string $databaseUrl,
        private ?string $configurationSource,
    ) {}

    public function provide(): MigrationConfigurationResultInterface
    {
        return new MigrationConfigurationResult(
            true,
            new MigrationConfiguration(
                $this->databaseName,
                $this->migrationsNamespace,
                $this->migrationsDirectory,
                $this->versionCollectionName,
                $this->databaseUrl,
                $this->configurationSource,
            ),
        );
    }
}
