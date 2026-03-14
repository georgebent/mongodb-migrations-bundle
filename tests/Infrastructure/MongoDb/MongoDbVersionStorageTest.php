<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Tests\Infrastructure\MongoDb;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\Factory\MigrationVersionFactory;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\MongoDb\DatabaseFactoryInterface;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\MongoDb\MongoDbVersionStorage;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\Driver\CursorInterface;
use PHPUnit\Framework\TestCase;

final class MongoDbVersionStorageTest extends TestCase
{
    public function testLatestExecutedReturnsNullWhenCollectionIsEmpty(): void
    {
        $configuration = $this->migrationConfiguration();
        $collection = $this->collectionMock();
        $database = $this->databaseMock($configuration, $collection);
        $databaseFactory = $this->databaseFactory($database);

        $collection->expects(self::once())
            ->method('createIndex')
            ->with(['version' => 1], ['unique' => true, 'name' => 'idx_version_unique'])
            ->willReturn('idx_version_unique');

        $collection->expects(self::once())
            ->method('findOne')
            ->with([], ['sort' => ['version' => -1]])
            ->willReturn(null);

        $versionStorage = new MongoDbVersionStorage($databaseFactory, new MigrationVersionFactory());

        self::assertNull($versionStorage->latestExecuted($configuration));
    }

    public function testHasReturnsTrueWhenVersionExists(): void
    {
        $configuration = $this->migrationConfiguration();
        $collection = $this->collectionMock();
        $database = $this->databaseMock($configuration, $collection);
        $databaseFactory = $this->databaseFactory($database);
        $migrationVersion = new MigrationVersion('20260221000000');

        $collection->expects(self::once())->method('createIndex')->willReturn('idx_version_unique');
        $collection->expects(self::once())
            ->method('findOne')
            ->with(['version' => '20260221000000'])
            ->willReturn(['version' => '20260221000000']);

        $versionStorage = new MongoDbVersionStorage($databaseFactory, new MigrationVersionFactory());

        self::assertTrue($versionStorage->has($configuration, $migrationVersion));
    }

    public function testMarkExecutedInsertsVersionDocument(): void
    {
        $configuration = $this->migrationConfiguration();
        $collection = $this->collectionMock();
        $database = $this->databaseMock($configuration, $collection);
        $databaseFactory = $this->databaseFactory($database);
        $migrationVersion = new MigrationVersion('20260221000000');

        $collection->expects(self::once())->method('createIndex')->willReturn('idx_version_unique');
        $collection->expects(self::once())
            ->method('insertOne')
            ->with(self::callback(static function (array $document): bool {
                return '20260221000000' === $document['version']
                    && $document['executed_at'] instanceof \MongoDB\BSON\UTCDateTime;
            }));

        $versionStorage = new MongoDbVersionStorage($databaseFactory, new MigrationVersionFactory());
        $versionStorage->markExecuted($configuration, $migrationVersion);

        self::assertTrue(true);
    }

    public function testMarkRolledBackDeletesVersionDocument(): void
    {
        $configuration = $this->migrationConfiguration();
        $collection = $this->collectionMock();
        $database = $this->databaseMock($configuration, $collection);
        $databaseFactory = $this->databaseFactory($database);
        $migrationVersion = new MigrationVersion('20260221000000');

        $collection->expects(self::once())->method('createIndex')->willReturn('idx_version_unique');
        $collection->expects(self::once())
            ->method('deleteOne')
            ->with(['version' => '20260221000000']);

        $versionStorage = new MongoDbVersionStorage($databaseFactory, new MigrationVersionFactory());
        $versionStorage->markRolledBack($configuration, $migrationVersion);

        self::assertTrue(true);
    }

    public function testAllReturnsSortedMigrationVersions(): void
    {
        $configuration = $this->migrationConfiguration();
        $collection = $this->collectionMock();
        $database = $this->databaseMock($configuration, $collection);
        $databaseFactory = $this->databaseFactory($database);
        $cursor = new class() implements CursorInterface, \IteratorAggregate
        {
            public function getIterator(): \Traversable
            {
                return new \ArrayIterator([
                    ['version' => '20260221000000'],
                    ['version' => '20260222000000'],
                ]);
            }
        };

        $collection->expects(self::once())->method('createIndex')->willReturn('idx_version_unique');
        $collection->expects(self::once())
            ->method('find')
            ->with([], ['sort' => ['version' => 1]])
            ->willReturn($cursor);

        $versionStorage = new MongoDbVersionStorage($databaseFactory, new MigrationVersionFactory());
        $migrationVersions = $versionStorage->all($configuration);

        self::assertCount(2, $migrationVersions);
        self::assertSame('20260221000000', $migrationVersions[0]->value());
        self::assertSame('20260222000000', $migrationVersions[1]->value());
    }

    private function migrationConfiguration(): MigrationConfiguration
    {
        return new MigrationConfiguration(
            'test_database',
            'App\\Migrations',
            '/tmp/migrations',
            'migrations',
            'mongodb://localhost:27017',
        );
    }

    private function collectionMock(): Collection
    {
        return $this->createMock(Collection::class);
    }

    private function databaseMock(MigrationConfiguration $configuration, Collection $collection): Database
    {
        $database = $this->createMock(Database::class);

        $database->expects(self::once())
            ->method('selectCollection')
            ->with($configuration->versionCollectionName())
            ->willReturn($collection);

        return $database;
    }

    private function databaseFactory(Database $database): DatabaseFactoryInterface
    {
        return new class($database) implements DatabaseFactoryInterface
        {
            public function __construct(private readonly Database $database)
            {
            }

            public function create(MigrationConfiguration $configuration): Database
            {
                return $this->database;
            }
        };
    }
}
