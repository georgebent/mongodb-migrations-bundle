<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Service;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationRegistryInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationStatusProviderInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationStatusResultInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\VersionStorageInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationStatusResult;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\Factory\MigrationStatusFactory;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\Factory\MigrationStatusNumbersFactory;

final readonly class ProvideMigrationStatusService implements MigrationStatusProviderInterface
{
    public function __construct(
        private MigrationRegistryInterface $migrationRegistry,
        private VersionStorageInterface $versionStorage,
        private MigrationStatusFactory $migrationStatusFactory,
        private MigrationStatusNumbersFactory $migrationStatusNumbersFactory,
    ) {}

    public function provide(MigrationConfiguration $configuration): MigrationStatusResultInterface
    {
        $availableMigrationDefinitions = $this->migrationRegistry->all($configuration);
        $executedMigrationVersions = $this->versionStorage->all($configuration);
        $availableVersionsMap = [];
        $executedVersionsMap = [];

        foreach ($availableMigrationDefinitions as $migrationDefinition) {
            $availableVersionsMap[$migrationDefinition->version()->value()] = true;
        }

        foreach ($executedMigrationVersions as $executedMigrationVersion) {
            $executedVersionsMap[$executedMigrationVersion->value()] = true;
        }

        $executedUnavailableMigrationsCount = 0;

        foreach ($executedMigrationVersions as $executedMigrationVersion) {
            if (false === isset($availableVersionsMap[$executedMigrationVersion->value()])) {
                ++$executedUnavailableMigrationsCount;
            }
        }

        $newMigrationsCount = 0;

        foreach ($availableMigrationDefinitions as $migrationDefinition) {
            if (false === isset($executedVersionsMap[$migrationDefinition->version()->value()])) {
                ++$newMigrationsCount;
            }
        }

        $latestMigrationDefinition = $availableMigrationDefinitions === []
            ? null
            : $availableMigrationDefinitions[array_key_last($availableMigrationDefinitions)];

        $migrationStatusNumbers = $this->migrationStatusNumbersFactory->create(
            count($executedMigrationVersions),
            $executedUnavailableMigrationsCount,
            count($availableMigrationDefinitions),
            $newMigrationsCount,
        );

        return new MigrationStatusResult(
            true,
            $this->migrationStatusFactory->create(
                $configuration->databaseName(),
                $configuration->configurationSource(),
                $configuration->versionCollectionName(),
                $configuration->migrationsNamespace(),
                $configuration->migrationsDirectory(),
                $this->versionStorage->current($configuration),
                $latestMigrationDefinition?->version(),
                $migrationStatusNumbers,
            ),
        );
    }
}
