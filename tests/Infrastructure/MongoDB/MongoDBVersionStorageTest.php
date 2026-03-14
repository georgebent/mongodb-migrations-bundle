<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Tests\Infrastructure\MongoDB;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory\MigrationVersionFactory;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\MongoDB\DatabaseFactoryInterface;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\MongoDB\MongoDBVersionStorage;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\Driver\CursorInterface;
use PHPUnit\Framework\TestCase;

final class MongoDBVersionStorageTest extends TestCase
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

        $versionStorage = new MongoDBVersionStorage($databaseFactory, new MigrationVersionFactory());

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

        $versionStorage = new MongoDBVersionStorage($databaseFactory, new MigrationVersionFactory());

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

        $versionStorage = new MongoDBVersionStorage($databaseFactory, new MigrationVersionFactory());
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

        $versionStorage = new MongoDBVersionStorage($databaseFactory, new MigrationVersionFactory());
        $versionStorage->markRolledBack($configuration, $migrationVersion);

        self::assertTrue(true);
    }

    public function testAllReturnsSortedMigrationVersions(): void
    {
        $configuration = $this->migrationConfiguration();
        $collection = $this->collectionMock();
        $database = $this->databaseMock($configuration, $collection);
        $databaseFactory = $this->databaseFactory($database);
        $cursor = new CursorFixture([
            ['version' => '20260221000000'],
            ['version' => '20260222000000'],
        ]);

        $collection->expects(self::once())->method('createIndex')->willReturn('idx_version_unique');
        $collection->expects(self::once())
            ->method('find')
            ->with([], ['sort' => ['version' => 1]])
            ->willReturn($cursor);

        $versionStorage = new MongoDBVersionStorage($databaseFactory, new MigrationVersionFactory());
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
        return new class ($database) implements DatabaseFactoryInterface {
            public function __construct(private readonly Database $database) {}

            public function create(MigrationConfiguration $configuration): Database
            {
                return $this->database;
            }
        };
    }
}

final class CursorFixture implements CursorInterface
{
    private int $position = 0;

    /**
     * @param array<int, array<string, string>> $documents
     */
    public function __construct(private readonly array $documents) {}

    /**
     * @return array<string, string>
     */
    public function current(): array
    {
        return $this->documents[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->documents[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function getId(): \MongoDB\BSON\Int64
    {
        return new \MongoDB\BSON\Int64('1');
    }

    /**
     * @throws \ReflectionException
     */
    public function getServer(): \MongoDB\Driver\Server
    {
        return new \ReflectionClass(\MongoDB\Driver\Server::class)->newInstanceWithoutConstructor();
    }

    public function isDead(): bool
    {
        return false;
    }

    public function setTypeMap(array $typemap): void {}

    /**
     * @return array<int, array<string, string>>
     */
    public function toArray(): array
    {
        return $this->documents;
    }
}
