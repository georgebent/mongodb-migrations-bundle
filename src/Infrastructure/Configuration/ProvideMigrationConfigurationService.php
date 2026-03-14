<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Infrastructure\Configuration;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfigurationProviderInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfigurationResultInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationConfigurationResult;

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
