<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Infrastructure\MongoDB;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\VersionStorageInterface;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory\MigrationVersionFactory;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Collection;

final readonly class MongoDBVersionStorage implements VersionStorageInterface
{
    private const string FIELD_VERSION = 'version';

    private const string FIELD_EXECUTED_AT = 'executed_at';

    private const string INDEX_NAME_VERSION = 'idx_version_unique';

    public function __construct(
        private DatabaseFactoryInterface $databaseFactory,
        private MigrationVersionFactory $migrationVersionFactory,
    ) {}

    public function current(MigrationConfiguration $configuration): ?MigrationVersion
    {
        return $this->latestExecuted($configuration);
    }

    public function latestExecuted(MigrationConfiguration $configuration): ?MigrationVersion
    {
        $executedMigrationDocument = $this->versionCollection($configuration)->findOne([], [
            'sort' => [self::FIELD_VERSION => -1],
        ]);

        if (null === $executedMigrationDocument) {
            return null;
        }

        return $this->migrationVersionFactory->fromString((string) $executedMigrationDocument[self::FIELD_VERSION]);
    }

    public function has(MigrationConfiguration $configuration, MigrationVersion $migrationVersion): bool
    {
        return $this->versionCollection($configuration)->findOne([
            self::FIELD_VERSION => $migrationVersion->value(),
        ]) !== null;
    }

    public function markExecuted(MigrationConfiguration $configuration, MigrationVersion $migrationVersion): void
    {
        $this->versionCollection($configuration)->insertOne([
            self::FIELD_VERSION => $migrationVersion->value(),
            self::FIELD_EXECUTED_AT => new UTCDateTime(),
        ]);
    }

    public function markRolledBack(MigrationConfiguration $configuration, MigrationVersion $migrationVersion): void
    {
        $this->versionCollection($configuration)->deleteOne([
            self::FIELD_VERSION => $migrationVersion->value(),
        ]);
    }

    /**
     * @return MigrationVersion[]
     */
    public function all(MigrationConfiguration $configuration): array
    {
        $executedMigrationVersions = [];
        $executedMigrationDocuments = $this->versionCollection($configuration)->find([], [
            'sort' => [self::FIELD_VERSION => 1],
        ]);

        foreach ($executedMigrationDocuments as $executedMigrationDocument) {
            $executedMigrationVersions[] = $this->migrationVersionFactory->fromString(
                (string) $executedMigrationDocument[self::FIELD_VERSION],
            );
        }

        return $executedMigrationVersions;
    }

    private function versionCollection(MigrationConfiguration $configuration): Collection
    {
        $versionCollection = $this->databaseFactory
            ->create($configuration)
            ->selectCollection($configuration->versionCollectionName());

        $versionCollection->createIndex(
            [self::FIELD_VERSION => 1],
            [
                'unique' => true,
                'name' => self::INDEX_NAME_VERSION,
            ],
        );

        return $versionCollection;
    }
}
