<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Tests\Infrastructure\Migration;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\VersionStorageInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Factory\MigrationDefinitionFactory;
use GeorgeBent\MongoDBMigrationsBundle\Application\Factory\MigrationPlanFactory;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\ExecutionDirection;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Migration\MongoDBMigrationRunner;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\MongoDB\DatabaseFactoryInterface;
use GeorgeBent\MongoDBMigrationsBundle\Migration\MigrationInterface;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;

final class MongoDBMigrationRunnerTest extends TestCase
{
    protected function tearDown(): void
    {
        RunnerUpMigrationFixture::$upCalls = 0;
        RunnerUpMigrationFixture::$downCalls = 0;
        RunnerUpMigrationFixture::$receivedDatabase = null;
    }

    public function testItRunsUpMigrationsAndMarksExecutedVersions(): void
    {
        $database = $this->createStub(Database::class);
        $databaseFactory = $this->databaseFactory($database);
        $versionStorage = $this->createMock(VersionStorageInterface::class);
        $configuration = $this->migrationConfiguration();
        $migrationVersion = new MigrationVersion('20260221000000');
        $migrationPlan = (new MigrationPlanFactory())->create(
            ExecutionDirection::Up,
            [new MigrationDefinitionFactory()->create($migrationVersion, RunnerUpMigrationFixture::class)],
        );

        $versionStorage->expects(self::once())
            ->method('markExecuted')
            ->with($configuration, $migrationVersion);

        $versionStorage->expects(self::never())->method('markRolledBack');

        $migrationExecutionResult = new MongoDBMigrationRunner($databaseFactory, $versionStorage)
            ->run($configuration, $migrationPlan);

        self::assertTrue($migrationExecutionResult->isSuccess());
        self::assertSame(1, RunnerUpMigrationFixture::$upCalls);
        self::assertSame($database, RunnerUpMigrationFixture::$receivedDatabase);
    }

    public function testItRunsDownMigrationsAndMarksRolledBackVersions(): void
    {
        $database = $this->createStub(Database::class);
        $databaseFactory = $this->databaseFactory($database);
        $versionStorage = $this->createMock(VersionStorageInterface::class);
        $configuration = $this->migrationConfiguration();
        $migrationVersion = new MigrationVersion('20260221000000');
        $migrationPlan = new MigrationPlanFactory()->create(
            ExecutionDirection::Down,
            [new MigrationDefinitionFactory()->create($migrationVersion, RunnerUpMigrationFixture::class)],
        );

        $versionStorage->expects(self::once())
            ->method('markRolledBack')
            ->with($configuration, $migrationVersion);

        $versionStorage->expects(self::never())->method('markExecuted');

        $migrationExecutionResult = new MongoDBMigrationRunner($databaseFactory, $versionStorage)
            ->run($configuration, $migrationPlan);

        self::assertTrue($migrationExecutionResult->isSuccess());
        self::assertSame(1, RunnerUpMigrationFixture::$downCalls);
    }

    public function testItReturnsErrorForInvalidMigrationClass(): void
    {
        $database = $this->createStub(Database::class);
        $databaseFactory = $this->databaseFactory($database);
        $versionStorage = $this->createMock(VersionStorageInterface::class);
        $configuration = $this->migrationConfiguration();
        $migrationVersion = new MigrationVersion('20260221000000');
        $migrationPlan = new MigrationPlanFactory()->create(
            ExecutionDirection::Up,
            [new MigrationDefinitionFactory()->create($migrationVersion, InvalidRunnerMigrationFixture::class)],
        );

        $versionStorage->expects(self::never())->method('markExecuted');
        $versionStorage->expects(self::never())->method('markRolledBack');

        $migrationExecutionResult = new MongoDBMigrationRunner($databaseFactory, $versionStorage)
            ->run($configuration, $migrationPlan);

        self::assertFalse($migrationExecutionResult->isSuccess());
        self::assertSame('migration_invalid_class', $migrationExecutionResult->error()?->identifier());
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

    private function databaseFactory(Database $database): DatabaseFactoryInterface
    {
        return new readonly class ($database) implements DatabaseFactoryInterface {
            public function __construct(private Database $database) {}

            public function create(MigrationConfiguration $configuration): Database
            {
                return $this->database;
            }
        };
    }
}

final class RunnerUpMigrationFixture implements MigrationInterface
{
    public static int $upCalls = 0;

    public static int $downCalls = 0;

    public static ?Database $receivedDatabase = null;

    public function up(Database $database): void
    {
        ++self::$upCalls;
        self::$receivedDatabase = $database;
    }

    public function down(Database $database): void
    {
        ++self::$downCalls;
        self::$receivedDatabase = $database;
    }
}

final class InvalidRunnerMigrationFixture {}
